/**
 * NOVA Filters — frontend behaviour
 *
 * - Optional submit-on-change (data-submit-on-change="1" on the form)
 * - Reset button: clears every field of the closest form, then submits
 *   it WITHOUT the cleared fields (so URL params are removed).
 */
(function () {
	'use strict';

	function ready(fn) {
		if (document.readyState !== 'loading') return fn();
		document.addEventListener('DOMContentLoaded', fn);
	}

	function init() {
		var forms = document.querySelectorAll('.nova-filters-form');
		forms.forEach(function (form) {
			bindForm(form);
		});
	}

	function bindForm(form) {
		// Submit-on-change
		if (form.dataset.submitOnChange === '1') {
			form.addEventListener('change', function (e) {
				var t = e.target;
				if (!t) return;
				// Only auto-submit for "instant" types — typing in a text/textarea/number/date is too noisy.
				var instantTypes = ['select-one', 'select-multiple', 'checkbox', 'radio', 'range'];
				if (instantTypes.indexOf(t.type) === -1 && t.tagName.toLowerCase() !== 'select') {
					return;
				}
				form.requestSubmit ? form.requestSubmit() : form.submit();
			});
		}

		// Reset button → clear all named fields, then submit with empty values
		var resetBtn = form.querySelector('[data-nova-filters-reset]');
		if (resetBtn) {
			resetBtn.addEventListener('click', function () {
				clearFormFields(form);
				// After clearing, submit so the URL is updated (without our params).
				form.requestSubmit ? form.requestSubmit() : form.submit();
			});
		}
	}

	function clearFormFields(form) {
		var inputs = form.querySelectorAll('input, select, textarea');
		inputs.forEach(function (el) {
			// Skip hidden inputs that come from "Preserve other query params" — they have NO data-nova-field-name ancestor.
			var isPreserved = !el.closest('.nova-filter-field');
			if (isPreserved && el.type === 'hidden') return;

			switch (el.type) {
				case 'checkbox':
				case 'radio':
					el.checked = false;
					break;
				case 'select-one':
					el.selectedIndex = 0;
					if (el.options[0]) el.options[0].selected = true;
					break;
				case 'select-multiple':
					Array.prototype.forEach.call(el.options, function (o) { o.selected = false; });
					break;
				default:
					el.value = '';
			}
		});
	}

	ready(init);

	// Re-init for Elementor editor previews (the widget can be re-rendered without a page reload).
	if (window.elementorFrontend && window.elementorFrontend.hooks) {
		window.elementorFrontend.hooks.addAction('frontend/element_ready/nova-filters.default', function ($scope) {
			var form = $scope && $scope[0] ? $scope[0].querySelector('.nova-filters-form') : null;
			if (form) bindForm(form);
		});
	}
})();
