@props(['storyPhases' => []])
@php
    $labels = [
        'harvest' => setting('story_label_harvest', 'من البستان'),
        'press'   => setting('story_label_press', 'في غرفة العصر'),
        'sip'     => setting('story_label_sip', 'إلى يدك'),
    ];
    $dbPhases = collect($storyPhases);

    if (setting('story_source', 'settings') === 'database' && $dbPhases->isNotEmpty()) {
        $phases = $dbPhases->values()->map(fn ($p) => [
            'order' => $p->phase_order,
            'label' => $labels[$p->phase_key] ?? setting('brand_name', 'رشة'),
            'title' => loc($p, 'title'),
            'body'  => loc($p, 'body'),
            'img'   => media_url($p->media?->storage_path, 'media/rashfa/bottle-poster.webp'),
        ]);
    } else {
        $keys = [1 => 'harvest', 2 => 'press', 3 => 'sip'];
        $defaults = [
            1 => ['نقطف في أوانها', 'نختار الفاكهة حين تقول النكهة نعم.', 'media/rashfa/photo-1600271886742-f049cd451bba.jpeg'],
            2 => ['نعصر على مهل', 'نعصر الفاكهة بلطف حتى يبقى الطعم صافياً.', 'media/rashfa/photo-1614707585284-9cb9fc018387.jpeg'],
            3 => ['نرشف بلا استعجال', 'تُحضَّر الرشة عند الطلب لتصلك طازجة.', 'media/rashfa/bottle-poster.webp'],
        ];
        $phases = collect([1, 2, 3])->map(fn ($n) => [
            'order' => $n,
            'label' => $labels[$keys[$n]],
            'title' => setting("story_p{$n}_title", $defaults[$n][0]),
            'body'  => setting("story_p{$n}_body", $defaults[$n][1]),
            'img'   => setting_image("story_p{$n}_image", $defaults[$n][2]),
        ]);
    }
@endphp
<section id="story" class="story-section" data-story>
    <div class="story-intro"><p class="eyebrow"><span></span> {{ setting('story_eyebrow', 'من البستان إلى الرشة') }}</p><h2{{ setting_style_attr('story_title') }}>{{ title_html(setting('story_title', 'لا نُسرّع الطعم.')) }}</h2><p{{ setting_style_attr('story_lead') }}>{{ setting('story_lead', 'ثلاث لحظات فقط تحفظ النكهة كما يجب أن تكون.') }}</p></div>
    <div class="story-track" data-story-track>
        <div class="story-sticky">
            <div class="story-media" data-story-media>
                @foreach($phases as $index => $phase)
                    <figure class="story-image {{ $index === 0 ? 'is-active' : '' }}" data-story-image="{{ $index }}"><div class="media-frame"><img class="media-bg" src="{{ $phase['img'] }}" alt="" aria-hidden="true" loading="lazy"><img class="media-img" src="{{ $phase['img'] }}" alt="{{ $phase['title'] }}" loading="lazy"></div><figcaption>{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }} / 03</figcaption></figure>
                @endforeach
            </div>
            <div class="story-copy" data-story-copy>
                @foreach($phases as $index => $phase)
                    <article class="story-step {{ $index === 0 ? 'is-active' : '' }}" data-story-step="{{ $index }}"><span class="story-index">0{{ $phase['order'] }}</span><p class="eyebrow">{{ $phase['label'] }}</p><h3>{{ $phase['title'] }}</h3><p>{{ $phase['body'] }}</p></article>
                @endforeach
                <div class="story-meter" aria-hidden="true"><i></i></div>
            </div>
        </div>
        <div class="story-sentinels" aria-hidden="true">@for($i = 0; $i < 3; $i++)<div data-story-sentinel="{{ $i }}"></div>@endfor</div>
    </div>
</section>
