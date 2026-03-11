<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @php $landing = asset('landing'); @endphp
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'ALOS - Legal Office Management')</title>
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $landing }}/assets/images/favicons/apple-touch-icon.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $landing }}/assets/images/favicons/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="{{ $landing }}/assets/images/favicons/favicon-16x16.png" />
    <link rel="manifest" href="{{ $landing }}/assets/images/favicons/site.webmanifest" />
    <meta name="description" content="ALOS - Legal Office Management. Professional solution for law firms and legal offices." />
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Marcellus&amp;family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&amp;family=Whisper&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ $landing }}/assets/vendors/bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" href="{{ $landing }}/assets/vendors/bootstrap-select/bootstrap-select.min.css" />
    <link rel="stylesheet" href="{{ $landing }}/assets/vendors/animate/animate.min.css" />
    <link rel="stylesheet" href="{{ $landing }}/assets/vendors/fontawesome/css/all.min.css" />
    <link rel="stylesheet" href="{{ $landing }}/assets/vendors/jquery-ui/jquery-ui.css" />
    <link rel="stylesheet" href="{{ $landing }}/assets/vendors/jarallax/jarallax.css" />
    <link rel="stylesheet" href="{{ $landing }}/assets/vendors/jquery-magnific-popup/jquery.magnific-popup.css" />
    <link rel="stylesheet" href="{{ $landing }}/assets/vendors/nouislider/nouislider.min.css" />
    <link rel="stylesheet" href="{{ $landing }}/assets/vendors/nouislider/nouislider.pips.css" />
    <link rel="stylesheet" href="{{ $landing }}/assets/vendors/tiny-slider/tiny-slider.css" />
    <link rel="stylesheet" href="{{ $landing }}/assets/vendors/procounsel-icons/style.css" />
    <link rel="stylesheet" href="{{ $landing }}/assets/vendors/slick/slick.css">
    <link rel="stylesheet" href="{{ $landing }}/assets/vendors/owl-carousel/css/owl.carousel.min.css" />
    <link rel="stylesheet" href="{{ $landing }}/assets/vendors/owl-carousel/css/owl.theme.default.min.css" />
    <link rel="stylesheet" href="{{ $landing }}/assets/css/procounsel.css" />
    @stack('styles')
</head>
<body class="custom-cursor">
    <div class="custom-cursor__cursor"></div>
    <div class="custom-cursor__cursor-two"></div>
    <div class="preloader">
        <div class="preloader__image" style="background-image: url({{ $landing }}/assets/images/loader.png);"></div>
    </div>
    <div class="page-wrapper">
        <header class="main-header sticky-header sticky-header--normal">
            <div class="container-fluid">
                <div class="main-header__inner">
                    <div class="main-header__logo">
                        <a href="{{ url('/') }}">
                            <img src="{{ $landing }}/assets/images/logo-light.png" alt="ALOS" width="160">
                        </a>
                    </div>
                    <nav class="main-header__nav main-menu">
                        <ul class="main-menu__list">
                            <li><a href="{{ url('/') }}">Home</a></li>
                            <li><a href="{{ url('/') }}#about">About</a></li>
                            <li><a href="{{ url('/') }}#services">Services</a></li>
                            <li><a href="{{ url('/') }}#contact">Contact</a></li>
                            <li><a href="{{ route('login') }}">Login</a></li>
                        </ul>
                    </nav>
                    <div class="main-header__right">
                        <div class="mobile-nav__btn mobile-nav__toggler">
                            <span></span><span></span><span></span>
                        </div>
                        <div class="main-header__btn">
                            <a href="{{ route('register') }}" class="procounsel-btn">
                                <i>Get started</i><span>Get started</span>
                            </a>
                        </div>
                        <div class="main-header__info">
                            <div class="main-header__info__icon">
                                <i class="icon-phone-1"></i>
                                <span class="main-header__info__icon__zoom"><i class="icon-phone-1"></i></span>
                            </div>
                            <div>
                                <span class="main-header__info__text">Call anytime</span>
                                <a href="tel:+3035550105">(303) 555-0105</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        @yield('content')

        <footer class="main-footer">
            <div class="main-footer__bg" style="background-image: url({{ $landing }}/assets/images/backgrounds/footer-bg.png);"></div>
            <div class="main-footer__top">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6 col-xl-4 wow fadeInUp" data-wow-delay="00ms">
                            <div class="footer-widget footer-widget--about">
                                <a href="{{ url('/') }}" class="footer-widget__logo">
                                    <img src="{{ $landing }}/assets/images/logo-light.png" width="160" alt="ALOS">
                                </a>
                                <p class="footer-widget__text">Discover a unique approach with our dedicated attorneys, committed effective legal solutions.</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-2 wow fadeInUp" data-wow-delay="100ms">
                            <div class="footer-widget footer-widget--links">
                                <h2 class="footer-widget__title">Links</h2>
                                <ul class="list-unstyled footer-widget__links">
                                    <li><a href="{{ url('/') }}#about">About Us</a></li>
                                    <li><a href="{{ url('/') }}#services">Services</a></li>
                                    <li><a href="{{ url('/') }}#contact">Contact</a></li>
                                    <li><a href="{{ route('login') }}">Login</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-2 wow fadeInUp" data-wow-delay="200ms">
                            <div class="footer-widget footer-widget--links">
                                <h2 class="footer-widget__title">Explore</h2>
                                <ul class="list-unstyled footer-widget__links">
                                    <li><a href="{{ url('/') }}#services">What We Offer</a></li>
                                    <li><a href="{{ url('/') }}#about">Our Story</a></li>
                                    <li><a href="{{ route('register') }}">Get started</a></li>
                                    <li><a href="{{ url('/') }}#contact">Help Center</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4 wow fadeInUp" data-wow-delay="300ms">
                            <div class="footer-widget footer-widget--mail">
                                <h2 class="footer-widget__title">Signup for our latest news<br> & articles</h2>
                                <form action="#" class="footer-widget__newsletter mc-form">
                                    <input type="text" name="EMAIL" placeholder="Email Address">
                                    <button type="submit"><i class="icon-right-arrow-2"></i><span class="sr-only">submit</span></button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="main-footer__info">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="main-footer__info__inner">
                                    <div class="main-footer__info__pin"><i class="icon-pin"></i></div>
                                    <div class="main-footer__info__location">6391 Elgin St. Delaware <br>New York. USA</div>
                                    <ul class="list-unstyled main-footer__info__list">
                                        <li class="main-footer__info__item">
                                            <div class="main-footer__info__icon"><i class="icon-telephone-call-1"></i></div>
                                            <div class="main-footer__info__content"><p class="main-footer__info__text"><a href="tel:+9238008060">+92 3800 8060</a></p></div>
                                        </li>
                                        <li class="main-footer__info__item">
                                            <div class="main-footer__info__icon"><i class="icon-mail"></i></div>
                                            <div class="main-footer__info__content"><p class="main-footer__info__text"><a href="mailto:info@example.com">info@example.com</a></p></div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="main-footer__info__social">
                                    <a href="https://facebook.com/"><i class="icon-facebook"></i><span class="sr-only">Facebook</span></a>
                                    <a href="https://pinterest.com/"><i class="icon-pinterest"></i><span class="sr-only">Pinterest</span></a>
                                    <a href="https://twitter.com/"><i class="icon-twitter"></i><span class="sr-only">Twitter</span></a>
                                    <a href="https://youtube.com/"><i class="icon-youtube"></i><span class="sr-only">Youtube</span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="main-footer__bottom wow fadeInUp" data-wow-delay="00ms">
                <div class="container">
                    <div class="main-footer__bottom__inner">
                        <p class="main-footer__copyright">&copy; Copyright <span class="dynamic-year"></span> by ALOS - Legal Office Management.</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <div class="mobile-nav__wrapper">
        <div class="mobile-nav__overlay mobile-nav__toggler"></div>
        <div class="mobile-nav__content">
            <span class="mobile-nav__close mobile-nav__toggler"><i class="fa fa-times"></i></span>
            <div class="logo-box">
                <a href="{{ url('/') }}" aria-label="logo"><img src="{{ $landing }}/assets/images/logo-light.png" width="155" alt="ALOS" /></a>
            </div>
            <div class="mobile-nav__container"></div>
            <ul class="mobile-nav__contact list-unstyled">
                <li><i class="fa fa-envelope"></i><a href="mailto:needhelp@example.com">needhelp@example.com</a></li>
                <li><i class="fa fa-phone-alt"></i><a href="tel:666-888-0000">666 888 0000</a></li>
            </ul>
            <div class="mobile-nav__social">
                <a href="https://facebook.com/"><i class="icon-facebook"></i><span class="sr-only">Facebook</span></a>
                <a href="https://pinterest.com/"><i class="icon-pinterest"></i><span class="sr-only">Pinterest</span></a>
                <a href="https://twitter.com/"><i class="icon-twitter"></i><span class="sr-only">Twitter</span></a>
                <a href="https://youtube.com/"><i class="icon-youtube"></i><span class="sr-only">Youtube</span></a>
            </div>
        </div>
    </div>
    <div class="search-popup">
        <div class="search-popup__overlay search-toggler"></div>
        <div class="search-popup__content">
            <form role="search" method="get" class="search-popup__form" action="#">
                <input type="text" id="search" placeholder="Search Here..." />
                <button type="submit" aria-label="search submit" class="procounsel-btn"><i><i class="icon-search"></i></i><span><i class="icon-search"></i></span></button>
            </form>
        </div>
    </div>
    <a href="#" data-target="html" class="scroll-to-target scroll-to-top">
        <span class="scroll-to-top__text">back top</span>
        <span class="scroll-to-top__wrapper"><span class="scroll-to-top__inner"></span></span>
    </a>

    <script src="{{ $landing }}/assets/vendors/jquery/jquery-3.7.1.min.js"></script>
    <script src="{{ $landing }}/assets/vendors/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="{{ $landing }}/assets/vendors/bootstrap-select/bootstrap-select.min.js"></script>
    <script src="{{ $landing }}/assets/vendors/jarallax/jarallax.min.js"></script>
    <script src="{{ $landing }}/assets/vendors/jquery-ui/jquery-ui.js"></script>
    <script src="{{ $landing }}/assets/vendors/jquery-ajaxchimp/jquery.ajaxchimp.min.js"></script>
    <script src="{{ $landing }}/assets/vendors/jquery-appear/jquery.appear.min.js"></script>
    <script src="{{ $landing }}/assets/vendors/jquery-circle-progress/jquery.circle-progress.min.js"></script>
    <script src="{{ $landing }}/assets/vendors/jquery-magnific-popup/jquery.magnific-popup.min.js"></script>
    <script src="{{ $landing }}/assets/vendors/jquery-validate/jquery.validate.min.js"></script>
    <script src="{{ $landing }}/assets/vendors/nouislider/nouislider.min.js"></script>
    <script src="{{ $landing }}/assets/vendors/tiny-slider/tiny-slider.js"></script>
    <script src="{{ $landing }}/assets/vendors/wnumb/wNumb.min.js"></script>
    <script src="{{ $landing }}/assets/vendors/owl-carousel/js/owl.carousel.min.js"></script>
    <script src="{{ $landing }}/assets/vendors/wow/wow.js"></script>
    <script src="{{ $landing }}/assets/vendors/imagesloaded/imagesloaded.min.js"></script>
    <script src="{{ $landing }}/assets/vendors/isotope/isotope.js"></script>
    <script src="{{ $landing }}/assets/vendors/slick/slick.min.js"></script>
    <script src="{{ $landing }}/assets/vendors/tilt/tilt.jquery.js"></script>
    <script src="{{ $landing }}/assets/vendors/countdown/countdown.min.js"></script>
    <script src="{{ $landing }}/assets/vendors/jquery-circleType/jquery.circleType.js"></script>
    <script src="{{ $landing }}/assets/vendors/jquery-lettering/jquery.lettering.min.js"></script>
    <script src="{{ $landing }}/assets/vendors/gsap/gsap.js"></script>
    <script src="{{ $landing }}/assets/vendors/gsap/scrolltrigger.min.js"></script>
    <script src="{{ $landing }}/assets/vendors/gsap/splittext.min.js"></script>
    <script src="{{ $landing }}/assets/vendors/gsap/procounsel-split.js"></script>
    <script src="{{ $landing }}/assets/js/procounsel.js"></script>
    @stack('scripts')
</body>
</html>
