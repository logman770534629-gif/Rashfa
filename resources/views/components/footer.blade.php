@props(['whatsapp' => null, 'instagram' => null, 'x' => null, 'website' => '#'])
@php
    $brand = (string) setting('brand_name', 'رشة');
    $siteUrl = safe_url(setting('contact.website') ?: $website) ?: '#';
    $email = trim((string) setting('contact.email', 'hello@rashfa.test'));

    $chips = collect([
        setting('hero_badge', 'طازج ١٠٠٪'),
        setting('hero_proof_1', 'مكوّنات حقيقية'),
        setting('hero_proof_2', 'يُحضّر عند الطلب'),
    ])->filter()->unique()->values();

    $bubbles = [
        ['x' => 5,  's' => 9,  'd' => 12, 'delay' => -2,  'dx' => -16],
        ['x' => 16, 's' => 15, 'd' => 16, 'delay' => -6,  'dx' => 12],
        ['x' => 29, 's' => 7,  'd' => 10, 'delay' => -4,  'dx' => -8],
        ['x' => 41, 's' => 20, 'd' => 19, 'delay' => -9,  'dx' => 20],
        ['x' => 54, 's' => 11, 'd' => 14, 'delay' => -1,  'dx' => -12],
        ['x' => 66, 's' => 8,  'd' => 11, 'delay' => -7,  'dx' => 8],
        ['x' => 77, 's' => 17, 'd' => 18, 'delay' => -11, 'dx' => -18],
        ['x' => 88, 's' => 12, 'd' => 13, 'delay' => -3,  'dx' => 14],
        ['x' => 95, 's' => 6,  'd' => 15, 'delay' => -8,  'dx' => -6],
    ];
@endphp
<footer class="site-footer" data-site-footer>
    <span class="footer-line" aria-hidden="true"></span>
    <div class="footer-aurora" aria-hidden="true" data-footer-aurora></div>
    <div class="footer-bubbles" aria-hidden="true">
        @foreach($bubbles as $bubble)
            <span style="--x: {{ $bubble['x'] }}%; --s: {{ $bubble['s'] }}px; --d: {{ $bubble['d'] }}s; --delay: {{ $bubble['delay'] }}s; --dx: {{ $bubble['dx'] }}px;"></span>
        @endforeach
    </div>
    <div class="footer-orb" aria-hidden="true"></div>
    <div class="fw fw-back" aria-hidden="true" data-footer-word="back">{{ setting('footer_word_back', 'SUN BIRD') }}</div>
    <div class="fw fw-front" aria-hidden="true" data-footer-word="front">{{ setting('footer_word_front', 'رشة') }}</div>

    <div class="footer-top">
        <div class="footer-pitch">
            <p class="eyebrow"{{ setting_style_attr('footer_eyebrow') }}><span></span> {{ setting('footer_eyebrow', 'رشة أخبار موسمية') }}</p>
            <h2{{ setting_style_attr('footer_title') }}>{{ title_html(setting('footer_title', 'ابقَ قريباً من الموسم.')) }}</h2>
            <p class="footer-note">{{ ui('newsletter_note') }}</p>
            <form class="newsletter-form" action="#" method="post" onsubmit="return false" data-newsletter><label for="newsletter-email"{{ setting_style_attr('footer_newsletter_label') }}>{{ setting('footer_newsletter_label', 'بريدك الإلكتروني') }}</label><input id="newsletter-email" type="email" placeholder="{{ setting('footer_newsletter_placeholder', 'name@example.com') }}" autocomplete="email"><button type="submit" aria-label="{{ ui('subscribe') }}">{{ ui('arrow_next') }}</button></form>
            @if($chips->isNotEmpty())
                <div class="footer-chips" role="list" aria-label="{{ ui('footer_highlights') }}">
                    @foreach($chips as $chip)
                        <span class="footer-chip" role="listitem">{{ $chip }}</span>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="footer-side">
            <div class="footer-badge-row">
                <div class="footer-seal" aria-hidden="true" data-footer-seal>
                    <svg class="seal-ring" viewBox="0 0 120 120" focusable="false">
                        <defs><path id="footer-seal-path" d="M60,60 m-44,0 a44,44 0 1,1 88,0 a44,44 0 1,1 -88,0" /></defs>
                        <text><textPath href="#footer-seal-path" startOffset="0" textLength="276" lengthAdjust="spacing">SUN BIRD · FRESH JUICE · SUN BIRD · FRESH · </textPath></text>
                    </svg>
                    <span class="seal-core">✦</span>
                </div>
                <span class="footer-logo-wrap">
                    <img class="footer-logo" src="{{ setting_image('footer_logo', 'media/rashfa/غلاف شعار شكل داري.png') }}" alt="{{ setting('footer_logo_alt', 'سن بيرد') }}" width="240" height="240" loading="lazy" decoding="async">
                </span>
            </div>
            <nav class="foot-links" aria-label="{{ ui('quick_links') }}"><a href="#top"{{ setting_style_attr('footer_link_home') }}>{{ setting('footer_link_home', 'الرئيسية') }}</a><a href="#flavors"{{ setting_style_attr('footer_link_flavors') }}>{{ setting('footer_link_flavors', 'النكهات') }}</a><a href="#story"{{ setting_style_attr('footer_link_story') }}>{{ setting('footer_link_story', 'الحكاية') }}</a><a href="#sunbird-story">{{ setting('nav_sunbird', 'نبذة عن سن بيرد') }}</a></nav>
            <div class="foot-social"><x-social-links :whatsapp="$whatsapp" :instagram="$instagram" :x="$x" /></div>
            <x-map-button variant="footer" class="foot-map" />
        </div>
    </div>

    <div class="footer-bottom">
        <div class="footer-sign">
            <a class="wordmark" href="#top"><span>{{ mb_substr($brand, 0, 1) }}</span>{{ mb_substr($brand, 1) }}</a>
            <a class="footer-top-link" href="#top" aria-label="{{ ui('back_to_top') }}" data-footer-top><span aria-hidden="true">↑</span></a>
        </div>
        <p{{ setting_style_attr('footer_tagline') }}>{{ setting('footer_tagline', 'صُنعت على مهل، لتُرتشف على مهل.') }}</p>
        <div><a href="{{ $siteUrl }}"{{ setting_style_attr('footer_site_label') }}>{{ setting('footer_site_label', 'موقعنا') }}</a>@if($email !== '')<a href="mailto:{{ $email }}"{{ setting_style_attr('footer_contact_label') }}>{{ setting('footer_contact_label', 'تواصل') }}</a>@endif</div>
    </div>
</footer>
