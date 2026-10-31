import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { splitWords, prefersReducedMotion, isFinePointer } from './utils';

gsap.registerPlugin(ScrollTrigger);

export function initHeroMotion() {
    const hero = document.querySelector('.hero');
    if (!hero || prefersReducedMotion()) return;

    const frame = hero.querySelector('.hero-frame');
    const float = hero.querySelector('.hero-float');
    const word = hero.querySelector('.hero-word > span');
    const text = hero.querySelector('.hero-text');
    const aside = hero.querySelector('.hero-aside');
    const blobs = hero.querySelectorAll('.blob');
    const title = hero.querySelector('h1');
    const tags = hero.querySelectorAll('.hero-tag, .hero-badge');
    const fades = hero.querySelectorAll('.hero-text .eyebrow, .hero-aside > *');
    const words = splitWords(title);

    // hidden start state (the intro curtain covers this)
    gsap.set(words, { yPercent: 115 });
    gsap.set(frame, { scale: 0.65, opacity: 0, rotation: 10 });
    gsap.set(word, { opacity: 0, scale: 0.85 });
    gsap.set(fades, { y: 30, opacity: 0 });
    gsap.set(tags, { scale: 0.6, opacity: 0 });
    gsap.set(blobs, { scale: 0.6, opacity: 0 });

    const createScrub = () => {
        gsap.timeline({ scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: 0.5 } })
            .to(frame, { yPercent: -14, scale: 1.15, rotation: -5, ease: 'none' }, 0)
            .to(word, { scale: 1.08, yPercent: -6, ease: 'none' }, 0)
            .to([text, aside], { yPercent: -18, opacity: 0, ease: 'none' }, 0.15);
    };

    const enter = () => {
        gsap.timeline({ defaults: { ease: 'power4.out' }, onComplete: createScrub })
            .to(word, { opacity: 1, scale: 1, duration: 1.4 }, 0)
            .to(blobs, { scale: 1, opacity: 1, duration: 1.8, stagger: 0.15, ease: 'power2.out' }, 0)
            .to(frame, { scale: 1, opacity: 1, rotation: 0, duration: 1.3, ease: 'back.out(1.4)' }, 0.1)
            .to(words, { yPercent: 0, duration: 1, stagger: 0.09 }, 0.2)
            .to(fades, { y: 0, opacity: 1, duration: 0.9, stagger: 0.1 }, 0.55)
            .to(tags, { scale: 1, opacity: 1, duration: 0.7, stagger: 0.12, ease: 'back.out(2)' }, 0.9);

        gsap.to(float, { y: -14, rotation: 1, duration: 3.6, ease: 'sine.inOut', yoyo: true, repeat: -1 });
        blobs.forEach((b, i) => gsap.to(b, { x: (i % 2 ? 1 : -1) * 36, y: (i < 2 ? -1 : 1) * 28, duration: 5 + i, ease: 'sine.inOut', yoyo: true, repeat: -1 }));
    };
    window.addEventListener('intro:done', enter, { once: true });

    if (isFinePointer()) {
        const layers = [...hero.querySelectorAll('[data-depth]')].map((el) => ({
            d: Number(el.dataset.depth),
            xTo: gsap.quickTo(el, 'x', { duration: 0.9, ease: 'power3.out' }),
            yTo: gsap.quickTo(el, 'y', { duration: 0.9, ease: 'power3.out' }),
        }));
        hero.addEventListener('pointermove', (e) => {
            const r = hero.getBoundingClientRect();
            const nx = (e.clientX - r.left) / r.width - 0.5;
            const ny = (e.clientY - r.top) / r.height - 0.5;
            layers.forEach((l) => { l.xTo(nx * l.d * -46); l.yTo(ny * l.d * -34); });
        }, { passive: true });
    }
}
