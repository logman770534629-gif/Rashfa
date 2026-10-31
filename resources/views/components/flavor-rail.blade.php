@php
    // النكهات تُقرأ حصرياً من قاعدة البيانات — لا بطاقات وهمية.
    $cards = collect();
    foreach ($flavors as $f) {
        $cards->push([
            'name' => loc($f, 'name'),
            'color' => $f->accent_color ?: '#FF7A1A',
            'cat' => loc($f->category, 'name', ui('category_default')),
            'tag' => $f->tags_json[0] ?? ui('tag_default'),
            'desc' => loc($f, 'short_desc'),
            'img' => media_url($f->primaryImage?->media?->storage_path, 'media/rashfa/bottle-poster.webp'),
            'alt' => loc($f->primaryImage, 'alt', loc($f, 'name')),
            'fx' => image_effects_class($f->primaryImage?->effects_json),
        ]);
    }
    // عندما يمتلئ الصف الأول (أكثر من 12 نكهة) ينتقل الشريط تلقائياً إلى عرض ملتف بدل التمرير الأفقي.
    $railMode = $cards->count() > 12 ? 'wrap' : 'scroll';

    // شريط النكهات: صف رئيسي بأسماء النكهات ونقاط بلونها، وصف خلفي بعبارات التصنيفات.
    $marqueeItems = $cards->map(fn ($card) => ['name' => $card['name'], 'color' => $card['color']])->values();
    if ($marqueeItems->isEmpty()) {
        $marqueeItems = collect([['name' => setting('brand_name', 'رشة'), 'color' => '#FF7A1A']]);
    }
    $marqueeRepeat = max(2, (int) ceil(9 / max(1, $marqueeItems->count())));

    $marqueeGhost = collect([
        setting('flavors_eyebrow', 'اختَر مزاج الرشة'),
        setting('sunbird_tagline', 'انتعاش الطبيعة في كل رشفة'),
    ])->merge($cards->pluck('cat'))->filter()->unique()->values();
    $marqueeGhostRepeat = max(2, (int) ceil(10 / max(1, $marqueeGhost->count())));
@endphp
<section id="flavors" class="flavors-section" aria-labelledby="flavors-title">
    <div class="marquee" data-marquee data-marquee-speed="{{ max(26, $cards->count() * 4) }}" style="--marquee-duration: {{ max(26, $cards->count() * 4) }}s" aria-hidden="true">
        <span class="marquee-glow"></span>
        <div class="marquee-row marquee-row--main">
            <div class="marquee-track" data-marquee-track>
                @for($loop_i = 0; $loop_i < 2; $loop_i++)
                    <div class="marquee-group">
                        @for($rep = 0; $rep < $marqueeRepeat; $rep++)
                            @foreach($marqueeItems as $item)
                                <span class="marquee-item" style="--dot: {{ $item['color'] }}"><i class="marquee-dot"></i>{{ $item['name'] }}</span>
                                <i class="marquee-star">✦</i>
                            @endforeach
                        @endfor
                    </div>
                @endfor
            </div>
        </div>
        <div class="marquee-row marquee-row--ghost">
            <div class="marquee-track marquee-track--reverse" data-marquee-track data-marquee-direction="right" data-marquee-speed="{{ max(34, $cards->count() * 5) }}">
                @for($loop_i = 0; $loop_i < 2; $loop_i++)
                    <div class="marquee-group">
                        @for($rep = 0; $rep < $marqueeGhostRepeat; $rep++)
                            @foreach($marqueeGhost as $word)
                                <span class="marquee-item marquee-item--ghost">{{ $word }}</span>
                                <i class="marquee-star marquee-star--ghost">✧</i>
                            @endforeach
                        @endfor
                    </div>
                @endfor
            </div>
        </div>
    </div>

    <div class="section-heading"><div><p class="eyebrow"><span></span> {{ setting('flavors_eyebrow', 'اختَر مزاج الرشة') }}</p><h2 id="flavors-title"{{ setting_style_attr('flavors_title') }}>{{ title_html(setting('flavors_title', 'رحلة النكهات.')) }}</h2></div><p{{ setting_style_attr('flavors_lead') }}>{{ setting('flavors_lead', 'مرّر الماوس فوق أي بطاقة وشاهد الموقع كله يتلوّن بنكهتها.') }}</p></div>
    @if($railMode === 'scroll' && $cards->count() > 0)
    <div class="rail-controls" aria-label="{{ ui('rail_nav') }}"><button type="button" data-rail-prev aria-label="{{ ui('rail_prev') }}">{{ ui('arrow_prev') }}</button><span data-rail-count>01 / {{ str_pad((string) $cards->count(), 2, '0', STR_PAD_LEFT) }}</span><button type="button" data-rail-next aria-label="{{ ui('rail_next') }}">{{ ui('arrow_next') }}</button></div>
    @endif

    @if($cards->count() === 0)
        <p class="flavors-empty">{{ ui('rail_empty') }}</p>
    @endif

    <div class="flavor-rail" data-flavor-rail data-rail-mode="{{ $railMode }}" tabindex="0" aria-label="{{ ui('rail_gallery') }}">
        @foreach($cards as $index => $card)
            <article class="flavor-card liquid-card" data-flavor-card data-liquid-card style="--flavor-accent: {{ $card['color'] }}">
                @if($card['img'])
                    <div class="flavor-media media-frame {{ $card['fx'] }}">
                        <span class="flavor-number" aria-hidden="true">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <img class="media-bg" src="{{ $card['img'] }}" alt="" aria-hidden="true" loading="lazy" decoding="async">
                        <img class="media-img" src="{{ $card['img'] }}" alt="{{ $card['alt'] }}" loading="lazy" decoding="async">
                    </div>
                @else
                    <div class="flavor-media media-frame flavor-placeholder" role="img" aria-label="{{ $card['name'] }}">
                        <span class="flavor-number" aria-hidden="true">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <b aria-hidden="true">{{ mb_substr($card['name'], 0, 1) }}</b>
                    </div>
                @endif
                <div class="flavor-content">
                    <p class="flavor-meta">{{ $card['cat'] }} <span>•</span> {{ $card['tag'] }}</p>
                    <h3>{{ $card['name'] }}</h3>
                    <p>{{ $card['desc'] }}</p>
                    <div class="flavor-footer">
                        <span class="flavor-swatch" aria-hidden="true"><i></i></span>
                    </div>
                </div>
                <span class="flavor-glare" aria-hidden="true"></span>
            </article>
        @endforeach
    </div>
    @if($railMode === 'scroll' && $cards->count() > 0)
    <div class="rail-scrub" data-rail-scrub role="slider" tabindex="0" aria-label="{{ ui('rail_nav') }}" aria-valuemin="1" aria-valuemax="{{ $cards->count() }}" aria-valuenow="1">
        <div class="rail-progress" aria-hidden="true"><span data-rail-progress></span></div>
        <i class="rail-thumb" data-rail-thumb aria-hidden="true"></i>
    </div>
    @endif
</section>
