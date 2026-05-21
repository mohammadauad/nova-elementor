/**
 * NOVA Slider News Widget - JavaScript
 * Basé sur l'exemple fourni avec GSAP et ScrollTrigger
 */

(function ($) {
    'use strict';

    // Fonction d'initialisation pour chaque slider
    function initNovaSliderNews($slider) {
        // Guard : éviter la double initialisation (document.ready + elementor hook)
        if ($slider.data('nova-initialized')) return;
        $slider.data('nova-initialized', true);

        const sliderId = $slider.attr('id');
        const $track = $slider.find('.marquee-track');
        const $container = $slider.closest('.nova-slider-news-container');

        if (!$track.length) return;

        // Forcer immédiatement la largeur du container
        $container.css({
            'width': '100% !important',
            'max-width': '100% !important',
            'flex': '1 1 100% !important'
        });

        // Récupérer les data attributes
        const animationSpeed = parseFloat($slider.closest('.nova-slider-news-wrapper').data('animation-speed')) || 80;
        const hoverEffect = $slider.closest('.nova-slider-news-wrapper').data('hover-effect') === 'yes';
        const hoverSpeedFactor = parseFloat($slider.closest('.nova-slider-news-wrapper').data('hover-speed-factor')) || 0.25;

        // Cloner le contenu pour une boucle infinie
        const $clone = $track.clone();
        $clone.attr('aria-hidden', 'true');
        $track.parent().append($clone);

        // Calculer la largeur totale
        const totalWidth = $track[0].scrollWidth;

        // Initialiser les positions
        gsap.set([$track[0], $clone[0]], { x: 0 });

        // Créer l'animation marquee
        const tl = gsap.timeline({
            repeat: -1,
            paused: false
        });

        tl.to([$track[0], $clone[0]], {
            x: `-=${totalWidth}`,
            duration: totalWidth / animationSpeed,
            ease: 'none',
            modifiers: {
                x: gsap.utils.unitize(x => parseFloat(x) % totalWidth)
            }
        });

        // Effet de ralentissement au survol
        if (hoverEffect) {
            $slider.on('mouseenter', function () {
                gsap.to(tl, {
                    timeScale: hoverSpeedFactor,
                    duration: 0.4
                });
            });

            $slider.on('mouseleave', function () {
                gsap.to(tl, {
                    timeScale: 1,
                    duration: 0.4
                });
            });
        }

        // Reveal wrapper so CSS knows JS is ready and takes over animation
        // Using slight timeout to ensure GSAP has set initial states
        setTimeout(function () {
            $slider.closest('.nova-slider-news-wrapper').addClass('nova-js-ready');
        }, 50);

        // Animation d'entrée avec ScrollTrigger
        // Utilise opacity au lieu de scaleX : opacity ne touche pas les dimensions physiques
        // scaleX causait un collapse visuel au moment où le ScrollTrigger se déclenchait
        gsap.from($slider, {
            immediateRender: false,
            scrollTrigger: {
                trigger: $slider,
                start: 'top 85%',
                once: true
            },
            opacity: 0,
            duration: 0.6,
            ease: 'power2.out'
        });

        // Stocker la timeline pour le nettoyage
        $slider.data('nova-timeline', tl);

        // Surveiller et corriger la largeur pendant l'animation
        let widthCheckInterval = setInterval(function () {
            if ($container.width() < $container.parent().width()) {
                $container.css({
                    'width': '100% !important',
                    'max-width': '100% !important',
                    'flex': '1 1 100% !important'
                });
            }
            // Forcer aussi le wrapper
            $slider.closest('.nova-slider-news-wrapper').css({
                'width': '100%',
                'min-width': '100%'
            });
        }, 500); // Vérification plus fréquente

        // Surveillance continue (pas d'arrêt)
        $slider.data('width-check-interval', widthCheckInterval);

        // Protection contre les mutations DOM
        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (mutation.type === 'attributes' &&
                    (mutation.attributeName === 'style' || mutation.attributeName === 'class')) {
                    // Forcer la largeur si modifiée
                    setTimeout(function () {
                        $container.css({
                            'width': '100% !important',
                            'max-width': '100% !important',
                            'flex': '1 1 100% !important'
                        });
                    }, 10);
                }
            });
        });

        observer.observe($container[0], {
            attributes: true,
            attributeFilter: ['style', 'class']
        });

        $slider.data('mutation-observer', observer);
    }

    // Fonction pour détruire un slider
    function destroyNovaSliderNews($slider) {
        // Réinitialiser le flag pour permettre une ré-initialisation future
        $slider.data('nova-initialized', false);

        const tl = $slider.data('nova-timeline');
        if (tl) {
            tl.kill();
        }

        // Nettoyer l'intervalle de surveillance
        const widthCheckInterval = $slider.data('width-check-interval');
        if (widthCheckInterval) {
            clearInterval(widthCheckInterval);
        }

        // Nettoyer le MutationObserver
        const observer = $slider.data('mutation-observer');
        if (observer) {
            observer.disconnect();
        }

        // Supprimer les clones
        $slider.find('.marquee-track[aria-hidden="true"]').remove();

        // Nettoyer les événements
        $slider.off('mouseenter mouseleave');

        // Tuer le ScrollTrigger s'il existe
        ScrollTrigger.getAll().forEach(trigger => {
            if (trigger.trigger === $slider[0]) {
                trigger.kill();
            }
        });
    }

    // Initialisation au chargement du DOM
    $(document).ready(function () {
        // Vérifier si GSAP est disponible
        if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
            console.warn('NOVA Slider News: GSAP ou ScrollTrigger n\'est pas disponible');
            return;
        }

        // Initialiser tous les sliders
        $('.nova-slider-news-divider').each(function () {
            initNovaSliderNews($(this));
        });
    });

    // Support pour Elementor Editor
    $(window).on('elementor/frontend/init', function () {
        if (typeof elementorFrontend !== 'undefined') {
            elementorFrontend.hooks.addAction('frontend/element_ready/nova-slider-news.default', function ($scope) {
                const $slider = $scope.find('.nova-slider-news-divider');
                if ($slider.length) {
                    initNovaSliderNews($slider);
                }
            });
        }
    });

    // Nettoyage lors de la destruction d'éléments Elementor
    $(window).on('elementor/frontend/init', function () {
        if (typeof elementorFrontend !== 'undefined') {
            elementorFrontend.hooks.addAction('frontend/element_destroy/nova-slider-news', function ($scope) {
                const $slider = $scope.find('.nova-slider-news-divider');
                if ($slider.length) {
                    destroyNovaSliderNews($slider);
                }
            });
        }
    });

    // Rafraîchissement pour les changements dynamiques
    function refreshNovaSliders() {
        $('.nova-slider-news-divider').each(function () {
            const $slider = $(this);
            destroyNovaSliderNews($slider);
            initNovaSliderNews($slider);
        });
    }

    // Exposer les fonctions globalement pour les appels externes
    window.NovaSliderNews = {
        init: initNovaSliderNews,
        destroy: destroyNovaSliderNews,
        refresh: refreshNovaSliders
    };

    // Gérer le redimensionnement de la fenêtre
    let resizeTimer;
    $(window).on('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            refreshNovaSliders();
        }, 250);
    });

})(jQuery);
