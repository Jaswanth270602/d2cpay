@extends('agent.layout.header')
@section('content')



    <div class="main-content-body">
        <div class="row row-sm">

            @include('agent.developer.left_side')

            <div class="col-lg-8 col-xl-9">


                <div class="card" id="basic-alert">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Get Provider</h6>
                        </div>
                        <hr>

                        <table class="table main-table-reference mt-0 mb-0">
                            <thead>
                            <tr>
                                <th class="wd-40p">ATTRIBUTE</th>
                                <th class="wd-60p">DESCRIPTIONS</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>api_token</td>
                                <td>Api token provider by {{ $company_website }} OR <a href="{{url('agent/developer/settings')}}">Click Here</a> </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                            <pre>POST: {{url('api/application/v1/get-provider')}}</pre>
                        <hr>
                        <div class="alert alert-danger mg-b-0" role="alert">
                            <strong>Alert! </strong> Provider id also available in left side menu (provider list)
                        </div>
                    </div>
                </div>


                <div class="card" id="basic-alert">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Payment</h6>
                        </div>
                        <hr>

                        <table class="table main-table-reference mt-0 mb-0">
                            <thead>
                            <tr>
                                <th class="wd-40p">ATTRIBUTE</th>
                                <th class="wd-60p">DESCRIPTIONS</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>api_token</td>
                                <td>Api token provider by {{ $company_website }} OR <a href="{{url('agent/developer/settings')}}">Click Here</a> </td>
                            </tr>

                            <tr>
                                <td>number</td>
                                <td>Customer Number</td>
                            </tr>

                            <tr>
                                <td>amount</td>
                                <td>Amount For Recharge</td>
                            </tr>

                            <tr>
                                <td>provider_id</td>
                                <td>Provider id</td>
                            </tr>

                            <tr>
                                <td>client_id</td>
                                <td>Your Side Uniq Id</td>
                            </tr>

                            <tr>
                                <td>optional1</td>
                                <td>Optional Value</td>
                            </tr>

                            <tr>
                                <td>optional2</td>
                                <td>Optional Value</td>
                            </tr>

                            <tr>
                                <td>optional3</td>
                                <td>Optional Value</td>
                            </tr>

                            <tr>
                                <td>optional4</td>
                                <td>Optional Value</td>
                            </tr>

                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <pre>GET: {{url('api/telecom/v1/payment')}}?api_token=[api_token]&number={{Auth::User()->mobile}}&amount=10&provider_id=1&client_id=12345</pre>
                        <hr>
                        <pre style="color: #0ba360;">Success Response : {"status":"success","message":"Dear {{Auth::User()->name}}, Recharge Success Number: {{ Auth::User()->mobile }} Operator: AIRTEL And Amount: 149, Your Remaining Balance is 17935.126 Thanks","operator_ref":"1246746281","payid":21}</pre>
                        <pre style="color: #f53c5b;">Failure Response : {"status":"failure","message":"Dear {{Auth::User()->name}}, Recharge Failure Number: {{ Auth::User()->mobile }} Operator: AIRTEL And Amount: 149, Your Remaining Balance is 17935.126 Thanks","operator_ref":"","payid":21}</pre>
                        <pre style="color: #ffc107;">Pending Response : {"status":"pending","message":"Dear {{Auth::User()->name}}, Recharge Success Number: {{ Auth::User()->mobile }} Operator: AIRTEL And Amount: 149, Your Remaining Balance is 17935.126 Thanks","operator_ref":"","payid":21}</pre>
                        <hr>
                        <div class="alert alert-danger mg-b-0" role="alert">
                          if status is pending  then we will send the actual status & operator ref id in the call back & you need to update the call back from the setting .
                        </div>
                    </div>
                </div>



                <div class="card" id="basic-alert">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Check Balance</h6>
                        </div>
                        <hr>

                        <table class="table main-table-reference mt-0 mb-0">
                            <thead>
                            <tr>
                                <th class="wd-40p">ATTRIBUTE</th>
                                <th class="wd-60p">DESCRIPTIONS</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>api_token</td>
                                <td>Api token provider by {{ $company_website }} OR <a href="{{url('agent/developer/settings')}}">Click Here</a> </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <pre>GET: {{url('api/telecom/v1/check-balance')}}?api_token=[api_token]</pre>
                        <hr>
                        <pre>Response : {"status":"success","balance":{"normal_balance":"2,999.1"}}</pre>
                    </div>
                </div>


                <div class="card" id="basic-alert">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Check Status</h6>
                        </div>
                        <hr>

                        <table class="table main-table-reference mt-0 mb-0">
                            <thead>
                            <tr>
                                <th class="wd-40p">ATTRIBUTE</th>
                                <th class="wd-60p">DESCRIPTIONS</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>api_token</td>
                                <td>Api token provider by {{ $company_website }} OR <a href="{{url('agent/developer/settings')}}">Click Here</a> </td>
                            </tr>

                            <tr>
                                <td>client_id</td>
                                <td>your uniq id</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <pre>GET: {{url('api/telecom/v1/check-status')}}?api_token=[api_token]&client_id=[client_id]</pre>
                        <hr>
                        <pre style="color: #0ba360;">Success Response : {"status":"success","message":"success","transaction":{"id":12345,"provider":"VODAFONE","date":"2020-12-24 10:40:07","number":"{{Auth::User()->mobile}}","amount":"10.00","profit":"0.00","txnid":"","client_id":"12345","ip_address":"","status":"Success"}}</pre>
                        <pre style="color: #f53c5b;">Failure Response : {"status":"success","message":"success","transaction":{"id":12345,"provider":"VODAFONE","date":"2020-12-24 10:40:07","number":"{{Auth::User()->mobile}}","amount":"10.00","profit":"0.00","txnid":"","client_id":"12345","ip_address":"","status":"Failure"}}</pre>
                        <pre style="color: #ffc107;">Pending Response : {"status":"success","message":"success","transaction":{"id":12345,"provider":"VODAFONE","date":"2020-12-24 10:40:07","number":"{{Auth::User()->mobile}}","amount":"10.00","profit":"0.00","txnid":"","client_id":"12345","ip_address":"","status":"Pending"}}</pre>

                    </div>
                </div>

            </div>
            <!--/div-->

        </div>

    </div>
    </div>
    </div>



@endsection