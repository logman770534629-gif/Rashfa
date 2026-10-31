import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { prefersReducedMotion } from './utils';
import { consumeLangSwitch, flagLangReload, langState } from './lang-switch';

gsap.registerPlugin(ScrollTrigger);

/**
 * Sun Bird opening screen -> cinematic reveal of the Rasha site.
 * Fires `intro:done` once the site is visible so the hero can animate in.
 */
export function initIntro() {
    const root = document.documentElement;
    const intro = document.getElementById('sunbird-intro');
    const site = document.getElementById('rashfa-site');
    let fired = false;

    const showSite = () => {
        if (!site) return;
        site.classList.remove('opacity-0');
        site.style.opacity = '1';
        site.removeAttribute('inert');
        site.removeAttribute('aria-hidden');
    };
    const done = () => {
        if (fired) return;
        fired = true;
        root.classList.remove('is-intro');
        window.scrollTo(0, 0);
        window.dispatchEvent(new CustomEvent('intro:done'));
        requestAnimationFrame(() => ScrollTrigger.refresh());
    };

    // بعد تبديل اللغة: لا نُعيد شاشة سن بيرد، نُظهر الموقع فوراً ونعود لنفس موضع التمرير
    const switched = consumeLangSwitch();
    if (switched) {
        intro?.remove();
        showSite();
        setTimeout(() => {
            done();
            window.scrollTo(0, switched.y);
            requestAnimationFrame(() => ScrollTrigger.refresh());
        }, 0);
        return;
    }

    if (!intro) { showSite(); setTimeout(done, 0); return; }

    root.classList.add('is-intro');
    site?.setAttribute('inert', '');
    site?.setAttribute('aria-hidden', 'true');

    const cta = intro.querySelector('[data-sb-cta]');
    const reduced = prefersReducedMotion();

    /* ---------- entrance ---------- */
    if (!reduced) {
        const logo = intro.querySelector('[data-sb-logo]');
        const halo = intro.querySelector('[data-sb-halo]');
        const ring = intro.querySelector('.sb-ring');
        const tagline = intro.querySelector('[data-sb-tagline]');
        const socials = intro.querySelectorAll('[data-sb-social] a');

        gsap.timeline({ defaults: { ease: 'power3.out' } })
            .from(halo, { scale: 0.3, opacity: 0, duration: 1.9, ease: 'power2.out' }, 0)
            .from(ring, { scale: 0.5, opacity: 0, duration: 1.8, ease: 'expo.out' }, 0.15)
            .from(logo, { scale: 0.55, opacity: 0, filter: 'blur(16px)', duration: 1.6, ease: 'expo.out' }, 0.2)
            .from(tagline, { y: 22, opacity: 0, duration: 1.1 }, 1)
            .from(cta, { scale: 0.7, opacity: 0, duration: 1, ease: 'back.out(1.9)' }, 1.4)
            .from(socials, { y: 26, opacity: 0, duration: 0.8, stagger: 0.11, clearProps: 'transform,opacity' }, 1.6)
            .add(() => gsap.to(logo, { y: -10, duration: 3.2, ease: 'sine.inOut', yoyo: true, repeat: -1 }), 1.8);
    }

    /* ---------- reveal on click ---------- */
    let started = false;
    cta?.addEventListener('click', () => {
        if (started) return;
        started = true;
        cta.disabled = true;

        const finish = () => { intro.remove(); };

        // اللغة تغيّرت داخل هذه الشاشة: الموقع خلفها بلغته القديمة، فنعيد تحميله بلغته الجديدة ونتخطى الافتتاحية (الستار يغطي التحميل)
        const reloadInNewLang = () => { flagLangReload(0); window.location.reload(); };

        if (reduced) {
            if (langState.dirty) { reloadInNewLang(); return; }
            showSite(); done(); finish(); return;
        }

        const stage = intro.querySelector('[data-sb-stage]');
        const lead = intro.querySelector('[data-sb-wave-lead]');
        const wave = intro.querySelector('[data-sb-wave]');
        const r = cta.getBoundingClientRect();
        const x = r.left + r.width / 2;
        const y = r.top + r.height / 2;
        const radius = Math.hypot(Math.max(x, innerWidth - x), Math.max(y, innerHeight - y)) + 40;
        const closed = `circle(0px at ${x}px ${y}px)`;
        const open = `circle(${radius}px at ${x}px ${y}px)`;

        gsap.set([lead, wave], { opacity: 1, clipPath: closed });

        gsap.timeline({ onComplete: finish })
            // 1) golden wave floods the screen from the button
            .to(lead, { clipPath: open, duration: 0.75, ease: 'power2.out' }, 0)
            .to(wave, { clipPath: open, duration: 0.85, ease: 'power3.inOut' }, 0.1)
            .to(stage, { scale: 1.08, opacity: 0, duration: 0.6, ease: 'power2.in' }, 0.15)
            // 2) under the wave: swap to the Rasha site
            .add(() => {
                if (langState.dirty) { reloadInNewLang(); return; }
                stage.style.visibility = 'hidden'; showSite(); window.scrollTo(0, 0);
            }, 0.95)
            // 3) curtain lifts, Rasha is revealed
            .add(() => { if (!langState.dirty) done(); }, 1.0)
            .to(intro, { clipPath: 'inset(0 0 100% 0)', duration: 1.05, ease: 'power4.inOut' }, 1.0);
    });
}
