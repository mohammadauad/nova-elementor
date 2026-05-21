/**
 * Elementor Pro Page Transitions Fix
 * 
 * This script ensures that Elementor Pro page transitions remain visible
 * until the page is completely loaded (window.load event).
 * 
 * The issue: Elementor Pro transitions use the 'pageshow' event which fires
 * before the page is fully loaded, causing the transition to hide prematurely.
 * 
 * Solution: Monitor the transition element and prevent it from hiding
 * until window.load event fires.
 */

(function () {
	'use strict';

	const DEBUG = window.DEBUG || false;
	const log = DEBUG ? console.log.bind(console) : function () { };

	let pageTransitionElement = null;
	let isPageFullyLoaded = false;
	let transitionObserver = null;

	/**
	 * Wait for the page transition element to be available
	 */
	function waitForTransitionElement(callback) {
		const checkInterval = 100;
		let attempts = 0;
		const maxAttempts = 50; // 5 seconds max

		const check = () => {
			attempts++;

			// Try to find the element
			const element = document.querySelector('e-page-transition');

			if (element) {
				log('✅ Elementor Page Transition element found');
				callback(element);
				return;
			}

			if (attempts >= maxAttempts) {
				log('⚠️  Elementor Page Transition element not found after max attempts');
				return;
			}

			setTimeout(check, checkInterval);
		};

		check();
	}

	/**
	 * Monitor the transition element and prevent hiding until page is loaded
	 */
	function monitorTransitionElement(element) {
		pageTransitionElement = element;

		log('🔍 Starting to monitor transition element');

		// Watch for class changes
		transitionObserver = new MutationObserver((mutations) => {
			mutations.forEach((mutation) => {
				if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
					const classes = element.classList;

					// If transition is trying to hide (entered class added)
					// but page is not fully loaded, prevent it
					if (classes.contains('e-page-transition--entered') && !isPageFullyLoaded) {
						log('⚠️  Transition trying to hide before page load, preventing...');

						// Remove the entered class temporarily
						element.classList.remove('e-page-transition--entered');

						// Also remove entering class if present (to reset state)
						if (classes.contains('e-page-transition--entering')) {
							element.classList.remove('e-page-transition--entering');
						}
					}
				}
			});
		});

		transitionObserver.observe(element, {
			attributes: true,
			attributeFilter: ['class']
		});

		log('✅ MutationObserver set up to monitor transition element');
	}

	/**
	 * Allow transition to complete once page is loaded
	 */
	function allowTransitionToComplete() {
		if (!pageTransitionElement) {
			return;
		}

		log('✅ Page fully loaded, allowing transition to complete');

		// Stop observing
		if (transitionObserver) {
			transitionObserver.disconnect();
			transitionObserver = null;
		}

		// Small delay to ensure everything is ready
		setTimeout(() => {
			// Check if element still exists
			if (pageTransitionElement && document.body.contains(pageTransitionElement)) {
				const classes = pageTransitionElement.classList;

				// If transition is still in entering state, complete it
				if (classes.contains('e-page-transition--entering') && !classes.contains('e-page-transition--entered')) {
					log('▶️  Completing transition manually');
					classes.remove('e-page-transition--entering');
					classes.add('e-page-transition--entered');
				}

				log('✅ Transition monitoring disabled, transition should be complete');
			}
		}, 300);
	}

	/**
	 * Initialize
	 */
	function init() {
		log('🚀 Elementor Page Transitions Fix: Initializing...');

		// Check if page is already loaded
		if (document.readyState === 'complete') {
			isPageFullyLoaded = true;
			log('✅ Page already fully loaded on script init');
		} else {
			// Wait for window.load event
			window.addEventListener('load', () => {
				isPageFullyLoaded = true;
				log('✅ Page fully loaded (window.load event)');
				allowTransitionToComplete();
			}, { once: true });
		}

		// Wait for transition element
		setTimeout(() => {
			waitForTransitionElement((element) => {
				monitorTransitionElement(element);

				// If page is already loaded, allow transition
				if (isPageFullyLoaded) {
					allowTransitionToComplete();
				}
			});
		}, 500);
	}

	// Start when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

})();
