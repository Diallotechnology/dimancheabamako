@php
$cat = App\Models\Category::all();
@endphp
<style>
    .dropdown-menu.show {
        inset: -39px 41px auto auto !important;
    }
</style>
<header class="header-area header-style-1 header-height-2">
    <div class="header-middle header-middle-ptb-1 d-none d-lg-block">
        <div id="google_translate_element"></div>
        <div class="container">
            <div class="header-wrap">
                <div class="logo logo-width-1">
                    <a href="{{ route('home') }}"><img src="{{ asset('assets/imgs/theme/logo.svg') }}" alt="logo" /></a>
                </div>
                <div class="header-right justify-content-end">
                    <div class="header-action-right">
                        <div class="header-info header-info-right px-4">
                            <ul>
                                <li>
                                    <a class="language-dropdown-active" href="#">
                                        <i class="fi-rs-euro"></i>
                                        {{ session('devise') === "EUR" ? "Euro" : "CFA" }}
                                        <i class="fi-rs-angle-small-down"></i>
                                    </a>
                                    <ul class="language-dropdown">
                                        <li>
                                            <a href="/devise/EUR">Euro</a>
                                            <a href="/devise/CFA">CFA</a>
                                        </li>
                                    </ul>
                                </li>
                                <li>
                                    <a class="language-dropdown-active" href="#">
                                        <i class="fi-rs-world"></i>
                                        {{ session('locale') === "fr" ? "Français" : "English" }}
                                        <i class="fi-rs-angle-small-down"></i>
                                    </a>
                                    <ul class="language-dropdown">
                                        <li>
                                            <a href="/lang/fr">
                                                <img src="{{ asset('assets/imgs/theme/flag-fr.png') }}" alt="flag-fr" />
                                                Français
                                            </a>
                                            <a href="/lang/en">
                                                <img src="{{ asset('assets/imgs/theme/flag-us.png') }}" alt="flag-us" />
                                                English
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                @guest
                                <li>
                                    <i class="fi-rs-user"></i>
                                    <a href="{{ route('login') }}">@lang('messages.login')</a>
                                </li>
                                @else
                                <li class="dropdown nav-item">
                                    <a class="dropdown-toggle" data-bs-toggle="dropdown" href="#" id="dropdownAccount"
                                        aria-expanded="false">
                                        <i class="fi-rs-user" style="font-size: large"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownAccount">
                                        <a class="dropdown-item" href="{{ route('profil') }}"><i
                                                class="material-icons md-perm_identity"></i>Profil</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item text-danger"
                                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                            as="button">
                                            <i class="material-icons md-exit_to_app"></i>@lang('messages.logout')
                                            <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                                class="d-none">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ Auth::user()->id }}">
                                            </form>
                                        </a>
                                    </div>
                                </li>
                                @endguest

                            </ul>
                        </div>

                        <div class="header-action-2">
                            <div class="header-action-icon-2">
                                @livewire('counter')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="header-bottom header-bottom-bg-color sticky-bar">
        <div class="container">
            <div class="header-wrap header-space-between position-relative">
                <div class="logo logo-width-1 d-block d-lg-none">
                    <a href="{{ route('home') }}"><img src="{{ asset('assets/imgs/theme/logo.svg') }}" alt="logo" /></a>
                </div>
                <div class="header-nav d-none d-lg-flex">
                    <div class="main-menu main-menu-padding-1 main-menu-lh-2 d-none d-lg-block">
                        <nav>
                            <ul>
                                <x-nav-link url='home' name="messages.home" />
                                <li>
                                    <a href="">{{ __('messages.category') }}
                                        <i class="fi-rs-angle-down"></i></a>
                                    <ul class="sub-menu">
                                        @foreach($cat as $row)
                                        <li>
                                            <a
                                                href="{{ route('shop', ['category'=>$row->id,'slug'=>Str::slug($row->nom, '-')]) }}">{{
                                                $row->nom }}</a>
                                        </li>
                                        @endforeach
                                    </ul>
                                </li>
                                <x-nav-link url='about' name="messages.about" />
                                <x-nav-link url='livraison' name="messages.delivery" />
                                <x-nav-link url='contact' name="messages.contact" />
                            </ul>
                        </nav>
                    </div>
                </div>

                <div class="hotline d-none d-lg-block">
                    <p>
                        <i class="fi-rs-headset"></i><span>Contact </span> +223 66 03 51 54
                    </p>
                </div>

                <div class="header-action-right d-block d-lg-none">
                    <div class="header-action-2">
                        <div class="header-action-icon-2">
                            <ul class="me-4">
                                @auth
                                <li class="dropdown nav-item">
                                    <a class="dropdown-toggle" data-bs-toggle="dropdown" href="#" id="dropdownAccount"
                                        aria-expanded="false">
                                        <i class="fi-rs-user" style="font-size: large"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownAccount">
                                        <a class="dropdown-item" href="{{ route('profil') }}"><i
                                                class="material-icons md-perm_identity"></i>Profil</a>
                                        <a class="dropdown-item text-danger"
                                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                            as="button">
                                            <i class="material-icons md-exit_to_app"></i>@lang('messages.logout')
                                            <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                                class="d-none">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ Auth::user()->id }}">
                                            </form>
                                        </a>

                                    </div>
                                </li>
                                @else
                                <li class="mini-cart-icon">
                                    <a href="{{ route('login') }}">
                                        <i class="fi-rs-user text-dark" style="font-size: x-large"></i>
                                    </a>
                                </li>
                                @endauth
                            </ul>
                            @livewire('counter')

                        </div>
                        <div class="header-action-icon-2 d-block d-lg-none">
                            <div class="burger-icon burger-icon-white">
                                <span class="burger-icon-top"></span>
                                <span class="burger-icon-mid"></span>
                                <span class="burger-icon-bottom"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<div class="mobile-header-active mobile-header-wrapper-style">
    <div class="mobile-header-wrapper-inner">
        <div class="mobile-header-top">
            <div class="mobile-header-logo">
                <a href="{{ route('home') }}"><img src="{{ asset('assets/imgs/theme/logo-sm.svg') }}" alt="logo" /></a>
            </div>
            <div class="mobile-menu-close close-style-wrap close-style-position-inherit">
                <button class="close-style search-close">
                    <i class="icon-top"></i>
                    <i class="icon-bottom"></i>
                </button>
            </div>
        </div>
        <div class="mobile-header-content-area">
            <div class="mobile-menu-wrap mobile-header-border">
                <!-- mobile menu start -->
                <nav>
                    <ul class="mobile-menu">
                        <x-nav-link url='home' name="messages.home" />
                        <x-nav-link url='category' name="messages.category" />
                        <x-nav-link url='about' name="messages.about" />
                        <x-nav-link url='livraison' name="messages.delivery" />
                        <x-nav-link url='contact' name="messages.contact" />
                        <li class="menu-item-has-children">
                            <span class="menu-expand"></span><a href="#">Devise</a>
                            <ul class="dropdown">
                                <li><a href="/devise/EUR">Euro</a></li>
                                <li><a href="/devise/CFA">CFA</a></li>
                            </ul>
                        </li>
                        <li class="menu-item-has-children">
                            <span class="menu-expand"></span><a href="#">Language</a>
                            <ul class="dropdown">
                                <li><a href="/lang/en">English</a></li>
                                <li><a href="/lang/fr">French</a></li>
                            </ul>
                        </li>
                    </ul>
                </nav>
                <!-- mobile menu end -->
            </div>
            <div class="mobile-header-info-wrap mobile-header-border">
                <div class="single-mobile-header-info">
                    <a href="#">+223 66 03 51 54 </a>
                </div>
            </div>
            <div class="mobile-social-icon">
                <h5 class="mb-15 text-grey-4">Follow Us</h5>
                <a href="http://www.facebook.com/DIMANCHEABAMAKO"><img
                        src="{{ asset('assets/imgs/theme/icons/icon-facebook.svg') }}" alt="facebook" /></a>
                <a href="#"><img src="{{ asset('assets/imgs/theme/icons/icon-instagram.svg') }}" alt="instagram" /></a>
            </div>
        </div>
    </div>
</div>