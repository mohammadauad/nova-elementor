<?php
namespace Nova_Addons_Elementor;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Icons_Manager;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Background;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Widget NOVA Slider News - Slider animé avec marquee style
 */
class Slider_News_Widget extends Widget_Base {

	/**
	 * Récupère le nom du widget.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'nova-slider-news';
	}

	/**
	 * Récupère le titre du widget.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'NOVA Slider News', 'NOVA-addons' );
	}

	/**
	 * Récupère l'icône du widget.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-post-slider';
	}

	/**
	 * Récupère les catégories du widget.
	 *
	 * @return array
	 */
	public function get_categories() {
		return [ 'NOVA-addons' ];
	}

	/**
	 * Récupère les dépendances de style pour le widget.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return [ 'nova-slider-news-style' ];
	}

	/**
	 * Récupère les dépendances de script pour le widget.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return [ 'gsap', 'gsap-scrolltrigger', 'nova-slider-news-script' ];
	}

	/**
	 * Enregistre les contrôles du widget.
	 */
	protected function register_controls() {

		// Section Contenu - Éléments du Slider
		$this->start_controls_section(
			'section_slider_items',
			[
				'label' => esc_html__( 'Éléments du Slider', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'slider_items',
			[
				'label' => esc_html__( 'Éléments', 'NOVA-addons' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => [
					[
						'name' => 'text',
						'label' => esc_html__( 'Texte', 'NOVA-addons' ),
						'type' => Controls_Manager::TEXT,
						'default' => esc_html__( 'DÉVELOPPEMENT WEB', 'NOVA-addons' ),
						'placeholder' => esc_html__( 'Entrez le texte', 'NOVA-addons' ),
					],
					[
						'name' => 'dot_color',
						'label' => esc_html__( 'Couleur du point', 'NOVA-addons' ),
						'type' => Controls_Manager::SELECT,
						'default' => 'green',
						'options' => [
							'green' => esc_html__( 'Vert', 'NOVA-addons' ),
							'yellow' => esc_html__( 'Jaune', 'NOVA-addons' ),
							'orange' => esc_html__( 'Orange', 'NOVA-addons' ),
							'purple' => esc_html__( 'Violet', 'NOVA-addons' ),
						],
					],
				],
				'default' => [
					[
						'text' => esc_html__( 'DÉVELOPPEMENT WEB', 'NOVA-addons' ),
						'dot_color' => 'green',
					],
					[
						'text' => esc_html__( 'DESIGN UI/UX', 'NOVA-addons' ),
						'dot_color' => 'yellow',
					],
					[
						'text' => esc_html__( 'STRATÉGIE DIGITALE', 'NOVA-addons' ),
						'dot_color' => 'orange',
					],
					[
						'text' => esc_html__( 'SEO & PERFORMANCE', 'NOVA-addons' ),
						'dot_color' => 'purple',
					],
				],
				'title_field' => '{{{ text }}}',
			]
		);

		$this->end_controls_section();

		// Section Style - Apparence générale
		$this->start_controls_section(
			'section_general_style',
			[
				'label' => esc_html__( 'Apparence générale', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'height',
			[
				'label' => esc_html__( 'Hauteur du slider (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 120,
				'min' => 60,
				'max' => 300,
			]
		);

		$this->add_control(
			'background_color',
			[
				'label' => esc_html__( 'Couleur de fond', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#111111',
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
			]
		);

		$this->add_control(
			'border_color',
			[
				'label' => esc_html__( 'Couleur des bordures', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#111111',
			]
		);

		$this->add_control(
			'border_height',
			[
				'label' => esc_html__( 'Hauteur des bordures (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 3,
				'min' => 1,
				'max' => 10,
			]
		);

		$this->end_controls_section();

		// Section Style - Typographie
		$this->start_controls_section(
			'section_typography',
			[
				'label' => esc_html__( 'Typographie', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'text_typography',
				'label' => esc_html__( 'Typographie du texte', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .marquee-item',
			]
		);

		$this->add_control(
			'item_height',
			[
				'label' => esc_html__( 'Hauteur des éléments (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 72,
				'min' => 40,
				'max' => 150,
			]
		);

		$this->add_control(
			'item_padding',
			[
				'label' => esc_html__( 'Padding horizontal (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 24,
				'min' => 0,
				'max' => 50,
			]
		);

		$this->add_control(
			'gap_between_items',
			[
				'label' => esc_html__( 'Espacement entre éléments (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 24,
				'min' => 0,
				'max' => 50,
			]
		);

		$this->end_controls_section();

		// Section Style - Points
		$this->start_controls_section(
			'section_dots_style',
			[
				'label' => esc_html__( 'Points', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'dot_size',
			[
				'label' => esc_html__( 'Taille des points (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 10,
				'min' => 4,
				'max' => 20,
			]
		);

		$this->add_control(
			'dot_green_color',
			[
				'label' => esc_html__( 'Couleur du point vert', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#4ade80',
			]
		);

		$this->add_control(
			'dot_yellow_color',
			[
				'label' => esc_html__( 'Couleur du point jaune', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#facc15',
			]
		);

		$this->add_control(
			'dot_orange_color',
			[
				'label' => esc_html__( 'Couleur du point orange', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#fb923c',
			]
		);

		$this->add_control(
			'dot_purple_color',
			[
				'label' => esc_html__( 'Couleur du point violet', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#c084fc',
			]
		);

		$this->end_controls_section();

		// Section Animation
		$this->start_controls_section(
			'section_animation',
			[
				'label' => esc_html__( 'Animation', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'animation_speed',
			[
				'label' => esc_html__( 'Vitesse d\'animation (px/seconde)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 80,
				'min' => 20,
				'max' => 200,
			]
		);

		$this->add_control(
			'hover_effect',
			[
				'label' => esc_html__( 'Ralentir au survol', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'hover_speed_factor',
			[
				'label' => esc_html__( 'Facteur de ralentissement', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 0.25,
				'min' => 0.1,
				'max' => 1,
				'step' => 0.05,
				'condition' => [
					'hover_effect' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Affiche le widget sur le frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$slider_items = $settings['slider_items'];
		
		if ( empty( $slider_items ) ) {
			return;
		}

		$this->add_render_attribute( 'slider-wrapper', 'class', 'nova-slider-news-wrapper' );
		$this->add_render_attribute( 'slider-wrapper', 'class', 'slider-' . $this->get_id() );
		
		// Ajouter un conteneur externe pour l'isolation
		$this->add_render_attribute( 'slider-container', 'class', 'nova-slider-news-container' );
		$this->add_render_attribute( 'slider-container', 'class', 'container-' . $this->get_id() );
		
		// Ajouter les data-attributes pour le JS
		$this->add_render_attribute( 'slider-wrapper', 'data-animation-speed', $settings['animation_speed'] );
		$this->add_render_attribute( 'slider-wrapper', 'data-hover-effect', $settings['hover_effect'] );
		$this->add_render_attribute( 'slider-wrapper', 'data-hover-speed-factor', $settings['hover_speed_factor'] );
		
		// Styles dynamiques
		$wrapper_styles = sprintf(
			'--nova-slider-height: %spx; --nova-slider-bg: %s; --nova-slider-text-color: %s; --nova-slider-border-color: %s; --nova-slider-border-height: %spx; --nova-slider-item-height: %spx; --nova-slider-item-padding: %spx; --nova-slider-gap: %spx; --nova-slider-dot-size: %spx; --nova-slider-dot-green: %s; --nova-slider-dot-yellow: %s; --nova-slider-dot-orange: %s; --nova-slider-dot-purple: %s;',
			esc_attr( $settings['height'] ),
			esc_attr( $settings['background_color'] ),
			esc_attr( $settings['text_color'] ),
			esc_attr( $settings['border_color'] ),
			esc_attr( $settings['border_height'] ),
			esc_attr( $settings['item_height'] ),
			esc_attr( $settings['item_padding'] ),
			esc_attr( $settings['gap_between_items'] ),
			esc_attr( $settings['dot_size'] ),
			esc_attr( $settings['dot_green_color'] ),
			esc_attr( $settings['dot_yellow_color'] ),
			esc_attr( $settings['dot_orange_color'] ),
			esc_attr( $settings['dot_purple_color'] )
		);
		
		$this->add_render_attribute( 'slider-wrapper', 'style', $wrapper_styles );
		$this->add_render_attribute( 'slider-container', 'style', 'width: 100% !important; max-width: 100% !important; flex: 1 1 100% !important;' );
		?>
		<div <?php $this->print_render_attribute_string( 'slider-container' ); ?>>
			<div <?php $this->print_render_attribute_string( 'slider-wrapper' ); ?>>
				<div class="nova-slider-news-divider" id="nova-slider-<?php echo esc_attr( $this->get_id() ); ?>">
					<div class="marquee-track" id="marquee-<?php echo esc_attr( $this->get_id() ); ?>">
						<?php foreach ( $slider_items as $index => $item ) : ?>
							<div class="marquee-item">
								<?php echo esc_html( $item['text'] ); ?>
								<span class="dot dot--<?php echo esc_attr( $item['dot_color'] ); ?>"></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Affiche le widget en mode éditeur Elementor.
	 */
	protected function content_template() {
		?>
		<#
		const slider_items = settings.slider_items;
		if ( ! slider_items || slider_items.length === 0 ) {
			return;
		}

		const wrapperStyles = '--nova-slider-height: ' + settings.height + 'px; --nova-slider-bg: ' + settings.background_color + '; --nova-slider-text-color: ' + settings.text_color + '; --nova-slider-border-color: ' + settings.border_color + '; --nova-slider-border-height: ' + settings.border_height + 'px; --nova-slider-item-height: ' + settings.item_height + 'px; --nova-slider-item-padding: ' + settings.item_padding + 'px; --nova-slider-gap: ' + settings.gap_between_items + 'px; --nova-slider-dot-size: ' + settings.dot_size + 'px; --nova-slider-dot-green: ' + settings.dot_green_color + '; --nova-slider-dot-yellow: ' + settings.dot_yellow_color + '; --nova-slider-dot-orange: ' + settings.dot_orange_color + '; --nova-slider-dot-purple: ' + settings.dot_purple_color + ';';
		#>
		<div class="nova-slider-news-container container-{{ view.getID() }}" style="width: 100% !important; max-width: 100% !important; flex: 1 1 100% !important;">
			<div class="nova-slider-news-wrapper slider-{{ view.getID() }}" style="{{ wrapperStyles }}" data-animation-speed="{{ settings.animation_speed }}" data-hover-effect="{{ settings.hover_effect }}" data-hover-speed-factor="{{ settings.hover_speed_factor }}">
				<div class="nova-slider-news-divider" id="nova-slider-{{ view.getID() }}">
					<div class="marquee-track" id="marquee-{{ view.getID() }}">
						<# _.each( slider_items, function( item, index ) { #>
							<div class="marquee-item">
								{{{ item.text }}}
								<span class="dot dot--{{ item.dot_color }}"></span>
							</div>
						<# } ); #>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
