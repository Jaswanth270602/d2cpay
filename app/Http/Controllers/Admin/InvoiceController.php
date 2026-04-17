<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use App\User;
use App\Invoice;
use App\Provider;
use App\Report;
use PDF;
use Mail;

class InvoiceController extends Controller
{

    //$start = new Carbon('first day of last month');
    //$last_month_start = $start->format('Y-m-d H:i:s');

    // $end = new Carbon('last day of last month');
    // $last_month_end = $end->format('M-Y');


    function gst_invoice (Request $request){
        if (Auth::User()->role_id <= 2){
            if ($request->fromdate && $request->todate) {
                $fromdate = $request->fromdate;
                $todate = $request->todate;
            }else{
                $fromdate = date('Y-m-d', time());
                $todate = date('Y-m-d', time());
            }
            $last_money = new Carbon('last day of last month');
            $data = array(
                'page_title' => 'GST Invoice',
                'fromdate' => $fromdate,
                'todate' => $todate,
                'create_name' => 'Create Invoice Of '.$last_money->format('M-Y'),
                );
            $end = new Carbon('last day of last month');
            $todate = $end->format('Y-m-d');
            $invoices = Invoice::whereDate('created_at', '=', $todate)->get();
            return view('admin.invoice.gst_invoice', compact('invoices'))->with($data);
        }else{
            return redirect()->back();
        }
    }

    function create_invoice (Request $request){
        if (Auth::User()->role_id <= 2){
            $start = new Carbon('first day of last month');
            $fromdate = $start->format('Y-m-d');

            $end = new Carbon('last day of last month');
            $todate = $end->format('Y-m-d');

            $invoice_month = new Carbon('last day of last month');
            $invoice_month = $invoice_month->format('M-Y');
            $users = User::where('gst_type', 1)->get();

            $provider_id = Provider::where('gst_type', 1)->get(['id']);
            foreach ($users as $value){
                $invoices = Invoice::where('user_id', $value->id)->where('invoice_month', $invoice_month)->first();
                if (empty($invoices)){
                     $total_transaction = Report::whereIn('provider_id', $provider_id)
                        ->where('status_id', 1)
                        ->where('user_id', $value->id)
                        ->whereDate('created_at', '>=', $fromdate)
                        ->whereDate('created_at', '<=', $todate)
                        ->sum('amount');

                    $commission = Report::whereIn('provider_id', $provider_id)
                        ->where('status_id', 1)
                        ->where('user_id', $value->id)
                        ->whereDate('created_at', '>=', $fromdate)
                        ->whereDate('created_at', '<=', $todate)
                        ->sum('profit');
                    $quantity_unit = $total_transaction - $commission;
                    $taxable_amount = $quantity_unit * 100/118;
                    if ($value->user_gst_type == 1){
                        $igst = ($taxable_amount * 18) / 100;
                        $cgst = 0;
                        $sgst = 0;
                    }else{
                        $igst = 0;
                        $cgst = ($taxable_amount * 9) / 100;
                        $sgst = ($taxable_amount * 9) / 100;
                    }


                 $insert_id = Invoice::insertGetId([
                        'user_id' => $value->id,
                        'particulars' => 'E TOP UP',
                        'sac' => '9984',
                        'quantity_unit' => $quantity_unit,
                        'rate' => '-',
                        'taxable_amount' => $taxable_amount,
                        'igst' => $igst,
                        'cgst' => $cgst,
                        'sgst' => $sgst,
                        'total_amount' => $quantity_unit,
                        'created_at' => $todate,
                        'invoice_month' => $invoice_month,
                        'status_id' => 1,
                    ]);
                 $invoice_id = 'SMS/2021-22/'.$insert_id;
                 Invoice::where('id', $insert_id)->update(['invoice_id' => $invoice_id]);

                }
            }
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);


        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function generate_invoice_old ($id){
        if (Auth::User()->role_id <= 2){
            $invoices = Invoice::find($id);
            if ($invoices){


                $user_id = $invoices->user_id;
                $userdetails = User::find($user_id);

                 $quantity_unit_word = $this->numberTowords($invoices->quantity_unit);
                $data = array(
                    'seller_name' => $userdetails->company->company_name,
                    'seller_address' => $userdetails->company->company_address,
                    'seller_pan_number' => $userdetails->company->pan_number,
                    'seller_gst_number' => $userdetails->company->gst_number,

                    'buyer_name' => $userdetails->member->shop_name,
                    'buyer_address' => $userdetails->member->office_address,
                    'buyer_pan_number' => $userdetails->member->pan_number,
                    'buyer_gst_number' => $userdetails->member->gst_number,

                    'invoice_id' => $invoices->invoice_id,
                    'quantity_unit' => number_format($invoices->quantity_unit, 2),
                    'taxable_amount' => number_format($invoices->taxable_amount, 2),
                    'igst' => number_format($invoices->igst, 2),
                    'cgst' => number_format($invoices->cgst, 2),
                    'sgst' => number_format($invoices->sgst, 2),
                    'quantity_unit_word' => $quantity_unit_word,
                );
               // $pdf = PDF::loadView('admin.invoice.generate_invoice', $data);
               // return $pdf->download('invoice.pdf');
               return view('admin.invoice.generate_invoice')->with($data);
            }else{
                return redirect()->back();
            }

        }else{
            return redirect()->back();
        }
    }

    function generate_invoice ($id){
        if (Auth::User()->role_id <= 2){
            $invoices = Invoice::find($id);
            if ($invoices){


                $user_id = $invoices->user_id;
                $userdetails = User::find($user_id);

                $quantity_unit_word = $this->numberTowords($invoices->quantity_unit);
                $data = array(
                    'seller_name' => $userdetails->company->company_name,
                    'seller_address' => $userdetails->company->company_address,
                    'seller_pan_number' => $userdetails->company->pan_number,
                    'seller_gst_number' => $userdetails->company->gst_number,

                    'buyer_name' => $userdetails->member->shop_name,
                    'buyer_address' => $userdetails->member->office_address,
                    'buyer_pan_number' => $userdetails->member->pan_number,
                    'buyer_gst_number' => $userdetails->member->gst_number,

                    'invoice_id' => $invoices->invoice_id,
                    'quantity_unit' => number_format($invoices->quantity_unit, 2),
                    'taxable_amount' => number_format($invoices->taxable_amount, 2),
                    'igst' => number_format($invoices->igst, 2),
                    'cgst' => number_format($invoices->cgst, 2),
                    'sgst' => number_format($invoices->sgst, 2),
                    'quantity_unit_word' => $quantity_unit_word,
                );
                $data["email"] = "neeraj@pay2all.in";
                $data["title"] = "Smart Money";
                $data["body"] = "This is Demo";
                $pdf = PDF::loadView('admin.invoice.generate_invoice', $data);
                 Mail::send('mail.test_mail', $data, function($message)use($data, $pdf) {
                    $message->to($data["email"], $data["email"])
                        ->subject($data["title"])
                        ->attachData($pdf->output(), "text.pdf");
                });

                // return $pdf->download('invoice.pdf');
                return view('admin.invoice.generate_invoice')->with($data);
            }else{
                return redirect()->back();
            }

        }else{
            return redirect()->back();
        }
    }

    function numberTowords (float $amount){
        $amount_after_decimal = round($amount - ($num = floor($amount)), 2) * 100;
        // Check if there is any number after decimal
        $amt_hundred = null;
        $count_length = strlen($num);
        $x = 0;
        $string = array();
        $change_words = array(0 => '', 1 => 'One', 2 => 'Two',
            3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
            7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
            13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
            19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
            40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
            70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety');
        $here_digits = array('', 'Hundred','Thousand','Lakh', 'Crore');
        while( $x < $count_length ) {
            $get_divider = ($x == 2) ? 10 : 100;
            $amount = floor($num % $get_divider);
            $num = floor($num / $get_divider);
            $x += $get_divider == 10 ? 1 : 2;
            if ($amount) {
                $add_plural = (($counter = count($string)) && $amount > 9) ? 's' : null;
                $amt_hundred = ($counter == 1 && $string[0]) ? ' and ' : null;
                $string [] = ($amount < 21) ? $change_words[$amount].' '. $here_digits[$counter]. $add_plural.' 
         '.$amt_hundred:$change_words[floor($amount / 10) * 10].' '.$change_words[$amount % 10]. ' 
         '.$here_digits[$counter].$add_plural.' '.$amt_hundred;
            }else $string[] = null;
        }
        $implode_to_Rupees = implode('', array_reverse($string));
        $get_paise = ($amount_after_decimal > 0) ? "And " . ($change_words[$amount_after_decimal / 10] . " 
   " . $change_words[$amount_after_decimal % 10]) . ' Paise' : '';
        return ($implode_to_Rupees ? $implode_to_Rupees . 'Rupees ' : '') . $get_paise;
    }
}
