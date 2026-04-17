@extends('agent.layout.header')
@section('content')



    <div class="main-content-body">
        <div class="row row-sm">

            @include('agent.developer.left_side')

            <div class="col-lg-8 col-xl-9">


                <div class="card" id="basic-alert">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Bank List</h6>
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
                        <pre>POST: {{url('api/dmt/v2/bank-list')}}</pre>
                        <hr>
                        <pre>Response : {"status":"success","bank_list":[{"bank_id":"1","bank_name":"AXIS BANK","ifsc_code":"UTIB0000073"},{"bank_id":"2","bank_name":"BANK OF BARODA (BOB)","ifsc_code":"BARB0SAFDAR"},{"bank_id":"3","bank_name":"BANK OF INDIA (BOI)","ifsc_code":"BKID0007109"},{"bank_id":"4","bank_name":"CENTRAL BANK OF INDIA (CBI)","ifsc_code":"CBIN0011102"},{"bank_id":"5","bank_name":"CITIBANK NA","ifsc_code":""},{"bank_id":"6","bank_name":"HDFC BANK LTD","ifsc_code":"HDFC0000001"},{"bank_id":"7","bank_name":"ICICI BANK LTD","ifsc_code":"ICIC0000001"},{"bank_id":"8","bank_name":"IDBI BANK LTD","ifsc_code":"IBKL0000001"},{"bank_id":"9","bank_name":"INDIAN BANK (IB)","ifsc_code":"IDIB000S152"},{"bank_id":"10","bank_name":"INDIAN OVERSEAS BANK (IOB)","ifsc_code":"IOBA0001719"},{"bank_id":"11","bank_name":"PUNJAB NATIONAL BANK (PNB)","ifsc_code":"PUNB0012000"},{"bank_id":"12","bank_name":"STATE BANK OF BIKANER AND JAIPUR (SBBJ)","ifsc_code":"SBBJ0010811"},{"bank_id":"13","bank_name":"UNION BANK OF INDIA (UBI)","ifsc_code":"UBIN0564273"},{"bank_id":"14","bank_name":"UCO BANK","ifsc_code":"UCBA0002922"},{"bank_id":"15","bank_name":"YES BANK","ifsc_code":"YESB0000002"},{"bank_id":"16","bank_name":"DENA BANK","ifsc_code":"BKDN0721125"},{"bank_id":"17","bank_name":"ABHYUDAYA CO-OP BANK LTD","ifsc_code":null},{"bank_id":"18","bank_name":"ABU DHABI COMMERCIAL BANK","ifsc_code":null},{"bank_id":"19","bank_name":"ALLAHABAD BANK","ifsc_code":"ALLA0210918"},{"bank_id":"20","bank_name":"ANDHRA BANK","ifsc_code":"ANDB0001106"},{"bank_id":"21","bank_name":"BANK OF AMERICA","ifsc_code":null},{"bank_id":"22","bank_name":"BANK OF BAHRAIN AND KUWAIT","ifsc_code":null},{"bank_id":"23","bank_name":"BANK OF CEYLON","ifsc_code":null},{"bank_id":"24","bank_name":"BANK OF MAHARASHTRA","ifsc_code":"MAHB0001340"},{"bank_id":"25","bank_name":"BANK OF TOKYO-MITSUBISHI UFJ LTD","ifsc_code":null},{"bank_id":"26","bank_name":"BARCLAYS BANK PLC","ifsc_code":null},{"bank_id":"27","bank_name":"BASSEIN CATHOLIC CO-OP BANK LTD","ifsc_code":null},{"bank_id":"28","bank_name":"BNP PARIBAS","ifsc_code":"BNPA0009065"},{"bank_id":"29","bank_name":"CANARA BANK","ifsc_code":"CNRB0002886"},{"bank_id":"30","bank_name":"CATHOLIC SYRIAN BANK LTD","ifsc_code":"CSBK0000297"},{"bank_id":"31","bank_name":"CHINATRUST COMMERCIAL BANK","ifsc_code":null},{"bank_id":"32","bank_name":"CITIZEN CREDIT CO-OP BANK LTD","ifsc_code":null},{"bank_id":"33","bank_name":"CITY UNION BANK LTD","ifsc_code":null},{"bank_id":"34","bank_name":"CORPORATION BANK","ifsc_code":""},{"bank_id":"35","bank_name":"CREDIT AGRICOLE CORP N INVSMNT BANK","ifsc_code":null},{"bank_id":"36","bank_name":"DBS BANK LTD","ifsc_code":"DBSS0IN0820"},{"bank_id":"37","bank_name":"DEUTSCHE BANK AG","ifsc_code":null},{"bank_id":"38","bank_name":"DEVELOPMENT CREDIT BANK LIMITED","ifsc_code":"DCBL0000119"},{"bank_id":"39","bank_name":"DHANLAXMI BANK LTD","ifsc_code":""},{"bank_id":"40","bank_name":"DICGC","ifsc_code":null},{"bank_id":"41","bank_name":"DOMBIVLI NAGARI SAHAKARI BANK LIMITED","ifsc_code":null},{"bank_id":"42","bank_name":"FIRSTRAND BANK LIMITED","ifsc_code":null},{"bank_id":"43","bank_name":"HSBC","ifsc_code":"HSBC0110007"},{"bank_id":"44","bank_name":"INDUSIND BANK LIMITED","ifsc_code":"INDB0000588"},{"bank_id":"45","bank_name":"ING VYSYA BANK","ifsc_code":null},{"bank_id":"46","bank_name":"JANAKALYAN SAHAKARI BANK LTD","ifsc_code":null},{"bank_id":"47","bank_name":"JANATA SAHAKARI BANK LTD (PUNE)","ifsc_code":null},{"bank_id":"48","bank_name":"JPMORGAN CHASE BANK NA","ifsc_code":null},{"bank_id":"49","bank_name":"KAPOLE CO OP BANK","ifsc_code":null},{"bank_id":"50","bank_name":"KARNATAKA BANK LTD","ifsc_code":"KARB0000545"},{"bank_id":"51","bank_name":"KARUR VYSYA BANK (KVB)","ifsc_code":"KVBL0001101"},{"bank_id":"52","bank_name":"KOTAK MAHINDRA BANK (KMB)","ifsc_code":"KKBK0000181"},{"bank_id":"53","bank_name":"MAHANAGAR CO-OP BANK LTD","ifsc_code":null},{"bank_id":"54","bank_name":"MAHARASHTRA STATE CO OPERATIVE BANK","ifsc_code":null},{"bank_id":"55","bank_name":"MASHREQ BANK PSC","ifsc_code":null},{"bank_id":"56","bank_name":"MIZUHO CORPORATE BANK LTD","ifsc_code":null},{"bank_id":"57","bank_name":"NEW INDIA CO-OPERATIVE BANK LTD","ifsc_code":null},{"bank_id":"58","bank_name":"NKGSB CO-OP BANK LTD","ifsc_code":null},{"bank_id":"59","bank_name":"NUTAN NAGARIK SAHAKARI BANK LTD","ifsc_code":null},{"bank_id":"60","bank_name":"OMAN INTERNATIONAL BANK SAOG","ifsc_code":null},{"bank_id":"61","bank_name":"ORIENTAL BANK OF COMMERCE (OBC)","ifsc_code":"ORBC0100931"},{"bank_id":"62","bank_name":"PARSIK JANATA SAHAKARI BANK LTD","ifsc_code":null},{"bank_id":"63","bank_name":"PUNJAB AND MAHARASHTRA CO-OP BANK LTD","ifsc_code":null},{"bank_id":"64","bank_name":"PUNJAB AND SIND BANK (PSB)","ifsc_code":"PSIB0000878"},{"bank_id":"65","bank_name":"RAJKOT NAGARIK SAHAKARI BANK LTD","ifsc_code":null},{"bank_id":"66","bank_name":"RESERVE BANK OF INDIA","ifsc_code":null},{"bank_id":"67","bank_name":"SHINHAN BANK","ifsc_code":null},{"bank_id":"68","bank_name":"SOCIETE GENERALE","ifsc_code":null},{"bank_id":"69","bank_name":"SOUTH INDIAN BANK (SIB)","ifsc_code":""},{"bank_id":"70","bank_name":"STANDARD CHARTERED BANK (SCB)","ifsc_code":"SCBL0036024"},{"bank_id":"71","bank_name":"STATE BANK OF HYDERABAD (SBH)","ifsc_code":"SBHY0020730"},{"bank_id":"72","bank_name":"STATE BANK OF MAURITIUS LTD","ifsc_code":null},{"bank_id":"73","bank_name":"STATE BANK OF MYSORE (SBM)","ifsc_code":null},{"bank_id":"74","bank_name":"STATE BANK OF TRAVANCORE (SBT)","ifsc_code":"SBTR0000925"},{"bank_id":"75","bank_name":"STATE BANK OF PATIALA (SBP)","ifsc_code":"STBP0001021"},{"bank_id":"76","bank_name":"SYNDICATE BANK","ifsc_code":"SYNB0008816"},{"bank_id":"77","bank_name":"TAMILNAD MERCANTILE BANK LTD","ifsc_code":null},{"bank_id":"78","bank_name":"THE BANK OF NOVA SCOTIA","ifsc_code":null},{"bank_id":"79","bank_name":"THE AHMEDABAD MERCANTILE CO-OPERATIVE BANK LTD","ifsc_code":null},{"bank_id":"80","bank_name":"THE BHARAT CO-OPERATIVE BANK (MUMBAI) LTD","ifsc_code":null},{"bank_id":"81","bank_name":"THE COSMOS CO-OPERATIVE BANK LTD","ifsc_code":null},{"bank_id":"82","bank_name":"THE FEDERAL BANK LTD (FBL)","ifsc_code":""},{"bank_id":"83","bank_name":"THE GREATER BOMBAY CO-OP BANK LTD","ifsc_code":null},{"bank_id":"84","bank_name":"THE JAMMU AND KASHMIR BANK LTD (J&K)","ifsc_code":""},{"bank_id":"85","bank_name":"THE KALUPUR COMMERCIAL CO OP BANK LTD","ifsc_code":null},{"bank_id":"86","bank_name":"THE KARNATAKA STATE APEX COOP BANK","ifsc_code":null},{"bank_id":"87","bank_name":"THE KALYAN JANATA SAHAKARI BANK LTD","ifsc_code":null},{"bank_id":"88","bank_name":"THE LAKSHMI VILAS BANK LTD","ifsc_code":"LAVB0000504"},{"bank_id":"89","bank_name":"THE MEHSANA URBAN COOPERATIVE BANK LTD","ifsc_code":null},{"bank_id":"90","bank_name":"THE NAINITAL BANK LIMITED","ifsc_code":null},{"bank_id":"91","bank_name":"THE RATNAKAR BANK LTD (RBL)","ifsc_code":"RATN0000182"},{"bank_id":"92","bank_name":"THE ROYAL BANK OF SCOTLAND","ifsc_code":null},{"bank_id":"93","bank_name":"THE SARASWAT CO-OPERATIVE BANK LTD","ifsc_code":null},{"bank_id":"94","bank_name":"THE SHAMRAO VITHAL CO-OPERATIVE BANK","ifsc_code":null},{"bank_id":"95","bank_name":"THE SURAT PEOPLES CO-OPERATIVE BANK","ifsc_code":null},{"bank_id":"96","bank_name":"THE THANE JANATA SAHAKARI BANK LTD","ifsc_code":null},{"bank_id":"97","bank_name":"THE TAMILNADU STATE APEX COOPERATVE BANK","ifsc_code":null},{"bank_id":"98","bank_name":"WEST BENGAL STATE CO-OPERATIVE BANK","ifsc_code":null},{"bank_id":"99","bank_name":"VIJAYA BANK (VB)","ifsc_code":null},{"bank_id":"100","bank_name":"STATE BANK OF INDIA (SBI)","ifsc_code":"SBIN0008079"},{"bank_id":"101","bank_name":"THE A.P. MAHESH CO-OP URBAN BANK LTD.","ifsc_code":null},{"bank_id":"102","bank_name":"THE KARAD URBAN CO-OP BANK LTD","ifsc_code":null},{"bank_id":"103","bank_name":"THE KARNATAKA STATE CO-OPERATIVE APEX BANK LTD BANGALORE","ifsc_code":null},{"bank_id":"104","bank_name":"THE NASIK MERCHANTS CO-OP BANK LTD. NASHIK","ifsc_code":null},{"bank_id":"105","bank_name":"UBS AG","ifsc_code":null},{"bank_id":"106","bank_name":"UNITED BANK OF INDIA (UBI)","ifsc_code":"UTBI0SCN560 "},{"bank_id":"107","bank_name":"The Kangra Co-Operative Bank Ltd","ifsc_code":null},{"bank_id":"108","bank_name":"KANGRA CENTRAL CO-OP BANK LIMITED (THE)","ifsc_code":null},{"bank_id":"109","bank_name":"PRATHAMA BANK","ifsc_code":null},{"bank_id":"110","bank_name":"Chaitanya Godavari Grameena Bank","ifsc_code":null},{"bank_id":"111","bank_name":"Allahabad UP Gramin Bank","ifsc_code":null},{"bank_id":"112","bank_name":"Rushikulya Gramin Bank","ifsc_code":null},{"bank_id":"113","bank_name":"Sharda Gramin Bank","ifsc_code":null},{"bank_id":"114","bank_name":"Nainital Almora Kshetriya Gramin Bank","ifsc_code":null},{"bank_id":"115","bank_name":"Baroda Rajasthan Kshetriya Gramin Bank","ifsc_code":null},{"bank_id":"116","bank_name":"Baroda Uttar Pradesh Gramin Bank","ifsc_code":null},{"bank_id":"117","bank_name":"Baroda Gujarat Gramin Bank","ifsc_code":null},{"bank_id":"118","bank_name":"Jhabua Dhar Kshetriya Gramin Bank","ifsc_code":null},{"bank_id":"119","bank_name":"Dena Gujarat Gramin Bank","ifsc_code":null},{"bank_id":"120","bank_name":"Durg Rajnandgaon Gramin Bank","ifsc_code":null},{"bank_id":"121","bank_name":"Baitarani Gramin Bank","ifsc_code":null},{"bank_id":"122","bank_name":"Aryavart Gramin Bank","ifsc_code":null},{"bank_id":"123","bank_name":"Wainganga Krishna Gramin Bank","ifsc_code":null},{"bank_id":"124","bank_name":"Uttar Bihar Gramin Bank","ifsc_code":null},{"bank_id":"125","bank_name":"Ballia Etawah Gramin Bank","ifsc_code":null},{"bank_id":"126","bank_name":"Hadoti Kshetriya Gramin Bank","ifsc_code":null},{"bank_id":"127","bank_name":"Surguja Kshetriya Gramin Bank","ifsc_code":null},{"bank_id":"128","bank_name":"South Malabar Gramin Bank","ifsc_code":null},{"bank_id":"129","bank_name":"Chickmangalur Kodagu Gramin Bank","ifsc_code":null},{"bank_id":"130","bank_name":"Pragathi Gramin Bank","ifsc_code":null},{"bank_id":"131","bank_name":"Shreyas Gramin Bank","ifsc_code":null},{"bank_id":"132","bank_name":"Satpura Narmada Kshetriya Gramin Bank","ifsc_code":null},{"bank_id":"133","bank_name":"Uttar Banga Kshetriya Gramin Bank","ifsc_code":null},{"bank_id":"134","bank_name":"Vidharbha Kshetriya Gramin Bank","ifsc_code":null},{"bank_id":"135","bank_name":"Madhya Bharat Gramin Bank","ifsc_code":null},{"bank_id":"136","bank_name":"Gurgaon Gramin Bank","ifsc_code":null},{"bank_id":"137","bank_name":"Malwa Gramin Bank","ifsc_code":null},{"bank_id":"138","bank_name":"Mewar Anchalik Gramin Bank","ifsc_code":null},{"bank_id":"139","bank_name":"Pallavan Grama Bank","ifsc_code":null},{"bank_id":"140","bank_name":"Neelachal Gramya Bank","ifsc_code":null},{"bank_id":"141","bank_name":"Pandyan Gramin Bank","ifsc_code":null},{"bank_id":"142","bank_name":"Puduvai Bharathiar Grama Bank","ifsc_code":null},{"bank_id":"143","bank_name":"J & K Grameen Bank","ifsc_code":null},{"bank_id":"144","bank_name":"Maharashtra Gramin Bank","ifsc_code":null},{"bank_id":"145","bank_name":"Rajasthan Gramin Bank","ifsc_code":null},{"bank_id":"146","bank_name":"Sarva UP Gramin Bank","ifsc_code":null},{"bank_id":"147","bank_name":"Sutlej Gramin Bank","ifsc_code":null},{"bank_id":"148","bank_name":"Himachal Gramin Bank","ifsc_code":null},{"bank_id":"149","bank_name":"Madhya Bihar Gramin Bank","ifsc_code":null},{"bank_id":"150","bank_name":"Haryana Gramin Bank","ifsc_code":null},{"bank_id":"151","bank_name":"Andhra Pradesh Grameena Vikas Bank","ifsc_code":null},{"bank_id":"152","bank_name":"Arunachal Pradesh Rural Bank","ifsc_code":null},{"bank_id":"153","bank_name":"MG Baroda Gramin Bank","ifsc_code":null},{"bank_id":"154","bank_name":"Deccan Grameena Bank","ifsc_code":null},{"bank_id":"155","bank_name":"Chhattisgarh Gramin Bank","ifsc_code":null},{"bank_id":"156","bank_name":"Ellaqui Dehati Bank","ifsc_code":null},{"bank_id":"157","bank_name":"Narmada Malwa Gramin Bank","ifsc_code":null},{"bank_id":"158","bank_name":"Jharkhand Gramin Bank","ifsc_code":null},{"bank_id":"159","bank_name":"Cauvery Kalpatharu Grameena Bank","ifsc_code":null},{"bank_id":"160","bank_name":"Vidisha Bhopal Kshetriya Gramin Bank","ifsc_code":null},{"bank_id":"161","bank_name":"Krishna Gramin Bank","ifsc_code":null},{"bank_id":"162","bank_name":"Langpi Dehangi Rural Bank","ifsc_code":null},{"bank_id":"163","bank_name":"Meghalaya Rural Bank","ifsc_code":null},{"bank_id":"164","bank_name":"Parvatiya Gramin Bank","ifsc_code":null},{"bank_id":"165","bank_name":"Purvanchal Gramin Bank","ifsc_code":null},{"bank_id":"166","bank_name":"Saurashtra Gramin Bank","ifsc_code":null},{"bank_id":"167","bank_name":"Samastipur Kshetriya GB","ifsc_code":null},{"bank_id":"168","bank_name":"Uttarakhand Gramin Bank","ifsc_code":null},{"bank_id":"169","bank_name":"Utkal Gramya Bank","ifsc_code":null},{"bank_id":"170","bank_name":"Karnataka Vikas Grameena Bank","ifsc_code":null},{"bank_id":"171","bank_name":"Andhra Pragathi Grameena Bank","ifsc_code":null},{"bank_id":"172","bank_name":"North Malabar Gramin Bank","ifsc_code":null},{"bank_id":"173","bank_name":"Assam Gramin Vikash Bank","ifsc_code":null},{"bank_id":"174","bank_name":"Kashi Gomati Samyut Gramin Bank","ifsc_code":null},{"bank_id":"175","bank_name":"Mahakaushal Kshetriya Gramin Bank","ifsc_code":null},{"bank_id":"176","bank_name":"Bihar Kshetriya Gramin Bank","ifsc_code":null},{"bank_id":"177","bank_name":"Kalinga Gramya Bank","ifsc_code":null},{"bank_id":"178","bank_name":"Jaipur Thar Gramin Bank","ifsc_code":null},{"bank_id":"179","bank_name":"Paschim Banga Gramin Bank","ifsc_code":null},{"bank_id":"180","bank_name":"Rewa-Sidhi Gramin Bank","ifsc_code":null},{"bank_id":"181","bank_name":"Bangiya Gramin Bank","ifsc_code":null},{"bank_id":"182","bank_name":"Manipur Rural Bank","ifsc_code":null},{"bank_id":"183","bank_name":"Tripura Gramin Bank","ifsc_code":null},{"bank_id":"184","bank_name":"Visveshwaraya Gramin Bank","ifsc_code":null},{"bank_id":"185","bank_name":"SWARNA BHARAT TRUST CYBER GRAMEEN","ifsc_code":null},{"bank_id":"186","bank_name":"NEFT MALWA GRAMIN BANK","ifsc_code":null},{"bank_id":"187","bank_name":"CREDIT CARD - ABN Amro Bank","ifsc_code":null},{"bank_id":"188","bank_name":"CREDIT CARD - Barclays","ifsc_code":null},{"bank_id":"189","bank_name":"CREDIT CARD - Citibank","ifsc_code":null},{"bank_id":"190","bank_name":"CREDIT CARD - HDFC Bank","ifsc_code":null},{"bank_id":"191","bank_name":"CREDIT CARD - HSBC","ifsc_code":null},{"bank_id":"192","bank_name":"CREDIT CARD - ICICI Bank","ifsc_code":null},{"bank_id":"193","bank_name":"CREDIT CARD - Kotak Mahindra Bank","ifsc_code":null},{"bank_id":"194","bank_name":"CREDIT CARD - SBI","ifsc_code":null},{"bank_id":"195","bank_name":"CREDIT CARD - Standard Chartered","ifsc_code":null},{"bank_id":"196","bank_name":"CREDIT CARD - UTI Axis Bank","ifsc_code":null},{"bank_id":"197","bank_name":"CREDIT CARD - Vijaya Bank","ifsc_code":null},{"bank_id":"198","bank_name":"CREDIT CARD - AMERICAN EXPRESS","ifsc_code":null},{"bank_id":"199","bank_name":"Janaseva Sahakari Bank Ltd.","ifsc_code":null},{"bank_id":"200","bank_name":"Kallapana Ichalkaranji Awade Janaseva Sahakari Bank","ifsc_code":null},{"bank_id":"201","bank_name":"Pandharpur Merchant Co-operative Bank","ifsc_code":null},{"bank_id":"202","bank_name":"Gayatri Bank","ifsc_code":null},{"bank_id":"203","bank_name":"Pochampally Co-op Urban Bank Ltd.","ifsc_code":null},{"bank_id":"204","bank_name":"Dr. Annasaheb Chougule Urban Co-op Bank Ltd.","ifsc_code":null},{"bank_id":"205","bank_name":"Surat District Co-op Bank","ifsc_code":null},{"bank_id":"206","bank_name":"Suco Souharda Sahakari Bank Ltd","ifsc_code":null},{"bank_id":"207","bank_name":"Pune Peoples Co-Operative Bank","ifsc_code":null},{"bank_id":"208","bank_name":"Shri Arihant Co-operative Bank Ltd.","ifsc_code":null},{"bank_id":"209","bank_name":"The National Co-operative Bank Ltd.","ifsc_code":null},{"bank_id":"210","bank_name":"Parshwanath Co-operative Bank Ltd.","ifsc_code":null},{"bank_id":"211","bank_name":"APNA Sahakari Bank Ltd","ifsc_code":null},{"bank_id":"212","bank_name":"Jalore Nagrik Sahakari Bank Ltd.","ifsc_code":null},{"bank_id":"213","bank_name":"Varachha Co-op Bank Ltd.","ifsc_code":null},{"bank_id":"214","bank_name":"Janata Co-operative Bank Ltd., Malegaon","ifsc_code":null},{"bank_id":"215","bank_name":"Shri Basaveshwar Sahakari Bank Niyamit, Bagalkot","ifsc_code":null},{"bank_id":"216","bank_name":"The Shirpur Peoples\u2019 Co-op Bank Ltd","ifsc_code":null},{"bank_id":"217","bank_name":"Bhartiya Mahila bank","ifsc_code":null},{"bank_id":"218","bank_name":"Kerala Gramin Bank","ifsc_code":null},{"bank_id":"219","bank_name":"Pragathi Krishna Gramin Bank","ifsc_code":null},{"bank_id":"220","bank_name":"Yadagiri Lakshmi Narasimha Swamy Co Op Urban Bank Ltd","ifsc_code":null},{"bank_id":"221","bank_name":"Hutatma Sahakari Bank Ltd.","ifsc_code":null},{"bank_id":"222","bank_name":"Himachal Pradesh Co-op Bank","ifsc_code":null},{"bank_id":"223","bank_name":"The Adarsh Urban Co-op. Bank Ltd., Hyderabad","ifsc_code":null},{"bank_id":"224","bank_name":"The Mayani Urban Co-operative Bank Ltd","ifsc_code":null},{"bank_id":"225","bank_name":"The Pandharpur Urban Co-op Bank Ltd","ifsc_code":null},{"bank_id":"226","bank_name":"VANANCHAL GRAMIN BANK","ifsc_code":null},{"bank_id":"227","bank_name":"PUNJAB GRAMIN BANK","ifsc_code":null},{"bank_id":"228","bank_name":"Shree Veershaiv Co-op Bank Ltd","ifsc_code":null},{"bank_id":"229","bank_name":"Thrissur District Central Co-op Bank Ltd","ifsc_code":null},{"bank_id":"230","bank_name":"Vishweshwar Co-op. Bank Ltd.","ifsc_code":null},{"bank_id":"231","bank_name":"Raipur Urban Mercantile Co-operative Bank Ltd.","ifsc_code":null},{"bank_id":"232","bank_name":"Zila Sahkari bank","ifsc_code":null},{"bank_id":"233","bank_name":"TITWALA","ifsc_code":null},{"bank_id":"234","bank_name":"DOMBIVLI EAST","ifsc_code":null},{"bank_id":"235","bank_name":"FARIDABAD","ifsc_code":null},{"bank_id":"236","bank_name":"Mgcb Main","ifsc_code":null},{"bank_id":"237","bank_name":"SINDHUDURG DIST CENT COOP BANK LTD","ifsc_code":null},{"bank_id":"238","bank_name":"HAMIRPUR DISTRICT CO OPERATIVE BANK LTD MAHOBA","ifsc_code":null},{"bank_id":"239","bank_name":"SHIVALIK MERCANTILE CO-OP. BANK LTD","ifsc_code":null},{"bank_id":"240","bank_name":"The Hasti Co-op Bank Ltd.","ifsc_code":null},{"bank_id":"241","bank_name":"Rajgurunagar Sahakari Bank Ltd.","ifsc_code":null},{"bank_id":"242","bank_name":"Bandhan Bank","ifsc_code":null},{"bank_id":"243","bank_name":"Dapoli Urban Co-Op Bank, Dapoli","ifsc_code":null},{"bank_id":"244","bank_name":"The Gujarat State Co-op Bank Ltd.","ifsc_code":null},{"bank_id":"245","bank_name":"The Municipal Co-operative Bank Ltd.","ifsc_code":null},{"bank_id":"246","bank_name":"Rajapur Urban Co-op Bank Ltd.","ifsc_code":null},{"bank_id":"247","bank_name":"Ahmedabad District Central Co-op Bank Ltd.","ifsc_code":null},{"bank_id":"248","bank_name":"IDFC Bank","ifsc_code":"IDFB0080391"},{"bank_id":"249","bank_name":"Rajasthan Marudhara Gramin Bank","ifsc_code":""},{"bank_id":"250","bank_name":"Suvarnayug Sahakari Bank Ltd.","ifsc_code":null},{"bank_id":"251","bank_name":"The Sutex Co-operative Bank Ltd.","ifsc_code":null},{"bank_id":"252","bank_name":"PAYTM Bank","ifsc_code":"PYTM0123456"},{"bank_id":"253","bank_name":"Sarva haryana gramin bank","ifsc_code":"PUNB0HGB001"},{"bank_id":"254","bank_name":"Equitas Small Finance Bank","ifsc_code":null},{"bank_id":"255","bank_name":"Federal Bank","ifsc_code":null},{"bank_id":"256","bank_name":"Fino Payments Bank","ifsc_code":null},{"bank_id":"257","bank_name":"Gramin bank of Aryavart","ifsc_code":null},{"bank_id":"258","bank_name":"Kaveri Grameena Bank","ifsc_code":null},{"bank_id":"259","bank_name":"Madhyanchal Gramin Bank","ifsc_code":null},{"bank_id":"260","bank_name":"Odisha Gramya Bank","ifsc_code":null},{"bank_id":"261","bank_name":"Telangana Grameena Bank","ifsc_code":null}]}</pre>
                    </div>
                </div>



                <div class="card" id="basic-alert">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Get Customer</h6>
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
                                <td>mobile_number</td>
                                <td>Remiter Mobile Number</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <pre>POST: {{url('api/dmt/v2/get-customer')}}</pre>
                        <hr>
                        <pre>Success : {"status":"success","name":"{{ Auth::User()->name }}","mobile_number":"{{ Auth::User()->mobile }}","total_limit":25000}</pre>
                        <pre>Failure : {"status":"failure","message":"customer_id does not exist in system"}</pre>
                    </div>
                </div>



                <div class="card" id="basic-alert">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Add Sender</h6>
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
                                <td>mobile_number</td>
                                <td>Remiter Mobile Number</td>
                            </tr>
                            <tr>
                                <td>first_name</td>
                                <td>Remiter Fist Name</td>
                            </tr>

                            <tr>
                                <td>last_name</td>
                                <td>Remiter Last Name</td>
                            </tr>

                            <tr>
                                <td>pin_code</td>
                                <td>Remiter Pin Code</td>
                            </tr>

                            <tr>
                                <td>address</td>
                                <td>Remiter Remiter Address</td>
                            </tr>

                            <tr>
                                <td>state</td>
                                <td>Remiter State</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <pre>POST: {{url('api/dmt/v2/add-sender')}}</pre>
                        <hr>
                        <pre>Success : {"status":"success","message":"Success"}</pre>
                        <pre>Failure : {"status":"failure","message":"Sender Already Exists"}</pre>
                    </div>
                </div>




                <div class="card" id="basic-alert">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Resend OTP</h6>
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
                                <td>mobile_number</td>
                                <td>Remiter Mobile Number</td>
                            </tr>
                            <tr>
                                <td>first_name</td>
                                <td>Remiter Fist Name</td>
                            </tr>

                            <tr>
                                <td>last_name</td>
                                <td>Remiter Last Name</td>
                            </tr>

                            <tr>
                                <td>pin_code</td>
                                <td>Remiter Pin Code</td>
                            </tr>

                            <tr>
                                <td>address</td>
                                <td>Remiter Remiter Address</td>
                            </tr>

                            <tr>
                                <td>state</td>
                                <td>Remiter State</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <pre>POST: {{url('api/dmt/v2/resend-otp')}}</pre>
                        <hr>
                        <pre>Success : {"status":"success","message":"Success"}</pre>
                        <pre>Failure : {"status":"failure","message":"Sender Already Exists"}</pre>
                    </div>
                </div>



                <div class="card" id="basic-alert">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Sender Confirmation</h6>
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
                                <td>mobile_number</td>
                                <td>Remiter Mobile Number</td>
                            </tr>

                            <tr>
                                <td>otp</td>
                                <td>6 Digit OTP</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <pre>POST: {{url('api/dmt/v2/sender-confirmation')}}</pre>
                        <hr>
                        <pre>Success : {"status":"success","message":"Success"}</pre>
                        <pre>Failure : {"status":"failure","message":"OTP verification failed"}</pre>
                    </div>
                </div>



                <div class="card" id="basic-alert">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Get All Beneficiary</h6>
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
                                <td>mobile_number</td>
                                <td>Remiter Mobile Number</td>
                            </tr>

                            <tr>
                                <td>sender_name</td>
                                <td>Remiter Name</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <pre>POST: {{url('api/dmt/v2/get-all-beneficiary')}}</pre>
                        <hr>
                        <pre>Success : {"status": "success","message": "Success","recipient_list": [{"sr_no": 1,"recipient_id": 271717,"recipient_bank": "INDUSIND BANK LIMITED","recipient_mobile": "","recipient_name": "{{ Auth::User()->name }}","recipient_ifsc": "INDB0000588","recipient_account": "123456789012"}]}</pre>
                        <pre>Failure : {"status":"failure","message":"Beneficiary not found"}</pre>
                    </div>
                </div>



                <div class="card" id="basic-alert">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Account Validate</h6>
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
                                <td>mobile_number</td>
                                <td>Remiter Mobile Number</td>
                            </tr>

                            <tr>
                                <td>bank_id</td>
                                <td>Bank id available in bank list api</td>
                            </tr>

                            <tr>
                                <td>ifsc_code</td>
                                <td>Bank IFSC Code</td>
                            </tr>

                            <tr>
                                <td>account_number</td>
                                <td>Beneficiary Account Number</td>
                            </tr>

                            <tr>
                                <td>client_id</td>
                                <td>Your side unique id </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <pre>POST: {{url('api/dmt/v2/account-verify')}}</pre>
                        <hr>
                        <pre>Success : {"status":"success","beneficiary_name":"{{ Auth::User()->name }}","message":"Success Message"}</pre>
                        <pre>Failure : {"status":"failure","message":"Failure Message"}</pre>
                    </div>
                </div>


                <div class="card" id="basic-alert">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Add Beneficiary</h6>
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
                                <td>mobile_number</td>
                                <td>Remiter Mobile Number</td>
                            </tr>

                            <tr>
                                <td>bank_id</td>
                                <td>Bank id available in bank list api</td>
                            </tr>

                            <tr>
                                <td>ifsc_code</td>
                                <td>Bank IFSC Code</td>
                            </tr>

                            <tr>
                                <td>account_number</td>
                                <td>Beneficiary Account Number</td>
                            </tr>

                            <tr>
                                <td>beneficiary_name</td>
                                <td>Beneficiary Name</td>
                            </tr>

                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <pre>POST: {{url('api/dmt/v2/add-beneficiary')}}</pre>
                        <hr>
                        <pre>Success : {"status":"success","message":"Success Message"}</pre>
                        <pre>Failure : {"status":"failure","message":"Failure Message"}</pre>
                    </div>
                </div>



                <div class="card" id="basic-alert">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Transfer Now</h6>
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
                                <td>mobile_number</td>
                                <td>Remiter Mobile Number</td>
                            </tr>

                            <tr>
                                <td>recipient_id</td>
                                <td>Recipient Id</td>
                            </tr>

                            <tr>
                                <td>account_number</td>
                                <td>Beneficiary Account Number</td>
                            </tr>

                            <tr>
                                <td>ifsc_code</td>
                                <td>Beneficiary IFSC Code</td>
                            </tr>

                            <tr>
                                <td>mode</td>
                                <td>NEFT = 1, IMPS = 2</td>
                            </tr>

                            <tr>
                                <td>amount</td>
                                <td>Transfer Amount</td>
                            </tr>

                            <tr>
                                <td>client_id</td>
                                <td>Your Side Uniq Id</td>
                            </tr>



                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <pre>POST: {{url('api/dmt/v2/transfer')}}</pre>
                        <hr>
                        <pre>Success : {"status":"success","message":"success","payid":2658,"utr":"033216658733"}</pre>
                        <pre>Failure : {"status":"failure","message":"Failure Message"}</pre>
                        <pre>Pending : {"status":"pending","message":"Pending Message"}</pre>
                    </div>
                </div>


                <div class="card" id="basic-alert">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Delete Beneficiary</h6>
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
                                <td>mobile_number</td>
                                <td>Remiter Mobile Number</td>
                            </tr>

                            <tr>
                                <td>recipient_id</td>
                                <td>Recipient Id</td>
                            </tr>



                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <pre>POST: {{url('api/dmt/v2/delete-beneficiary')}}</pre>
                        <hr>
                        <pre>Success : {"status":"success","message":"Success Message"}</pre>
                        <pre>Failure : {"status":"failure","message":"Failure Message"}</pre>
                    </div>
                </div>







            </div>
            <!--/div-->

        </div>

    </div>
    </div>
    </div>



@endsection