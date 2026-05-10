import './bootstrap';

import Alpine from 'alpinejs';
import lottie from 'lottie-web';
import pasyaLoadingAnimation from '../lottie/loading-screen.json';

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

const setupPwaAutoHideHeaders = () => {
	const scrollAreas = Array.from(document.querySelectorAll('[data-hide-header-scroll]'));

	if (!scrollAreas.length) {
		return;
	}

	const mobileViewport = window.matchMedia('(max-width: 1023px)');
	const controllers = [];
	const scrollThreshold = 8;
	const topThreshold = 6;

	scrollAreas.forEach((scrollArea) => {
		const shell = scrollArea.closest('[data-mobile-app-shell]');
		const header = shell ? shell.querySelector('[data-mobile-app-header]') : null;

		if (!shell || !header) {
			return;
		}

		const state = {
			hidden: false,
			lastScrollTop: Math.max(scrollArea.scrollTop, 0),
			rafId: null,
		};

		const setHeaderHeight = () => {
			const height = Math.ceil(header.getBoundingClientRect().height);

			if (height > 0) {
				shell.style.setProperty('--pasya-header-height', `${height}px`);
				shell.style.setProperty('--pasya-header-offset', `${height + 8}px`);
			}
		};

		const showHeader = () => {
			if (!state.hidden && header.classList.contains('mobile-header-visible')) {
				return;
			}

			state.hidden = false;
			shell.classList.remove('mobile-header-is-hidden');
			header.classList.remove('mobile-header-hidden');
			header.classList.add('mobile-header-visible');
		};

		const hideHeader = () => {
			if (!mobileViewport.matches) {
				showHeader();
				return;
			}

			state.hidden = true;
			shell.classList.add('mobile-header-is-hidden');
			header.classList.add('mobile-header-hidden');
			header.classList.remove('mobile-header-visible');
		};

		const handleScroll = () => {
			const maxScrollTop = Math.max(scrollArea.scrollHeight - scrollArea.clientHeight, 0);
			const currentScrollTop = Math.min(Math.max(scrollArea.scrollTop, 0), maxScrollTop);
			const delta = currentScrollTop - state.lastScrollTop;
			const headerHeight = Number.parseFloat(shell.style.getPropertyValue('--pasya-header-height')) || header.offsetHeight || 72;

			if (!mobileViewport.matches || currentScrollTop <= topThreshold) {
				showHeader();
				state.lastScrollTop = currentScrollTop;
				state.rafId = null;
				return;
			}

			if (Math.abs(delta) >= scrollThreshold) {
				if (delta > 0 && currentScrollTop > headerHeight) {
					hideHeader();
				} else if (delta < 0) {
					showHeader();
				}

				state.lastScrollTop = currentScrollTop;
			}

			state.rafId = null;
		};

		scrollArea.addEventListener(
			'scroll',
			() => {
				if (state.rafId === null) {
					state.rafId = window.requestAnimationFrame(handleScroll);
				}
			},
			{ passive: true }
		);

		controllers.push({ setHeaderHeight, showHeader });
		setHeaderHeight();
		showHeader();
	});

	if (!controllers.length) {
		return;
	}

	const refreshHeaders = () => {
		controllers.forEach((controller) => {
			controller.setHeaderHeight();
			controller.showHeader();
		});
	};

	document.addEventListener('pasya-show-mobile-header', refreshHeaders);

	document.addEventListener('focusin', (event) => {
		if (event.target instanceof Element && event.target.closest('[data-mobile-app-header]')) {
			refreshHeaders();
		}
	});

	window.addEventListener('resize', refreshHeaders);
	window.addEventListener('orientationchange', refreshHeaders);
	window.addEventListener('load', refreshHeaders, { once: true });

	if (typeof mobileViewport.addEventListener === 'function') {
		mobileViewport.addEventListener('change', refreshHeaders);
	} else if (typeof mobileViewport.addListener === 'function') {
		mobileViewport.addListener(refreshHeaders);
	}
};

const setupPageTransitionLoader = () => {
	const loader = document.getElementById('pasya-page-loader');
	const animationContainer = document.getElementById('pasya-page-loader-animation');

	if (!loader || !animationContainer) {
		return;
	}

	const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	const loaderDelay = 240;
	let navigationStarted = false;
	let loaderDelayId = null;
	let animation = null;

	if (!prefersReducedMotion) {
		animation = lottie.loadAnimation({
			container: animationContainer,
			renderer: 'svg',
			loop: true,
			autoplay: false,
			animationData: pasyaLoadingAnimation,
			rendererSettings: {
				preserveAspectRatio: 'xMidYMid meet',
			},
		});

		animation.addEventListener('DOMLoaded', () => {
			loader.classList.add('has-lottie');
		});
	}

	const showLoader = () => {
		if (loaderDelayId) {
			window.clearTimeout(loaderDelayId);
			loaderDelayId = null;
		}

		loader.classList.add('is-visible');
		loader.setAttribute('aria-hidden', 'false');
		document.body.classList.add('pasya-page-is-loading');

		if (animation) {
			animation.goToAndPlay(0, true);
		}
	};

	const hideLoader = () => {
		navigationStarted = false;
		if (loaderDelayId) {
			window.clearTimeout(loaderDelayId);
			loaderDelayId = null;
		}
		loader.classList.remove('is-visible');
		loader.setAttribute('aria-hidden', 'true');
		document.body.classList.remove('pasya-page-is-loading');

		if (animation) {
			animation.stop();
		}
	};

	const scheduleLoader = () => {
		navigationStarted = true;
		if (loaderDelayId || loader.classList.contains('is-visible')) {
			return;
		}

		loaderDelayId = window.setTimeout(showLoader, loaderDelay);
	};

	const isEligibleLink = (link, event) => {
		if (
			event.defaultPrevented ||
			event.button !== 0 ||
			event.metaKey ||
			event.ctrlKey ||
			event.shiftKey ||
			event.altKey ||
			link.hasAttribute('download') ||
			link.dataset.noPageLoader !== undefined
		) {
			return false;
		}

		const target = link.getAttribute('target');

		if (target && target.toLowerCase() !== '_self') {
			return false;
		}

		const href = link.getAttribute('href');

		if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('javascript:')) {
			return false;
		}

		let destination;

		try {
			destination = new URL(href, window.location.href);
		} catch {
			return false;
		}

		if (destination.origin !== window.location.origin) {
			return false;
		}

		return !(
			destination.pathname === window.location.pathname &&
			destination.search === window.location.search &&
			destination.hash
		);
	};

	document.addEventListener('click', (event) => {
		const link = event.target instanceof Element ? event.target.closest('a[href]') : null;

		if (!link || !isEligibleLink(link, event) || navigationStarted) {
			return;
		}

		scheduleLoader();
	});

	document.addEventListener('submit', (event) => {
		const form = event.target;

		if (event.defaultPrevented || !(form instanceof HTMLFormElement) || form.dataset.noPageLoader !== undefined) {
			return;
		}

		scheduleLoader();
	});

	window.addEventListener('pageshow', hideLoader);
	window.addEventListener('load', hideLoader, { once: true });
};

document.addEventListener('DOMContentLoaded', () => {
	setupPageTransitionLoader();
	setupHeroSceneryFade();
	setupNavbarSmoothScroll();
	setupPwaAutoHideHeaders();
});
