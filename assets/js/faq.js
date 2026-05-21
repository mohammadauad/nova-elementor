/**
 * NOVA FAQ Widget JavaScript
 */
(function ($) {
	'use strict';

	$(document).ready(function () {
		initNovaFAQ();
	});

	// Réinitialiser lors du chargement dynamique Elementor
	$(window).on('elementor/frontend/init', function () {
		initNovaFAQ();
	});

	function initNovaFAQ() {
		$('.nova-faq-widget').each(function () {
			const $widget = $(this);
			const $items = $widget.find('.nova-faq-item');

			// Éviter la double initialisation
			if ($widget.data('initialized')) {
				return;
			}
			$widget.data('initialized', true);

			// Récupérer la configuration
			const config = $widget.data('faq-config') || {};
			const accordionType = config.accordionType || 'single';
			const animationSpeed = config.animationSpeed || 300;
			const animationType = config.animationType || 'slide';
			const iconType = $widget.data('icon-type') || 'plus_minus';


			// Ajouter l'attribut data-animation-type pour le CSS
			$widget.attr('data-animation-type', animationType);

			// Gérer le clic sur les questions
			$items.find('.nova-faq-question').off('click.faq').on('click.faq', function (e) {
				e.preventDefault();
				e.stopPropagation();
				const $item = $(this).closest('.nova-faq-item');
				const $answerWrapper = $item.find('.nova-faq-answer-wrapper');
				const isActive = $item.hasClass('active');

				// Si type single, fermer les autres items d'abord
				if (accordionType === 'single' && !isActive) {
					$items.not($item).each(function () {
						const $otherItem = $(this);
						if ($otherItem.hasClass('active')) {
							closeItem($otherItem, animationSpeed, animationType);
						}
					});
				}

				// Toggle l'item actuel avec un petit délai pour éviter les conflits
				setTimeout(function () {
					if (isActive) {
						closeItem($item, animationSpeed, animationType);
						// Mettre à jour l'icône après fermeture
						setTimeout(function () {
							updateIcon($item, false);
						}, 50);
					} else {
						openItem($item, animationSpeed, animationType);
						// Mettre à jour l'icône après ouverture
						setTimeout(function () {
							updateIcon($item, true);
						}, 50);
					}
					// Mettre à jour l'attribut aria-expanded
					updateAriaAttributes($item);
				}, 10);
			});

			// Gérer le clavier (accessibilité)
			$items.find('.nova-faq-question').on('keydown', function (e) {
				if (e.key === 'Enter' || e.key === ' ') {
					e.preventDefault();
					$(this).trigger('click');
				}
			});

			// Fonction pour ouvrir un item
			function openItem($item, speed, type) {
				const $answerWrapper = $item.find('.nova-faq-answer-wrapper');
				const $icon = $item.find('.nova-faq-icon');
				const animationId = (Date.now() + Math.random()).toString(16);

				// Ne pas ouvrir si déjà ouvert
				if ($item.hasClass('active')) {
					return;
				}
				// Annuler toute transition en cours (clics rapides)
				$answerWrapper.off('transitionend.novaFaq');
				$answerWrapper.data('novaFaqAnimationId', animationId);
				$item.addClass('active');
				$item.find('.nova-faq-question').attr('aria-expanded', 'true');

				if (type === 'slide') {
					// Animation slide
					// Forcer le display block d'abord pour calculer la hauteur
					$answerWrapper.css({
						'display': 'block',
						'max-height': '0',
						'opacity': '0',
						'overflow': 'hidden'
					});

					// Obtenir la hauteur réelle après le display block
					const height = $answerWrapper[0].scrollHeight;

					// Utiliser requestAnimationFrame pour s'assurer que le display block est appliqué
					requestAnimationFrame(function () {
						// Si une autre animation a démarré, abandonner
						if ($answerWrapper.data('novaFaqAnimationId') !== animationId) {
							return;
						}
						$answerWrapper.css({
							'max-height': height + 'px',
							'opacity': '1',
							'transition': 'max-height ' + speed + 'ms cubic-bezier(0.22, 0.61, 0.36, 1), opacity ' + speed + 'ms ease'
						});
						$answerWrapper.on('transitionend.novaFaq', function (ev) {
							// Éviter double déclenchement (opacity + max-height)
							if (ev.originalEvent && ev.originalEvent.propertyName !== 'max-height') {
								return;
							}
							$answerWrapper.off('transitionend.novaFaq');
							if ($answerWrapper.data('novaFaqAnimationId') !== animationId) {
								return;
							}
							// Après l'animation, permettre la hauteur automatique
							if ($item.hasClass('active')) {
								$answerWrapper.css({
									'max-height': '',
									'transition': ''
								});
							}
						});
					});
				} else if (type === 'fade') {
					// Animation fade
					$answerWrapper.css({
						'display': 'block',
						'opacity': '0',
						'transform': 'translateY(-10px)',
						'transition': 'opacity ' + speed + 'ms ease, transform ' + speed + 'ms cubic-bezier(0.22, 0.61, 0.36, 1)'
					});

					requestAnimationFrame(function () {
						if ($answerWrapper.data('novaFaqAnimationId') !== animationId) {
							return;
						}
						$answerWrapper.css({
							'opacity': '1',
							'transform': 'translateY(0)'
						});
					});
				} else {
					// Animation none
					$answerWrapper.css('display', 'block');
				}

				// Mettre à jour l'icône immédiatement
				updateIcon($item, true);
			}

			// Fonction pour fermer un item
			function closeItem($item, speed, type) {
				const $answerWrapper = $item.find('.nova-faq-answer-wrapper');
				const animationId = (Date.now() + Math.random()).toString(16);

				// Ne pas fermer si déjà fermé
				if (!$item.hasClass('active')) {
					return;
				}
				// Annuler toute transition en cours (clics rapides)
				$answerWrapper.off('transitionend.novaFaq');
				$answerWrapper.data('novaFaqAnimationId', animationId);
				$item.removeClass('active');
				$item.find('.nova-faq-question').attr('aria-expanded', 'false');

				if (type === 'slide') {
					// Animation slide
					// Obtenir la hauteur actuelle avant de commencer l'animation
					const currentHeight = $answerWrapper[0].scrollHeight;
					$answerWrapper.css({
						'display': 'block',
						'max-height': currentHeight + 'px',
						'opacity': '1',
						'overflow': 'hidden'
					});
					// Forcer un reflow pour que la transition soit prise en compte
					$answerWrapper[0].offsetHeight;

					// Utiliser requestAnimationFrame pour s'assurer que la hauteur est appliquée
					requestAnimationFrame(function () {
						if ($answerWrapper.data('novaFaqAnimationId') !== animationId) {
							return;
						}
						$answerWrapper.css({
							'max-height': '0',
							'opacity': '0',
							'transition': 'max-height ' + speed + 'ms cubic-bezier(0.22, 0.61, 0.36, 1), opacity ' + speed + 'ms ease'
						});
						$answerWrapper.on('transitionend.novaFaq', function (ev) {
							if (ev.originalEvent && ev.originalEvent.propertyName !== 'max-height') {
								return;
							}
							$answerWrapper.off('transitionend.novaFaq');
							if ($answerWrapper.data('novaFaqAnimationId') !== animationId) {
								return;
							}
							// Si l'item a été ré-ouvert pendant l'animation, ne pas cacher
							if ($item.hasClass('active')) {
								return;
							}
							$answerWrapper.css({
								'display': 'none',
								'max-height': '',
								'opacity': '',
								'transition': ''
							});
						});
					});
				} else if (type === 'fade') {
					// Animation fade
					$answerWrapper.css({
						'opacity': '0',
						'transform': 'translateY(-10px)',
						'transition': 'opacity ' + speed + 'ms ease, transform ' + speed + 'ms cubic-bezier(0.22, 0.61, 0.36, 1)'
					});
					$answerWrapper.on('transitionend.novaFaq', function (ev) {
						if (ev.originalEvent && ev.originalEvent.propertyName !== 'opacity') {
							return;
						}
						$answerWrapper.off('transitionend.novaFaq');
						if ($answerWrapper.data('novaFaqAnimationId') !== animationId) {
							return;
						}
						if ($item.hasClass('active')) {
							return;
						}
						$answerWrapper.css({
							'display': 'none',
							'opacity': '',
							'transform': '',
							'transition': ''
						});
					});
				} else {
					// Animation none
					$answerWrapper.css('display', 'none');
				}

				// Mettre à jour l'icône immédiatement
				updateIcon($item, false);
			}

			// Fonction pour mettre à jour l'icône
			function updateIcon($item, isOpen) {
				// Chercher l'icône dans la question de l'item
				const $icon = $item.find('.nova-faq-question .nova-faq-icon');

				// Vérifier que l'icône existe
				if ($icon.length === 0) {
					return;
				}

				const iconType = $widget.data('icon-type') || 'plus_minus';

				// Mettre à jour l'icône selon le type
				if (iconType === 'plus_minus') {
					// Pour plus_minus, on change le SVG
					if (isOpen) {
						// Minus icon (horizontal line only)
						$icon.html('<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 8H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>');
						$icon.addClass('icon-open');
					} else {
						// Plus icon (both lines)
						$icon.html('<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 3V13M3 8H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>');
						$icon.removeClass('icon-open');
					}
				} else if (iconType === 'chevron') {
					// Pour chevron, on change le SVG entre ouvert et fermé
					if (isOpen) {
						// Chevron vers le haut (ouvert)
						$icon.html('<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>');
						$icon.addClass('icon-open');
					} else {
						// Chevron vers le bas (fermé)
						$icon.html('<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 10L8 6L12 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>');
						$icon.removeClass('icon-open');
					}
				} else if (iconType === 'arrow') {
					// Pour arrow, on utilise la rotation CSS (l'icône est déjà dans le HTML)
					if (isOpen) {
						// Arrow vers le haut (rotation -90deg)
						$icon.css('transform', 'rotate(-90deg)');
						$icon.addClass('icon-open');
					} else {
						// Arrow vers la droite (rotation 0deg)
						$icon.css('transform', 'rotate(0deg)');
						$icon.removeClass('icon-open');
					}
				} else if (iconType === 'custom') {
					// Pour les icônes personnalisées, on doit remplacer l'icône
					// Les icônes sont stockées dans les attributs data
					let iconOpenData = $icon.attr('data-icon-open');
					let iconClosedData = $icon.attr('data-icon-closed');

					// Parser les données JSON
					let iconOpen = null;
					let iconClosed = null;

					if (iconOpenData) {
						try {
							iconOpen = JSON.parse(iconOpenData);
						} catch (e) {
							// Erreur silencieuse
						}
					}

					if (iconClosedData) {
						try {
							iconClosed = JSON.parse(iconClosedData);
						} catch (e) {
							// Erreur silencieuse
						}
					}

					// Fonction pour rendre une icône Elementor
					function renderElementorIcon(iconData) {
						if (!iconData) {
							return '';
						}

						// Si iconData est un objet avec value et library
						if (typeof iconData === 'object' && iconData.value) {
							const library = iconData.library || 'fa-solid';
							const value = iconData.value;

							// Si c'est une icône SVG (structure Elementor)
							if (library === 'svg') {
								// Si value est un objet avec une URL
								if (typeof value === 'object' && value.url) {
									// Créer une balise img pour charger le SVG depuis l'URL
									const imgHtml = '<img src="' + value.url + '" alt="" aria-hidden="true" style="width: 16px; height: 16px; display: inline-block;" />';
									return imgHtml;
								}
								// Si value est un objet avec un path (SVG inline)
								if (typeof value === 'object' && value.path) {
									return '<svg width="16" height="16" viewBox="0 0 ' + (value.width || 16) + ' ' + (value.height || 16) + '" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="' + value.path + '" fill="currentColor"/></svg>';
								}
								// Si value est une string (code SVG direct)
								if (typeof value === 'string') {
									return value;
								}
								return '';
							}

							// Si c'est une icône Font Awesome
							if (library === 'fa-solid' || library === 'fa-regular' || library === 'fa-brands') {
								return '<i class="' + library + ' ' + value + '" aria-hidden="true"></i>';
							}

							// Pour les autres types de bibliothèque Elementor
							return '<i class="' + value + '" aria-hidden="true"></i>';
						}

						// Si c'est juste une string (nom de classe)
						if (typeof iconData === 'string') {
							return '<i class="' + iconData + '" aria-hidden="true"></i>';
						}

						return '';
					}

					if (isOpen && iconOpen) {
						const iconHtml = renderElementorIcon(iconOpen);
						if (iconHtml) {
							$icon.html(iconHtml);
						}
						$icon.addClass('icon-open');
					} else if (!isOpen && iconClosed) {
						const iconHtml = renderElementorIcon(iconClosed);
						if (iconHtml) {
							$icon.html(iconHtml);
						}
						$icon.removeClass('icon-open');
					} else {
						// Fallback : juste changer la classe
						if (isOpen) {
							$icon.addClass('icon-open');
						} else {
							$icon.removeClass('icon-open');
						}
					}
				} else {
					// Pour les autres types ou aucun, on ajoute juste une classe
					if (isOpen) {
						$icon.addClass('icon-open');
					} else {
						$icon.removeClass('icon-open');
					}
				}
			}

			// Fonction pour mettre à jour les attributs ARIA
			function updateAriaAttributes($item) {
				const isActive = $item.hasClass('active');
				$item.find('.nova-faq-question').attr('aria-expanded', isActive ? 'true' : 'false');
			}

			// Initialiser les items ouverts par défaut
			$items.filter('.active').each(function () {
				const $item = $(this);
				const $answerWrapper = $item.find('.nova-faq-answer-wrapper');

				if (animationType === 'slide') {
					$answerWrapper.css({
						'max-height': $answerWrapper[0].scrollHeight + 'px',
						'opacity': '1'
					});
				} else if (animationType === 'fade') {
					$answerWrapper.css({
						'opacity': '1',
						'transform': 'translateY(0)'
					});
				}

				// Mettre à jour l'icône pour les items ouverts par défaut
				updateIcon($item, true);
			});

			// Initialiser les icônes pour tous les items après un court délai
			// pour s'assurer que le HTML est complètement chargé
			setTimeout(function () {
				$items.each(function () {
					const $item = $(this);
					const isActive = $item.hasClass('active');
					updateIcon($item, isActive);
				});

				// Reveal wrapper so CSS knows JS is ready and takes over animation
				$widget.addClass('nova-js-ready');
			}, 100);

			// Gérer le redimensionnement de la fenêtre pour les items ouverts
			$(window).on('resize', function () {
				$items.filter('.active').each(function () {
					const $item = $(this);
					const $answerWrapper = $item.find('.nova-faq-answer-wrapper');

					if (animationType === 'slide' && $answerWrapper.css('max-height') !== 'none') {
						$answerWrapper.css('max-height', $answerWrapper[0].scrollHeight + 'px');
					}
				});
			});
		});
	}

})(jQuery);
