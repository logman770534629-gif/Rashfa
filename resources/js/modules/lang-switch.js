import gsap from 'gsap';
import { prefersReducedMotion } from './utils';
import { stashCurrentTheme } from './theme';

const FLAG = 'rashfa:lang';

/** هل غُيّرت اللغة داخل شاشة سن بيرد (والموقع خلفها ما زال بلغته القديمة)؟ */
export const langState = { dirty: false };

/** يُستدعى من intro.js: هل وصلنا لتوّنا من تبديل لغة؟ يعيد موضع التمرير المحفوظ ويمسح العلم. */
export function consumeLangSwitch() {
    try {
        const raw = sessionStorage.getItem(FLAG);
        if (raw === null) return null;
        sessionStorage.removeItem(FLAG);
        return { y: parseInt(raw, 10) || 0 };
    } catch (e) {
        return null;
    }
}

/** يضع علم "إعادة التحميل بعد تبديل لغة" (يُستخدم عند دخول الموقع بعد تغيير اللغة في الشاشة الافتتاحية). */
export function flagLangReload(y = 0) {
    try { sessionStorage.setItem(FLAG, String(Math.round(y))); } catch (e) { /* ignore */ }
}

/** ستار ناعم يُرفع بعد التبديل (الصفحة الجديدة تظهر بتلاشٍ بدل وميض). */
export function liftLangVeil() {
    const root = document.documentElement;
    const veil = document.querySelector('[data-lang-veil]');
    if (!root.classList.contains('lang-switching')) return;
    if (!veil || prefersReducedMotion()) { root.classList.remove('lang-switching'); return; }
    gsap.to(veil, {
        opacity: 0, duration: 0.7, ease: 'power2.out', delay: 0.05,
        onComplete: () => { root.classList.remove('lang-switching'); gsap.set(veil, { clearProps: 'all' }); },
    });
    // شبكة أمان: لو تعطّل محرّك الحركة لأي سبب، لا يبقى الستار حاجبًا للموقع
    setTimeout(() => {
        if (!root.classList.contains('lang-switching')) return;
        root.classList.remove('lang-switching');
        gsap.set(veil, { clearProps: 'all' });
    }, 1200);
}

/* ------------------------------------------------------------------ */
/*  الكشف القطري (Slash Reveal): نُظهر الستار المطويّ ثم نفتحه بـCSS  */
/* ------------------------------------------------------------------ */
const SLASH_MS = 600;
const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

function openLangVeil() {
    const veil = document.querySelector('[data-lang-veil]');
    if (!veil) return null;

    // نظهر الستار أولاً ونحسب حالته المطويّة، ثم نضيف الكلاس ليبدأ الانتقال
    veil.style.display = 'block';
    void veil.offsetWidth;
    document.documentElement.classList.add('lang-switching');

    return veil;
}

/* ------------------------------------------------------------------ */
/*  تبديل اللغة داخل شاشة سن بيرد: بلا إعادة تحميل ولا تخطٍّ للشاشة   */
/* ------------------------------------------------------------------ */
const INTRO_TEXT = [
    '[data-sb-tagline]',
    '.sb-cta span',
    '.sb-site span',
    '.sb-map .map-btn-label',
    '.sb-logo-fallback',
];

async function switchIntroInPlace(btn) {
    const intro = document.getElementById('sunbird-intro');
    if (!intro) return;

    const reduced = prefersReducedMotion();
    const veil = reduced ? null : openLangVeil();

    // نترك الكشف القطري يكتمل (0.6s) قبل جلب الصفحة الجديدة وتحديث النصوص
    if (veil) await wait(SLASH_MS);

    try {
        // 1) نحفظ اللغة على الخادم (جلسة + كوكي) عبر مسار /lang/{locale} بصيغة JSON
        const res = await fetch(btn.getAttribute('href'), {
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!res.ok) throw new Error('lang request failed');
        const meta = await res.json();

        // 2) نجلب الصفحة بلغتها الجديدة ونقرأ منها نصوص الشاشة الافتتاحية فقط
        const html = await (await fetch(window.location.href.split('#')[0], {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })).text();
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const next = doc.getElementById('sunbird-intro');
        if (!next) throw new Error('intro not found in response');

        const pairs = [];
        INTRO_TEXT.forEach((sel) => {
            const cur = intro.querySelector(sel);
            const nxt = next.querySelector(sel);
            if (cur && nxt && cur.textContent !== nxt.textContent) pairs.push([cur, nxt.textContent]);
        });
        const img = intro.querySelector('.sb-logo img');
        const nextImg = next.querySelector('.sb-logo img');
        const nextLinks = [...next.querySelectorAll('[data-sb-social] a')];
        const curLinks = [...intro.querySelectorAll('[data-sb-social] a')];

        let applied = false;
        const apply = () => {
            pairs.forEach(([el, text]) => { el.textContent = text; });
            if (img && nextImg) img.alt = nextImg.alt;
            curLinks.forEach((a, i) => {
                const n = nextLinks[i];
                if (n && n.hasAttribute('aria-label')) a.setAttribute('aria-label', n.getAttribute('aria-label'));
            });

            // اتجاه الصفحة ولغتها (يغيّر الخط من Tajawal إلى Inter وموضع الأزرار)
            const root = document.documentElement;
            root.lang = meta.locale;
            root.dir = meta.dir;
            document.body.className = document.body.className.replace(/\blang-\w+\b/, 'lang-' + meta.locale);
            if (doc.title) document.title = doc.title;

            // كل أزرار اللغة (الافتتاحية + القائمة) تتحدّث لتشير إلى اللغة المقابلة الجديدة
            const nextSwitches = [...doc.querySelectorAll('[data-lang-switch]')];
            document.querySelectorAll('[data-lang-switch]').forEach((a, i) => {
                const n = nextSwitches[i];
                if (!n) return;
                ['href', 'data-locale', 'hreflang', 'lang', 'aria-label', 'title'].forEach((attr) => a.setAttribute(attr, n.getAttribute(attr) || ''));
                const front = a.querySelector('.ls-front');
                const back = a.querySelector('.ls-back');
                const nFront = n.querySelector('.ls-front');
                const nBack = n.querySelector('.ls-back');
                if (front && nFront) front.textContent = nFront.textContent;
                if (back && nBack) back.textContent = nBack.textContent;
            });
            langState.dirty = true;
            applied = true;
        };

        const orb = btn.querySelector('.ls-orb');
        if (reduced) { apply(); return; }

        const targets = pairs.map(([el]) => el);
        await Promise.race([
            new Promise((resolve) => {
                gsap.timeline({ onComplete: resolve })
                    .to(orb, { rotationY: 180, duration: 0.5, ease: 'power2.inOut' }, 0)
                    .to(targets, { opacity: 0, y: -10, duration: 0.25, ease: 'power2.in' }, 0)
                    .add(apply, 0.3)
                    .set(orb, { rotationY: 0 }, 0.52) // الوجه الخلفي = الوجه الأمامي الجديد، فلا يظهر أي قفز
                    .fromTo(targets, { opacity: 0, y: 12 }, { opacity: 1, y: 0, duration: 0.45, ease: 'power3.out', clearProps: 'opacity,transform' }, 0.55);
            }),
            // شبكة أمان: لا نُبقي الستار معلّقًا لو تعطّلت الحركة
            wait(1600).then(() => { if (!applied) apply(); }),
        ]);
    } finally {
        // نرفع الستار بعد أن تصبح النصوص الجديدة جاهزة
        if (veil) liftLangVeil();
    }
}

export function initLangSwitch() {
    document.querySelectorAll('[data-lang-switch]').forEach((btn) => {
        let busy = false;
        btn.addEventListener('click', async (e) => {
            if (e.metaKey || e.ctrlKey || e.shiftKey) return;
            e.preventDefault();
            if (busy) return;
            busy = true;

            // داخل شاشة سن بيرد: نبدّل نصوصها فقط ونبقى فيها
            if (btn.closest('#sunbird-intro') && document.getElementById('sunbird-intro')) {
                try {
                    await switchIntroInPlace(btn);
                } catch (err) {
                    console.error(err);
                } finally {
                    busy = false;
                }
                return;
            }

            // داخل الموقع: كشف قطري سريع ثم إعادة تحميل بنفس موضع التمرير
            const href = btn.getAttribute('href');
            // نحفظ لون الثيم الحالي ليبقى كما هو بعد إعادة التحميل (بلا تغيير عشوائي)
            stashCurrentTheme();
            const go = () => {
                flagLangReload(window.scrollY);
                window.location.assign(href);
            };

            if (prefersReducedMotion()) { go(); return; }

            const veil = openLangVeil();
            if (!veil) { go(); return; }

            const orb = btn.querySelector('.ls-orb');
            if (orb) gsap.to(orb, { rotationY: 180, duration: 0.5, ease: 'power2.inOut' });

            // لا نعيد التوجيه قبل أن يكتمل الكشف القطري (0.6s)
            setTimeout(go, SLASH_MS);
        });
    });
}
