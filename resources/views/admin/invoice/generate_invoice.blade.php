
<html>
<head>
    <title>GST Invoice</title>
    <style type="text/css">
        #page-wrap {
            width: 1000px;
            margin: 0 auto;
            border: solid 1px;
        }
        .center-justified {
            text-align: justify;
            margin: 0 auto;
            width: 30em;
        }
        table{
            border: 1px solid;
        }
        table.outline-table {
            border: 1px solid;
            border-spacing: 0;
        }
        tr.border-bottom td, td.border-bottom {
            border-bottom: 1px solid;
        }
        tr.border-top td, td.border-top {
            border-top: 1px solid;
        }
        tr.border-right td, td.border-right {
            border-right: 1px solid;
        }
        tr.border-right td:last-child {
            border-right: 0px;
        }
        tr.center td, td.center {
            text-align: center;
            vertical-align: text-top;
        }
        td.pad-left {
            padding-left: 5px;
        }
        tr.right-center td, td.right-center {
            text-align: right;
            padding-right: 50px;
        }
        tr.right td, td.right {
            text-align: right;
        }
        .grey {
            background:grey;
        }
        .white{
            color:white;
        }
        .space{
            padding: 10px;
        }
    </style>
</head>
<body>
<div id="page-wrap">
    <table width="100%">
        <tbody>
        <tr>
            <td width="10%">
                <img src="{{ $company_logo }}" style="width: 80px;"> <!-- your logo here -->
            </td>
            <td class="right" width="33%">
                <h2>GST Invoice</h2>
            </td>
            <td class="right" width="33%">

            </td>
        </tr>
        </tbody>
    </table>
    <table width="100%" class="outline-table">
        <tbody>
        <tr class="border-bottom border-right center">
            <td><strong>Date: 22/12/2021</strong></td>
            <td><strong>Invoice : {{ $invoice_id }}</strong></td>
        </tr>

        <tr class="border-bottom border-right center">
            <td width="50%"><strong>Seller</strong></td>
            <td width="50%"><strong>Buyer</strong>
            </td>
        </tr>
        <tr class="border-right">
            <td class="space pad-left">
                <p><strong>{{ $seller_name }}</strong></p>
                <p>{{ $seller_address }}</p>
                <p><strong>PAN:</strong> {{ $seller_pan_number }}</p>
                <p><strong>GSTIN:</strong> {{ $seller_gst_number }}</p>
            </td>
            <td class="space pad-left">
                <p><strong>{{ $buyer_name }}</strong></p>
                <p>{{ $buyer_address }}</p>
                <p><strong>PAN:</strong> {{ $buyer_pan_number }}</p>
                <p><strong>GSTIN:</strong> {{ $buyer_gst_number }}</p>
            </td>
        </tr>
        </tbody>
    </table>
    <table width="100%" class="outline-table">
        <tbody>

        <tr class="border-bottom border-right">
            <td rowspan="2"><strong>Sr No.</strong></td>
            <td width="100px" rowspan="2"><strong>Particulars</strong></td>
            <td rowspan="2"><strong>SAC</strong></td>
            <td rowspan="2"><strong>Quantity / Unit</strong></td>
            <td rowspan="2"><strong><center> Rate </center></strong></td>
            <td rowspan="2"><strong><center>Taxable Amount</center></strong></td>
            <td class="center" colspan="2"><strong>CGST 9%</strong></td>
            <td class="center" colspan="2"><strong>SGST 9%</strong></td>
            <td class="center" colspan="2"><strong>IGST 18%</strong></td>
        </tr>
        <tr class="border-bottom border-right center">
            <td>Amount</td>
            <td></td>
            <td>Amount</td>
            <td></td>
            <td>Amount</td>
            <td></td>
        </tr>
        <tr class="border-right center">
            <td>01</td>
            <td>E-Topup</td>
            <td>9984</td>
            <td>{{ $quantity_unit }}</td>
            <td>-</td>
            <td>{{ $taxable_amount }}</td>
            <td>{{ $cgst }}</td>
            <td></td>
            <td>{{ $sgst }}</td>
            <td></td>
            <td>{{ $igst }}</td>
            <td></td>
        </tr>



        <tr class="border-top border-right center">
            <td style="text-align:right" colspan="4"><strong>Total</strong></td>
            <td></td>
            <td><strong>{{ $taxable_amount }}</strong></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>{{ $igst }}</td>
            <td></td>
        </tr>
      {{--  <tr class="border-top border-right center">
            <td style="text-align:right" colspan="4"><strong>Amount Payable</strong></td>
            <td style="text-align:center;" colspan="8"><strong>763000/-</strong></td>
        </tr>--}}
        <tr class="border-top border-right center">
            <td style="text-align:right" colspan="4"><strong>Amount Chargeable (in Words)</strong></td>
            <td style="text-align:center;" colspan="8"><strong>{{ $quantity_unit_word }}</strong></td>
        </tr>
        </tbody>
    </table>
    <table class="space">
        <tbody>
        <tr>
            <td>
                <strong>Declaration:</strong> We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.You are required to check the above invoice of your account and revert to us if you encounter any discrepancies
            </td>
        </tr>
        </tbody>
    </table>
</div>
</body>
</html>