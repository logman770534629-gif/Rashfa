<x-layouts.app :title="setting('site_title', 'رشة | غرفة تذوق رقمية')">
    <x-sunbird-intro :whatsapp="$whatsapp ?? null" />

    <div id="rashfa-site" class="opacity-0">
        <x-nav />
        <main id="main-content">
            <x-hero />
            <x-flavor-rail :flavors="$flavors" />
            <x-story :story-phases="$storyPhases ?? []" />
            <section class="ingredients-section" aria-labelledby="ingredients-title">
                <div class="ingredients-heading">
                    <p class="eyebrow"><span></span> {{ setting('ingredients_eyebrow', 'ما في الزجاجة؟') }}</p>
                    <h2 id="ingredients-title"{{ setting_style_attr('ingredients_title') }}>{{ title_html(setting('ingredients_title', 'المكوّن يتكلم.')) }}</h2>
                    <p{{ setting_style_attr('ingredients_lead') }}>{{ setting('ingredients_lead', 'نحافظ على القائمة قصيرة حتى تبقى النكهة واضحة.') }}</p>
                </div>
                <div class="ingredients-grid">
                    @forelse($ingredients as $index => $ingredient)
                        <x-ingredient-card :ingredient="$ingredient" :index="$index" />
                    @empty
                        <x-ingredient-card :ingredient="(object) ['name_ar' => ui('ingredient_default'), 'name_en' => ui('ingredient_default'), 'description_ar' => null]" :index="0" />
                    @endforelse
                </div>
            </section>
            <x-sunbird-story />
        </main>
        <x-footer :whatsapp="$whatsapp ?? null" />
    </div>
</x-layouts.app>
