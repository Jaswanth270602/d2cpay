<!--  Navbar Starts / Breadcrumb Area  -->
<div class="sub-header-container">
    <header class="header navbar navbar-expand-sm">
        <ul class="navbar-nav flex-row">
            <li>
                <div class="page-header">
                    <nav class="breadcrumb-one" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboards</a></li>
                            <li class="breadcrumb-item active" aria-current="page"><span>{{ $page_title }}</span></li>
                        </ol>
                    </nav>
                </div>
            </li>
        </ul>

        <ul class="navbar-nav d-flex align-center ml-auto right-side-filter">

            <li class="nav-item">
                <button type="button" class="btn btn-sm btn-outline-success">Today Sale : <span id="dashboard_today_sale"></span></button>
            </li>
            <li class="nav-item">&nbsp; &nbsp; &nbsp;</li>

            @if(Auth::User()->company->aeps == 1 && Auth::User()->profile->aeps == 1)
                <li class="nav-item">
                    <button type="button" class="btn btn-sm btn-outline-success">Today Aeps Sale : <span id="dashboard_aeps_sale"></span></button>
                </li>

                <li class="nav-item">&nbsp; &nbsp; &nbsp;</li>
            @endif

            <li class="nav-item">
                <button type="button" class="btn btn-sm btn-outline-success">Today Profit : <span id="dashboard_today_profit"></span></button>
            </li>

        </ul>

    </header>
</div>
<!--  Navbar Ends / Breadcrumb Area  -->