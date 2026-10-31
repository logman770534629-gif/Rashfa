import gsap from 'gsap';
import { prefersReducedMotion } from './utils';

/**
 * شريط النكهات:
 *  - صفّان يتحركان في اتجاهين متعاكسين.
 *  - يتسارع الشريط مع سرعة التمرير ويميل قليلًا في اتجاه الحركة.
 *  - يتباطأ عند المرور عليه حتى تُقرأ الأسماء بسهولة.
 *  - عند تقليل الحركة يبقى الشريط ثابتًا (CSS يتولى ذلك).
 *
 * يضمن ensureCoverage أن كل مجموعة عريضة بما يكفي لتغطية الشاشة،
 * وإلا ظهرت فجوة في الشريط على الشاشات العريضة جدًا.
 * نكرّر نفس العناصر في المجموعتين حتى يبقى الانتقال −50% سلسًا.
 */
function ensureCoverage(track) {
    const groups = [...track.querySelectorAll('.marquee-group')];
    if (groups.length !== 2) return;

    const needed = Math.max(window.innerWidth, 768) * 1.15;
    const template = [...groups[0].children].map((node) => node.cloneNode(true));
    if (!template.length) return;

    let guard = 0;
    while (groups[0].scrollWidth < needed && guard < 5) {
        groups.forEach((group) => template.forEach((node) => group.append(node.cloneNode(true))));
        guard += 1;
    }
}

export function initMarquee() {
    const marquees = [...document.querySelectorAll('[data-marquee]')];
    if (!marquees.length || prefersReducedMotion()) return;

    marquees.forEach((marquee) => {
        const tracks = [...marquee.querySelectorAll('[data-marquee-track]')];
        if (!tracks.length) return;

        tracks.forEach(ensureCoverage);

        let hovered = false;

        const loops = tracks.map((track) => {
            const reverse = track.dataset.marqueeDirection === 'right'
                || marquee.dataset.marqueeDirection === 'right';
            const rawDuration = Number(track.dataset.marqueeSpeed || marquee.dataset.marqueeSpeed || 30);
            const duration = Number.isFinite(rawDuration) && rawDuration > 0 ? rawDuration : 30;

            // نوقف حركة CSS ونتولى القيادة بـGSAP لضمان سرعة قابلة للتغيير بسلاسة.
            track.style.animation = 'none';
            gsap.set(track, { xPercent: reverse ? -50 : 0 });

            const tween = gsap.to(track, {
                xPercent: reverse ? 0 : -50,
                duration,
                ease: 'none',
                repeat: -1,
            });

            const skew = gsap.quickTo(track, 'skewX', { duration: 0.35, ease: 'power2.out' });

            return { tween, skew };
        });

        const setSpeed = (scale) => loops.forEach(({ tween }) => tween.timeScale(scale));

        // ننعّم تغيّر السرعة حتى لا يهتز الشريط مع كل حدث تمرير.
        const speedState = { value: 1 };
        const speedTo = gsap.quickTo(speedState, 'value', {
            duration: 0.3,
            ease: 'power2.out',
            onUpdate: () => setSpeed(speedState.value),
        });

        marquee.addEventListener('pointerenter', () => {
            hovered = true;
            speedTo(0.18);
        });
        marquee.addEventListener('pointerleave', () => {
            hovered = false;
            speedTo(1);
        });
        marquee.addEventListener('focusin', () => {
            hovered = true;
            speedTo(0.18);
        });
        marquee.addEventListener('focusout', () => {
            hovered = false;
            speedTo(1);
        });

        let lastY = window.scrollY;
        let settle;

        window.addEventListener('scroll', () => {
            const y = window.scrollY;
            const dy = y - lastY;
            lastY = y;
            if (hovered) return;

            // كلما تحرّكت الصفحة أسرع، تسارع الشريط أكثر (بحد أقصى 3.2×).
            speedTo(gsap.utils.clamp(0.9, 3.2, 1 + Math.abs(dy) / 16));

            const tilt = gsap.utils.clamp(-7, 7, dy * 0.16);
            loops.forEach(({ skew }) => skew(tilt));

            clearTimeout(settle);
            settle = setTimeout(() => {
                speedTo(1);
                loops.forEach(({ skew }) => skew(0));
            }, 160);
        }, { passive: true });
    });
}
