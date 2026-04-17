@extends('front.template1.header')
@section('content')



    <!--<section class="form-12" id="home">-->
    <!--    <div class="">-->
    <!--        <div class="">-->
    <!--            <div class="grid">-->
    <!--                <div class="column2">-->
    <!--                </div>-->

    <!--                <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">-->
    <!--                    <div class="carousel-inner">-->
    <!--                        @foreach($frontbanner as $key => $banner)-->
    <!--                            <div class="carousel-item {{ $key == 0 ? ' active' : '' }}">-->
    <!--                                <img class="d-block w-100" src="{{$banner->banners}}" alt="Slider Banner">-->
    <!--                            </div>-->
    <!--                        @endforeach-->
    <!--                    </div>-->
    <!--                    <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">-->
    <!--                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>-->
    <!--                        <span class="sr-only">Previous</span>-->
    <!--                    </a>-->
    <!--                    <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">-->
    <!--                        <span class="carousel-control-next-icon" aria-hidden="true"></span>-->
    <!--                        <span class="sr-only">Next</span>-->
    <!--                    </a>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--    </div>-->
    <!--</section>-->

    <!-- content-with-photo4 block -->
    <!--<section class="w3l-content-with-photo-4">-->
    <!--    <div id="content-with-photo4-block" class="pt-5">-->
    <!--        <div class="container py-md-5">-->
    <!--            <div class="cwp4-two row">-->
    <!--            <div class="row align-items-center">-->
    <!--                <div class="cwp4-image col-lg-6 pr-lg-5 mb-lg-0 mb-5">-->
    <!--                    <img src="{{ url('front/images/d2c-about-img.png') }}" class="img-fluid" alt="" />-->
    <!--                </div>-->
    <!--                <div class="cwp4-text col-lg-6">-->
    <!--                    <h3>{{ $company_name }} — Simplifying Digital Payments</h3>-->
    <!--                    <p>-->
    <!--                        {{ $company_name }} provides a secure and scalable payment gateway built for B2B merchants, enabling fast and reliable online transactions.-->
    <!--                    </p>-->
    <!--                    <p>-->
    <!--                        Accept UPI, cards, net banking, and wallets with real-time processing and easy API integration for websites and apps.-->
    <!--                    </p>-->
    <!--                    <p>-->
    <!--                        With instant onboarding, PCI-DSS compliance, and 24/7 support, {{ $company_name }} is your trusted digital payment partner across India.-->
    <!--                    </p>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--    </div>-->
    <!--</section>-->
    <!-- content-with-photo4 block -->
    <!---728x90--->

    <!-- specifications -->
    <!--<section class="w3l-specifications-9">-->
    <!--    <div class="main-w3 pb-5" id="stats">-->
    <!--        <div class="container py-md-5 mt-4">-->
    <!--            <div class="main-cont-wthree-fea row">-->
    <!--                <div class="grids-speci1 col-lg-3 col-6">-->
    <!--                    <div class="spec-2">-->
    <!--                        <span class="fa fa-heart text-dark"></span>-->
    <!--                        <h3 class="title-spe">40450</h3>-->
    <!--                        <p>Our Clients</p>-->
    <!--                    </div>-->
    <!--                </div>-->
    <!--                <div class="grids-speci1 midd-eff-spe col-lg-3 col-6">-->
    <!--                    <div class="spec-2">-->
    <!--                        <span class="fa fa-thumbs-up text-dark"></span>-->
    <!--                        <h3 class="title-spe">13500</h3>-->
    <!--                        <p>Packages Delivered</p>-->
    <!--                    </div>-->
    <!--                </div>-->
    <!--                <div class="grids-speci1 las-but col-lg-3 col-6  mt-lg-0 mt-4">-->
    <!--                    <div class="spec-2">-->
    <!--                        <span class="fa fa-address-card-o text-dark"></span>-->
    <!--                        <h3 class="title-spe">1500</h3>-->
    <!--                        <p>Repeat Customers</p>-->
    <!--                    </div>-->
    <!--                </div>-->
    <!--                <div class="grids-speci1 las-t col-lg-3 col-6  mt-lg-0 mt-4">-->
    <!--                    <div class="spec-2">-->
    <!--                        <span class="fa fa-cog text-dark"></span>-->
    <!--                        <h3 class="title-spe">2000 </h3>-->
    <!--                        <p>Commercial Goods</p>-->
    <!--                    </div>-->
    <!--                </div>-->
    <!--            </div>-->

    <!--        </div>-->
    <!--    </div>-->
        <!-- //specifications -->
    <!--</section>-->

    <!-- skills-1 block -->
    <!--<section class="w3l-skills-1" id="features" style="display:none;">-->
    <!--    <div id="skills-1-block" class="py-5 bg bg2" data-selector=".bg.bg2">-->
    <!--        <div class="container py-md-5">-->
    <!--            <div class="team-1 row">-->
    <!--                <div class="right-single-team col-lg-6">-->
    <!--                    <h6>Here are a few places to explore shipping.</h6>-->
    <!--                    <h3 class="mb-4">Are You a Sender?</h3>-->
    <!--                    <li><span class="fa fa-check"></span> Core freight</li>-->
    <!--                    <li><span class="fa fa-check"></span> Integrated logistics – LLP</li>-->
    <!--                    <li><span class="fa fa-check"></span> Strategic-Xpert</li>-->
    <!--                    <li><span class="fa fa-check"></span> One time solutions</li>-->
    <!--                    <li><span class="fa fa-check"></span> Geo-Gateways</li>-->
    <!--                </div>-->
    <!--                <div class="left-single-team  col-lg-6">-->
    <!--                    <h6>Things need to know about shipping.</h6>-->
    <!--                    <h3 class="mb-4">Are You a Shipper?</h3>-->
    <!--                    <li><span class="fa fa-check"></span> Customs & Tax Representation</li>-->
    <!--                    <li><span class="fa fa-check"></span> Reusable Packaging</li>-->
    <!--                    <li><span class="fa fa-check"></span> Warehousing</li>-->
    <!--                    <li><span class="fa fa-check"></span> Finished Vehicle Logistics</li>-->
    <!--                    <li><span class="fa fa-check"></span> Control Tower</li>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--    </div>-->
    <!--</section>-->
    <!-- Teams14 block -->
    <!---728x90--->

    <!--<section class="w3l-feature-2">-->
    <!--    <div class="grid top-bottom py-5">-->
    <!--        <div class="container py-md-5">-->
    <!--            <div class="heading text-center mx-auto">-->
    <!--                <h3 class="head">Our Services</h3>-->
    <!--                <p class="my-3 head">-->
    <!--                    We provide all online services like bill payments-->
    <!--                </p>-->
    <!--            </div>-->
    <!--            <div class="middle-section row mt-5 pt-3">-->
    <!--             {{--   <div class="three-grids-columns col-lg-4 col-sm-6 ">-->
    <!--                    <div class="icon"> <span class="fa fa-mobile" aria-hidden="true"></span></div>-->
    <!--                    <h4>Recharges</h4>-->
    <!--                    <p>Fast and secure way to recharge any mobile, any operator instantly through website, mobile app Quick Recharge all mobile operator.</p>-->
    <!--                    <a href="#" class="red mt-3">Read More <span class="fa fa-angle-right pl-1"></span></a>-->
    <!--                </div>--}}-->
    <!--           {{--     <div class="three-grids-columns col-lg-4 col-sm-6 mt-sm-0 mt-5">-->
    <!--                    <div class="icon"> <span class="fa fa-lightbulb-o" aria-hidden="true"></span></div>-->
    <!--                    <h4>Bill Payment</h4>-->
    <!--                    <p>Pay Gas Bill, Postpaid Bill, Water Bill and Electricity bills in a seconds using our plartform and avoid late payment charges.</p>-->
    <!--                    <a href="#" class="red mt-3">Read More <span class="fa fa-angle-right pl-1"></span></a>-->
    <!--                </div>-->
    <!--                <div class="three-grids-columns col-lg-4 col-sm-6 mt-lg-0 mt-5">-->
    <!--                    <div class="icon"> <span class="fa fa-id-card" aria-hidden="true"></span></div>-->
    <!--                    <h4>Pan Card</h4>-->
    <!--                    <p>Our UTI Pan Service direct from UTIITSL & also we provide NSDL Pan Service through NSDL software. Pan allote within 3-5 days.</p>-->
    <!--                    <a href="#" class="red mt-3">Read More <span class="fa fa-angle-right pl-1"></span></a>-->
    <!--                </div>--}}-->
    <!--            </div>-->
    <!--           {{-- <div class="middle-section row mt-5 pt-3">-->
    <!--                <div class="three-grids-columns col-lg-4 col-sm-6 ">-->
    <!--                    <div class="icon"> <span class="fa fa-money" aria-hidden="true"></span></div>-->
    <!--                    <h4>Money Transfer</h4>-->
    <!--                    <p>Transfer money to more than 200 banks in India. Instant and easy DMR service allows you to transfer money to any bank account in India.</p>-->
    <!--                    <a href="#" class="red mt-3">Read More <span class="fa fa-angle-right pl-1"></span></a>-->
    <!--                </div>-->
    <!--                <div class="three-grids-columns col-lg-4 col-sm-6 mt-sm-0 mt-5">-->
    <!--                    <div class="icon"> <span class="fa fa-university" aria-hidden="true"></span></div>-->
    <!--                    <h4>AEPS</h4>-->
    <!--                    <p>Aadhar Enabled Payment System is safe and secure banking system. Balace Inquiry, Cash Withdrawal, Mini Statement Available in AEPS.</p>-->
    <!--                    <a href="#" class="red mt-3">Read More <span class="fa fa-angle-right pl-1"></span></a>-->
    <!--                </div>-->
    <!--                <div class="three-grids-columns col-lg-4 col-sm-6 mt-lg-0 mt-5">-->
    <!--                    <div class="icon"> <span class="fa fa-calculator" aria-hidden="true"></span></div>-->
    <!--                    <h4>Micro ATM</h4>-->
    <!--                    <p>Accept Payments or Withdraw on Your Smartphone/Tablet through our mATM Solutions. Credit/Debit Card Accepted, Real-Time Settlement.</p>-->
    <!--                    <a href="#" class="red mt-3">Read More <span class="fa fa-angle-right pl-1"></span></a>-->
    <!--                </div>-->
    <!--            </div>--}}-->
    <!--        </div>-->
    <!--    </div>-->
    <!--</section>-->

    <!--customers-7-->
    <!--<section class="w3l-customers-8" id="testimonials">-->
    <!--    <div class="customers_sur py-5">-->
    <!--        <div class="container py-md-5">-->
    <!--            <div class="heading text-center mx-auto">-->
    <!--                <h3 class="head text-white">Words From Our Clients</h3>-->
    <!--                <p class="my-3 head text-white">-->
                        <!--                    Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae;-->
                        <!--                    Nulla mollis dapibus nunc, ut rhoncus-->
                        <!--                    turpis sodales quis. Integer sit amet mattis quam.-->
    <!--                </p>-->
    <!--            </div>-->
    <!--            <div class="customers-top_sur row mt-5 pt-3">-->
    <!--                <div class="customers-left_sur col-md-6">-->
    <!--                    <div class="customers_grid">-->

    <!--                        <p class="sub-test"><span class="fa fa-quote-left"></span>-->
    <!--                            I have never seen such a great quality. Good luck and keep it up! Thanks for their support which helps us to grow our business.-->
    <!--                        </p>-->

    <!--                    </div>-->
    <!--                    <div class="customers-bottom_sur row">-->
    <!--                        <div class="custo-img-res col-2">-->
                                <!-- <img src="images/te2.jpg" alt=" " class="img-responsive"> -->
    <!--                        </div>-->
    <!--                        {{--<div class="custo_grid col-10">-->
    <!--                            <h5 class="text-white">Drx Laxman Singh Sonigara</h5>-->
    <!--                            <span>Client</span>-->
    <!--                        </div>--}}-->

    <!--                    </div>-->
    <!--                </div>-->
    <!--                <div class="customers-middle_sur col-md-6 mt-md-0 mt-4">-->
    <!--                    <div class="customers_grid">-->

    <!--                        <p class="sub-test"><span class="fa fa-quote-left"></span>-->
    <!--                            Their quality services lead us to achieve new grwoth in market.Thanks to webtech solution.net for their outstanding support and services.-->
    <!--                        </p>-->

    <!--                    </div>-->
    <!--                    <div class="customers-bottom_sur row">-->
    <!--                        <div class="custo-img-res col-2">-->
                                <!-- <img src="images/te1.jpg" alt=" " class="img-responsive"> -->
    <!--                        </div>-->
    <!--                        {{--<div class="custo_grid col-10">-->
    <!--                            <h5 class="text-white">Lokesh Agarwal</h5>-->
    <!--                            <span>Client</span>-->
    <!--                        </div>--}}-->

    <!--                    </div>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--    </div>-->
    <!--</section>-->
    <!--//customers-7-->

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>D2cPAY | Smart Payment Platform</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f9fafb;
      font-family: 'Poppins', sans-serif;
    }
    .brand-gradient {
      background: linear-gradient(to right, #FF7E3F, #FF416C);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    .btn-primary {
      background-color: #00C9FF;
      color: #fff;
    }
    .btn-primary:hover {
      background-color: #17EAD9;
    }
    .section-bg {
      background-color: #e5e7eb;
    }
  </style>
</head>
<body>

  <!-- Hero Section -->
  <section class="text-gray-900 py-24 px-8" data-aos="fade-up">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between">
      <div class="mb-10 md:mb-0 md:w-1/2">
        <h1 class="text-5xl font-bold mb-6 brand-gradient">Empower Your Digital Transactions</h1>
        <p class="text-lg mb-6 text-gray-700">With D2cPAY, enjoy smart, swift, and secure financial services built for the future.</p>
        <a href="#" class="btn-primary px-6 py-3 rounded-lg shadow-lg">Start Now</a>
      </div>
      <div class="md:w-1/2" data-aos="zoom-in">
        <img src="assets/img/ecommerce/d2c-hero-image.jpg" alt="Payment Animation" class="w-full"  style="border-radius:10px;" />
      </div>
    </div>
  </section>

  <!-- Services Overview -->
  <section class="section-bg py-20 px-8 text-gray-900" data-aos="fade-up">
    <div class="max-w-6xl mx-auto text-center">
      <h2 class="text-3xl font-bold brand-gradient mb-12">Our Core Features</h2>
      <div class="grid md:grid-cols-3 gap-10">
        <div class="p-6 rounded-lg bg-white shadow hover:shadow-xl">
          <i class="fas fa-shield-alt text-3xl text-blue-500 mb-4"></i>
          <h3 class="text-xl font-semibold mb-2">Secure API Integration</h3>
          <p class="text-gray-600">Plug-and-play secure APIs for developers to build on top of our infrastructure.</p>
        </div>
        <div class="p-6 rounded-lg bg-white shadow hover:shadow-xl">
          <i class="fas fa-clock text-3xl text-green-500 mb-4"></i>
          <h3 class="text-xl font-semibold mb-2">Real-Time Settlement</h3>
          <p class="text-gray-600">Instant settlements so your money is where it needs to be, when it needs to be.</p>
        </div>
        <div class="p-6 rounded-lg bg-white shadow hover:shadow-xl">
          <i class="fas fa-shield-virus text-3xl text-red-500 mb-4"></i>
          <h3 class="text-xl font-semibold mb-2">Fraud Protection</h3>
          <p class="text-gray-600">Advanced AI algorithms to detect and prevent fraudulent activity instantly.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- 24x7 Service Section -->
  <!--<section class="bg-white py-20 px-8 text-gray-900" data-aos="fade-up">-->
  <!--  <div class="max-w-6xl mx-auto">-->
  <!--    <div class="text-center mb-12">-->
  <!--      <h2 class="text-3xl font-bold brand-gradient">24x7 Customer Support</h2>-->
  <!--      <p class="text-gray-600 mt-4">Our dedicated team is here for you anytime, any day. We're just a message away!</p>-->
  <!--    </div>-->
  <!--    <div class="flex flex-col md:flex-row items-center justify-center gap-12">-->
  <!--      <div class="text-center">-->
  <!--        <img src="assets/img/ecommerce/live-chat-image.jpg" alt="Live Chat" class="w-64 mx-auto" />-->
  <!--        <h3 class="text-xl font-semibold mt-6">Live Chat</h3>-->
  <!--        <p class="text-gray-600">Instant responses through our in-app chat system.</p>-->
  <!--      </div>-->
  <!--      <div class="text-center">-->
  <!--        <img src="assets/img/ecommerce/" alt="Helpdesk" class="w-64 mx-auto" />-->
  <!--        <h3 class="text-xl font-semibold mt-6">Ticketing System</h3>-->
  <!--        <p class="text-gray-600">Submit queries and receive expert help round the clock.</p>-->
  <!--      </div>-->
  <!--    </div>-->
  <!--  </div>-->
  <!--</section>-->

  <!-- Analytics Insight Section -->
  <!--<section class="bg-white py-20 px-8 text-gray-900" data-aos="fade-up">-->
  <!--  <div class="max-w-6xl mx-auto">-->
  <!--    <div class="text-center mb-12">-->
  <!--      <h2 class="text-3xl font-bold brand-gradient">Insights & Analytics</h2>-->
  <!--      <p class="text-gray-600 mt-4">Track, analyze, and improve your transactions with powerful dashboards and real-time reporting tools.</p>-->
  <!--    </div>-->
  <!--    <div class="flex flex-col md:flex-row items-center justify-center gap-10">-->
  <!--      <div class="text-center">-->
  <!--        <img src="https://lottie.host/8709e779-eab6-4401-9989-ec8a273a8414/6NcknxEr8k.json" alt="Analytics" class="w-72 mx-auto" />-->
  <!--        <h3 class="text-xl font-semibold mt-6">Smart Dashboard</h3>-->
  <!--        <p class="text-gray-600">Visualize earnings, growth metrics, and transaction history.</p>-->
  <!--      </div>-->
  <!--      <div class="text-center">-->
  <!--        <img src="https://lottie.host/e1872299-25c7-41db-817c-1b4f0ae5455b/bHOK6Vb3mW.json" alt="Reports" class="w-72 mx-auto" />-->
  <!--        <h3 class="text-xl font-semibold mt-6">Custom Reports</h3>-->
  <!--        <p class="text-gray-600">Generate downloadable reports tailored to your business needs.</p>-->
  <!--      </div>-->
  <!--    </div>-->
  <!--  </div>-->
  <!--</section>-->

  <!-- Call to Action Section -->
  <section class="section-bg py-20 px-8 text-gray-900" data-aos="fade-up">
    <div class="max-w-4xl mx-auto text-center">
      <h2 class="text-3xl font-bold brand-gradient mb-6">Ready to Power Your Payments?</h2>
      <p class="text-gray-600 mb-8">Join thousands of businesses already processing secure payments with D2cPAY. It’s your time now.</p>
      <!--<a href="#" class="btn-primary px-6 py-3 rounded-lg shadow-lg">Create Your Free Account</a>-->
    </div>
  </section>

  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>
    AOS.init();
  </script>

</body>
</html>




@endsection