<!DOCTYPE html>
{{-- v6: قائمة جانبية منزلقة (drawer) على الجوال والتابلت + زر قائمة، مع إبقاء كل روابط الإدارة ظاهرة. --}}
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex,nofollow"><meta name="theme-color" content="#07120e">
    <title>{{ $title ?? 'إدارة رشة' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=Noto+Kufi+Arabic:wght@500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-body">
    <div class="admin-shell">
        <div class="admin-overlay" data-nav-overlay hidden></div>
        <aside class="admin-sidebar" id="admin-sidebar" aria-label="تنقل الإدارة">
            <a class="admin-brand" href="{{ route('admin.dashboard') }}"><span>ر</span> شـة <small>STUDIO</small></a>
            <nav>
                <a href="{{ route('admin.dashboard') }}" @class(['active' => request()->routeIs('admin.dashboard')])><i>▦</i>نظرة عامة</a>
                @can('manage-content')<a href="{{ route('admin.content.index') }}" @class(['active' => request()->routeIs('admin.content.*')])><i>✎</i>محتوى الموقع</a>@endcan
                <a href="{{ route('admin.products.index') }}" @class(['active' => request()->routeIs('admin.products.*')])><i>◍</i>النكهات</a>
                <a href="{{ route('admin.ingredients.index') }}" @class(['active' => request()->routeIs('admin.ingredients.*')])><i>❀</i>المكوّنات</a>
                <a href="{{ route('admin.media.index') }}" @class(['active' => request()->routeIs('admin.media.*')])><i>▣</i>مكتبة الوسائط</a>
                @can('manage-settings')<a href="{{ route('admin.settings.index') }}" @class(['active' => request()->routeIs('admin.settings.*')])><i>⚙</i>الإعدادات</a>@endcan
            </nav>
            <div class="admin-sidebar-footer">
                <p>{{ (auth('admin')->user()?->full_name ?? 'المدير') }}<br><span>{{ (auth('admin')->user()?->role ?? 'super_admin') }}</span></p>
                <form action="{{ route('admin.logout') }}" method="POST">@csrf<button type="submit" class="text-button">تسجيل الخروج</button></form>
            </div>
        </aside>
        <main class="admin-main">
            @if(config('rashfa.preview_read_only'))
                <div class="flash" role="status">معاينة للقراءة فقط — حفظ التغييرات ورفع الملفات غير متاحين.</div>
            @endif
            <header class="admin-topbar"><button type="button" class="admin-menu-btn" data-nav-toggle aria-controls="admin-sidebar" aria-expanded="false" aria-label="فتح القائمة"><span></span><span></span><span></span></button><a href="{{ route('home') }}" target="_blank" rel="noopener">معاينة الموقع ↗</a><span class="admin-live"><i></i> لوحة التحكم</span></header>
            @if(session('status'))<div class="flash flash-success" role="status">{{ session('status') }}</div>@endif
            @if($errors->any())<div class="flash flash-error" role="alert"><strong>تعذر الحفظ.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            {{ $slot }}
        </main>
    </div>
<script>
    document.querySelectorAll('[data-dropzone]').forEach(function (dz) {
        var input = dz.querySelector('input[type=file]'), img = dz.querySelector('.dz-preview');
        function show() {
            if (input.files && input.files[0]) { img.src = URL.createObjectURL(input.files[0]); img.hidden = false; dz.classList.add('has-file'); dz.querySelector('strong').textContent = input.files[0].name; }
        }
        input.addEventListener('change', show);
        ['dragenter', 'dragover'].forEach(function (ev) { dz.addEventListener(ev, function (e) { e.preventDefault(); dz.classList.add('is-over'); }); });
        ['dragleave', 'drop'].forEach(function (ev) { dz.addEventListener(ev, function (e) { e.preventDefault(); dz.classList.remove('is-over'); }); });
        dz.addEventListener('drop', function (e) { if (e.dataTransfer.files.length) { input.files = e.dataTransfer.files; show(); } });
    });
    (function () {
        var body = document.body, btn = document.querySelector('[data-nav-toggle]'), overlay = document.querySelector('[data-nav-overlay]'), side = document.getElementById('admin-sidebar');
        if (!btn || !side) return;
        function setOpen(open) {
            body.classList.toggle('admin-nav-open', open);
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            btn.setAttribute('aria-label', open ? 'إغلاق القائمة' : 'فتح القائمة');
            if (overlay) overlay.hidden = !open;
        }
        btn.addEventListener('click', function () { setOpen(!body.classList.contains('admin-nav-open')); });
        if (overlay) overlay.addEventListener('click', function () { setOpen(false); });
        side.addEventListener('click', function (e) { if (e.target.closest('a')) setOpen(false); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });
        window.addEventListener('resize', function () { if (window.innerWidth > 900) setOpen(false); });
    })();
    document.addEventListener('submit', function (e) {
        var msg = e.target && e.target.getAttribute && e.target.getAttribute('data-confirm');
        if (msg && !window.confirm(msg)) { e.preventDefault(); }
    });
</script>
</body>
</html>
