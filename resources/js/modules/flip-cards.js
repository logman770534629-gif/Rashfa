/**
 * بطاقات المكوّنات (Flip Cards)
 *
 * - سطح المكتب (ماوس + شاشة > 900px): التقليب عند المرور بالماوس، والنقر "يثبّت" الوجه الخلفي / يعيده.
 * - الهاتف / التابلت / اللمس: لا يوجد hover إطلاقاً — النقر أو اللمس على البطاقة يبدّل الوجهين فقط.
 *   (كان hover العالق على اللمس هو سبب بقاء البطاقة مقلوبة وتعطّل التفاعل.)
 * - مصدر حالة واحد لكل بطاقة، والعرض كله عبر كلاس .is-flipped (CSS هو من يقرّر 3D أو تلاشي).
 */
const DESKTOP_QUERY = '(hover: hover) and (pointer: fine) and (min-width: 901px)';

export function initFlipCards() {
    const cards = [...document.querySelectorAll('[data-flip-card]')];
    if (!cards.length) return;

    const desktopMq = window.matchMedia(DESKTOP_QUERY);

    const setup = (card) => {
        const front = card.querySelector('.ingredient-front');
        const back = card.querySelector('.ingredient-back');
        const triggers = [...card.querySelectorAll('[data-flip-trigger]')];
        const state = { pinned: false, hovering: false, suppressed: false };

        const isFlipped = () => state.pinned || (state.hovering && !state.suppressed);

        // الوجه الخلفي لا يصبح "حاوية تمرير" إلا إذا كان نصّه أطول فعلاً من البطاقة.
        // سابقاً: overflow:auto + overscroll-behavior:contain كانا يحجزان عجلة الماوس فوق البطاقة المقلوبة ويمنعان تمرير الصفحة.
        const syncScrollable = () => {
            if (!back) return;
            back.classList.toggle('is-scrollable', back.scrollHeight > back.clientHeight + 2);
        };

        const render = () => {
            const flipped = isFlipped();
            card.classList.toggle('is-flipped', flipped);
            // الوجه المخفي لا يستقبل تركيزاً ولا لمساً ولا قارئ شاشة
            if (front) { front.inert = flipped; front.setAttribute('aria-hidden', String(flipped)); }
            if (back) { back.inert = !flipped; back.setAttribute('aria-hidden', String(!flipped)); }
            triggers.forEach((button) => {
                if (button.closest('.ingredient-front')) button.setAttribute('aria-expanded', String(flipped));
            });
        };

        const toggleByClick = () => {
            if (isFlipped()) {
                state.pinned = false;
                state.suppressed = state.hovering; // على سطح المكتب: لا يعيد hover القلب فوراً
            } else {
                state.pinned = true;
            }
            render();
        };

        const reset = () => {
            state.pinned = false; state.hovering = false; state.suppressed = false;
            render();
        };

        // النقر على أي مكان من البطاقة (الأزرار تفقّس إلى هنا — مستمع واحد فلا يحدث تبديل مزدوج)
        card.addEventListener('click', toggleByClick);

        // Hover بالماوس فقط (وليس اللمس)
        card.addEventListener('pointerenter', (e) => {
            if (e.pointerType !== 'mouse' || !desktopMq.matches) return;
            state.hovering = true;
            render();
        });
        card.addEventListener('pointerleave', (e) => {
            if (e.pointerType !== 'mouse') return;
            state.hovering = false;
            state.suppressed = false;
            render();
        });

        // Esc يعيد البطاقة لوجهها الأمامي
        card.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && card.classList.contains('is-flipped')) {
                state.pinned = false;
                state.suppressed = state.hovering;
                render();
                front?.querySelector('[data-flip-trigger]')?.focus({ preventScroll: true });
            }
        });

        render();
        syncScrollable();
        return { reset, syncScrollable };
    };

    const handles = cards.map(setup);

    // إعادة قياس قابلية التمرير عند تغيّر الحجم أو بعد تحميل الخطوط
    let measureRaf = 0;
    const measureAll = () => {
        if (measureRaf) return;
        measureRaf = requestAnimationFrame(() => { measureRaf = 0; handles.forEach((h) => h && h.syncScrollable()); });
    };
    window.addEventListener('resize', measureAll, { passive: true });
    window.addEventListener('load', measureAll, { once: true });
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(measureAll);

    // عند تدوير الجهاز أو تغيير حجم النافذة بين وضعَي (3D / تلاشي): نعيد كل البطاقات لحالتها الطبيعية
    const onModeChange = () => { handles.forEach((h) => h && h.reset()); measureAll(); };
    if (desktopMq.addEventListener) desktopMq.addEventListener('change', onModeChange);
    else if (desktopMq.addListener) desktopMq.addListener(onModeChange);
}
