import gsap from 'gsap';
import { isFinePointer, prefersReducedMotion } from './utils';

const MAX_TILT = 12;

export function initLiquidHover() {
    const cards = document.querySelectorAll('.liquid-card');
    if (!cards.length || !isFinePointer() || prefersReducedMotion()) return;

    cards.forEach((card) => {
        const media = card.querySelector('.media-img');
        gsap.set(card, { transformPerspective: 900, transformOrigin: 'center center' });

        const rotateX = gsap.quickTo(card, 'rotationX', { duration: 0.45, ease: 'power3.out' });
        const rotateY = gsap.quickTo(card, 'rotationY', { duration: 0.45, ease: 'power3.out' });
        const mediaX = media ? gsap.quickTo(media, 'x', { duration: 0.6, ease: 'power3.out' }) : null;
        const mediaY = media ? gsap.quickTo(media, 'y', { duration: 0.6, ease: 'power3.out' }) : null;
        let rect = null;

        card.addEventListener('pointerenter', () => {
            rect = card.getBoundingClientRect();
            card.classList.add('is-glowing');
            gsap.to(card, { y: -12, scale: 1.02, duration: 0.5, ease: 'power3.out', overwrite: 'auto' });
            if (media) gsap.to(media, { scale: 1.08, duration: 0.7, ease: 'power3.out', overwrite: 'auto' });
        });

        card.addEventListener('pointermove', (e) => {
            if (!rect) return;
            const nx = (e.clientX - rect.left) / rect.width;
            const ny = (e.clientY - rect.top) / rect.height;
            rotateY((nx - 0.5) * MAX_TILT);
            rotateX(-(ny - 0.5) * MAX_TILT);
            mediaX?.((nx - 0.5) * -16);
            mediaY?.((ny - 0.5) * -16);
            card.style.setProperty('--gx', `${nx * 100}%`);
            card.style.setProperty('--gy', `${ny * 100}%`);
        }, { passive: true });

        card.addEventListener('pointerleave', () => {
            rect = null;
            card.classList.remove('is-glowing');
            rotateX(0); rotateY(0); mediaX?.(0); mediaY?.(0);
            gsap.to(card, { y: 0, scale: 1, duration: 0.8, ease: 'power3.out', overwrite: 'auto' });
            if (media) gsap.to(media, { scale: 1, duration: 0.8, ease: 'power3.out', overwrite: 'auto' });
        });
    });
}
