export const prefersReducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;
export const isFinePointer = () => window.matchMedia('(pointer: fine)').matches;

const ARABIC = /[\u0590-\u08FF\uFB1D-\uFDFF\uFE70-\uFEFF]/;
const LATIN = /[A-Za-z\u00C0-\u024F]/;

/**
 * Splits an element's text into word spans (never letters, so Arabic stays connected).
 * Works for Arabic AND English:
 *  - a "word" is a whitespace-delimited token, so punctuation stays glued to its word ("Rasha," / "taste.")
 *  - every word is its own bidi island with an explicit dir (Arabic word -> rtl, Latin word -> ltr),
 *    so punctuation can never jump to the other side of the word and the visual order follows the page direction.
 */
export function splitWords(el) {
    if (el.dataset.split) return [...el.querySelectorAll('.wi')];
    el.dataset.split = '1';
    el.setAttribute('aria-label', el.textContent.replace(/\s+/g, ' ').trim());

    const base = getComputedStyle(el).direction === 'rtl' ? 'rtl' : 'ltr';
    const dirOf = (word) => {
        if (ARABIC.test(word)) return 'rtl';
        if (LATIN.test(word)) return 'ltr';
        return base; // digits / symbols only: follow the surrounding text
    };

    const walk = (node) => {
        [...node.childNodes].forEach((child) => {
            if (child.nodeType === 3) {
                const frag = document.createDocumentFragment();
                child.textContent.split(/(\s+)/).forEach((part) => {
                    if (!part) return;
                    if (/^\s+$/.test(part)) { frag.appendChild(document.createTextNode(' ')); return; }
                    const outer = document.createElement('span');
                    outer.className = 'w';
                    outer.setAttribute('aria-hidden', 'true');
                    outer.setAttribute('dir', dirOf(part));
                    const inner = document.createElement('span');
                    inner.className = 'wi';
                    inner.textContent = part;
                    outer.appendChild(inner);
                    frag.appendChild(outer);
                });
                child.replaceWith(frag);
            } else if (child.nodeType === 1 && child.tagName !== 'BR') {
                walk(child);
            }
        });
    };
    walk(el);
    return [...el.querySelectorAll('.wi')];
}

/** Picks a readable text color (dark or white) for a given background color. */
export function readableOn(color) {
    const m = String(color).trim().match(/^#?([0-9a-f]{3}|[0-9a-f]{6})$/i);
    if (!m) return '#ffffff';
    let h = m[1];
    if (h.length === 3) h = h.split('').map((c) => c + c).join('');
    const [r, g, b] = [0, 2, 4].map((i) => {
        const v = parseInt(h.slice(i, i + 2), 16) / 255;
        return v <= 0.03928 ? v / 12.92 : ((v + 0.055) / 1.055) ** 2.4;
    });
    return 0.2126 * r + 0.7152 * g + 0.0722 * b > 0.42 ? '#24140b' : '#ffffff';
}
