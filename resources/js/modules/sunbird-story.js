import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { splitWords, prefersReducedMotion } from './utils';

gsap.registerPlugin(ScrollTrigger);

/**
 * "About Sun Bird" — cinematic section:
 *  - parallax background + floating fruit layers (scrubbed)
 *  - title words rise from a mask
 *  - the lead paragraph "lights up" word by word as you scroll (scrub)
 *  - gold rule draws, product block and chips fade in
 */
export function initSunbirdStory() {
    const root = document.querySelector('[data-sbs]');
    if (!root) return;

    const reduced = prefersReducedMotion();
    const bg = root.querySelector('[data-sbs-bg]');
    const glow = root.querySelector('[data-sbs-glow]');
    const floats = gsap.utils.toArray('[data-sbs-float]', root);
    const title = root.querySelector('.sbs-title');
    const lead = root.querySelector('[data-sbs-lead]');
    const rule = root.querySelector('[data-sbs-rule]');
    const reveals = gsap.utils.toArray('[data-sbs-reveal]', root);
    const chips = gsap.utils.toArray('[data-sbs-chip]', root);

    if (reduced) { return; }

    // ---- parallax layers ----
    // المهمة 3: سطح المكتب/التابلت بقيمه الأصلية تماماً؛ الهاتف (≤650px) بحركة أخف وتكبير أقل
    // حتى لا تُقصّ الصورة أو تتكبّر على الشاشات الطويلة. matchMedia يعيد البناء عند تدوير الجهاز.
    if (bg) {
        const mm = gsap.matchMedia();
        mm.add('(min-width: 651px)', () => {
            gsap.fromTo(bg, { yPercent: -12, scale: 1.18 }, {
                yPercent: 12, scale: 1.05, ease: 'none',
                scrollTrigger: { trigger: root, start: 'top bottom', end: 'bottom top', scrub: 0.6 },
            });
        });
        mm.add('(max-width: 650px)', () => {
            // الطبقة في CSS بامتداد -6% فقط؛ ±4% من ارتفاعها يبقى داخل الهامش فلا تظهر حواف فارغة، والتكبير ≥ 1
            gsap.fromTo(bg, { yPercent: -4, scale: 1.04 }, {
                yPercent: 4, scale: 1, ease: 'none',
                scrollTrigger: { trigger: root, start: 'top bottom', end: 'bottom top', scrub: 0.6 },
            });
        });
    }
    if (glow) {
        gsap.fromTo(glow, { opacity: 0.25, scale: 0.8 }, {
            opacity: 0.9, scale: 1.15, ease: 'none',
            scrollTrigger: { trigger: root, start: 'top 80%', end: 'center center', scrub: 0.8 },
        });
    }
    // الصور العائمة: حركة كاملة على الشاشات العريضة (عمود جانبي محجوز)، وخفيفة جداً تحت 1200px كي لا تقترب من النص.
    const floatK = () => (window.innerWidth >= 1200 ? 1 : 0.18);
    floats.forEach((el) => {
        const travel = parseFloat(el.dataset.sbsFloat) || 60;
        gsap.fromTo(el, { y: () => -travel * floatK(), rotation: travel > 0 ? -6 : 6 }, {
            y: () => travel * floatK(), rotation: travel > 0 ? 6 : -6, ease: 'none',
            scrollTrigger: { trigger: root, start: 'top bottom', end: 'bottom top', scrub: 0.9, invalidateOnRefresh: true },
        });
    });

    // ---- title: words rise (Arabic stays connected: split by word only) ----
    if (title) {
        const words = splitWords(title);
        gsap.set(words, { yPercent: 118, rotation: 3 });
        ScrollTrigger.create({
            trigger: title, start: 'top 85%', once: true,
            onEnter: () => gsap.to(words, { yPercent: 0, rotation: 0, duration: 1.2, stagger: 0.1, ease: 'power4.out' }),
        });
    }

    // ---- lead: cinematic word-by-word light-up tied to scroll ----
    if (lead) {
        const words = splitWords(lead);
        gsap.set(words, { opacity: 0.16, yPercent: 0 });
        gsap.to(words, {
            opacity: 1, ease: 'none', stagger: 0.12,
            scrollTrigger: { trigger: lead, start: 'top 82%', end: 'bottom 48%', scrub: 0.5 },
        });
    }

    // ---- gold rule draws itself ----
    if (rule) {
        gsap.fromTo(rule, { scaleX: 0 }, {
            scaleX: 1, ease: 'power2.out', duration: 1.4,
            scrollTrigger: { trigger: rule, start: 'top 90%', once: true },
        });
    }

    // ---- fade-ins ----
    reveals.forEach((el) => {
        gsap.from(el, {
            y: 46, opacity: 0, duration: 1.1, ease: 'power3.out',
            scrollTrigger: { trigger: el, start: 'top 88%', once: true },
        });
    });
    if (chips.length) {
        gsap.from(chips, {
            y: 24, opacity: 0, scale: 0.9, duration: 0.8, stagger: 0.12, ease: 'back.out(1.6)',
            scrollTrigger: { trigger: chips[0], start: 'top 92%', once: true },
        });
    }
}
