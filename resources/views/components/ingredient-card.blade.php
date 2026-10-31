@props(['ingredient', 'index'])
@php
    $fallbacks = ['photo-1600271886742-f049cd451bba.jpeg','photo-1614707585284-9cb9fc018387.jpeg','kiwi.webp','photo-1603569283847-aa295f0d016a.jpeg','bottle-poster.webp','photo-1534353473418-4cfa6c56fd38.jpeg'];
    $slot = $index % count($fallbacks);
    $ingImg = setting_image('ingredient_img_' . ($slot + 1), 'media/rashfa/' . $fallbacks[$slot]);
    $own = data_get($ingredient, 'image.storage_path');
    if ($own) { $ingImg = media_url($own); }
    $ingName = loc($ingredient, 'name', ui('ingredient_default'));
    $ingDesc = loc($ingredient, 'description');
    $fxClass = image_effects_class(data_get($ingredient, 'image_effects'));
@endphp
<article class="ingredient-card aspect-square" data-flip-card>
    <div class="ingredient-inner">
        <div class="ingredient-face ingredient-front"><div class="media-frame aspect-square {{ $fxClass }}"><img class="media-img w-full h-full object-cover" src="{{ $ingImg }}" alt="" width="800" height="800" loading="lazy" decoding="async" draggable="false"></div><div><span>0{{ $index + 1 }}</span><h3>{{ $ingName }}</h3><button type="button" data-flip-trigger aria-expanded="false">{{ setting('ingredient_btn_more', 'اعرف المصدر') }} <b>{{ ui('arrow_diag') }}</b></button></div></div>
        <div class="ingredient-face ingredient-back" aria-hidden="true" inert><span class="ingredient-mark">{{ mb_substr((string) setting('brand_name', 'رشة'), 0, 1) }}</span><p class="eyebrow"><span></span> {{ setting('ingredient_back_eyebrow', 'أصل المكوّن') }}</p><h3>{{ $ingName }}</h3><p>{{ $ingDesc ?: setting('ingredient_default_desc', 'نختاره يدوياً من موسمٍ واضح ونتركه يتكلم بنكهته.') }}</p><dl><div><dt>{{ setting('ingredient_fact1_label', 'القطف') }}</dt><dd>{{ setting('ingredient_fact1_value', 'يدوي') }}</dd></div><div><dt>{{ setting('ingredient_fact2_label', 'المصدر') }}</dt><dd>{{ setting('ingredient_fact2_value', 'موسمي') }}</dd></div></dl><button type="button" data-flip-trigger>{{ setting('ingredient_btn_back', 'عودة') }} <b>↗</b></button></div>
    </div>
</article>
