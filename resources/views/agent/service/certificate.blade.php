
<center>
    <style>
        .signature, .title {
            float:left;
            border-top: 1px solid #000;
            width: 200px;
            text-align: center;
        }
    </style>

    <style type="text/css" media="print">
        @page {
            size: auto;   /* auto is the initial value */
            margin: 0;  /* this affects the margin in the printer settings */
        }
    </style>

    <script>
        window.print();
    </script>



    <div style="width:800px; height:600px; padding:20px; text-align:center; border: 10px solid #787878">
        <div style="width:750px; height:550px; padding:20px; text-align:center; border: 5px solid #787878">
            <div style="width:100%">
                <img src="{{$cdnLink}}{{$company_logo}}" style="height:80px;display:block;margin:auto" >
            </div>

            <span style="font-size:50px; font-weight:bold">Certificate of Authorization</span>
            <br><br>
            <span style="font-size:25px"><i>This is to certify that</i></span>
            <br><br>
            <span style="font-size:30px"><b>{{ Auth::User()->member->shop_name }}</b></span><br/><br/>
            <span style="font-size:25px"><i>User Id  : {{Auth::id()}} </i></span> <br/><br/>
            <span style="font-size:25px"><i>has been appointed as authorised merchent </i></span> <br/><br/>
            <!--<span style="font-size:30px">$course.getName()</span> <br/><br/>-->
            <!--<span style="font-size:20px">with score of <b>$grade.getPoints()%</b></span> <br/><br/><br/><br/>-->
            <span style="font-size:25px"><i>Issued Date</i></span><br>
            <span style="font-size:25px"><i>{{ Auth::User()->created_at }}</i></span><br>




            <table style="margin-top:40px;float:left">
                <tr>
                    <td style="text-align:center"><span><b>{{ $brand_name }}</b></td>
                </tr>
                <tr>
                    <td style="width:200px;float:left;border:0;border-bottom:1px solid #000;"></td>
                </tr>
                <tr>
                    <td style="text-align:center"><span><b>Certificate Issuesed By</b></td>
                </tr>
            </table>
            <table style="margin-top:40px;float:right">
                <tr>
                    <td style="text-align:center"><span><b>{{ Auth::User()->name }} {{ Auth::User()->last_name }}</b></td>
                </tr>
                <tr>
                    <td style="width:200px;float:right;border:0;border-bottom:1px solid #000;"></td>
                </tr>
                <tr>
                    <td style="text-align:center"><span><b>Certificate Issuesed For</b></td>
                </tr>
            </table>
        </div>
    </div>
</center>



