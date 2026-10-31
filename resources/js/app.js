import './bootstrap';
import { initTheme } from './modules/theme';
import { initIntro } from './modules/intro';
import { initHeroMotion } from './modules/hero-motion';
import { initScrollFx } from './modules/scroll-fx';
import { initFlavorRail } from './modules/flavor-rail';
import { initMarquee } from './modules/marquee';
import { initLiquidHover } from './modules/liquid-hover';
import { initStory } from './modules/story';
import { initFooterFx } from './modules/footer-fx';
import { initFlipCards } from './modules/flip-cards';
import { initSunbirdStory } from './modules/sunbird-story';
import { initLangSwitch, liftLangVeil } from './modules/lang-switch';
import { initNav } from './modules/nav';

document.addEventListener('DOMContentLoaded', () => {
    initHeroMotion();   // registers its intro:done listener first
    initNav();
    initFlavorRail();
    initMarquee();      // يبدأ بعد أن يصبح الشريط في DOM
    initTheme();        // reads the first flavor color (after the rail exists)
    initLiquidHover();
    initStory();
    initFlipCards();
    initScrollFx();
    initFooterFx();
    initSunbirdStory();
    initLangSwitch();
    initIntro();
    liftLangVeil();
});
