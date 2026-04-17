<html>
<title>{{ $company_name }}</title>
<head>

</head>
<body>

<div id="invoice-POS">

    <center id="top">
        <div class="info">
            <img src="{{$cdnLink}}{{$company_logo}}" style="height: 40px;">
            <h3>Transaction Receipt</h3>
            <span>(Payment Through Cash Collection)</span>
        </div><!--End Info-->
    </center><!--End InvoiceTop-->
    <hr>


    <div id="bot">

        <div id="table">
            <table border="1" style="width:100%;  border: 1px solid black; border-collapse: collapse;">
                <tr>
                    <th>Reference No</th>
                    <td>{{$report_id}}</td>
                </tr>

                <tr>
                    <th>Date & Time</th>
                    <td>{{$created_at}}</td>
                </tr>
                <tr>
                    <th>Provider Name</th>
                    <td>{{$provider_name}}</td>
                </tr>

                <tr>
                    <th>Number</th>
                    <td>{{$number}}</td>
                </tr>

                <tr>
                    <th>Txnid</th>
                    <td>{{$txnid}}</td>
                </tr>

                <tr>
                    <th>Amount</th>
                    <td>{{$amount}}</td>
                </tr>

                <tr>
                    <th>Status</th>
                    <td>{{$status}}</td>
                </tr>
                
            </table>
        </div><!--End Table-->
        <center id="top">
            <div class="info">
               <h4>Shop Name : {{ $agent_name }}</h4>
               <h4>Agent Number : {{ $agent_number }}</h4>
            </div><!--End Info-->
        </center><!--End InvoiceTop-->

    </div><!--End InvoiceBot-->
</div><!--End Invoice-->

<div style="text-align: center; margin-top: 2%;">
    <button id="printPageButton" onClick="window.print();">Print</button>
</div>

<style>

    @media print {
        #printPageButton {
            display: none;
        }
    }

    #invoice-POS{
        box-shadow: 0 0 1in -0.25in rgba(0, 0, 0, 0.5);
        padding:2mm;
        margin: 0 auto;
        width: 77mm;
        border: dotted;
        background: #FFF;




    ::selection {background: #f31544; color: #FFF;}
    ::moz-selection {background: #f31544; color: #FFF;}
    h1{
        font-size: 1.5em;
        color: #222;
    }
    h2{font-size: .9em;}
    h3{
        font-size: 1.2em;
        font-weight: 300;
        line-height: 2em;
    }
    p{
        font-size: .7em;
        color: #666;
        line-height: 1.2em;
    }

    #top, #mid,#bot{ /* Targets all id with 'col-' */
        border-bottom: 1px solid #EEE;
    }

    #top{min-height: 100px;}
    #mid{min-height: 80px;}
    #bot{ min-height: 50px;}

    #top .logo{
    //float: left;
        height: 60px;
        width: 60px;
        background: url(http://michaeltruong.ca/images/logo1.png) no-repeat;
        background-size: 60px 60px;
    }
    .clientlogo{
        float: left;
        height: 60px;
        width: 60px;
        background: url(http://michaeltruong.ca/images/client.jpg) no-repeat;
        background-size: 60px 60px;
        border-radius: 50px;
    }
    .info{
        display: block;
    //float:left;
        margin-left: 0;
    }
    .title{
        float: right;
    }
    .title p{text-align: right;}

    table, th, td {
        border: 1px solid black;
        border-collapse: collapse;
    }

    .tabletitle{
    //padding: 5px;
        font-size: .5em;
        background: #EEE;
    }
    .service{border-bottom: 1px solid #EEE;}
    .item{width: 24mm;}
    .itemtext{font-size: .5em;}

    #legalcopy{
        margin-top: 5mm;
    }



    }
</style>
</body>
</html>