import gsap from 'gsap';
import { prefersReducedMotion } from './utils';

/**
 * الناف بار:
 *  - قائمة الهاتف/التابلت بملء الشاشة (برجر) تُغلق بالنقر على رابط أو بـ Esc، وتقفل تمرير الصفحة
 *  - الرابط النشط حسب القسم الظاهر (خط سفلي متحرك)
 *  - تأثير مغناطيسي ناعم للروابط وزر "تصفّح النكهات" على أجهزة الماوس
 */
export function initNav() {
    const nav = document.querySelector('[data-site-nav]');
    const toggle = nav?.querySelector('[data-nav-toggle]');
    const panel = nav?.querySelector('[data-nav-panel]');
    if (!nav || !toggle || !panel) return;

    const root = document.documentElement;

    /* ---------- القائمة الصغيرة ---------- */
    const setOpen = (open) => {
        nav.classList.toggle('is-open', open);
        root.classList.toggle('nav-open', open);
        toggle.setAttribute('aria-expanded', String(open));
    };

    toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        setOpen(!nav.classList.contains('is-open'));
    });
    panel.addEventListener('click', (e) => { if (e.target.closest('a')) setOpen(false); });
    document.addEventListener('click', (e) => { if (nav.classList.contains('is-open') && !nav.contains(e.target)) setOpen(false); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && nav.classList.contains('is-open')) { setOpen(false); toggle.focus(); } });
    window.matchMedia('(min-width: 821px)').addEventListener('change', (e) => { if (e.matches) setOpen(false); });

    /* ---------- الرابط النشط ---------- */
    const links = [...nav.querySelectorAll('[data-nav-link]')];
    const byId = new Map(links.map((a) => [a.getAttribute('href').replace('#', ''), a]));
    const sections = [...byId.keys()].map((id) => document.getElementById(id)).filter(Boolean);

    if (sections.length && 'IntersectionObserver' in window) {
        const setActive = (id) => {
            links.forEach((a) => {
                const on = a.getAttribute('href') === '#' + id;
                a.classList.toggle('is-active', on);
                if (on) a.setAttribute('aria-current', 'location'); else a.removeAttribute('aria-current');
            });
        };
        const io = new IntersectionObserver((entries) => {
            entries.forEach((en) => { if (en.isIntersecting) setActive(en.target.id); });
        }, { rootMargin: '-45% 0px -50% 0px', threshold: 0 });
        sections.forEach((sec) => io.observe(sec));

        // الأعلى (الهيرو) = لا رابط نشط
        window.addEventListener('scroll', () => {
            if (window.scrollY < 120) links.forEach((a) => { a.classList.remove('is-active'); a.removeAttribute('aria-current'); });
        }, { passive: true });
    }

    /* ---------- التأثير المغناطيسي ---------- */
    const fine = window.matchMedia('(pointer: fine)').matches;
    if (!fine || prefersReducedMotion()) return;

    const targets = [...nav.querySelectorAll('[data-nav-link]')].map((el) => {
        const strength = 0.22;
        return {
            el, strength,
            x: gsap.quickTo(el, 'x', { duration: 0.6, ease: 'power3.out' }),
            y: gsap.quickTo(el, 'y', { duration: 0.6, ease: 'power3.out' }),
        };
    });

    const mq = window.matchMedia('(min-width: 821px)');
    const RADIUS = 70;

    nav.addEventListener('pointermove', (e) => {
        if (!mq.matches) return;
        targets.forEach((t) => {
            const r = t.el.getBoundingClientRect();
            const cx = r.left + r.width / 2 - gsap.getProperty(t.el, 'x');
            const cy = r.top + r.height / 2 - gsap.getProperty(t.el, 'y');
            const dx = e.clientX - cx;
            const dy = e.clientY - cy;
            const reach = Math.max(r.width, r.height) / 2 + RADIUS;
            const clamp = (v) => Math.max(-8, Math.min(8, v)); // حد أقصى صغير حتى لا تتداخل العناصر
            if (Math.hypot(dx, dy) < reach) { t.x(clamp(dx * t.strength)); t.y(clamp(dy * t.strength)); }
            else { t.x(0); t.y(0); }
        });
    });
    nav.addEventListener('pointerleave', () => targets.forEach((t) => { t.x(0); t.y(0); }));
}
