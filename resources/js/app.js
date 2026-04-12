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

const setupNavbarSmoothScroll = () => {
	const navbar = document.getElementById('main-pill-navbar');
	const navLinks = document.querySelectorAll('[data-nav-scroll]');

	if (!navLinks.length) {
		return;
	}

	const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	const normalizedPath = (pathname) => pathname.replace(/\/+$/, '') || '/';

	const scrollToHashTarget = (hash, updateHistory = false) => {
		if (!hash || hash === '#') {
			return false;
		}

		const target = document.querySelector(hash);

		if (!target) {
			return false;
		}

		const navbarHeight = navbar ? navbar.getBoundingClientRect().height : 0;
		const offset = navbarHeight + 18;
		const targetTop = Math.max(window.scrollY + target.getBoundingClientRect().top - offset, 0);

		window.scrollTo({
			top: targetTop,
			behavior: prefersReducedMotion ? 'auto' : 'smooth',
		});

		if (updateHistory) {
			history.pushState(null, '', hash);
		}

		return true;
	};

	navLinks.forEach((link) => {
		link.addEventListener('click', (event) => {
			const href = link.getAttribute('href');

			if (!href || !href.includes('#')) {
				return;
			}

			const targetUrl = new URL(href, window.location.origin);
			const currentPath = normalizedPath(window.location.pathname);
			const destinationPath = normalizedPath(targetUrl.pathname);

			if (currentPath !== destinationPath) {
				return;
			}

			if (scrollToHashTarget(targetUrl.hash, true)) {
				event.preventDefault();
			}
		});
	});

	if (window.location.hash) {
		window.requestAnimationFrame(() => {
			scrollToHashTarget(window.location.hash, false);
		});

		window.addEventListener(
			'load',
			() => {
				scrollToHashTarget(window.location.hash, false);
			},
			{ once: true }
		);
	}
};

document.addEventListener('DOMContentLoaded', () => {
	setupHeroSceneryFade();
	setupNavbarSmoothScroll();
});
