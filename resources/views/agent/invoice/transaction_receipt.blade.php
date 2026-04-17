<!DOCTYPE html>
<html>
<title>{{ $company_name }}</title>
<head>
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <style>
        body{
            background-color: #F6F6F6;
        }
        .brandSection{
            background-color: #fff;
            border:1px solid #417482;
        }
        .headerLeft h1{
            color:#fff;
            margin: 0px;
            font-size:28px;
        }
        .header{
            border-bottom: 2px solid #417482;
            padding: 10px;
        }
        .headerRight p{
            margin: 0px;
            font-size:10px;
            color:#000;
            text-align: right;
        }
        .contentSection{
            background-color: #fff;
            padding: 0px;
        }
        .content{
            background-color: #fff;
            padding:20px;
        }
        .content h1{
            font-size:22px;
            margin:0px;
        }
        .content p{
            margin: 0px;
            font-size: 11px;
        }
        .content span{
            font-size: 11px;
            color:#F2635F;
        }
        .panelPart{
            background-color: #fff;
        }
        .panel-body{
            background-color: #3BA4C2;
            color:#fff;
            padding: 5px;
        }
        .panel-footer {
            background-color:#fff;
        }
        .panel-footer h1{
            font-size: 20px;
            padding:15px;
            border:1px dotted #DDDDDD;
        }
        .panel-footer p{
            font-size:13px;
            /*background-color: #F6F6F6;*/

        }
        .tableSection{
            background-color: #fff;
        }
        .tableSection h1{
            font-size:18px;
            margin:0px;
        }
        th{
            background-color: #383C3D;
            color:#fff;
            text-align: center;
        }
        .table{
            padding-bottom: 10px;
            margin:0px;
            border:1px solid #DDDDDD;
        }

        td {
            height: 100%;
            text-align: center;
        }
        .bg {
            background-color: #f00;
            width: 100%;
            height: 100%;
            display: block;
        }
        .lastSectionleft{
            background-color: #fff;
            padding-top:20px;
        }
        .Sectionleft p{
            border:1px solid #DDDDDD;
            height:auto;
            padding: 5px;
        }
        .Sectionleft span{
            color:#42A5C5;
        }
        .lastPanel{
            text-align:center;
        }
        .panelLastLeft p,.panelLastRight p{
            font-size:11px;
            padding:5px 2px 5px 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2 col-sm-8 col-sm-offset-2 col-xs-12 brandSection">
            <div class="row">
                <div class="col-md-12 col-sm-12 header">
                    <div class="col-md-3 col-sm-3 headerLeft">
                        <img src="{{$cdnLink}}{{$company_logo}}" style="height: 50px;">
                    </div>
                    <div class="col-md-9 col-sm-9 headerRight">
                        <p>{{url('')}}</p>
                        <p> {{ $company_email }}</p>
                        <p>+91 {{ $support_number }}</p>
                    </div>
                </div>
                <div class="col-md-12 col-sm-12 content">
                    <h1>Invoice<strong> {{ $report_id }}</strong></h1>
                    <p>{{ $created_at }}</p>

                </div>
                <div class="col-md-12 col-sm-12 panelPart">
                    <div class="row">
                        <div class="col-md-4 col-sm-6 panelPart">
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    Agent Details
                                </div>
                                <div class="panel-footer">
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                            <p>Shop Name: {{$agent_name}}</p>
                                            <p>Mobile No: {{$agent_number}}</p>
                                            <p>Agent Address: {{$office_address}}</p>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 col-sm-6 tableSection">

                            <table class="table text-center">
                                <thead>
                                <tr class="tableHead">
                                    <th>Order Id</th>
                                    <th>Oprator Name</th>
                                    <th>TXN ID</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>{{$report_id}}</td>
                                    <td>{{$provider_name}}</td>
                                    <td>{{$txnid}}</td>
                                    <td>{{$status}}</td>
                                    <td>{{$amount}}</td>
                                </tr>


                                </tbody>
                                <tbody>
                                <tr>
                                    <td><b>Number.</b></td>
                                    <td>{{ $number }}</td>
                                    <td></td>
                                    <td><b>TOTAL</b></td>

                                    <td><b>{{$amount}}</b>  </td>
                                </tr>


                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-8 col-sm-6 Sectionleft">
                            <p><b>Notes:</b><i> This is a Computer Generated Receipt. Signature Not Required. website {{ url('') }}</i></p>
                            <span><i></i> </span>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>
</div>
</body>
</html>