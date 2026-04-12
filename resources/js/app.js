import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const setupHeroSceneryFade = () => {
	const scenery = document.getElementById('hero-scenery');

	if (!scenery) {
		return;
	}

	if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		return;
	}

	let ticking = false;
	const maxScrollForFade = 420;
	const minOpacity = 0.18;

	const handleScroll = () => {
		const currentScrollY = Math.max(window.scrollY, 0);
		const progress = Math.min(currentScrollY / maxScrollForFade, 1);
		const opacity = 1 - progress * (1 - minOpacity);

		scenery.style.opacity = opacity.toFixed(3);
		ticking = false;
	};

	window.addEventListener(
		'scroll',
		() => {
			if (!ticking) {
				window.requestAnimationFrame(handleScroll);
				ticking = true;
			}
		},
		{ passive: true }
	);

	handleScroll();
};

document.addEventListener('DOMContentLoaded', setupHeroSceneryFade);
