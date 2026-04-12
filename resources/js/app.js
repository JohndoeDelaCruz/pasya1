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
	const navList = document.getElementById('pill-nav-list');
	const navIndicator = document.getElementById('pill-nav-indicator');
	const mobileMenu = document.getElementById('navbar-sticky');
	const menuToggle = document.querySelector('[data-collapse-toggle="navbar-sticky"]');
	const navLinks = Array.from(document.querySelectorAll('[data-nav-scroll]'));

	if (!navLinks.length) {
		return;
	}

	const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	const normalizedPath = (pathname) => pathname.replace(/\/+$/, '') || '/';
	const isDesktopViewport = () => window.matchMedia('(min-width: 768px)').matches;

	const getLinkHash = (link) => {
		const href = link.getAttribute('href');

		if (!href || !href.includes('#')) {
			return '';
		}

		try {
			return new URL(href, window.location.origin).hash;
		} catch {
			return '';
		}
	};

	const setActiveLink = (activeLink) => {
		navLinks.forEach((link) => {
			link.classList.toggle('is-active', link === activeLink);
		});
	};

	const positionIndicator = (activeLink) => {
		if (!navIndicator) {
			return;
		}

		if (!isDesktopViewport()) {
			navIndicator.style.opacity = '0';
			return;
		}

		if (!navList || !activeLink) {
			return;
		}

		if (navList.offsetParent === null) {
			return;
		}

		const listRect = navList.getBoundingClientRect();
		const linkRect = activeLink.getBoundingClientRect();

		navIndicator.style.width = `${linkRect.width}px`;
		navIndicator.style.height = `${linkRect.height}px`;
		navIndicator.style.transform = `translate(${linkRect.left - listRect.left}px, ${linkRect.top - listRect.top}px)`;
		navIndicator.style.opacity = '1';
	};

	const closeMobileMenu = () => {
		if (isDesktopViewport() || !mobileMenu) {
			return;
		}

		mobileMenu.classList.add('hidden');

		if (menuToggle) {
			menuToggle.setAttribute('aria-expanded', 'false');
		}
	};

	const activateByHash = (hash) => {
		const normalizedHash = hash && hash !== '#' ? hash : '#home';
		const activeLink = navLinks.find((link) => getLinkHash(link) === normalizedHash) || navLinks[0];

		setActiveLink(activeLink);
		positionIndicator(activeLink);

		return activeLink;
	};

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

			activateByHash(targetUrl.hash);

			if (currentPath !== destinationPath) {
				return;
			}

			if (scrollToHashTarget(targetUrl.hash, true)) {
				event.preventDefault();
				closeMobileMenu();
			}
		});
	});

	window.addEventListener('resize', () => {
		activateByHash(window.location.hash);
	});

	window.addEventListener('hashchange', () => {
		activateByHash(window.location.hash);
	});

	if (menuToggle) {
		menuToggle.addEventListener('click', () => {
			window.requestAnimationFrame(() => {
				window.requestAnimationFrame(() => {
					activateByHash(window.location.hash);
				});
			});
		});
	}

	activateByHash(window.location.hash);

	if (window.location.hash) {
		window.requestAnimationFrame(() => {
			activateByHash(window.location.hash);
			scrollToHashTarget(window.location.hash, false);
		});

		window.addEventListener(
			'load',
			() => {
				activateByHash(window.location.hash);
				scrollToHashTarget(window.location.hash, false);
			},
			{ once: true }
		);
	} else {
		activateByHash('#home');
	}
};

document.addEventListener('DOMContentLoaded', () => {
	setupHeroSceneryFade();
	setupNavbarSmoothScroll();
});
