export function initStory() {
    const section = document.querySelector('[data-story]');
    if (!section) return;
    const track = section.querySelector('[data-story-track]');
    const steps = [...section.querySelectorAll('[data-story-step]')];
    const images = [...section.querySelectorAll('[data-story-image]')];
    const count = steps.length;
    if (!track || !count) return;

    let current = -1;
    const activate = (index) => {
        if (index === current) return;
        current = index;
        steps.forEach((item, i) => item.classList.toggle('is-active', i === index));
        images.forEach((item, i) => item.classList.toggle('is-active', i === index));
        // 33% / 66% / 100% — نفس الرقم الذي يظهر (01 / 02 / 03)
        section.style.setProperty('--story-progress', String((index + 1) / count));
        section.dataset.storyStep = String(index + 1);
    };

    // مصدر واحد للحقيقة: موضع التمرير داخل المسار. كل مرحلة = 1/count من طول التمرير.
    const sync = () => {
        const rect = track.getBoundingClientRect();
        const total = rect.height - window.innerHeight;
        if (total <= 0) { activate(0); return; }
        const scrolled = Math.min(Math.max(-rect.top, 0), total);
        activate(Math.min(count - 1, Math.floor((scrolled / total) * count)));
    };

    let ticking = false;
    const onScroll = () => {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(() => { sync(); ticking = false; });
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    sync();
    if (current === -1) activate(0);
}
