<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $company_name }}</title>
    <style>
        @media print {
            #inv {
                width: 99% !important;
                margin: 0px !important;

            }

            .logsec {
                padding: 2px 20px !important;
            }

            .sd {
                margin-top: 0px !important;
            }

            .st {
                margin: 0px !important;
            }

            .btnss {
                display: none !important;
            }

            .spac {
                padding: 4px 8px !important;
            }

            #logo {
                width: 18% !important;
            }

            #invh h1 {
                font-size: 18px !important;
            }

            #invh p {
                font-size: 12px !important;
            }

            #width {
                width: 32.5% !important;
            }

            #width h3 {
                padding: 5px 10px !important;
                font-size: 16px !important;
            }

            #ptag {
                font-size: 12px !important;
                margin: 0px !important;
                margin-bottom: 2px !important;
            }

            #font {
                font-size: 18px !important;
            }
        }
    </style>
</head>

<body style="margin: 0px; padding: 0px; box-sizing: border-box;">
<div style="border: 1px solid #333; margin: 50px auto; width: 80%;" id="inv">
    <div style=" display:flex;
                        justify-content:space-between;
                        align-items: center;
                        padding: 10px 20px;
                        border-bottom: 1px solid #333;" class="logsec">
        <div style="width: 33%;text-align: left;">
            <img src="{{$cdnLink}}{{$company_logo}}" alt="" style="width: 24%;" id="logo">
        </div>
        <div style="width: 33%;text-align: center;" id="invh">
            <h1 style="margin: 0px; padding: 0px 20px;">Invoice</h1>
            <p style="margin: 0px; padding: 0px 20px; margin-bottom: 2px;">{{$created_at}}</p>
        </div>
        <div style="width: 33%;text-align: right;">
            <a href="" style="color:#333; display: block; text-decoration: none;">{{url('')}}</a>
            <a href="" style="color:#333; display: block; text-decoration: none;">{{ $company_email }}</a>
        </div>
    </div>


    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 10px 20px;margin-top: 5px;"
         class="sd spac">
        <div style="border:1px solid #ddd; border-radius:4px; width: 31%;" id="width">
            <h3 style="padding: 8px 10px; background-color: #808080; color: #fff; text-transform: capitalize; margin: 0px;"
                class="spac">
                agent details</h3>
            <div style="padding:20px; text-transform: capitalize;" class="spac">
                <p style="margin: 0px; margin-bottom: 8px; white-space: nowrap;" id="ptag"><b>Shop name : </b>{{$agent_name}}</p>
                <p style="margin: 0px; margin-bottom: 8px;" id="ptag"><b>Mobile No : </b>{{$agent_number}}</p>
                <p style="margin: 0px; margin-bottom: 8px;" id="ptag"><b>agent address : </b>{{ $office_address }}</p>

            </div>
        </div>
        <div style="border:1px solid #ddd; border-radius:4px; width: 31%;" id="width">
            <h3 style="padding: 8px 10px; background-color: #808080; color: #fff; text-transform: capitalize; margin: 0px;"
                class="spac">
                customer details</h3>
            <div style="padding:20px; text-transform: capitalize;" class="spac">
                <p style="margin: 0px; margin-bottom: 8px;" id="ptag"><b>Remitter name : </b>{{$remiter_name}}</p>
                <p style="margin: 0px; margin-bottom: 8px;" id="ptag"><b>mode : </b>{{$channel}}</p>
                <p style="margin: 0px; margin-bottom: 8px;" id="ptag"><b>remitter number : </b>{{$remiter_number}}</p>

            </div>
        </div>
        <div style="border:1px solid #ddd; border-radius:4px; width: 31%;" id="width">
            <h3 style="padding: 8px 10px; background-color: #808080; color: #fff; text-transform: capitalize; margin: 0px;"
                class="spac">
                beneficiary details</h3>
            <div style="padding:20px; text-transform: capitalize;" class="spac">
                <p style="margin: 0px; margin-bottom: 8px;" id="ptag"><b>bank name : </b>{{ $bank_name }}</p>
                <p style="margin: 0px; margin-bottom: 8px;" id="ptag"><b>name : </b>{{ $beneficiary_name }}</p>
                <p style="margin: 0px; margin-bottom: 8px;" id="ptag"><b>account number : </b>{{ $account_number }}</p>

            </div>
        </div>
    </div>
    <div style="padding: 0px 20px;">
        <table style="width: 100%; border: 1px solid #ddd; border-collapse: collapse;">
            <tr>
                <th style="background-color: #333;padding: 10px; text-align: center; color: #fff; text-transform: capitalize;width: 20%;" class="spac">order id</th>
                <th style="background-color: #333;padding: 10px; text-align: center; color: #fff; text-transform: capitalize; width: 20%;" class="spac">UTR number</th>
                <th style="background-color: #333;padding: 10px; text-align: center; color: #fff; text-transform: capitalize; width: 20%;" class="spac">amount</th>
                <th style="background-color: #333;padding: 10px; text-align: center; color: #fff; text-transform: capitalize; width: 20%;" class="spac">charge</th>
                <th style="background-color: #333;padding: 10px; text-align: center; color: #fff; text-transform: capitalize; width: 20%;" class="spac">status</th>
            </tr>
            @foreach($reports as $value)
                <tr style="border-bottom: 1px solid #ddd;">
                    <td style="padding: 15px 10px; text-align: center; color: #333; text-transform: capitalize; width: 20%; border-bottom: 1px solid #ddd;" class="spac">{{ $value->id }}</td>
                    <td style="padding: 15px 10px; text-align: center; color: #333; text-transform: capitalize; width: 20%; border-bottom: 1px solid #ddd;" class="spac">{{ $value->txnid }}</td>
                    <td style="padding: 15px 10px; text-align: center; color: #333; text-transform: capitalize; width: 20%; border-bottom: 1px solid #ddd;" class="spac">{{ number_format($value->amount, 2) }}</td>
                    <td style="padding: 15px 10px; text-align: center; color: #333; text-transform: capitalize; width: 20%; border-bottom: 1px solid #ddd;" class="spac">1%</td>
                    <td style="padding: 15px 10px; text-align: center; color: #333; text-transform: capitalize; width: 20%; border-bottom: 1px solid #ddd;" class="spac">
                        <span style="padding:5px 10px; background-color:rgb(122, 122, 122); color:#fff; border-radius:8px;">{{ $value->status->status }}</span>
                    </td>

                </tr>
            @endforeach

        </table>
    </div>
    <div style="display: flex; justify-content: space-between; padding: 0px 20px; margin-top: 0px; margin-bottom: 0px; align-items: flex-start;"
         class="st">
        <div style="width: 60%; padding: 10px; border: 1px solid #ddd; margin-bottom: 10px; margin-top: 10px;"
             class="spac">
            <p style="margin: 0px;" id="ptag"><b>Notes: </b>This is a Computer Generated Receipt. Signature Not
                Required.
                <br>website {{ url('') }}
            </p>
        </div>
        <div style="border: 1px solid #ddd; width: 33%; margin-bottom: 10px; margin-top: 10px;" id="width">
            <h3 style="padding: 6px 10px; background-color: #808080; color: #fff; text-transform: capitalize; margin: 0px; text-align: center;" class="spac">Total Amount</h3>
            <h2 style="text-align: center; padding: 10px; margin: 0px;" class="spac" id="font">
                Rs. {{ $full_amount }}/-
            </h2>
        </div>
    </div>

    <div style="text-align: center; margin-bottom: 20px;" class="btnss">
        <button style="text-align: center;" onclick="window.print();">
            Print
        </button>
    </div>

</div>

</body>

</html>