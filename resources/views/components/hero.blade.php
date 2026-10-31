@php
    $badgeWords = preg_split('/\s+/u', trim((string) setting('hero_badge', 'طازج ١٠٠٪')), -1, PREG_SPLIT_NO_EMPTY);
    $badgeLast = array_pop($badgeWords);
    $badgeRest = implode(' ', $badgeWords);
@endphp
<section class="hero" aria-labelledby="hero-title">
    <div class="hero-blobs" aria-hidden="true">
        <span class="blob blob-theme"></span>
        <span class="blob blob-mango"></span>
        <span class="blob blob-berry"></span>
        <span class="blob blob-leaf"></span>
    </div>

    <div class="hero-text">
        <p class="eyebrow"{{ setting_style_attr('hero_eyebrow') }}><span></span> {{ setting('hero_eyebrow', 'غرفة تذوق رقمية') }}</p>
        <h1 id="hero-title"{{ setting_style_attr('hero_title') }}>{{ title_html(setting('hero_title', 'رشة تُصمَّم على ذوقك.'), true) }}</h1>
    </div>

    <div class="hero-stage">
    <div class="hero-word" aria-hidden="true"><span>{{ setting('hero_word', 'رشة') }}</span></div>
    <div class="hero-frame" data-depth="0.8">
        <div class="hero-float">
            <div class="bottle-glass-bg"></div>
            <img class="hero-bottle" src="{{ setting_image('hero_bottle_image', 'media/rashfa/bottle-poster.webp') }}" alt="{{ setting('hero_bottle_alt', 'زجاجة عصير رشة') }}" width="1080" height="1080" style="position: relative; z-index: 2;" fetchpriority="high">
        </div>
    </div>
    <div class="hero-tag" data-depth="1.4"><span>{{ setting('hero_tag_number', '01') }}</span><p>{{ setting('hero_tag_label', 'إصدار الموسم') }}<br><strong>{{ setting('hero_tag_value', 'خوخ مشمشي') }}</strong></p></div>
    <div class="hero-badge" data-depth="1.1">@if($badgeRest !== ''){{ $badgeRest }}<br>@endif<strong>{{ $badgeLast }}</strong></div>
</div>

    <div class="hero-aside">
        <p class="hero-lead"{{ setting_style_attr('hero_lead') }}>{{ setting('hero_lead', 'عصائر طازجة، وكل نكهة تلوّن الأزرار والبطاقات والتوهج. اختر نكهتك وشاهد اللمعة تتغيّر.') }}</p>
        <div class="hero-actions"><a class="button button-primary" href="#flavors">{{ setting('hero_cta_primary', 'اكتشف النكهات') }} <span>{{ ui('arrow_next') }}</span></a><a class="text-link" href="#story">{{ setting('hero_cta_secondary', 'اقرأ الحكاية') }} <span>{{ ui('arrow_diag') }}</span></a></div>
        <div class="hero-proof"><span>{{ setting('hero_proof_1', 'مكوّنات حقيقية') }}</span><i></i><span>{{ setting('hero_proof_2', 'يُحضّر عند الطلب') }}</span></div>
    </div>

    <a class="scroll-cue" href="#flavors" aria-label="{{ setting('hero_scroll_cue', 'اسحب لتتذوق') }}"><span>{{ setting('hero_scroll_cue', 'اسحب لتتذوق') }}</span><i></i></a>
</section>
