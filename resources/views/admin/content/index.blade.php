@php
    $paletteDefault = '#FF7A1A,#7BB82D,#FFB300,#F06A8D,#4B4FB8';
    $palette = array_values(array_filter(array_map('trim', explode(',', (string) setting('brand_palette', $paletteDefault)))));
    $weights = ['400' => 'عادي', '500' => 'متوسط', '600' => 'شبه عريض', '700' => 'عريض', '800' => 'عريض جداً'];
    $aligns = ['right' => 'يمين', 'center' => 'وسط', 'left' => 'يسار'];
@endphp
<x-layouts.admin title="محتوى الموقع | رشة">
<div class="cms-app" data-cms data-current="{{ $current }}">

    <header class="cms-head">
        <div>
            <p class="cms-kicker">تحرير مباشر</p>
            <h1>محتوى الموقع</h1>
        </div>
        <div class="cms-head-actions">
            <div class="cms-langswitch" role="group" aria-label="لغة التحرير" data-global-lang>
                <span>لغة التحرير</span>
                <button type="button" class="is-on" data-global-lang-btn="ar">العربية</button>
                <button type="button" data-global-lang-btn="en">English</button>
            </div>
            <a class="cms-btn cms-btn-light" href="{{ route('home') }}" target="_blank" rel="noopener">معاينة الموقع ↗</a>
            <button class="cms-btn cms-btn-primary" type="submit" form="cms-form">حفظ التغييرات</button>
        </div>
    </header>

    <form id="cms-form" method="POST" action="{{ route('admin.content.update') }}" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')
        <input type="hidden" name="tab" value="{{ $current }}" data-tab-input>

        <nav class="cms-tabs" role="tablist" aria-label="أقسام المحتوى">
            @foreach($schema as $gkey => $group)
                <button type="button" role="tab" class="cms-tab" data-tab="{{ $gkey }}" aria-selected="{{ $gkey === $current ? 'true' : 'false' }}">
                    <i aria-hidden="true">{{ $group['icon'] ?? '•' }}</i><span>{{ $group['label'] }}</span>
                </button>
            @endforeach
        </nav>

        @foreach($schema as $gkey => $group)
            <section class="cms-pane" data-pane="{{ $gkey }}" role="tabpanel" @if($gkey !== $current) hidden @endif>
                <div class="cms-pane-head">
                    <h2>{{ $group['label'] }}</h2>
                    <span>{{ count($group['fields']) }} حقل</span>
                </div>

                <div class="cms-grid">
                @foreach($group['fields'] as $key => $f)
                    @php
                        $id = str_replace('.', '__', $key);
                        $default = $f['default'] ?? '';
                        $type = $f['type'];
                        // قيمة عربية صريحة: ما حُفظ بالعربية ← افتراضي ar.json ← افتراضي المخطط (لا تعتمد على لغة التطبيق)
                        $value = old('f.' . $id, setting_db($key) ?? setting_lang_default($key, 'ar') ?? $default);
                        $st = setting_style($key);
                        $wide = in_array($type, ['textarea', 'image', 'palette'], true) || ! empty($f['styleable']);
                        $tr = cms_translatable($f);
                        $enValue = old('f_en.' . $id, setting_db($key . '__en') ?? setting_lang_default($key, 'en') ?? '');
                    @endphp
                    <div class="cms-card {{ $wide ? 'is-wide' : '' }}" data-field="{{ $id }}">

                        @if($type === 'image')
                            <span class="cms-label">{{ $f['label'] }}</span>
                            <div class="cms-image">
                                <button type="button" class="cms-thumb" data-open-preview="{{ $id }}" aria-label="معاينة الصورة">
                                    <img src="{{ setting_image($key, $default) }}" alt="" data-preview="{{ $id }}">
                                </button>
                                <div class="cms-image-actions">
                                    <label class="cms-btn cms-btn-light cms-file">
                                        اختيار صورة جديدة
                                        <input type="file" name="img[{{ $id }}]" accept="image/jpeg,image/png,image/webp" data-file="{{ $id }}">
                                    </label>
                                    <button type="button" class="cms-btn cms-btn-ghost" data-open-preview="{{ $id }}">معاينة</button>
                                    <small data-file-name="{{ $id }}">JPG أو PNG أو WebP، حتى 6 ميجابايت.</small>
                                </div>
                            </div>

                        @elseif($type === 'palette')
                            <span class="cms-label">{{ $f['label'] }}</span>
                            <div class="cms-palette" data-palette>
                                <div class="cms-palette-swatches" data-palette-swatches></div>
                                <button type="button" class="cms-btn cms-btn-ghost" data-palette-add>+ أضف لوناً</button>
                                <input type="hidden" name="f[{{ $id }}]" value="{{ $value }}" data-palette-output>
                            </div>

                        @elseif($type === 'toggle')
                            <input type="hidden" name="f[{{ $id }}]" value="0">
                            <label class="cms-switch">
                                <input type="checkbox" name="f[{{ $id }}]" value="1" @checked((string) $value === '1')>
                                <span class="cms-switch-ui" aria-hidden="true"></span>
                                <span>{{ $f['label'] }}</span>
                            </label>

                        @elseif($type === 'select')
                            <label class="cms-label" for="f-{{ $id }}">{{ $f['label'] }}</label>
                            <select id="f-{{ $id }}" name="f[{{ $id }}]">
                                @foreach($f['options'] as $optKey => $optLabel)
                                    <option value="{{ $optKey }}" @selected((string) $value === (string) $optKey)>{{ $optLabel }}</option>
                                @endforeach
                            </select>

                        @elseif($type === 'textarea')
                            <label class="cms-label" for="f-{{ $id }}">{{ $f['label'] }}</label>
                            @if($tr)
                                <div class="cms-i18n" data-i18n>
                                    <div class="cms-langtabs" role="tablist" aria-label="لغة الحقل">
                                        <button type="button" class="is-on" data-lang-btn="ar">العربية</button>
                                        <button type="button" data-lang-btn="en">English</button>
                                    </div>
                                    <div data-lang-pane="ar" dir="rtl" lang="ar"><textarea id="f-{{ $id }}" name="f[{{ $id }}]" rows="3" dir="rtl" data-text-source>{{ $value }}</textarea></div>
                                    <div data-lang-pane="en" dir="ltr" lang="en" hidden><textarea id="f-en-{{ $id }}" name="f_en[{{ $id }}]" rows="3" dir="ltr" lang="en" data-text-source-en>{{ $enValue }}</textarea></div>
                                </div>
                            @else
                                <textarea id="f-{{ $id }}" name="f[{{ $id }}]" rows="3" data-text-source>{{ $value }}</textarea>
                            @endif

                        @else
                            <label class="cms-label" for="f-{{ $id }}">{{ $f['label'] }}</label>
                            @if($tr)
                                <div class="cms-i18n" data-i18n>
                                    <div class="cms-langtabs" role="tablist" aria-label="لغة الحقل">
                                        <button type="button" class="is-on" data-lang-btn="ar">العربية</button>
                                        <button type="button" data-lang-btn="en">English</button>
                                    </div>
                                    <div data-lang-pane="ar" dir="rtl" lang="ar"><input id="f-{{ $id }}" type="text" name="f[{{ $id }}]" value="{{ $value }}" dir="rtl" data-text-source></div>
                                    <div data-lang-pane="en" dir="ltr" lang="en" hidden><input id="f-en-{{ $id }}" type="text" name="f_en[{{ $id }}]" value="{{ $enValue }}" dir="ltr" lang="en" data-text-source-en></div>
                                </div>
                            @else
                                <input id="f-{{ $id }}" type="{{ $type === 'url' ? 'url' : 'text' }}" name="f[{{ $id }}]" value="{{ $value }}" @if($type === 'url' || ! empty($f['ltr'])) dir="ltr" @endif data-text-source>
                            @endif
                        @endif

                        @if(! empty($f['help']))<small class="cms-help">{{ $f['help'] }}</small>@endif

                        @if(! empty($f['styleable']))
                            <details class="cms-style" @if($st) open @endif>
                                <summary>
                                    <span>تنسيق النص</span>
                                    @if($st)<b class="cms-badge">مُفعّل</b>@endif
                                </summary>

                                <div class="cms-style-preview" data-style-preview>معاينة التنسيق</div>

                                <div class="cms-style-grid">
                                    <label>حجم الخط
                                        <select name="style[{{ $id }}][font_size]" data-style="font_size">
                                            <option value="">تلقائي</option>
                                            @php $sizes = font_size_choices(); if (! empty($st['font_size']) && ! in_array((int) $st['font_size'], $sizes, true)) { $sizes[] = (int) $st['font_size']; sort($sizes); } @endphp
                                            @foreach($sizes as $sz)
                                                <option value="{{ $sz }}" @selected((string) ($st['font_size'] ?? '') === (string) $sz)>{{ $sz }} px</option>
                                            @endforeach
                                        </select>
                                    </label>

                                    <label>شكل الخط
                                        <select name="style[{{ $id }}][font_family]" data-style="font_family">
                                            <option value="">تلقائي</option>
                                            @foreach(font_choices() as $fk => $fv)
                                                <option value="{{ $fk }}" data-css="{{ $fv['css'] }}" @selected(($st['font_family'] ?? '') === $fk)>{{ $fv['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </label>

                                    <label>سماكة الخط
                                        <select name="style[{{ $id }}][font_weight]" data-style="font_weight">
                                            <option value="">تلقائي</option>
                                            @foreach($weights as $wk => $wv)
                                                <option value="{{ $wk }}" @selected((string) ($st['font_weight'] ?? '') === (string) $wk)>{{ $wv }}</option>
                                            @endforeach
                                        </select>
                                    </label>

                                    <label>محاذاة النص
                                        <select name="style[{{ $id }}][text_align]" data-style="text_align">
                                            <option value="">تلقائي</option>
                                            @foreach($aligns as $ak => $av)
                                                <option value="{{ $ak }}" @selected(($st['text_align'] ?? '') === $ak)>{{ $av }}</option>
                                            @endforeach
                                        </select>
                                    </label>

                                    <div class="cms-color" data-color-box>
                                        <span>لون الخط</span>
                                        <div class="cms-color-row">
                                            <input type="color" value="{{ $st['color'] ?? '#24140B' }}" data-color-picker aria-label="اختر لون الخط">
                                            <input type="hidden" name="style[{{ $id }}][color]" value="{{ $st['color'] ?? '' }}" data-color-value data-style="color">
                                            <code data-color-label>{{ $st['color'] ?? 'تلقائي' }}</code>
                                            <button type="button" class="cms-link" data-color-clear>تلقائي</button>
                                        </div>
                                        <div class="cms-swatches">
                                            @foreach($palette as $pc)
                                                <button type="button" style="background:{{ $pc }}" data-pick-color="{{ $pc }}" aria-label="{{ $pc }}"></button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <label class="cms-check"><input type="checkbox" name="style_reset[{{ $id }}]" value="1"> إلغاء كل تنسيق هذا النص</label>
                            </details>
                        @endif

                        <label class="cms-check cms-reset"><input type="checkbox" name="reset[{{ $id }}]" value="1"> استعادة القيمة الافتراضية</label>
                    </div>
                @endforeach
                </div>
            </section>
        @endforeach

        <div class="cms-savebar" data-savebar>
            <span class="cms-dirty" data-dirty hidden>● لديك تغييرات غير محفوظة</span>
            <span class="cms-clean" data-clean>كل شيء محفوظ</span>
            <button class="cms-btn cms-btn-primary" type="submit">حفظ التغييرات</button>
        </div>
    </form>

    <div class="cms-modal" data-modal hidden>
        <div class="cms-modal-backdrop" data-modal-close></div>
        <figure class="cms-modal-body">
            <button type="button" class="cms-modal-x" data-modal-close aria-label="إغلاق">×</button>
            <img src="" alt="معاينة الصورة" data-modal-img>
        </figure>
    </div>
</div>

<script>
(function () {
    var app = document.querySelector('[data-cms]');
    if (!app) return;
    var form = document.getElementById('cms-form');
    var tabs = [].slice.call(app.querySelectorAll('[data-tab]'));
    var panes = [].slice.call(app.querySelectorAll('[data-pane]'));
    var tabInput = app.querySelector('[data-tab-input]');
    var dirty = false;

    /* ---------- التبويبات (بدون إعادة تحميل، ولا تضيع التعديلات) ---------- */
    function showTab(name, push) {
        var found = tabs.some(function (t) { return t.dataset.tab === name; });
        if (!found) name = tabs[0].dataset.tab;
        tabs.forEach(function (t) { t.setAttribute('aria-selected', t.dataset.tab === name ? 'true' : 'false'); });
        panes.forEach(function (p) { p.hidden = p.dataset.pane !== name; });
        tabInput.value = name;
        if (push) { history.replaceState(null, '', '#' + name); }
        var active = tabs.filter(function (t) { return t.dataset.tab === name; })[0];
        if (active && active.scrollIntoView) { active.scrollIntoView({ block: 'nearest', inline: 'center' }); }
    }
    tabs.forEach(function (t) { t.addEventListener('click', function () { showTab(t.dataset.tab, true); }); });
    showTab(location.hash ? location.hash.slice(1) : app.dataset.current, false);

    /* ---------- تتبّع التغييرات ---------- */
    function markDirty() {
        if (dirty) return;
        dirty = true;
        app.querySelector('[data-dirty]').hidden = false;
        app.querySelector('[data-clean]').hidden = true;
    }
    form.addEventListener('input', markDirty);
    form.addEventListener('change', markDirty);
    form.addEventListener('submit', function () { dirty = false; });
    window.addEventListener('beforeunload', function (e) { if (dirty) { e.preventDefault(); e.returnValue = ''; } });

    /* ---------- معاينة التنسيق الحي ---------- */
    function updatePreview(card) {
        var box = card.querySelector('[data-style-preview]');
        if (!box) return;
        var srcAr = card.querySelector('[data-text-source]');
        var srcEn = card.querySelector('[data-text-source-en]');
        var src = (srcEn && srcEn.closest('[data-lang-pane]') && !srcEn.closest('[data-lang-pane]').hidden) ? srcEn : srcAr;
        box.textContent = (src && src.value.replace(/\|/g, ' ').trim()) || 'معاينة التنسيق';
        function val(n) { var el = card.querySelector('[data-style="' + n + '"]'); return el ? el.value : ''; }
        var fam = card.querySelector('[data-style="font_family"]');
        var opt = fam && fam.options[fam.selectedIndex];
        function put(prop, v) { if (v) { box.style.setProperty(prop, v, 'important'); } else { box.style.removeProperty(prop); } }
        put('font-size', val('font_size') ? Math.min(parseFloat(val('font_size')), 56) + 'px' : '');
        put('font-family', opt && opt.dataset.css ? opt.dataset.css : '');
        put('font-weight', val('font_weight'));
        put('text-align', val('text_align'));
        put('color', val('color'));
    }
    app.querySelectorAll('.cms-card').forEach(function (card) {
        if (!card.querySelector('[data-style-preview]')) return;
        ['input', 'change'].forEach(function (ev) { card.addEventListener(ev, function () { updatePreview(card); }); });
        updatePreview(card);
    });

    /* ---------- أداة اللون (المعاينة تتحدّث لحظياً أثناء الاختيار) ---------- */
    app.querySelectorAll('[data-color-box]').forEach(function (box) {
        var card = box.closest('.cms-card');
        var picker = box.querySelector('[data-color-picker]');
        var hidden = box.querySelector('[data-color-value]');
        var label = box.querySelector('[data-color-label]');
        function set(v) {
            hidden.value = v;
            label.textContent = v || 'تلقائي';
            if (v) { picker.value = v; }
            updatePreview(card);
            markDirty();
        }
        ['input', 'change'].forEach(function (ev) { picker.addEventListener(ev, function () { set(picker.value.toUpperCase()); }); });
        box.querySelector('[data-color-clear]').addEventListener('click', function () { set(''); });
        box.querySelectorAll('[data-pick-color]').forEach(function (b) {
            b.addEventListener('click', function () { set(b.dataset.pickColor.toUpperCase()); });
        });
    });

    /* ---------- لوحة ألوان العلامة ---------- */
    app.querySelectorAll('[data-palette]').forEach(function (box) {
        var out = box.querySelector('[data-palette-output]');
        var wrap = box.querySelector('[data-palette-swatches]');
        var colors = out.value.split(',').filter(Boolean);
        function sync() { out.value = colors.join(','); out.dispatchEvent(new Event('input', { bubbles: true })); }
        function render() {
            wrap.innerHTML = '';
            colors.forEach(function (c, i) {
                var w = document.createElement('span'); w.className = 'cms-palette-swatch';
                var inp = document.createElement('input'); inp.type = 'color'; inp.value = c;
                inp.addEventListener('input', function () { colors[i] = inp.value; sync(); });
                var x = document.createElement('button'); x.type = 'button'; x.textContent = '×'; x.setAttribute('aria-label', 'حذف اللون');
                x.addEventListener('click', function () { colors.splice(i, 1); render(); sync(); });
                w.appendChild(inp); w.appendChild(x); wrap.appendChild(w);
            });
        }
        box.querySelector('[data-palette-add]').addEventListener('click', function () { colors.push('#FF7A1A'); render(); sync(); });
        render();
    });

    /* ---------- الصور: معاينة فورية + نافذة المعاينة ---------- */
    var modal = app.querySelector('[data-modal]');
    var modalImg = app.querySelector('[data-modal-img]');
    function openModal(src) { modalImg.src = src; modal.hidden = false; document.body.style.overflow = 'hidden'; }
    function closeModal() { modal.hidden = true; modalImg.src = ''; document.body.style.overflow = ''; }
    app.querySelectorAll('[data-modal-close]').forEach(function (b) { b.addEventListener('click', closeModal); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) closeModal(); });

    app.querySelectorAll('[data-open-preview]').forEach(function (b) {
        b.addEventListener('click', function () {
            var img = app.querySelector('img[data-preview="' + b.dataset.openPreview + '"]');
            if (img && img.src) openModal(img.src);
        });
    });
    app.querySelectorAll('input[type=file][data-file]').forEach(function (input) {
        input.addEventListener('change', function () {
            var id = input.dataset.file;
            var img = app.querySelector('img[data-preview="' + id + '"]');
            var name = app.querySelector('[data-file-name="' + id + '"]');
            if (input.files && input.files[0]) {
                if (img) img.src = URL.createObjectURL(input.files[0]);
                if (name) name.textContent = 'تم اختيار: ' + input.files[0].name + ' — اضغط «حفظ التغييرات» للتثبيت.';
            }
        });
    });

    /* ---------- لغة التحرير: عربي / English (لكل حقل، أو لكل الحقول دفعة واحدة) ---------- */
    function setFieldLang(box, lang) {
        box.querySelectorAll('[data-lang-pane]').forEach(function (p) { p.hidden = p.dataset.langPane !== lang; });
        box.querySelectorAll('[data-lang-btn]').forEach(function (b) { b.classList.toggle('is-on', b.dataset.langBtn === lang); });
        var card = box.closest('.cms-card');
        if (card) { updatePreview(card); }
    }
    app.querySelectorAll('[data-i18n]').forEach(function (box) {
        box.querySelectorAll('[data-lang-btn]').forEach(function (b) {
            b.addEventListener('click', function () { setFieldLang(box, b.dataset.langBtn); });
        });
    });
    var globalBox = app.querySelector('[data-global-lang]');
    if (globalBox) {
        globalBox.querySelectorAll('[data-global-lang-btn]').forEach(function (b) {
            b.addEventListener('click', function () {
                globalBox.querySelectorAll('[data-global-lang-btn]').forEach(function (x) { x.classList.toggle('is-on', x === b); });
                app.querySelectorAll('[data-i18n]').forEach(function (box) { setFieldLang(box, b.dataset.globalLangBtn); });
            });
        });
    }

    /* ---------- حفظ بـ Ctrl/⌘ + S ---------- */
    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') { e.preventDefault(); dirty = false; form.requestSubmit(); }
    });
})();
</script>
</x-layouts.admin>
