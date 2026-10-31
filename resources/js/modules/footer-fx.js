import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { prefersReducedMotion } from './utils';

gsap.registerPlugin(ScrollTrigger);

/**
 * فوتر حيوي:
 *  - توهج ناعم يتبع المؤشر داخل الفوتر.
 *  - الكلمتان العملاقتان تظهران بتلاشٍ ناعم مع Parallax بطيء.
 *  - الختم الدوّار يتسارع عند التفاعل مع حقل البريد.
 *  - شرائح المزايا وروابط التواصل تظهر بتتابع خفيف.
 */
export function initFooterFx() {
    const footer = document.querySelector('[data-site-footer]');
    if (!footer || prefersReducedMotion()) return;

    /* ---------- 1) توهج يتبع المؤشر ---------- */
    if (footer.querySelector('[data-footer-aurora]')) {
        let frame = null;
        let px = 50;
        let py = 22;

        footer.addEventListener('pointermove', (event) => {
            const rect = footer.getBoundingClientRect();
            px = ((event.clientX - rect.left) / rect.width) * 100;
            py = ((event.clientY - rect.top) / rect.height) * 100;
            if (frame) return;
            frame = requestAnimationFrame(() => {
                footer.style.setProperty('--mx', `${px}%`);
                footer.style.setProperty('--my', `${py}%`);
                frame = null;
            });
        }, { passive: true });

        footer.addEventListener('pointerleave', () => {
            footer.style.setProperty('--mx', '50%');
            footer.style.setProperty('--my', '22%');
        });
    }

    /* ---------- 1-ب) الهاتف: توهج الفوتر يشتعل عند ظهوره وعند اللمس ---------- */
    footer.addEventListener('pointerdown', (event) => {
        if (event.pointerType === 'mouse') return;
        const rect = footer.getBoundingClientRect();
        footer.style.setProperty('--mx', `${((event.clientX - rect.left) / rect.width) * 100}%`);
        footer.style.setProperty('--my', `${((event.clientY - rect.top) / rect.height) * 100}%`);
        footer.classList.add('is-charged');
    }, { passive: true });
    ScrollTrigger.create({
        trigger: footer, start: 'top 80%', end: 'bottom top',
        onToggle: (self) => { if (matchMedia('(pointer: coarse)').matches) footer.classList.toggle('is-charged', self.isActive); },
    });

    /* ---------- 2) الختم يتسارع عند الكتابة ---------- */
    const sealRing = footer.querySelector('.seal-ring');
    const form = footer.querySelector('[data-newsletter]');
    if (sealRing && form) {
        form.addEventListener('focusin', () => {
            footer.classList.add('is-charged');
            sealRing.style.animationDuration = '5.5s';
        });
        form.addEventListener('focusout', (event) => {
            if (form.contains(event.relatedTarget)) return;
            footer.classList.remove('is-charged');
            sealRing.style.animationDuration = '';
        });
    }

    /* ---------- 3) الكلمتان العملاقتان ---------- */
    const back = footer.querySelector('[data-footer-word="back"]');
    const front = footer.querySelector('[data-footer-word="front"]');

    const fadeIn = (el) => {
        if (!el) return;
        gsap.from(el, {
            opacity: 0, duration: 2.4, ease: 'power2.out',
            scrollTrigger: { trigger: footer, start: 'top 85%', once: true },
        });
    };
    fadeIn(back);
    fadeIn(front);

    const scrub = { trigger: footer, start: 'top bottom', end: 'bottom bottom', scrub: 1.4, invalidateOnRefresh: true };
    if (front) gsap.fromTo(front, { yPercent: -50 + 9 }, { yPercent: -50 - 9, ease: 'none', scrollTrigger: scrub });
    if (back) gsap.fromTo(back, { yPercent: -5 }, { yPercent: 5, ease: 'none', scrollTrigger: scrub });

    /* ---------- 4) الختم ---------- */
    const seal = footer.querySelector('[data-footer-seal]');
    if (seal) {
        gsap.from(seal, {
            scale: .55, opacity: 0, rotate: -25, duration: 1.1, ease: 'back.out(1.7)',
            scrollTrigger: { trigger: footer, start: 'top 78%', once: true },
            onComplete: () => gsap.set(seal, { clearProps: 'transform,opacity' }),
        });
    }

    /* ---------- 5) شرائح المزايا ---------- */
    const chips = footer.querySelectorAll('.footer-chip');
    if (chips.length) {
        gsap.set(chips, { y: 12, opacity: 0 });
        ScrollTrigger.create({
            trigger: footer, start: 'top 72%', once: true,
            onEnter: () => gsap.to(chips, {
                y: 0, opacity: 1, duration: .7, stagger: .08, ease: 'power2.out',
                onComplete: () => gsap.set(chips, { clearProps: 'transform,opacity' }),
            }),
        });
    }

    /* ---------- 6) روابط التواصل (+ زر موقع المصنع) ---------- */
    // لا نُخفي الروابط أبداً قبل ضمان إظهارها: تظهر عند اقتراب الفوتر، أو فوراً إذا كان ظاهراً أصلاً، أو بعد مهلة أمان.
    const socials = footer.querySelectorAll('.foot-social a, .foot-map');
    if (socials.length) {
        let shown = false;
        const reveal = () => {
            if (shown) return;
            shown = true;
            gsap.to(socials, {
                y: 0, opacity: 1, duration: 0.9, stagger: 0.1, ease: 'power2.out', overwrite: true,
                onComplete: () => gsap.set(socials, { clearProps: 'transform,opacity,visibility' }),
            });
        };
        gsap.set(socials, { y: 14, opacity: 0 });
        ScrollTrigger.create({ trigger: footer, start: 'top 95%', once: true, onEnter: reveal });
        ScrollTrigger.create({ trigger: footer, start: 'bottom bottom', once: true, onEnter: reveal });
        window.addEventListener('load', () => setTimeout(() => {
            if (!shown && footer.getBoundingClientRect().top < window.innerHeight) reveal();
        }, 400), { once: true });
        // شبكة أمان نهائية: مهما حدث لا تبقى الروابط مخفية
        setTimeout(() => { if (!shown && window.scrollY > 40) reveal(); }, 6000);
    }
}
