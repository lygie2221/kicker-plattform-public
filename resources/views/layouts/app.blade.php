<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} @yield('title')</title>
    <link rel="icon" href="/images/logo.png" sizes="32x32" />
    <link rel="icon" href="/images/logo.png" sizes="192x192" />

    <link href="{{ asset('css/app2.css') }}?v=1" rel="stylesheet">

    <link href="{{ asset('css/material.css') }}" rel="stylesheet">

    <style type="text/css">
        .ui-widget {
            font-size: 0.8em;
        }
        .pagination-wrapper {
            padding: 0 3.2rem;
            text-align: right;
        }

        @media (max-width: 575.98px) {


            .pagination-wrapper {
                padding: 0 1.6rem;
            }

            #e-commerce-product div.h4 {
                display: none;
            }
        }

        .page-layout.carded.full-width > .page-content-wrapper .page-content-card {
            margin-bottom: 40px;
        }
        .table th {
            padding: 1.2rem 1.6rem !important;
        }
        .table td {
            padding: 0.6rem 0.8rem !important;
        }


    </style>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
</head>

<body class="layout layout-vertical layout-left-navigation layout-above-toolbar layout-above-footer">
<main>
    @guest

    @else
    <nav id="toolbar" class="bg-white">

        <div class="row no-gutters align-items-center flex-nowrap">

            <div class="col">

                <div class="row no-gutters align-items-center flex-nowrap">

                    <button type="button" class="toggle-aside-button btn btn-icon d-block d-lg-none" data-fuse-bar-toggle="aside">
                        <i class="icon icon-menu"></i>
                    </button>

                    <div class="toolbar-separator d-block d-lg-none"></div>

                    <div class="shortcuts-wrapper row no-gutters align-items-center px-0 px-sm-2">

                        <div class="shortcuts row no-gutters align-items-center d-none d-md-flex">

                            <a href="{{ route('home') }}" class="shortcut-button btn btn-icon mx-1">
                                <i class="icon icon-ticket-star"></i> Kicker-Plattform: Begegnungen
                            </a>

                            <a href="{{ route('spiele') }}" class="shortcut-button btn btn-icon mx-1">
                                <i class="icon icon-tag"></i> Spiele
                            </a>


                            <a href="{{ route('spieler') }}" class="shortcut-button btn btn-icon mx-1">
                                <i class="icon icon-account-box"></i> Spieler
                            </a>
                        </div>


                        <div class="toolbar-separator"></div>
                </div>

                <div class="col justify-content-end">

                    <div class="row no-gutters align-items-center justify-content-end">

                        <div class="user-menu-button dropdown">


                        </div>

                        <div class="user-menu-button dropdown">

                            <div class="dropdown-toggle ripple row align-items-center no-gutters px-2 px-sm-4" role="button" id="dropdownUserMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <div class="avatar-wrapper">
                                    <img class="avatar" src="/images/profile.jpg" />
                                </div>
                                <span class="username mx-3 d-none d-md-block">{{ Auth::user()->name }}</span>
                            </div>

                            <div class="dropdown-menu open" aria-labelledby="dropdownUserMenu">

                                <a class="dropdown-item" href="/logout">
                                        <a class="dropdown-item" href="{{ route('logout') }}"
                                           onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                            {{ __('Logout') }}
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            @csrf
                                        </form>
                                </a>

                                <div class="dropdown-divider"></div>

                            </div>
                        </div>

                        <div class="toolbar-separator"></div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </nav>
    @endguest
    <div id="wrapper">
        <aside id="aside" class="aside aside-left d-lg-none" data-fuse-bar="aside" data-fuse-bar-media-step="md" data-fuse-bar-position="left">
            <div class="aside-content bg-primary-700 text-auto">

                <div class="aside-toolbar">

                    <div class="logo">
                        <span class="logo-text">CryptoPanel</span>
                    </div>

                    <button id="toggle-fold-aside-button" type="button" class="btn btn-icon d-none d-lg-block" data-fuse-aside-toggle-fold>
                        <i class="icon icon-backburger"></i>
                    </button>

                </div>
            </div>

        </aside>


        <div class="content-wrapper">
            <div class="content custom-scrollbar">
            @yield('content')
            </div>
        </div>
    </div>


        <script src="{{ asset('js/app.js') }}"></script>
        <script src="{{ asset('js/structured-filter.js') }}"></script>
        <script src="{{ asset('js/dtView.js') }}"></script>


        @yield('page-script')

</main>
</body>

</html>
