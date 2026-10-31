import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { splitWords, prefersReducedMotion, isFinePointer } from './utils';

gsap.registerPlugin(ScrollTrigger);
// على الهاتف: تغيّر شريط العنوان أثناء التمرير يغيّر innerHeight — لا نريد refresh كامل يقفز بالصفحة أو يجمّد التمرير
ScrollTrigger.config({ ignoreMobileResize: true });

function progressBar() {
    const bar = document.querySelector('[data-scroll-progress]');
    if (!bar) return;
    gsap.fromTo(bar, { scaleX: 0 }, {
        scaleX: 1, ease: 'none',
        scrollTrigger: { start: 0, end: 'max', scrub: 0.3 },
    });
}

function nav() {
    const el = document.querySelector('[data-site-nav]');
    if (!el) return;
    ScrollTrigger.create({ start: 80, onToggle: (self) => el.classList.toggle('is-scrolled', self.isActive) });
}

function headings() {
    const selector = '.section-heading h2, .story-intro h2, .ingredients-heading h2, .footer-top h2';
    document.querySelectorAll(selector).forEach((h) => {
        const words = splitWords(h);
        gsap.set(words, { yPercent: 115, rotation: 4 });
        ScrollTrigger.create({
            trigger: h, start: 'top 88%', once: true,
            onEnter: () => gsap.to(words, { yPercent: 0, rotation: 0, duration: 1, stagger: 0.08, ease: 'power4.out' }),
        });
    });
}

function reveals() {
    const items = gsap.utils.toArray(
        '.eyebrow, .section-heading > p, .ingredients-heading > p, .story-intro > p:last-child, .newsletter-form, .footer-bottom'
    ).filter((el) => !el.closest('.hero') && !el.closest('.admin-body'));
    if (!items.length) return;

    gsap.set(items, { y: 50, opacity: 0 });
    ScrollTrigger.batch(items, {
        start: 'top 90%', once: true,
        onEnter: (batch) => gsap.to(batch, { y: 0, opacity: 1, duration: 0.9, stagger: 0.1, ease: 'power3.out' }),
    });
}

/**
 * ظهور بطاقات المكوّنات.
 * clearProps يزيل transform/opacity المضافَين inline بعد انتهاء الحركة؛ فلا يبقى على البطاقة أي transform
 * يربك التقليب أو الـ stacking context أو اللمس على الهاتف.
 */
function ingredientReveals() {
    const cards = gsap.utils.toArray('.ingredient-card');
    if (!cards.length) return;
    const small = !isFinePointer() || window.innerWidth <= 900;

    gsap.set(cards, { y: small ? 24 : 50, opacity: 0 });
    ScrollTrigger.batch(cards, {
        start: 'top 92%', once: true,
        onEnter: (batch) => gsap.to(batch, {
            y: 0, opacity: 1, duration: small ? 0.6 : 0.9, stagger: 0.1, ease: 'power3.out',
            overwrite: true, clearProps: 'transform,opacity',
        }),
    });
}

function railEntrance() {
    const rail = document.querySelector('[data-flavor-rail]');
    if (!rail) return;
    const cards = rail.querySelectorAll('[data-flavor-card]');
    gsap.set(cards, { y: 110, opacity: 0, rotation: 4 });
    ScrollTrigger.create({
        trigger: rail, start: 'top 85%', once: true,
        onEnter: () => gsap.to(cards, {
            y: 0, opacity: 1, rotation: 0, duration: 1.1, stagger: 0.09, ease: 'power4.out',
        }),
    });
}

function storyImages() {
    document.querySelectorAll('.story-media .media-img').forEach((img) => {
        gsap.fromTo(img, { scale: 1.12 }, {
            scale: 1, ease: 'none',
            scrollTrigger: { trigger: '.story-track', start: 'top bottom', end: 'bottom top', scrub: 0.6 },
        });
    });
}

function magnetic() {
    if (!isFinePointer()) return;
    document.querySelectorAll('.button, .nav-order, .rail-controls button').forEach((el) => {
        const xTo = gsap.quickTo(el, 'x', { duration: 0.5, ease: 'power3.out' });
        const yTo = gsap.quickTo(el, 'y', { duration: 0.5, ease: 'power3.out' });
        el.addEventListener('pointermove', (e) => {
            const r = el.getBoundingClientRect();
            xTo((e.clientX - r.left - r.width / 2) * 0.3);
            yTo((e.clientY - r.top - r.height / 2) * 0.4);
        }, { passive: true });
        el.addEventListener('pointerleave', () => { xTo(0); yTo(0); });
    });
}

export function initScrollFx() {
    if (prefersReducedMotion()) return;
    progressBar();
    nav();
    headings();
    reveals();
    ingredientReveals();
    railEntrance();
    storyImages();
    magnetic();
    // بعد تحميل الخطوط والصور نعيد حساب المواضع مرة واحدة
    window.addEventListener('load', () => ScrollTrigger.refresh(), { once: true });
}
