<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="custom.css">
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>body{
            background-color:#EFF8FF;
        }
        h1, p{
            margin:0px;
        }
        .main-section{
            background-color: #FFF;
            border: 1px solid #8D43AC;
        }
        .header{
            background-color: #8D43AC;
            padding:30px 15px 20px 15px ;
            color:#fff;
        }
        .content{
            padding:20px 15px 20px 15px;
        }
        th{
            background-color: #8D43AC;
            color: #fff;
            text-align: right;
        }
        .table td:nth-child(1),
        .table th:nth-child(1){
            text-align:left;
        }
        .lastSection{
            padding: 20px 15px 30px 15px;
        }</style>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="row main-section">
                <div class="col-md-12 col-sm-12 header">
                    <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-6">
                            <h1><img src="{{ $company_logo }}" style="height: 70px;"></h1>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                            <p>Invoice : SMA/2101/01</p>
                            <span>Date : 31-Jan-2021</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-sm-12 content">
                    <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-6">
                            <p>Seller</p>
                            <strong>SMART MONEY SOLUTION</strong>
                            <p>pune,</p>
                            <p>pune</p>
                            <p>MAHARASHTRA - 41001</p>
                            <p>PAN : CNLPK6259H</p>
                            <p>GSTIN : 27CNLPK6259H1ZI</p>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                            <p>Buyer</p>
                            <strong>M/s.STAR COMMUNICATIONS</strong>
                            <p># 14/7, Kuppanda Gounder Street,
                                Othakkalmandapam Post, Okkilipalayam,
                                Coimbatore, </p>
                            <p>Tamil Nadu - 641032</p>
                            <p>PAN : AOAPB0124L</p>
                            <p>GSTIN. : 33AOAPB0124L1ZO , TAN : CMBM08537E</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-sm-12 text-right">
                    <table class="table" border="1">

                        <tr>
                            <th>S. No.</th>
                            <th>Particulars</th>
                            <th>SAC.</th>
                            <th>Quantity / Unit</th>
                            <th>Rate</th>
                            <th>Taxable Amount</th>
                            <th>IGST 18%</th>
                            <th>Total Amount</th>

                        </tr>

                        <tbody>
                        <tr>
                            <td>1</td>
                            <td>E TOP UP</td>
                            <td>9984</td>
                            <td>2,700,000.00</td>
                            <td>-</td>
                            <td>2,288,135.59</td>
                            <td>411,864.41</td>
                            <td>2,700,000.00</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td colspan="2" style="text-align: right;">Total:</td>
                            <td>2,700,000.00</td>
                            <td>-</td>
                            <td>2,288,135.59</td>
                            <td>411,864.41</td>
                            <td>2,700,000.00</td>
                        </tr>
                        <tr>
                            <th colspan="3">Amount Chargeable (in words)</th>
                            <th colspan="5">Thirteen Thousand Four Hundred Seventeen and Ninety Eight paise Only</th>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-12 col-sm-12 lastSection">
                        <div class="row">

                            <div class="col-md-4" style="float: right">
                                <strong>{{ $company_name }}</strong> <br>
                                <img src="https://media.istockphoto.com/vectors/approved-stamp-vector-id864414216?k=6&m=864414216&s=612x612&w=0&h=JdiTkiirdWov4fnl-mjD1PWyS7ERijWhiLABMGmsMWs=" style="width: 100px;">
                                <p>Authorised Signatory</p>
                            </div>
                        </div>
                    <p>This is a Computer Generated Invoice</p>
                </div>


            </div>
        </div>
    </div>
</div>
</body>
</html>