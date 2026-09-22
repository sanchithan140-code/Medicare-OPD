<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#073b72">
    <title>@yield('title','MEDICARE OPD')</title>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('assets/images/brand/medicare-app-icon.png') }}?v=4">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/brand/medicare-app-icon.png') }}?v=4">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    @stack('styles')
</head>
<body class="public-body">
<header class="public-header">
    <a class="brand" href="{{ route('home') }}"><img class="brand-logo" src="{{ asset('assets/images/brand/mediland-hospital-logo.png') }}" alt="Mediland Hospital logo"><span><strong>MEDICARE</strong><small>Mediland Hospital OPD</small></span></a>
    <nav aria-label="Public navigation"><a href="{{ route('home') }}">Home</a><a href="{{ route('home') }}#doctors">Our Doctors</a><a href="{{ route('home') }}#about">About</a><a href="{{ route('home') }}#contact">Contact</a>@auth<a class="btn btn-primary" href="{{ route(auth()->user()->dashboardRoute()) }}">Dashboard</a>@else<a href="{{ route('login') }}">Login</a><a class="btn btn-primary" href="{{ route('register') }}">Create account</a>@endauth</nav>
</header>
@yield('content')
<footer class="public-footer"><div><div class="brand brand-light"><img class="brand-logo" src="{{ asset('assets/images/brand/mediland-hospital-logo.png') }}" alt="Mediland Hospital logo"><span><strong>MEDICARE</strong><small>Compassionate care, beautifully connected</small></span></div><p>Secure appointments, connected health records, laboratory services and payments—all supporting your journey to better health.</p></div><div class="footer-hospital"><strong>Mediland Hospital</strong><span>Kalmunai, Sri Lanka</span><small>Every step forward is a step toward better health.</small></div><div class="footer-socials" aria-label="Mediland Hospital social media">
    <div class="footer-social">
        <svg class="social-logo social-facebook" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.095 10.125 24v-8.437H7.078v-3.49h3.047v-2.66c0-3.025 1.792-4.697 4.533-4.697 1.312 0 2.686.236 2.686.236v2.971h-1.513c-1.491 0-1.956.931-1.956 1.887v2.263h3.328l-.532 3.49h-2.796V24C19.612 23.095 24 18.1 24 12.073Z"/></svg>
        <span><small>Facebook</small><strong>Mediland Hospital Official</strong></span>
    </div>
    <div class="footer-social">
        <svg class="social-logo social-instagram" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
        <span><small>Instagram</small><strong>Mediland Hospital Official</strong></span>
    </div>
    <a class="footer-social" href="https://wa.me/94783471110" target="_blank" rel="noopener noreferrer" aria-label="Chat with Mediland Hospital on WhatsApp at +94783471110">
        <svg class="social-logo social-whatsapp" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" d="M21 11.5a9 9 0 0 1-13.4 7.85L3 21l1.5-4.65A9 9 0 1 1 21 11.5Z"/><path fill="currentColor" d="M8.1 6.9c-.3 0-.6.1-.8.4-.3.3-1 1-.9 2.3.1 1.4 1 2.7 1.2 2.9.2.2 2 3.2 5 4.2 2.4.8 3 .5 3.5.1.5-.4.9-1.2 1-1.7.1-.4 0-.6-.2-.7l-2.2-1.1c-.2-.1-.4-.1-.6.2l-.9 1c-.2.2-.3.2-.6.1a8 8 0 0 1-3.4-3c-.2-.3 0-.4.1-.6l.6-.8c.1-.2.2-.4.1-.6L9 7.3c-.1-.3-.3-.4-.5-.4Z"/></svg>
        <span><small>WhatsApp</small><strong>+94783471110</strong></span>
    </a>
</div></footer>
<script src="{{ asset('assets/js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
