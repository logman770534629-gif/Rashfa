{{-- نموذج النكهة (v6): الحقول التي لا تظهر في الموقع أُزيلت (قصة الرشة، القيم الغذائية/السعرات، المكوّنات، الأحجام والأسعار).
     زر "إظهار في الموقع" يضبط الحقلين is_active + is_published معاً، و"نكهة مميزة" يضبط is_featured. --}}
@php
    $accentColor = old('accent_color', $product->accent_color ?: '#C6F135');
    $isVisible   = (bool) old('is_visible', $product->exists ? ($product->is_active && $product->is_published) : true);
    $isFeatured  = (bool) old('is_featured', $product->is_featured);
@endphp
<x-layouts.admin :title="($product->exists ? 'تعديل' : 'إضافة') . ' نكهة | رشة'">
    <section class="admin-page-header">
        <div>
            <p class="eyebrow"><span></span> الكتالوج</p>
            <h1>{{ $product->exists ? 'تعديل' : 'إضافة' }} <em>نكهة.</em></h1>
        </div>
        <a class="text-button" href="{{ route('admin.products.index') }}">← العودة للنكهات</a>
    </section>

    <form class="admin-form" method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
        @csrf
        @if($product->exists) @method('PUT') @endif

        <section class="admin-panel form-panel">
            <div class="panel-header"><h2>هوية النكهة</h2><span>01</span></div>
            <div class="form-grid two">
                <label>الاسم بالعربية<input name="name_ar" value="{{ old('name_ar', $product->name_ar) }}" required></label>
                <label>الاسم بالإنجليزية<input name="name_en" dir="ltr" value="{{ old('name_en', $product->name_en) }}" required></label>
                <label class="span-all">الوصف بالعربية<textarea name="short_desc_ar" rows="2" required>{{ old('short_desc_ar', $product->short_desc_ar) }}</textarea></label>
                <label class="span-all">الوصف بالإنجليزية<textarea name="short_desc_en" rows="2" dir="ltr" required>{{ old('short_desc_en', $product->short_desc_en) }}</textarea></label>
            </div>
        </section>

        <section class="admin-panel form-panel">
            <div class="panel-header"><h2>المظهر والحالة</h2><span>02</span></div>
            <div class="form-grid two">
                <label class="accent-field span-all">لون النكهة
                    <span class="accent-control">
                        <span class="accent-swatch" data-accent-swatch style="background: {{ $accentColor }}">
                            <input type="color" name="accent_color" value="{{ $accentColor }}" data-accent-input aria-label="لون النكهة">
                        </span>
                        <span class="accent-meta">
                            <code data-accent-hex>{{ $accentColor }}</code>
                            <small class="cms-help">يتلوّن الموقع بهذا اللون عند المرور على بطاقة النكهة</small>
                        </span>
                    </span>
                </label>

                <label>الترتيب في الموقع
                    <input name="sort_order" type="number" min="1" inputmode="numeric" value="{{ old('sort_order', $product->exists ? $product->sort_order : $nextSort) }}">
                    <small class="cms-help">١ = أول نكهة. الرقم الذي تكتبه هو موضعها الفعلي، وتُزاح بقية النكهات تلقائياً.</small>
                </label>

                <div class="status-toggles span-all" role="group" aria-label="حالة النكهة">
                    <label class="status-toggle status-toggle--active {{ $isVisible ? 'is-on' : '' }}">
                        <span class="status-toggle-icon" aria-hidden="true">✓</span>
                        <span class="status-toggle-body">
                            <strong>إظهار في الموقع</strong>
                            <small data-state-text data-on="النكهة ظاهرة للزوار الآن" data-off="النكهة مخفية عن الزوار (لم تُحذف)">{{ $isVisible ? 'النكهة ظاهرة للزوار الآن' : 'النكهة مخفية عن الزوار (لم تُحذف)' }}</small>
                        </span>
                        <input type="hidden" name="is_visible" value="0">
                        <input type="checkbox" name="is_visible" value="1" @checked($isVisible)>
                        <span class="status-switch" aria-hidden="true"></span>
                        <span class="status-state" data-state-pill data-on="مفعّل" data-off="متوقف">{{ $isVisible ? 'مفعّل' : 'متوقف' }}</span>
                    </label>

                    <label class="status-toggle status-toggle--featured {{ $isFeatured ? 'is-on' : '' }}">
                        <span class="status-toggle-icon" aria-hidden="true">★</span>
                        <span class="status-toggle-body">
                            <strong>نكهة مميزة</strong>
                            <small data-state-text data-on="تُعرض كنكهة مميّزة" data-off="نكهة عادية">{{ $isFeatured ? 'تُعرض كنكهة مميّزة' : 'نكهة عادية' }}</small>
                        </span>
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" value="1" @checked($isFeatured)>
                        <span class="status-switch" aria-hidden="true"></span>
                        <span class="status-state" data-state-pill data-on="مفعّل" data-off="متوقف">{{ $isFeatured ? 'مفعّل' : 'متوقف' }}</span>
                    </label>
                </div>

                <div class="span-all cms-field">
                    <span class="cms-label">الوسوم</span>
                    <div class="tag-input" data-tag-input>
                        <div class="tag-chips" data-tag-chips></div>
                        <input type="text" placeholder="اكتب وسماً واضغط Enter" enterkeyhint="done" data-tag-field>
                    </div>
                    <input type="hidden" name="tags_json" data-tag-output value="{{ old('tags_json', json_encode($product->tags_json ?: [], JSON_UNESCAPED_UNICODE)) }}">
                    <small class="cms-help">أول وسم يظهر على بطاقة النكهة في الموقع.</small>
                </div>
            </div>
        </section>

        <section class="admin-panel form-panel">
            <div class="panel-header"><h2>الصورة الرئيسية</h2><span>03</span></div>
            <div class="form-grid two">
                @if($product->exists && $product->primaryImage?->media)
                    <div class="span-all"><img class="form-thumb" src="{{ $product->primaryImage->media->storage_path }}" alt="الصورة الحالية للنكهة"></div>
                @endif
                <label class="span-all">{{ $product->exists ? 'استبدال الصورة الرئيسية (اختياري)' : 'ارفع صورة النكهة' }}
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp" data-image-input>
                </label>
                <img class="form-thumb" data-image-preview hidden alt="معاينة الصورة الجديدة">
                <div class="toggle-row span-all">
                    <label><input type="checkbox" name="image_effects[]" value="glass" @checked(in_array('glass', old('image_effects', [])))> زجاجي</label>
                    <label><input type="checkbox" name="image_effects[]" value="transparent" @checked(in_array('transparent', old('image_effects', [])))> شفاف</label>
                    <label><input type="checkbox" name="image_effects[]" value="cutout" @checked(in_array('cutout', old('image_effects', [])))> قصّ حواف</label>
                </div>
            </div>
        </section>

        <div class="form-actions form-actions--sticky">
            <button class="button button-lime" type="submit">حفظ النكهة <span>←</span></button>
            @if($product->exists)
                <a class="button button-ghost" href="{{ route('admin.products.images.index', $product) }}">إدارة الصور</a>
                <a class="button button-ghost" href="{{ route('admin.products.story-phases.index', $product) }}">مراحل القصة</a>
            @endif
        </div>
    </form>

    @if($product->exists)
        <div class="danger-zone">
            <form action="{{ route('admin.products.force-destroy', $product) }}" method="POST" data-confirm="تحذير: سيتم حذف النكهة نهائياً مع صورها ومراحل قصتها، ولا يمكن التراجع. هل أنت متأكد؟">
                @csrf @method('DELETE')
                <button type="submit" class="danger-link">حذف النكهة نهائياً</button>
            </form>
            <span class="cms-help">لإخفائها فقط دون حذف: أوقف زر «إظهار في الموقع» ثم احفظ.</span>
        </div>
    @endif

    <script>
    (function () {
        // -- معاينة الصورة الجديدة --
        var imgIn = document.querySelector('[data-image-input]'), imgPrev = document.querySelector('[data-image-preview]');
        if (imgIn && imgPrev) imgIn.addEventListener('change', function () {
            if (imgIn.files[0]) { imgPrev.src = URL.createObjectURL(imgIn.files[0]); imgPrev.hidden = false; }
        });

        // -- معاينة لون النكهة (swatch + hex) --
        var accentInput = document.querySelector('[data-accent-input]');
        var accentSwatch = document.querySelector('[data-accent-swatch]');
        var accentHex = document.querySelector('[data-accent-hex]');
        function syncAccent() {
            if (!accentInput) return;
            if (accentSwatch) accentSwatch.style.background = accentInput.value;
            if (accentHex) accentHex.textContent = accentInput.value;
        }
        if (accentInput) { accentInput.addEventListener('input', syncAccent); accentInput.addEventListener('change', syncAccent); syncAccent(); }

        // -- أزرار الحالة: نص توضيحي يتبدّل فوراً (مفعّل/متوقف) --
        document.querySelectorAll('.status-toggle').forEach(function (label) {
            var box = label.querySelector('input[type=checkbox]');
            var text = label.querySelector('[data-state-text]');
            var pill = label.querySelector('[data-state-pill]');
            function sync() {
                var k = box.checked ? 'on' : 'off';
                label.classList.toggle('is-on', box.checked);
                if (text) text.textContent = text.getAttribute('data-' + k);
                if (pill) pill.textContent = pill.getAttribute('data-' + k);
            }
            box.addEventListener('change', sync);
            sync();
        });

        // -- محرر الوسوم (رقائق بدل JSON) --
        var tagWrap = document.querySelector('[data-tag-input]');
        if (tagWrap) {
            var chips = tagWrap.querySelector('[data-tag-chips]');
            var field = tagWrap.querySelector('[data-tag-field]');
            var tagOutput = tagWrap.parentElement.querySelector('[data-tag-output]');
            var tags = [];
            try { tags = JSON.parse(tagOutput.value) || []; } catch (e) { tags = []; }
            if (!Array.isArray(tags)) tags = [];

            function renderTags() {
                chips.innerHTML = '';
                tags.forEach(function (tag, idx) {
                    var chip = document.createElement('span');
                    chip.className = 'tag-chip';
                    chip.textContent = tag;
                    var x = document.createElement('button');
                    x.type = 'button'; x.textContent = '×'; x.setAttribute('aria-label', 'حذف الوسم ' + tag);
                    x.addEventListener('click', function () { tags.splice(idx, 1); renderTags(); });
                    chip.appendChild(x);
                    chips.appendChild(chip);
                });
                tagOutput.value = JSON.stringify(tags);
            }
            function commit() {
                var v = field.value.trim().replace(/[,،]$/, '');
                if (v !== '' && tags.indexOf(v) === -1) tags.push(v);
                field.value = '';
                renderTags();
            }
            field.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ',' || e.key === '،') { e.preventDefault(); commit(); }
            });
            // لا يضيع وسم مكتوب ولم يُضغط له Enter (مهم على الجوال): يُضاف عند مغادرة الحقل أو قبل الإرسال.
            field.addEventListener('blur', function () { if (field.value.trim() !== '') commit(); });
            tagWrap.closest('form').addEventListener('submit', function () { if (field.value.trim() !== '') commit(); });

            renderTags();
        }
    })();
    </script>
</x-layouts.admin>
