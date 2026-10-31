@php
    $chips = collect([1, 2, 3, 4])->map(fn ($n) => setting("sunbird_story_chip_{$n}"))->filter()->values();
    $defaultChips = [1 => 'طازجة ومنعشة', 2 => 'غنية بالفيتامينات والمعادن', 3 => 'خالية من المواد الحافظة', 4 => 'بلا ألوان صناعية'];
    if ($chips->isEmpty()) {
        $chips = collect($defaultChips)->values();
    }
    $bg   = setting_image('sunbird_story_bg', 'media/rashfa/photo-1600271886742-f049cd451bba.jpeg');
    $img1 = setting_image('sunbird_story_img1', 'media/rashfa/kiwi.webp');
    $img2 = setting_image('sunbird_story_img2', 'media/rashfa/photo-1614707585284-9cb9fc018387.jpeg');
@endphp
<section id="sunbird-story" class="sbs" data-sbs aria-labelledby="sbs-title">
    <div class="sbs-bg" aria-hidden="true" data-sbs-bg style="background-image:url('{{ $bg }}')"></div>
    <div class="sbs-shade" aria-hidden="true"></div>
    <div class="sbs-glow" aria-hidden="true" data-sbs-glow></div>
    <span class="sbs-bars" aria-hidden="true"><i></i><i></i></span>

    <figure class="sbs-float sbs-float--a" aria-hidden="true" data-sbs-float="-90"><img src="{{ $img1 }}" alt="" loading="lazy" decoding="async"></figure>
    <figure class="sbs-float sbs-float--b" aria-hidden="true" data-sbs-float="70"><img src="{{ $img2 }}" alt="" loading="lazy" decoding="async"></figure>

    <div class="sbs-inner">
        <p class="sbs-eyebrow" data-sbs-reveal{{ setting_style_attr('sunbird_story_eyebrow') }}><span></span> {{ setting('sunbird_story_eyebrow', 'قسم المشروبات والعصائر') }}</p>
        <h2 id="sbs-title" class="sbs-title"{{ setting_style_attr('sunbird_story_title') }}>{{ title_html(setting('sunbird_story_title', 'نبذة عن سن بيرد')) }}</h2>

        <p class="sbs-lead" data-sbs-lead{{ setting_style_attr('sunbird_story_lead') }}>{{ setting('sunbird_story_lead', 'نقدم في قسم المشروبات والعصائر بـ سن بيرد تشكيلة مميزة من المشروبات المصنوعة بعناية فائقة لتلبي أذواق جميع المستهلكين. نعتمد على أجود الفواكه الطبيعية والمكونات المختارة بعناية لتقديم طعم غني، منعش، وصحي يتماشى مع نمط الحياة العصري.') }}</p>

        <span class="sbs-rule" aria-hidden="true" data-sbs-rule></span>

        <div class="sbs-product" data-sbs-reveal>
            <p class="sbs-label">{{ setting('sunbird_story_products_label', 'تشكيلة المنتجات') }}</p>
            <h3{{ setting_style_attr('sunbird_story_products_title') }}>{{ setting('sunbird_story_products_title', 'عصائر الفواكه الطبيعية 100%') }}</h3>
            <p class="sbs-product-body"{{ setting_style_attr('sunbird_story_products_body') }}>{{ setting('sunbird_story_products_body', 'عصائر طازجة ومنعشة غنية بالفيتامينات والمعادن الأساسية، خالية تماماً من المواد الحافظة والألوان الصناعية.') }}</p>
            <ul class="sbs-chips">
                @foreach($chips as $chip)<li data-sbs-chip>{{ $chip }}</li>@endforeach
            </ul>
            <div class="sbs-actions">
                <a class="sbs-cta" href="#flavors"><span>{{ setting('sunbird_story_cta', 'اكتشف نكهاتنا') }}</span><b aria-hidden="true">{{ ui('arrow_next') }}</b></a>
                <x-map-button variant="story" />
            </div>
        </div>
    </div>
</section>
