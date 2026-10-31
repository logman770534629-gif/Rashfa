import gsap from 'gsap';

const root = document.documentElement;
export const DEFAULT_THEME = '#FFB300'; // لون المانجو (Rich Mango)
const KEEP_THEME_KEY = 'rashfa:keep_theme';
const LANG_FLAG_KEY = 'rashfa:lang'; // نفس علم lang-switch.js (مكرّر هنا لتفادي استيراد دائري)
const DEFAULT = DEFAULT_THEME;

let shown = null;   // palette currently on screen
let target = null;  // color we are heading to
let tween = null;

const luminance = (color) => {
    const [r, g, b] = gsap.utils.splitColor(color).slice(0, 3).map((v) => {
        v /= 255;
        return v <= 0.03928 ? v / 12.92 : ((v + 0.055) / 1.055) ** 2.4;
    });
    return 0.2126 * r + 0.7152 * g + 0.0722 * b;
};

function palette(color) {
    const light = luminance(color) > 0.42;
    return {
        color,
        deep: gsap.utils.interpolate(color, '#000000', 0.55),
        soft: gsap.utils.interpolate(color, '#ffffff', 0.88),
        on: light ? '#24140b' : '#ffffff',
        btn: light ? '#ffffff' : gsap.utils.interpolate(color, '#000000', 0.55),
    };
}

function apply(p) {
    root.style.setProperty('--theme', p.color);
    root.style.setProperty('--theme-deep', p.deep);
    root.style.setProperty('--theme-soft', p.soft);
    root.style.setProperty('--on-theme', p.on);
    root.style.setProperty('--btn-fg', p.btn);
    shown = p;
}

/** Smoothly (or instantly, duration 0) moves the whole site to a flavor color. */
export function setTheme(color, { duration = 0.9 } = {}) {
    if (!color || color === target) return;
    target = color;
    const next = palette(color);

    tween?.kill();
    if (!shown || !duration) { apply(next); return; }

    const from = shown;
    const p = { t: 0 };
    tween = gsap.to(p, {
        t: 1,
        duration,
        ease: 'power2.inOut',
        onUpdate: () => apply({
            color: gsap.utils.interpolate(from.color, next.color, p.t),
            deep: gsap.utils.interpolate(from.deep, next.deep, p.t),
            soft: gsap.utils.interpolate(from.soft, next.soft, p.t),
            on: gsap.utils.interpolate(from.on, next.on, p.t),
            btn: gsap.utils.interpolate(from.btn, next.btn, p.t),
        }),
        onComplete: () => apply(next),
    });
}

export function initTheme() {
    // اللون يتغير ويثبت: عند الضغط/التمرير على بطاقة، أو عند استقرار شريط النكهات على بطاقة (سحب/أسهم).
    // المهمة 2-د: لا نقبل أي إشارة flavor:active قبل انتهاء شاشة "سن بيرد" (intro:done)، حتى لا يتسرّب لون
    // نكهة من حدث تمرير تلقائي أثناء التحميل. النقر/اللمس/التمرير على بطاقة يعمل مباشرة كما كان (الموقع مقفل أثناء الافتتاحية أصلاً).
    let siteReady = false;
    window.addEventListener('intro:done', () => { siteReady = true; }, { once: true });
    document.addEventListener('flavor:active', (e) => {
        if (!siteReady) return;
        setTheme(e.detail.color, { duration: 0.5 });
    });

    // بعد تبديل اللغة من داخل الموقع (إعادة تحميل واحدة): نعيد نفس اللون الذي كان على الموقع قبل التبديل (مرة واحدة فقط).
    // هذا المفتاح يُكتب فقط لحظة الضغط على زر اللغة ويُمسح فور قراءته؛ ولا يُحفظ أي لون عند التحديث العادي.
    // تحصين: لا نعتمد اللون المحفوظ إلا إذا كان علم تبديل اللغة (rashfa:lang) موجوداً أيضاً، فلا يبقى مفتاح قديم عالقاً ليلوّن تحديثاً عادياً.
    let kept = null;
    let fromLangSwitch = false;
    try {
        fromLangSwitch = sessionStorage.getItem(LANG_FLAG_KEY) !== null;
        kept = sessionStorage.getItem(KEEP_THEME_KEY);
        if (kept !== null) sessionStorage.removeItem(KEEP_THEME_KEY);
    } catch (e) { /* ignore */ }
    if (fromLangSwitch && kept && window.CSS?.supports?.('color', kept)) { setTheme(kept, { duration: 0 }); return; }

    // أي تحميل أو تحديث عادي: لون المانجو دائماً (يطابق قيم :root في CSS فلا يظهر أي لون آخر قبل الرسم)
    setTheme(DEFAULT, { duration: 0 });
}

/** يحفظ اللون الحالي للموقع كما هو ليبقى بعد إعادة التحميل الناتجة عن تبديل اللغة. */
export function stashCurrentTheme() {
    const color = target || DEFAULT;
    try { sessionStorage.setItem(KEEP_THEME_KEY, color); } catch (e) { /* ignore */ }
    return color;
}
