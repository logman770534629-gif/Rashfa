// نظام ألوان النكهات: اللون يتغير ويثبت بدون أي مؤقت زمني.
//  - click (نقر/لمس حقيقي) و pointerenter (ماوس): يثبّت لون البطاقة حتى اختيار نكهة أخرى.
//  - عند استقرار الشريط فعلياً (سحب/أسهم/لوحة مفاتيح/تمرير لمسي): يُرسل flavor:active مرة واحدة بلون البطاقة النشطة.
//  - على الهاتف: أي تمرير أفقي يُعدّ تنقّلاً من المستخدم، لكن اللون لا يتغير إلا بعد أن يسكن الشريط تماماً
//    (لا أثناء الزخم ولا عند كل حدث scroll) — هذا ما يمنع اهتزاز الألوان وتغيّرها بأحداث قديمة.
import { isFinePointer, prefersReducedMotion } from './utils';
import { setTheme } from './theme';

// جهاز لمسي (هاتف/تابلت) أو شاشة ضيقة: لا يوجد hover حقيقي، فكل تمرير هو تنقّل مقصود من المستخدم
const isTouchLike = () => !isFinePointer()
    || window.matchMedia('(hover: none)').matches
    || window.innerWidth <= 900;

const colorOf = (card) => card.style.getPropertyValue('--flavor-accent').trim();

// بعد اختيار المستخدم لنكهة بالنقر/اللمس: هل يظل المرور بالماوس فوق بطاقة أخرى يغيّر اللون؟
//  true  = نعم (المرور فعل مقصود من المستخدم، وهو سلوك "مرّر فوق أي بطاقة وشاهد الموقع يتلوّن" الأصلي)
//  false = لا، النقر يقفل اللون حتى نقرة/لمسة على نكهة أخرى
const HOVER_RECOLORS_AFTER_PICK = true;

export function initFlavorRail() {
    const rail = document.querySelector('[data-flavor-rail]');
    if (!rail) return;

    const cards = [...rail.querySelectorAll('[data-flavor-card]')];
    if (!cards.length) return;

    const wrapMode = rail.dataset.railMode === 'wrap';
    const counter = document.querySelector('[data-rail-count]');
    const scrub = document.querySelector('[data-rail-scrub]');
    const thumb = document.querySelector('[data-rail-thumb]');
    const reduced = prefersReducedMotion();

    const emit = (card, index) => {
        const color = colorOf(card);
        if (color) document.dispatchEvent(new CustomEvent('flavor:active', { detail: { color, index } }));
    };

    const isRtl = () => getComputedStyle(rail).direction === 'rtl';
    const maxScroll = () => Math.max(0, rail.scrollWidth - rail.clientWidth);
    // تمرير برمجي: بلا حركة ناعمة عند تفضيل تقليل الحركة
    const scrollTo = (left, smooth = true) => rail.scrollTo({ left, behavior: smooth && !reduced ? 'smooth' : 'auto' });

    // true أثناء السحب بالماوس/الشريط السفلي — لا يتغير اللون أثناءه
    let dragging = false;
    let scrubbing = false;
    let touching = false;
    // آخر لحظة انتهى فيها سحب ماوس: نستخدمها لإلغاء النقرة الناتجة عن الإفلات (بدون مؤقتات)
    let blockClickUntil = 0;

    // ---- أي فعل صريح من المستخدم هو الأحدث؟ (الأحدث يفوز) ----
    //  choiceAt: آخر اختيار لبطاقة (نقر/لمس/مرور ماوس حقيقي)      navAt: آخر تنقّل بالشريط (سحب/أسهم/لمس/عجلة/لوحة مفاتيح)
    // سبب الخطأ الأصلي: الشريط كان يعيد لون "بطاقة المنتصف" عند كل استقرار تمرير (settle) حتى لو كان التمرير
    // تلقائياً وليس من المستخدم؛ فمرور الماوس فوق البطاقة (تكبير 1.02 في liquid-hover) ومغادرته يجعلان المتصفح
    // يعيد محاذاة scroll-snap بمقدار ~1px، فيُطلق scroll/scrollend فيعود اللون إلى بطاقة المنتصف فوراً.
    // الحل: لا نعيد لون المنتصف إلا إذا كان آخر فعل للمستخدم تنقّلاً بالشريط، لا اختياراً لبطاقة.
    // عدّاد متزايد بدل الوقت حتى لا يتساوى فعلان في نفس الميلي ثانية.
    let seq = 0;
    let choiceAt = 0;
    let navAt = 0;
    let picked = false; // هل نقر/لمس المستخدم نكهة فعلاً؟
    // آخر موضع تمرير مستقر/مختار: أي ابتعاد كبير عنه = تنقّل حقيقي (وليس إعادة محاذاة بسيطة بعد hover)
    let baseLeft = rail.scrollLeft;
    // وقت آخر حدث تمرير: نستخدمه لتمييز النقرة الحقيقية من النقرة المتولّدة بعد سحب
    let lastScrollAt = 0;
    const markNav = () => { navAt = ++seq; };

    // مرور الماوس الناتج عن تحرّك البطاقات تحت مؤشر ثابت (تمرير/أسهم) ليس اختياراً: نقارن إحداثيات آخر حركة حقيقية
    let lastX = null;
    let lastY = null;
    window.addEventListener('pointermove', (e) => { lastX = e.clientX; lastY = e.clientY; }, { passive: true });

    // ---- قياسات مخزّنة: نحسب مركز كل بطاقة مرة واحدة ولا نقرأ layout في كل إطار تمرير ----
    //  centers[i] = بُعد مركز البطاقة (عند scrollLeft = 0) عن حافة الشريط اليسرى، بإحداثيات فيزيائية.
    //  إحداثيات الفيزيائية تجعل المعادلة واحدة في RTL و LTR، ونستخدم scrollLeft كما هو من المتصفح.
    let centers = [];
    const measure = () => {
        const rr = rail.getBoundingClientRect();
        const s0 = rail.scrollLeft;
        centers = cards.map((card) => {
            const r = card.getBoundingClientRect();
            return (r.left + r.width / 2) - rr.left + s0;
        });
    };

    // البطاقة الأقرب إلى منتصف الشريط (حساب حسابي فقط — بلا إعادة تخطيط)
    const nearestIndex = (s) => {
        const mid = rail.clientWidth / 2;
        let active = 0;
        let best = Infinity;
        for (let i = 0; i < centers.length; i++) {
            const distance = Math.abs(centers[i] - s - mid);
            if (distance < best) { best = distance; active = i; }
        }
        // عند أول الشريط/آخره تكون البطاقة الأولى/الأخيرة هي النشطة (بطاقة المنتصف لا تصل إليهما أبداً)
        const max = maxScroll();
        const pos = Math.abs(s);
        if (max > 1) {
            if (pos <= 3) active = 0;
            else if (max - pos <= 3) active = cards.length - 1;
        }
        return active;
    };

    // ---- تحديد البطاقة النشطة (بدون كتابة DOM إلا عند التغيّر) ----
    let activeIndex = 0;
    // بعد اختيار بطاقة باللمس: نُبقي الإطار عليها (لا نعود لمنطق "الأقرب للمنتصف" الذي قد يشير لبطاقة مجاورة
    // على الشاشات العريضة) حتى يبدأ المستخدم تنقّلاً جديداً بالشريط
    let lockedIndex = null;
    // نقطة الالتصاق التي أُمر الشريط بالوصول إليها بعد اختيار بطاقة باللمس
    let pinTarget = null;
    const highlight = (index) => {
        if (!cards[index]) return;
        const changed = index !== activeIndex || !cards[index].classList.contains('is-active');
        activeIndex = index;
        if (!changed) return;
        cards.forEach((card, i) => card.classList.toggle('is-active', i === index));
        if (counter) counter.textContent = `${String(index + 1).padStart(2, '0')} / ${String(cards.length).padStart(2, '0')}`;
        if (scrub) scrub.setAttribute('aria-valuenow', String(index + 1));
    };

    // شريط التقدّم السفلي يتبع موضع التمرير الفعلي بمتغيّر واحد (--p) فلا يختلف المقبض عن التعبئة
    let lastP = -1;
    const syncProgress = () => {
        const max = maxScroll();
        const pos = Math.abs(rail.scrollLeft);
        const p = max > 1 ? Math.min(1, Math.max(0, pos / max)) : 0;
        if (Math.abs(p - lastP) < 0.0005) return;
        lastP = p;
        const value = String(p);
        if (scrub) scrub.style.setProperty('--p', value);
        if (thumb) thumb.style.setProperty('--p', value);
    };

    const sync = (authoritative = false) => {
        if (authoritative) measure();
        highlight(lockedIndex === null ? nearestIndex(rail.scrollLeft) : lockedIndex);
        syncProgress();
    };

    // ---- مراقب الاستقرار: يعتبر الشريط ساكناً بعد ثلاث لفات بلا حركة (بعد الزخم وبعد التصاق snap) ----
    //  لا نعتمد على touchend أو على مؤقت ثابت: كلاهما قد يقع في منتصف الزخم فيتغيّر اللون ثم يتغيّر مرة أخرى.
    let syncRaf = 0;
    let quietRaf = 0;
    let quietPos = null;
    let quietFrames = 0;
    let lastEmitKey = '';

    const cancelQuiet = () => {
        if (quietRaf) cancelAnimationFrame(quietRaf);
        quietRaf = 0;
        quietPos = null;
        quietFrames = 0;
    };

    const settleNow = () => {
        cancelQuiet();
        sync(true); // نقرأ الموضع النهائي مرة واحدة (موثوق حتى بعد إعادة محاذاة المتصفح)
        baseLeft = rail.scrollLeft;
        // كانت هذه نهاية محاذاة بطاقة اختارها المستخدم باللمس: لونها مطبَّق أصلاً، فلا نرسل لوناً مرة أخرى.
        // إن كان الشريط قد ابتعد عن نقطة تلك البطاقة فالاختيار لم يعد سارياً ونعود للمنطق العادي.
        if (lockedIndex !== null) {
            if (pinTarget !== null && Math.abs(rail.scrollLeft - pinTarget) <= 24) return;
            lockedIndex = null;
            pinTarget = null;
            sync(true); // إعادة حساب البطاقة النشطة بلا قفل
        }
        // آخر فعل للمستخدم كان اختيار بطاقة (أو لا تنقّل أصلاً) ⇒ استقرار الشريط تلقائي ولا يحق له تغيير اللون
        if (navAt <= choiceAt) return;
        // حماية من إرسال نفس اللون مرتين لنفس الموضع (مثلاً لو وصل scrollend بعد مراقب الاستقرار)
        const key = `${activeIndex}:${Math.round(rail.scrollLeft)}`;
        if (key === lastEmitKey) return;
        lastEmitKey = key;
        emit(cards[activeIndex], activeIndex);
    };

    const watchQuiet = () => {
        if (quietRaf) return;
        quietPos = rail.scrollLeft;
        quietFrames = 0;
        const tick = () => {
            // أثناء سحب ماوس/شريط تقدّم نشط: ننتظر ولا نرسل لوناً في منتصف السحب
            if (dragging && quietFrames < 90) {
                quietPos = rail.scrollLeft;
                quietFrames += 1;
                quietRaf = requestAnimationFrame(tick);
                return;
            }
            const pos = rail.scrollLeft;
            if (Math.abs(pos - quietPos) > 0.5) {
                quietPos = pos;
                quietFrames = 0;
                quietRaf = requestAnimationFrame(tick);
                return;
            }
            quietFrames += 1;
            if (quietFrames < 3) { quietRaf = requestAnimationFrame(tick); return; }
            quietRaf = 0;
            settleNow();
        };
        quietRaf = requestAnimationFrame(tick);
    };

    // ---- تغيير دائم للون الموقع (بدون مؤقت وبدون عودة تلقائية) ----
    //  لمس: click حقيقي فقط | ماوس: pointerenter (التمرير) أو click (النقر الحقيقي فقط، لا السحب)
    const apply = (card, index, markActive) => {
        const color = colorOf(card);
        if (!color) return;
        choiceAt = ++seq;
        baseLeft = rail.scrollLeft;
        lastEmitKey = ''; // اختيار جديد يلغي حماية التكرار حتى يُرسل لون البطاقة التالية عند الحاجة
        if (markActive) highlight(index);
        setTheme(color, { duration: 0.5 });
    };

    // نقلة البطاقة المختارة إلى موضع الالتصاق (الهاتف فقط): تصبح البطاقة النشطة هي الملوّنة فعلاً
    // (بدون هذا كان نقر بطاقة طرفية يغيّر لون الموقع ويترك الإطار على بطاقة أخرى).
    const snapCardToStart = (index) => {
        const card = cards[index];
        if (!card) return null;
        const cr = card.getBoundingClientRect();
        const rr = rail.getBoundingClientRect();
        const pad = parseFloat(getComputedStyle(rail).scrollPaddingInlineStart) || 0;
        const rtl = isRtl();
        const delta = rtl ? cr.right - (rr.right - pad) : cr.left - (rr.left + pad);
        const max = maxScroll();
        const raw = rail.scrollLeft + delta;
        const target = rtl ? Math.min(0, Math.max(-max, raw)) : Math.min(max, Math.max(0, raw));
        if (Math.abs(target - rail.scrollLeft) <= 1) return rail.scrollLeft;
        markNav();
        scrollTo(target, true);
        return target;
    };

    // هل هذه نقرة/لمسة حقيقية أم نقرة متولّدة عن سحب؟ (تعارض السحب والنقر على الهاتف)
    let tapStart = null;
    rail.addEventListener('pointerdown', (e) => {
        lockedIndex = null;
        if (e.pointerType === 'mouse') return;
        tapStart = { x: e.clientX, y: e.clientY, left: rail.scrollLeft, at: performance.now() };
    }, { passive: true });

    const isRealTap = (event) => {
        const now = performance.now();
        if (!event.detail) return true; // لوحة مفاتيح / تفعيل برمجي
        if (!tapStart) return now - lastScrollAt > 200;
        const moved = Math.abs(event.clientX - tapStart.x) > 10 || Math.abs(event.clientY - tapStart.y) > 10;
        const scrolled = Math.abs(rail.scrollLeft - tapStart.left) > 6;
        return !moved && !scrolled && now - lastScrollAt > 180 && now - tapStart.at < 900;
    };

    cards.forEach((card, index) => {
        card.addEventListener('pointerenter', (e) => {
            if (dragging) return;
            // اللمس يُطلق pointerenter عند بداية اللمس (قد تكون بداية تمرير): التلوين للمس عبر click فقط
            if (e.pointerType !== 'mouse') return;
            // دخول بلا حركة حقيقية للمؤشر (البطاقة تحركت تحته) لا يُعدّ اختياراً
            if (e.clientX === lastX && e.clientY === lastY) return;
            if (picked && !HOVER_RECOLORS_AFTER_PICK) return;
            apply(card, index, false);
        }, { passive: true });

        card.addEventListener('click', (event) => {
            if (performance.now() < blockClickUntil || !isRealTap(event)) return;
            picked = true;
            const touch = isTouchLike();
            if (touch && !wrapMode) {
                // نُبقي الإطار على البطاقة المختارة حتى تنتهي محاذاتها، فلا يقفز بين البطاقات في الطريق
                const target = snapCardToStart(index);
                if (target !== null) {
                    lockedIndex = index;
                    pinTarget = target;
                }
            }
            // على الهاتف: البطاقة المختارة تصبح هي النشطة وتُحاذى على نقطة الالتصاق، فيتطابق اللون والإطار
            apply(card, index, touch);
        });
    });

    // ---- تحسين الأداء: لا نُشغّل حركة طفو الصور لبطاقة خارج الشاشة (توفير GPU على الهاتف) ----
    //  نضيف "is-idle" للبطاقات البعيدة فقط؛ فإن تعطّل هذا السكربت لا تتوقف أي حركة (لا تغيير في الشكل).
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => entry.target.classList.toggle('is-idle', !entry.isIntersecting));
        }, { rootMargin: '240px 0px' });
        cards.forEach((card) => io.observe(card));
    }

    // ---- وضع الالتفاف (أكثر من 12 نكهة): لا يوجد تمرير أفقي، الصفحة تتمرر عمودياً ----
    if (wrapMode) {
        let current = null;
        let raf = 0;
        let settleRaf = 0;
        let settleLast = null;
        let settleFrames = 0;

        // البطاقة الأقرب إلى منتصف الشاشة عمودياً (من بين المرئية فقط)
        const centered = () => {
            const vh = window.innerHeight;
            const mid = vh / 2;
            let best = null;
            let bestDist = Infinity;
            cards.forEach((card) => {
                const r = card.getBoundingClientRect();
                if (r.bottom < 0 || r.top > vh) return;
                const d = Math.abs(r.top + r.height / 2 - mid);
                if (d < bestDist - 1) { bestDist = d; best = card; }
            });
            return best ? { card: best, dist: bestDist, vh } : null;
        };

        const markActive = () => {
            const hit = centered();
            if (hit && hit.card !== current) {
                current = hit.card;
                highlight(cards.indexOf(current));
            }
            return hit;
        };

        const settleWrap = () => {
            const hit = markActive();
            if (!hit || !isTouchLike()) return;
            // لا نلوّن إلا إذا كانت البطاقة فعلاً في منطقة منتصف الشاشة
            const r = hit.card.getBoundingClientRect();
            const inBand = r.top <= hit.vh / 2 && r.bottom >= hit.vh / 2;
            if (!inBand && hit.dist > hit.vh * 0.3) return;
            emit(hit.card, cards.indexOf(hit.card));
        };

        // نفس منطق مراقب الاستقرار: لا نلوّن أثناء التمرير، بل بعد سكون الصفحة
        const watchWrapQuiet = () => {
            if (settleRaf) return;
            settleLast = window.scrollY;
            settleFrames = 0;
            const tick = () => {
                const pos = window.scrollY;
                if (Math.abs(pos - settleLast) > 0.5) {
                    settleLast = pos;
                    settleFrames = 0;
                    settleRaf = requestAnimationFrame(tick);
                    return;
                }
                settleFrames += 1;
                if (settleFrames < 3) { settleRaf = requestAnimationFrame(tick); return; }
                settleRaf = 0;
                settleWrap();
            };
            settleRaf = requestAnimationFrame(tick);
        };

        const onScroll = () => {
            lastScrollAt = performance.now();
            if (!raf) raf = requestAnimationFrame(() => { raf = 0; markActive(); });
            watchWrapQuiet();
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onScroll, { passive: true });
        if ('onscrollend' in window) window.addEventListener('scrollend', watchWrapQuiet, { passive: true });
        markActive();
        return;
    }

    // ---- وضع التمرير الأفقي (12 نكهة أو أقل) ----
    // إعادة القياس عند تغيّر المقاس/الدوران/تحميل الخطوط (ليست أثناء التمرير)
    let resizeRaf = 0;
    const remeasure = () => {
        if (resizeRaf) return;
        resizeRaf = requestAnimationFrame(() => {
            resizeRaf = 0;
            measure();
            sync(false);
        });
    };
    window.addEventListener('resize', remeasure, { passive: true });
    window.addEventListener('orientationchange', remeasure, { passive: true });
    if ('ResizeObserver' in window) new ResizeObserver(remeasure).observe(rail);
    if (document.fonts?.ready) document.fonts.ready.then(remeasure).catch(() => {});
    // بعد انتهاء حركة دخول البطاقات (scroll-fx) تعود الأبعاد نهائية: نعيد القياس مرة واحدة
    window.addEventListener('load', remeasure, { once: true });
    window.addEventListener('intro:done', remeasure, { once: true });

    // لمس الشريط: أي تمرير أثناء بقاء الإصبع (وزخمه بعده) تنقّل من المستخدم
    rail.addEventListener('touchstart', () => { touching = true; lockedIndex = null; }, { passive: true });
    rail.addEventListener('touchend', () => { touching = false; }, { passive: true });
    rail.addEventListener('touchcancel', () => { touching = false; }, { passive: true });
    // عجلة/لوحة لمس أفقية فوق الشريط
    rail.addEventListener('wheel', (e) => { if (e.shiftKey || Math.abs(e.deltaX) > Math.abs(e.deltaY)) { lockedIndex = null; markNav(); } }, { passive: true });

    rail.addEventListener('scroll', () => {
        lastScrollAt = performance.now();
        // على الهاتف لا يوجد hover: أي تمرير هو تنقّل من المستخدم. على الحاسوب نشترط حركة فعلية (> 12px)
        if (touching || isTouchLike() || Math.abs(rail.scrollLeft - baseLeft) > 12) markNav();
        // تحديث الإطار والعداد بإطار واحد لكل تمرير (بلا قراءة layout)
        if (!syncRaf) syncRaf = requestAnimationFrame(() => { syncRaf = 0; sync(false); });
        watchQuiet();
    }, { passive: true });

    // scrollend مدعوم في Chromium فقط: نستخدمه لتقصير الانتظار، والقرار النهائي دائماً لمراقب الاستقرار
    rail.addEventListener('scrollend', () => { if (!dragging) watchQuiet(); }, { passive: true });

    // سحب بالماوس على الكمبيوتر (اللمس يتولاه المتصفح بسلاسة أصلاً)
    if (isFinePointer()) {
        const DRAG_THRESHOLD = 5;
        let startX = 0, startLeft = 0, down = false, isDragging = false;
        let lastDragX = 0, lastDragT = 0, velocity = 0;

        // منع سحب الصور/الروابط الأصلي في المتصفح لأنه يقطع السحب
        rail.addEventListener('dragstart', (e) => e.preventDefault());

        rail.addEventListener('pointerdown', (e) => {
            if (e.pointerType !== 'mouse' || e.button !== 0) return;
            down = true; isDragging = false;
            startX = lastDragX = e.clientX; startLeft = rail.scrollLeft;
            lastDragT = performance.now(); velocity = 0;
        });
        window.addEventListener('pointermove', (e) => {
            if (!down) return;
            if (!isDragging && Math.abs(e.clientX - startX) > DRAG_THRESHOLD) {
                isDragging = true; dragging = true; markNav();
                blockClickUntil = performance.now() + 400;
                rail.classList.add('is-dragging');
            }
            if (!isDragging) return;
            const now = performance.now();
            const dt = now - lastDragT;
            if (dt > 0) velocity = 0.8 * velocity + 0.2 * ((e.clientX - lastDragX) / dt);
            lastDragX = e.clientX; lastDragT = now;
            rail.scrollLeft = startLeft - (e.clientX - startX);
        }, { passive: true });

        const stop = () => {
            if (!down) return;
            down = false;
            if (!isDragging) return;
            isDragging = false;
            rail.classList.remove('is-dragging');
            markNav();
            // النقرة الناتجة عن إفلات السحب تُلغى (لا يتغير لون الموقع)
            blockClickUntil = performance.now() + 400;
            const fling = Math.max(-700, Math.min(700, -velocity * 260));
            if (Math.abs(velocity) > 0.3) rail.scrollBy({ left: fling, behavior: 'smooth' });
            dragging = false;
            sync(false);
            watchQuiet();
        };
        window.addEventListener('pointerup', stop);
        window.addEventListener('pointercancel', stop);
        // مرحلة الالتقاط: تسبق مستمعي البطاقات فلا يصلهم click بعد السحب
        rail.addEventListener('click', (e) => {
            if (performance.now() >= blockClickUntil) return;
            e.preventDefault();
            e.stopPropagation();
        }, true);
    }

    // ---- شريط التقدّم السفلي قابل للسحب: تحريكه يحرّك البطاقات والنكهات (ولون الموقع) فوراً ----
    if (scrub) {
        const seek = (clientX) => {
            // المسار الفعلي (بدون هوامش المقبض) هو مرجع الحساب، فيطابق موضع المؤشر موضع المقبض تماماً
            const r = (scrub.querySelector('.rail-progress') || scrub).getBoundingClientRect();
            if (!r.width) return;
            const rtl = isRtl();
            let t = (clientX - r.left) / r.width;
            if (rtl) t = 1 - t;
            t = Math.min(1, Math.max(0, t));
            rail.scrollLeft = (rtl ? -1 : 1) * t * maxScroll();
            markNav();
            sync(false);
        };

        // بعد الإفلات: نُلصق الشريط على أقرب نقطة التصاق إلى المكان الذي أُفلت عنده المقبض (نفس منطق scroll-snap)،
        // مع حصر كل نقطة في مدى التمرير الفعلي حتى تبقى بداية الشريط ونهايته قابلتين للوصول.
        const snapToNearest = () => {
            const cs = getComputedStyle(rail);
            const pad = parseFloat(cs.scrollPaddingInlineStart) || parseFloat(cs.paddingInlineStart) || 0;
            const rr = rail.getBoundingClientRect();
            const rtl = isRtl();
            const max = maxScroll();
            const lo = rtl ? -max : 0;
            const hi = rtl ? 0 : max;
            const now = rail.scrollLeft;
            let target = now;
            let best = Infinity;
            cards.forEach((card) => {
                const cr = card.getBoundingClientRect();
                const delta = rtl ? cr.right - (rr.right - pad) : cr.left - (rr.left + pad);
                const cand = Math.min(hi, Math.max(lo, now + delta));
                const dist = Math.abs(cand - now);
                if (dist < best - 0.5) { best = dist; target = cand; }
            });
            if (Math.abs(target - now) > 1) scrollTo(target, true);
        };

        const end = (e) => {
            if (!scrubbing) return;
            scrubbing = false;
            dragging = false;
            scrub.classList.remove('is-scrubbing');
            rail.classList.remove('is-scrubbing');
            try { scrub.releasePointerCapture(e.pointerId); } catch (err) { /* ignore */ }
            markNav();
            snapToNearest();
            watchQuiet(); // بعد انتهاء الالتصاق: لون الموقع = لون البطاقة المستقرة
        };

        scrub.addEventListener('pointerdown', (e) => {
            if (e.pointerType === 'mouse' && e.button !== 0) return;
            scrubbing = true;
            dragging = true; // يمنع الـ settle التلقائي أثناء السحب
            lockedIndex = null;
            scrub.classList.add('is-scrubbing');
            rail.classList.add('is-scrubbing');
            try { scrub.setPointerCapture(e.pointerId); } catch (err) { /* ignore */ }
            seek(e.clientX);
            e.preventDefault();
        });
        scrub.addEventListener('pointermove', (e) => { if (scrubbing) seek(e.clientX); });
        scrub.addEventListener('pointerup', end);
        scrub.addEventListener('pointercancel', end);
        scrub.addEventListener('lostpointercapture', end);

        // لوحة المفاتيح (slider): الأسهم تتحرك فيزيائياً يميناً/يساراً مثل الشريط نفسه، و Home / End للبداية والنهاية
        scrub.addEventListener('keydown', (e) => {
            const stepPx = rail.clientWidth * 0.72;
            const sign = isRtl() ? -1 : 1;
            if (e.key === 'ArrowRight' || e.key === 'ArrowUp') { e.preventDefault(); markNav(); rail.scrollBy({ left: stepPx, behavior: reduced ? 'auto' : 'smooth' }); }
            else if (e.key === 'ArrowLeft' || e.key === 'ArrowDown') { e.preventDefault(); markNav(); rail.scrollBy({ left: -stepPx, behavior: reduced ? 'auto' : 'smooth' }); }
            else if (e.key === 'Home') { e.preventDefault(); markNav(); scrollTo(0, true); }
            else if (e.key === 'End') { e.preventDefault(); markNav(); scrollTo(sign * maxScroll(), true); }
        });
    }

    // الاتجاه يتبع لغة الصفحة: في RTL التقدّم = scrollLeft سالب، وفي LTR موجب
    const dir = () => (isRtl() ? -1 : 1);
    const step = () => rail.clientWidth * 0.72;
    const stepBehavior = () => (reduced ? 'auto' : 'smooth');
    document.querySelector('[data-rail-next]')?.addEventListener('click', () => { lockedIndex = null; markNav(); rail.scrollBy({ left: dir() * step(), behavior: stepBehavior() }); });
    document.querySelector('[data-rail-prev]')?.addEventListener('click', () => { lockedIndex = null; markNav(); rail.scrollBy({ left: -dir() * step(), behavior: stepBehavior() }); });
    rail.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') { event.preventDefault(); lockedIndex = null; markNav(); rail.scrollBy({ left: -rail.clientWidth * 0.55, behavior: stepBehavior() }); }
        if (event.key === 'ArrowRight') { event.preventDefault(); lockedIndex = null; markNav(); rail.scrollBy({ left: rail.clientWidth * 0.55, behavior: stepBehavior() }); }
    });

    // القياس الأول + تحديد البطاقة النشطة (بدون إرسال لون: initTheme يضبط لون البداية)
    measure();
    sync(false);
}
