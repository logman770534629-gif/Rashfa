<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ locale_dir() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#F9F6F0">
    <meta name="description" content="{{ setting('site_description', 'رشة — عصائر طازجة بنكهات تغيّر لون كل شيء.') }}">
    <link rel="alternate" hreflang="ar" href="{{ route('lang.switch', 'ar') }}">
    <link rel="alternate" hreflang="en" href="{{ route('lang.switch', 'en') }}">
    <title>{{ $title ?? setting('site_title', 'رشة | عصائر طازجة') }}</title>
    <script>/* الموقع يبدأ دائماً من الأعلى بعد الافتتاحية: نمنع استعادة التمرير التلقائية للمتصفح عند التحديث */try{history.scrollRestoration='manual';}catch(e){}document.documentElement.classList.add('js');try{if(sessionStorage.getItem('rashfa:lang')){document.documentElement.classList.add('lang-switching');}}catch(e){}</script>
    <noscript><style>#rashfa-site{opacity:1!important}#sunbird-intro{display:none!important}</style></noscript>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body id="top" class="lang-{{ app()->getLocale() }}">
    <a class="skip-link" href="#main-content">{{ ui('skip') }}</a>
    <div class="scroll-progress" aria-hidden="true" data-scroll-progress></div>
    {{ $slot }}
    <div class="lang-veil" aria-hidden="true" data-lang-veil></div>
</body>
</html>
