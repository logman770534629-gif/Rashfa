@php
    $user = auth('admin')->user();
    $tints = ['mint', 'sand', 'sky', 'rose'];
    $quick = [
        ['route' => route('admin.content.index'),     'icon' => '✎', 'title' => 'تعديل المحتوى',  'text' => 'النصوص والألوان والصور وتنسيق الخطوط'],
        ['route' => route('admin.products.index'),    'icon' => '◍', 'title' => 'إدارة النكهات',  'text' => 'الاسم والوصف واللون وصورة كل نكهة'],
        ['route' => route('admin.ingredients.index'), 'icon' => '❀', 'title' => 'إدارة المكوّنات', 'text' => 'أضف مكوّناً أو عدّل صورته ووصفه'],
        ['route' => route('admin.media.index'),       'icon' => '▣', 'title' => 'إدارة الوسائط',  'text' => 'ارفع الصور وأدرها من مكان واحد'],
    ];
@endphp
<x-layouts.admin title="نظرة عامة | رشة">
<div class="dash">
    <header class="dash-head">
        <div>
            <p class="dash-kicker">أهلاً، {{ $user?->full_name ?? 'المدير' }}</p>
            <h1>لوحة التحكم</h1>
        </div>
        <div class="dash-actions">
            <a class="dash-btn dash-btn-light" href="{{ route('home') }}" target="_blank" rel="noopener">معاينة الموقع ↗</a>
            <a class="dash-btn dash-btn-primary" href="{{ route('admin.products.create') }}">+ نكهة جديدة</a>
        </div>
    </header>

    <section class="dash-stats" aria-label="إحصائيات">
        @foreach($stats as $i => $stat)
            <article class="dash-stat dash-{{ $tints[$i % 4] }}">
                <p>{{ $stat['label'] }}</p>
                <strong>{{ $stat['value'] }}</strong>
                <span>{{ $stat['hint'] }}</span>
            </article>
        @endforeach
    </section>

    <section class="dash-panel">
        <div class="dash-panel-head"><h2>روابط سريعة</h2></div>
        <div class="dash-quick">
            @foreach($quick as $q)
                <a class="dash-quick-card" href="{{ $q['route'] }}">
                    <i aria-hidden="true">{{ $q['icon'] }}</i>
                    <strong>{{ $q['title'] }}</strong>
                    <span>{{ $q['text'] }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="dash-panel">
        <div class="dash-panel-head"><h2>أقسام محتوى الموقع</h2><a href="{{ route('admin.content.index') }}">عرض الكل ←</a></div>
        <div class="dash-chips">
            @foreach(cms_schema() as $key => $group)
                <a href="{{ route('admin.content.index', $key) }}"><i>{{ $group['icon'] ?? '•' }}</i> {{ $group['label'] }} <small>{{ count($group['fields']) }}</small></a>
            @endforeach
        </div>
    </section>
</div>
</x-layouts.admin>
