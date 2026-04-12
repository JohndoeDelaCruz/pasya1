import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const setupScrollAwareNavbar = () => {
	const navbar = document.getElementById('site-navbar');

	if (!navbar) {
		return;
	}

	if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		return;
	}

	let lastScrollY = window.scrollY;
	let ticking = false;
	const minDelta = 8;
	const topThreshold = 24;

	const showNavbar = () => {
		navbar.style.opacity = '1';
		navbar.style.transform = 'translateY(0)';
		navbar.style.pointerEvents = 'auto';
	};

	const hideNavbar = () => {
		navbar.style.opacity = '0';
		navbar.style.transform = 'translateY(-120%)';
		navbar.style.pointerEvents = 'none';
	};

	const handleScroll = () => {
		const currentScrollY = window.scrollY;
		const delta = currentScrollY - lastScrollY;

		if (currentScrollY <= topThreshold) {
			showNavbar();
			lastScrollY = currentScrollY;
			ticking = false;
			return;
		}

		if (Math.abs(delta) >= minDelta) {
			if (delta > 0) {
				hideNavbar();
			} else {
				showNavbar();
			}
			lastScrollY = currentScrollY;
		}

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
};

document.addEventListener('DOMContentLoaded', setupScrollAwareNavbar);
