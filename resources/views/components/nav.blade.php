@php($brand = (string) setting('brand_name', 'رشة'))
<header class="site-nav" data-site-nav>
    <a class="wordmark" href="{{ route('home') }}" aria-label="{{ ui('home_aria', ['brand' => $brand]) }}"><span>{{ mb_substr($brand, 0, 1) }}</span>{{ mb_substr($brand, 1) }}</a>
    <nav class="nav-links" id="site-menu" data-nav-panel aria-label="{{ ui('main_nav') }}">
        <a href="#flavors" data-nav-link><span class="nl-text">{{ setting('nav_flavors', 'النكهات') }}</span></a>
        <a href="#story" data-nav-link><span class="nl-text">{{ setting('nav_story', 'الحكاية') }}</span></a>
        <a href="#sunbird-story" data-nav-link><span class="nl-text">{{ setting('nav_sunbird', 'نبذة عن سن بيرد') }}</span></a>
        <a href="#flavors" class="nav-panel-cta">{{ setting('nav_order', 'تصفّح النكهات') }} <span aria-hidden="true">{{ ui('arrow_diag') }}</span></a>
    </nav>
    <div class="nav-end">
        <x-lang-switch variant="nav" />
        <a href="#flavors" class="nav-order"><span class="no-label">{{ setting('nav_order', 'تصفّح النكهات') }}</span> <span class="no-arrow" aria-hidden="true">{{ ui('arrow_diag') }}</span></a>
        <button type="button" class="nav-toggle" data-nav-toggle aria-expanded="false" aria-controls="site-menu" aria-label="{{ ui('menu') }}"><span></span><span></span><span></span></button>
    </div>
</header>
