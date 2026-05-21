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
 * Widget NOVA Title - Titre avec deux textes et animation
 */
class Title_Widget extends Widget_Base {

	/**
	 * Récupère le nom du widget.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'nova-title';
	}

	/**
	 * Récupère le titre du widget.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'NOVA Title', 'NOVA-addons' );
	}

	/**
	 * Récupère l'icône du widget.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-heading';
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
		return [ 'nova-title-style' ];
	}

	/**
	 * Récupère les dépendances de script pour le widget.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return [ 'nova-title-script' ];
	}

	/**
	 * Enregistre les contrôles du widget.
	 */
	protected function register_controls() {

		// Section Contenu - Texte 1
		$this->start_controls_section(
			'section_text_1',
			[
				'label' => esc_html__( 'Texte 1', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'text_1',
			[
				'label' => esc_html__( 'Contenu', 'NOVA-addons' ),
				'type' => Controls_Manager::WYSIWYG,
				'default' => esc_html__( 'Votre premier texte ici...', 'NOVA-addons' ),
				'placeholder' => esc_html__( 'Entrez votre texte ici...', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'text_1_styled_words_enable',
			[
				'label' => esc_html__( 'Styliser des mots', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'separator' => 'before',
				'description' => esc_html__( 'Activez cette option pour styliser des mots spécifiques dans le texte avec des badges colorés.', 'NOVA-addons' ),
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'word_text',
			[
				'label' => esc_html__( 'Mot/Phrase à styliser', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => esc_html__( 'Ex: DÉVELOPPONS', 'NOVA-addons' ),
				'description' => esc_html__( 'Entrez exactement le mot ou la phrase tel qu\'il apparaît dans le texte ci-dessus.', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'word_background_color',
			[
				'label' => esc_html__( 'Couleur de fond', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#4A90E2',
				'selectors' => [
					'{{WRAPPER}} .nova-title-text-1 .nova-styled-word-{{CURRENT_ITEM}}' => 'background-color: {{VALUE}};',
				],
			]
		);

		$repeater->add_control(
			'word_text_color',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#000000',
				'selectors' => [
					'{{WRAPPER}} .nova-title-text-1 .nova-styled-word-{{CURRENT_ITEM}}' => 'color: {{VALUE}};',
				],
			]
		);

		$repeater->add_responsive_control(
			'word_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'default' => [
					'top' => '4',
					'right' => '12',
					'bottom' => '4',
					'left' => '12',
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-title-text-1 .nova-styled-word-{{CURRENT_ITEM}}' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$repeater->add_responsive_control(
			'word_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default' => [
					'top' => '4',
					'right' => '4',
					'bottom' => '4',
					'left' => '4',
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-title-text-1 .nova-styled-word-{{CURRENT_ITEM}}' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$repeater->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'word_border',
				'label' => esc_html__( 'Bordure', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-title-text-1 .nova-styled-word-{{CURRENT_ITEM}}',
			]
		);

		$repeater->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'word_box_shadow',
				'label' => esc_html__( 'Ombre', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-title-text-1 .nova-styled-word-{{CURRENT_ITEM}}',
			]
		);

		$repeater->add_control(
			'word_rotation',
			[
				'label' => esc_html__( 'Rotation', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'deg' ],
				'range' => [
					'deg' => [
						'min' => -360,
						'max' => 360,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'deg',
					'size' => 0,
				],
				// Pas de selectors - la rotation est gérée via les styles inline dans process_styled_words()
			]
		);

		// Section Animation pour les mots stylisés
		$repeater->add_control(
			'word_animation_enable',
			[
				'label' => esc_html__( 'Activer animation', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'separator' => 'before',
				'description' => esc_html__( 'Animer ce mot avec les styles configurés', 'NOVA-addons' ),
			]
		);

			$repeater->add_control(
			'word_animation_trigger',
			[
				'label' => esc_html__( 'Déclencheur', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'on_scroll_refresh',
				'options' => [
					'on_scroll_refresh' => esc_html__( 'Au scroll ou refresh (quand le bloc devient visible)', 'NOVA-addons' ),
					'on_scroll' => esc_html__( 'Au scroll uniquement (quand le bloc devient visible)', 'NOVA-addons' ),
					'on_load' => esc_html__( 'Au chargement', 'NOVA-addons' ),
					'on_hover' => esc_html__( 'Au survol', 'NOVA-addons' ),
				],
				'condition' => [
					'word_animation_enable' => 'yes',
				],
				'description' => esc_html__( 'L\'animation se déclenchera une seule fois : au refresh si le bloc est déjà visible, ou au scroll quand il devient visible', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'word_animation_initial_state',
			[
				'label' => esc_html__( 'État initial', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => esc_html__( 'Par défaut (sans style)', 'NOVA-addons' ),
					'transparent' => esc_html__( 'Transparent', 'NOVA-addons' ),
					'custom' => esc_html__( 'Personnalisé', 'NOVA-addons' ),
				],
				'condition' => [
					'word_animation_enable' => 'yes',
				],
			]
		);

		$repeater->add_control(
			'word_animation_properties',
			[
				'label' => esc_html__( 'Propriétés à animer', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT2,
				'multiple' => true,
				'options' => [
					'background_color' => esc_html__( 'Couleur de fond', 'NOVA-addons' ),
					'text_color' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
					'padding' => esc_html__( 'Padding', 'NOVA-addons' ),
					'border_radius' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
					'border_width' => esc_html__( 'Largeur de bordure', 'NOVA-addons' ),
					'border_color' => esc_html__( 'Couleur de bordure', 'NOVA-addons' ),
					'box_shadow' => esc_html__( 'Ombre', 'NOVA-addons' ),
					'rotation' => esc_html__( 'Rotation', 'NOVA-addons' ),
				],
				'default' => [ 'background_color', 'text_color', 'padding' ],
				'condition' => [
					'word_animation_enable' => 'yes',
				],
				'description' => esc_html__( 'Sélectionnez les propriétés qui seront animées', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'word_animation_duration',
			[
				'label' => esc_html__( 'Durée (ms)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 500,
				'min' => 0,
				'max' => 5000,
				'step' => 50,
				'condition' => [
					'word_animation_enable' => 'yes',
				],
			]
		);

		$repeater->add_control(
			'word_animation_delay_scroll',
			[
				'label' => esc_html__( 'Délai au scroll (ms)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 0,
				'min' => 0,
				'max' => 5000,
				'step' => 50,
				'condition' => [
					'word_animation_enable' => 'yes',
					'word_animation_trigger!' => ['on_load', 'on_hover'],
				],
				'description' => esc_html__( 'Délai avant le début de l\'animation lors du scroll (quand le bloc devient visible par scroll) pour ce mot spécifique. Utilisé pour on_scroll et on_scroll_refresh.', 'NOVA-addons' ),
			]
		);
		
		$repeater->add_control(
			'word_animation_delay_refresh',
			[
				'label' => esc_html__( 'Délai au refresh (ms)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 0,
				'min' => 0,
				'max' => 5000,
				'step' => 50,
				'condition' => [
					'word_animation_enable' => 'yes',
					'word_animation_trigger!' => ['on_scroll', 'on_hover'],
				],
				'description' => esc_html__( 'Délai avant le début de l\'animation lors du refresh (quand le bloc est déjà visible au chargement) pour ce mot spécifique. Utilisé pour on_scroll_refresh et on_load.', 'NOVA-addons' ),
			]
		);
		
		// Garder le champ delay pour compatibilité avec on_hover
		$repeater->add_control(
			'word_animation_delay',
			[
				'label' => esc_html__( 'Délai (ms) - Au survol', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 0,
				'min' => 0,
				'max' => 5000,
				'step' => 50,
				'condition' => [
					'word_animation_enable' => 'yes',
					'word_animation_trigger' => 'on_hover',
				],
				'description' => esc_html__( 'Délai avant le début de l\'animation au survol pour ce mot spécifique.', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'word_animation_timing',
			[
				'label' => esc_html__( 'Fonction de timing', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'ease',
				'options' => [
					'linear' => esc_html__( 'Linear', 'NOVA-addons' ),
					'ease' => esc_html__( 'Ease', 'NOVA-addons' ),
					'ease-in' => esc_html__( 'Ease In', 'NOVA-addons' ),
					'ease-out' => esc_html__( 'Ease Out', 'NOVA-addons' ),
					'ease-in-out' => esc_html__( 'Ease In Out', 'NOVA-addons' ),
					'cubic-bezier(0.68, -0.55, 0.265, 1.55)' => esc_html__( 'Back', 'NOVA-addons' ),
				],
				'condition' => [
					'word_animation_enable' => 'yes',
				],
			]
		);

		$this->add_control(
			'text_1_styled_words',
			[
				'label' => esc_html__( 'Mots stylisés', 'NOVA-addons' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [],
				'title_field' => '{{{ word_text }}}',
				'condition' => [
					'text_1_styled_words_enable' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		// Section Contenu - Texte 2
		$this->start_controls_section(
			'section_text_2',
			[
				'label' => esc_html__( 'Texte 2', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'text_2',
			[
				'label' => esc_html__( 'Contenu', 'NOVA-addons' ),
				'type' => Controls_Manager::WYSIWYG,
				'default' => esc_html__( 'Votre deuxième texte ici...', 'NOVA-addons' ),
				'placeholder' => esc_html__( 'Entrez votre texte ici...', 'NOVA-addons' ),
			]
		);

		$this->end_controls_section();

		// Section Contenu - Badge
		$this->start_controls_section(
			'section_badge',
			[
				'label' => esc_html__( 'Badge', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'badge_show',
			[
				'label' => esc_html__( 'Afficher le badge', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
			]
		);

		$this->add_control(
			'badge_text',
			[
				'label' => esc_html__( 'Texte du badge', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => esc_html__( 'Entrez le texte du badge', 'NOVA-addons' ),
				'condition' => [
					'badge_show' => 'yes',
				],
			]
		);

		$this->add_control(
			'badge_icon',
			[
				'label' => esc_html__( 'Icône', 'NOVA-addons' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => '',
					'library' => '',
				],
				'condition' => [
					'badge_show' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		// Section Contenu - Bouton
		$this->start_controls_section(
			'section_button',
			[
				'label' => esc_html__( 'Bouton', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'button_show',
			[
				'label' => esc_html__( 'Afficher le bouton', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
			]
		);

		$this->add_control(
			'button_position',
			[
				'label' => esc_html__( 'Position', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'after',
				'options' => [
					'before' => esc_html__( 'Avant le contenu', 'NOVA-addons' ),
					'after' => esc_html__( 'Après le contenu', 'NOVA-addons' ),
				],
				'condition' => [
					'button_show' => 'yes',
				],
			]
		);

		$this->add_control(
			'button_text',
			[
				'label' => esc_html__( 'Texte du bouton', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'En savoir plus', 'NOVA-addons' ),
				'placeholder' => esc_html__( 'Entrez le texte du bouton', 'NOVA-addons' ),
				'condition' => [
					'button_show' => 'yes',
				],
			]
		);

		$this->add_control(
			'button_link',
			[
				'label' => esc_html__( 'Lien', 'NOVA-addons' ),
				'type' => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://votre-lien.com', 'NOVA-addons' ),
				'show_external' => true,
				'default' => [
					'url' => '',
					'is_external' => true,
					'nofollow' => true,
				],
				'condition' => [
					'button_show' => 'yes',
				],
			]
		);

		$this->add_control(
			'button_icon',
			[
				'label' => esc_html__( 'Icône', 'NOVA-addons' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => '',
					'library' => '',
				],
				'condition' => [
					'button_show' => 'yes',
				],
			]
		);

		$this->add_control(
			'button_icon_position',
			[
				'label' => esc_html__( 'Position de l\'icône', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'after',
				'options' => [
					'before' => esc_html__( 'Avant le texte', 'NOVA-addons' ),
					'after' => esc_html__( 'Après le texte', 'NOVA-addons' ),
				],
				'condition' => [
					'button_show' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		// Section Animation
		$this->start_controls_section(
			'section_animation',
			[
				'label' => esc_html__( 'Animation', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'animation_enable',
			[
				'label' => esc_html__( 'Activer l\'animation', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
			]
		);

		$this->add_control(
			'animation_delay',
			[
				'label' => esc_html__( 'Délai (ms)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 100,
				'min' => 0,
				'max' => 5000,
				'step' => 50,
				'condition' => [
					'animation_enable' => 'yes',
				],
			]
		);

		$this->add_control(
			'animation_translate_y',
			[
				'label' => esc_html__( 'Translation Y (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 30,
				'min' => 0,
				'max' => 9999,
				'step' => 5,
				'description' => esc_html__( 'Distance de translation verticale au démarrage de l\'animation', 'NOVA-addons' ),
				'condition' => [
					'animation_enable' => 'yes',
				],
			]
		);

		$this->add_control(
			'animation_duration',
			[
				'label' => esc_html__( 'Durée (ms)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 800,
				'min' => 0,
				'max' => 5000,
				'step' => 50,
				'condition' => [
					'animation_enable' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		// Section Icônes décoratives (plusieurs icônes par titre, position + animation scroll)
		$this->start_controls_section(
			'section_title_icons',
			[
				'label' => esc_html__( 'Icônes décoratives', 'NOVA-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'title_icons_show',
			[
				'label'        => esc_html__( 'Afficher les icônes', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'no',
				'return_value' => 'yes',
			]
		);

		$this->add_responsive_control(
			'title_icons_scroll_trigger_top',
			[
				'label'       => esc_html__( 'Déclencher animation à (depuis le haut)', 'NOVA-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => [ 'px', '%', 'vh' ],
				'range'       => [
					'px' => [
						'min'  => 0,
						'max'  => 1000,
						'step' => 1,
					],
					'%' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
					'vh' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'     => [
					'unit' => 'px',
					'size' => 82,
				],
				'condition'   => [
					'title_icons_show' => 'yes',
				],
				'description' => esc_html__( 'L\'animation des icônes se déclenche quand le haut du bloc atteint cette position depuis le haut de la fenêtre. Utilisez px pour une valeur fixe, % pour un pourcentage de la hauteur de la fenêtre, ou vh pour les unités viewport.', 'NOVA-addons' ),
			]
		);

		$icons_repeater = new \Elementor\Repeater();

		$icons_repeater->add_control(
			'icon_type',
			[
				'label'   => esc_html__( 'Type d\'icône', 'NOVA-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'icon',
				'options' => [
					'icon'  => esc_html__( 'Icône de police', 'NOVA-addons' ),
					'image' => esc_html__( 'Image PNG/SVG', 'NOVA-addons' ),
				],
			]
		);

		$icons_repeater->add_control(
			'icon',
			[
				'label'     => esc_html__( 'Icône', 'NOVA-addons' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => [ 'value' => '', 'library' => '' ],
				'condition' => [
					'icon_type' => 'icon',
				],
			]
		);

		$icons_repeater->add_control(
			'icon_image',
			[
				'label'     => esc_html__( 'Image', 'NOVA-addons' ),
				'type'      => Controls_Manager::MEDIA,
				'default'   => [
					'url' => '',
				],
				'condition' => [
					'icon_type' => 'image',
				],
				'description' => esc_html__( 'Choisissez une image PNG, SVG ou tout autre format d\'image.', 'NOVA-addons' ),
			]
		);

		$icons_repeater->add_control(
			'position_heading',
			[
				'label' => esc_html__( 'Position', 'NOVA-addons' ),
				'type'  => Controls_Manager::HEADING,
			]
		);

		$icons_repeater->add_control(
			'align_h',
			[
				'label'   => esc_html__( 'Alignement horizontal', 'NOVA-addons' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left'   => [
						'title' => esc_html__( 'Gauche', 'NOVA-addons' ),
						'icon'  => 'eicon-h-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Centre', 'NOVA-addons' ),
						'icon'  => 'eicon-h-align-center',
					],
					'right'  => [
						'title' => esc_html__( 'Droite', 'NOVA-addons' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'default' => 'left',
			]
		);

		$icons_repeater->add_control(
			'align_v',
			[
				'label'   => esc_html__( 'Alignement vertical', 'NOVA-addons' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'top'    => [
						'title' => esc_html__( 'Haut', 'NOVA-addons' ),
						'icon'  => 'eicon-v-align-top',
					],
					'center' => [
						'title' => esc_html__( 'Centre', 'NOVA-addons' ),
						'icon'  => 'eicon-v-align-middle',
					],
					'bottom' => [
						'title' => esc_html__( 'Bas', 'NOVA-addons' ),
						'icon'  => 'eicon-v-align-bottom',
					],
				],
				'default' => 'top',
			]
		);

		$icons_repeater->add_responsive_control(
			'position_x',
			[
				'label'      => esc_html__( 'Translate X (initial)', 'NOVA-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em' ],
				'range'      => [
					'px' => [ 'min' => -500, 'max' => 500, 'step' => 1 ],
					'%'  => [ 'min' => -100, 'max' => 200, 'step' => 1 ],
					'em' => [ 'min' => -30, 'max' => 30, 'step' => 0.1 ],
				],
				'default'    => [ 'unit' => 'px', 'size' => 0 ],
				'description' => esc_html__( 'Décalage X depuis l\'alignement. Utilisé avec l\'animation au scroll.', 'NOVA-addons' ),
			]
		);

		$icons_repeater->add_responsive_control(
			'position_y',
			[
				'label'      => esc_html__( 'Translate Y (initial)', 'NOVA-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em' ],
				'range'      => [
					'px' => [ 'min' => -500, 'max' => 500, 'step' => 1 ],
					'%'  => [ 'min' => -100, 'max' => 200, 'step' => 1 ],
					'em' => [ 'min' => -30, 'max' => 30, 'step' => 0.1 ],
				],
				'default'    => [ 'unit' => 'px', 'size' => 0 ],
			]
		);

		$icons_repeater->add_control(
			'scroll_animation_heading',
			[
				'label'     => esc_html__( 'Animation au scroll', 'NOVA-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$icons_repeater->add_responsive_control(
			'scroll_position_x',
			[
				'label'      => esc_html__( 'Translate X (au scroll)', 'NOVA-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em' ],
				'range'      => [
					'px' => [ 'min' => -500, 'max' => 500, 'step' => 1 ],
					'%'  => [ 'min' => -100, 'max' => 200, 'step' => 1 ],
					'em' => [ 'min' => -30, 'max' => 30, 'step' => 0.1 ],
				],
				'default'    => [ 'unit' => 'px', 'size' => 0 ],
				'description' => esc_html__( 'Décalage X cible au scroll.', 'NOVA-addons' ),
			]
		);

		$icons_repeater->add_responsive_control(
			'scroll_position_y',
			[
				'label'      => esc_html__( 'Translate Y (au scroll)', 'NOVA-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em' ],
				'range'      => [
					'px' => [ 'min' => -500, 'max' => 500, 'step' => 1 ],
					'%'  => [ 'min' => -100, 'max' => 200, 'step' => 1 ],
					'em' => [ 'min' => -30, 'max' => 30, 'step' => 0.1 ],
				],
				'default'    => [ 'unit' => 'px', 'size' => 0 ],
			]
		);

		$icons_repeater->add_control(
			'scroll_animation_duration',
			[
				'label'   => esc_html__( 'Durée animation (ms)', 'NOVA-addons' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 800,
				'min'     => 100,
				'max'     => 3000,
				'step'    => 50,
			]
		);

		$icons_repeater->add_control(
			'icon_size',
			[
				'label'      => esc_html__( 'Taille icône', 'NOVA-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [ 'min' => 10, 'max' => 200, 'step' => 1 ],
					'em' => [ 'min' => 0.5, 'max' => 10, 'step' => 0.1 ],
				],
				'default'    => [ 'unit' => 'px', 'size' => 48 ],
				'selectors'  => [
					'{{WRAPPER}} .nova-title-icons-wrapper .nova-title-decoration-icon-{{CURRENT_ITEM}} .nova-title-decoration-icon-inner' => 'font-size: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-title-icons-wrapper .nova-title-decoration-icon-{{CURRENT_ITEM}} .nova-title-decoration-icon-inner svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
				'condition'   => [
					'icon_type' => 'icon',
				],
			]
		);

		$icons_repeater->add_control(
			'icon_image_size_heading',
			[
				'label'     => esc_html__( 'Taille image (PNG/SVG)', 'NOVA-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'icon_type' => 'image',
				],
			]
		);

		$icons_repeater->add_control(
			'icon_image_width_type',
			[
				'label'     => esc_html__( 'Largeur', 'NOVA-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'custom',
				'options'   => [
					'auto'   => esc_html__( 'Auto', 'NOVA-addons' ),
					'custom' => esc_html__( 'Personnalisée', 'NOVA-addons' ),
				],
				'condition' => [
					'icon_type' => 'image',
				],
			]
		);

		$icons_repeater->add_responsive_control(
			'icon_image_width',
			[
				'label'      => esc_html__( 'Largeur (px)', 'NOVA-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [ 'min' => 10, 'max' => 400, 'step' => 1 ],
					'em' => [ 'min' => 0.5, 'max' => 25, 'step' => 0.1 ],
				],
				'default'    => [ 'unit' => 'px', 'size' => 48 ],
				'selectors'  => [
					'{{WRAPPER}} .nova-title-icons-wrapper .nova-title-decoration-icon-{{CURRENT_ITEM}}' => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-title-icons-wrapper .nova-title-decoration-icon-{{CURRENT_ITEM}} .nova-title-decoration-icon-inner img' => 'width: 100%; height: auto;',
				],
				'condition'  => [
					'icon_type'           => 'image',
					'icon_image_width_type' => 'custom',
				],
			]
		);

		$icons_repeater->add_control(
			'icon_image_height_type',
			[
				'label'     => esc_html__( 'Hauteur', 'NOVA-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'auto',
				'options'   => [
					'auto'   => esc_html__( 'Auto', 'NOVA-addons' ),
					'custom' => esc_html__( 'Personnalisée', 'NOVA-addons' ),
				],
				'condition' => [
					'icon_type' => 'image',
				],
			]
		);

		$icons_repeater->add_responsive_control(
			'icon_image_height',
			[
				'label'      => esc_html__( 'Hauteur (px)', 'NOVA-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [ 'min' => 10, 'max' => 400, 'step' => 1 ],
					'em' => [ 'min' => 0.5, 'max' => 25, 'step' => 0.1 ],
				],
				'default'    => [ 'unit' => 'px', 'size' => 48 ],
				'selectors'  => [
					'{{WRAPPER}} .nova-title-icons-wrapper .nova-title-decoration-icon-{{CURRENT_ITEM}}' => 'height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-title-icons-wrapper .nova-title-decoration-icon-{{CURRENT_ITEM}} .nova-title-decoration-icon-inner img' => 'height: 100%; width: auto;',
				],
				'condition'  => [
					'icon_type'            => 'image',
					'icon_image_height_type' => 'custom',
				],
			]
		);

		$icons_repeater->add_control(
			'icon_color',
			[
				'label'     => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-title-icons-wrapper .nova-title-decoration-icon-{{CURRENT_ITEM}} .nova-title-decoration-icon-inner' => 'color: {{VALUE}};',
					'{{WRAPPER}} .nova-title-icons-wrapper .nova-title-decoration-icon-{{CURRENT_ITEM}} .nova-title-decoration-icon-inner svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$icons_repeater->add_control(
			'icon_z_index',
			[
				'label'   => esc_html__( 'Z-Index', 'NOVA-addons' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 1,
				'min'     => -10,
				'max'     => 100,
				'selectors' => [
					'{{WRAPPER}} .nova-title-icons-wrapper .nova-title-decoration-icon-{{CURRENT_ITEM}}' => 'z-index: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'title_icons',
			[
				'label'       => esc_html__( 'Icônes', 'NOVA-addons' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $icons_repeater->get_controls(),
				'default'     => [],
				'title_field' => '{{{ "Icône " + (typeof index !== "undefined" ? index + 1 : "") }}}',
				'condition'   => [
					'title_icons_show' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Conteneur
		$this->start_controls_section(
			'section_style_container',
			[
				'label' => esc_html__( 'Conteneur', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);


		$this->add_responsive_control(
			'container_max_width',
			[
				'label' => esc_html__( 'Largeur max', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 2000,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'vw' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 100,
					'unit' => '%',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-title-overlay-content' => 'max-width: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'container_flex_direction',
			[
				'label' => esc_html__( 'Direction Flex', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'row' => [
						'title' => esc_html__( 'Ligne', 'NOVA-addons' ),
						'icon' => 'eicon-arrow-right',
					],
					'column' => [
						'title' => esc_html__( 'Colonne', 'NOVA-addons' ),
						'icon' => 'eicon-arrow-down',
					],
				],
				'default' => 'row',
				'selectors' => [
					'{{WRAPPER}} .nova-title-overlay-content' => 'flex-direction: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_justify_content',
			[
				'label' => esc_html__( 'Justifier le contenu', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'space-between',
				'options' => [
					'flex-start' => esc_html__( 'Début', 'NOVA-addons' ),
					'flex-end' => esc_html__( 'Fin', 'NOVA-addons' ),
					'center' => esc_html__( 'Centre', 'NOVA-addons' ),
					'space-between' => esc_html__( 'Espace entre', 'NOVA-addons' ),
					'space-around' => esc_html__( 'Espace autour', 'NOVA-addons' ),
					'space-evenly' => esc_html__( 'Espace égal', 'NOVA-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} .nova-title-overlay-content' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_align_items',
			[
				'label' => esc_html__( 'Alignement vertical', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'flex-start',
				'options' => [
					'flex-start' => esc_html__( 'Début', 'NOVA-addons' ),
					'flex-end' => esc_html__( 'Fin', 'NOVA-addons' ),
					'center' => esc_html__( 'Centre', 'NOVA-addons' ),
					'stretch' => esc_html__( 'Étirer', 'NOVA-addons' ),
					'baseline' => esc_html__( 'Ligne de base', 'NOVA-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} .nova-title-overlay-content' => 'align-items: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_gap',
			[
				'label' => esc_html__( 'Espacement (Gap)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 5,
					],
				],
				'default' => [
					'size' => 20,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-title-overlay-content' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-title-overlay-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-title-overlay-content' => 'margin-top: {{TOP}}{{UNIT}}; margin-bottom: {{BOTTOM}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_align_horizontal',
			[
				'label' => esc_html__( 'Alignement horizontal', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Gauche', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Centre', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Droite', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default' => 'center',
				'toggle' => true,
				'selectors' => [
					'{{WRAPPER}} .nova-title-overlay-content' => '{{VALUE}}',
				],
				'selectors_dictionary' => [
					'left' => 'margin-left: 0 !important; margin-right: auto !important;',
					'center' => 'margin-left: auto !important; margin-right: auto !important;',
					'right' => 'margin-left: auto !important; margin-right: 0 !important;',
				],
			]
		);

		$this->add_responsive_control(
			'container_text_align',
			[
				'label' => esc_html__( 'Alignement texte', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Gauche', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Centre', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Droite', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}} .nova-title-overlay-content' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Groupe Gauche (Badge + Texte 1)
		$this->start_controls_section(
			'section_style_left_group',
			[
				'label' => esc_html__( 'Groupe Gauche (Badge + Texte 1)', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'left_group_display',
			[
				'label' => esc_html__( 'Display', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'flex',
				'options' => [
					'flex' => 'Flex',
					'block' => 'Block',
					'inline-block' => 'Inline Block',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-title-left-group' => 'display: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'left_group_flex_direction',
			[
				'label' => esc_html__( 'Direction Flex', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'row' => [
						'title' => esc_html__( 'Ligne', 'NOVA-addons' ),
						'icon' => 'eicon-arrow-right',
					],
					'column' => [
						'title' => esc_html__( 'Colonne', 'NOVA-addons' ),
						'icon' => 'eicon-arrow-down',
					],
				],
				'default' => 'column',
				'condition' => [
					'left_group_display' => 'flex',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-title-left-group' => 'flex-direction: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'left_group_justify_content',
			[
				'label' => esc_html__( 'Justifier', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'flex-start',
				'options' => [
					'flex-start' => esc_html__( 'Début', 'NOVA-addons' ),
					'flex-end' => esc_html__( 'Fin', 'NOVA-addons' ),
					'center' => esc_html__( 'Centre', 'NOVA-addons' ),
					'space-between' => esc_html__( 'Espace entre', 'NOVA-addons' ),
					'space-around' => esc_html__( 'Espace autour', 'NOVA-addons' ),
				],
				'condition' => [
					'left_group_display' => 'flex',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-title-left-group' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'left_group_align_items',
			[
				'label' => esc_html__( 'Alignement vertical', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'flex-start',
				'options' => [
					'flex-start' => esc_html__( 'Début', 'NOVA-addons' ),
					'flex-end' => esc_html__( 'Fin', 'NOVA-addons' ),
					'center' => esc_html__( 'Centre', 'NOVA-addons' ),
					'stretch' => esc_html__( 'Étirer', 'NOVA-addons' ),
				],
				'condition' => [
					'left_group_display' => 'flex',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-title-left-group' => 'align-items: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'left_group_gap',
			[
				'label' => esc_html__( 'Espacement (Gap)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-title-left-group' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Texte 1
		$this->start_controls_section(
			'section_style_text_1',
			[
				'label' => esc_html__( 'Texte 1', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'text_1_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .nova-title-text-1, {{WRAPPER}} .nova-title-text-1 h1, {{WRAPPER}} .nova-title-text-1 h2, {{WRAPPER}} .nova-title-text-1 h3, {{WRAPPER}} .nova-title-text-1 h4, {{WRAPPER}} .nova-title-text-1 h5, {{WRAPPER}} .nova-title-text-1 h6, {{WRAPPER}} .nova-title-text-1 p' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'text_1_typography',
				'selector' => '{{WRAPPER}} .nova-title-text-1, {{WRAPPER}} .nova-title-text-1 h1, {{WRAPPER}} .nova-title-text-1 h2, {{WRAPPER}} .nova-title-text-1 h3, {{WRAPPER}} .nova-title-text-1 h4, {{WRAPPER}} .nova-title-text-1 h5, {{WRAPPER}} .nova-title-text-1 h6, {{WRAPPER}} .nova-title-text-1 p',
			]
		);

		$this->add_control(
			'text_1_max_width_auto',
			[
				'label' => esc_html__( 'Largeur automatique', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'selectors' => [
					'{{WRAPPER}} .nova-title-text-left' => 'max-width: none !important; width: auto !important;',
				],
			]
		);

		$this->add_responsive_control(
			'text_1_max_width',
			[
				'label' => esc_html__( 'Largeur max', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1200,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'vw' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 100,
					'unit' => '%',
				],
				'condition' => [
					'text_1_max_width_auto' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-title-text-left' => 'max-width: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'text_1_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-title-text-left' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Texte 2
		$this->start_controls_section(
			'section_style_text_2',
			[
				'label' => esc_html__( 'Texte 2', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'text_2_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .nova-title-text-2, {{WRAPPER}} .nova-title-text-2 h1, {{WRAPPER}} .nova-title-text-2 h2, {{WRAPPER}} .nova-title-text-2 h3, {{WRAPPER}} .nova-title-text-2 h4, {{WRAPPER}} .nova-title-text-2 h5, {{WRAPPER}} .nova-title-text-2 h6, {{WRAPPER}} .nova-title-text-2 p' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'text_2_typography',
				'selector' => '{{WRAPPER}} .nova-title-text-2, {{WRAPPER}} .nova-title-text-2 h1, {{WRAPPER}} .nova-title-text-2 h2, {{WRAPPER}} .nova-title-text-2 h3, {{WRAPPER}} .nova-title-text-2 h4, {{WRAPPER}} .nova-title-text-2 h5, {{WRAPPER}} .nova-title-text-2 h6, {{WRAPPER}} .nova-title-text-2 p',
			]
		);

		$this->add_control(
			'text_2_max_width_auto',
			[
				'label' => esc_html__( 'Largeur automatique', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'selectors' => [
					'{{WRAPPER}} .nova-title-text-right' => 'max-width: none !important; width: auto !important;',
				],
			]
		);

		$this->add_responsive_control(
			'text_2_max_width',
			[
				'label' => esc_html__( 'Largeur max', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1200,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'vw' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 100,
					'unit' => '%',
				],
				'condition' => [
					'text_2_max_width_auto' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-title-text-right' => 'max-width: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'text_2_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-title-text-right' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Bouton
		$this->start_controls_section(
			'section_style_button',
			[
				'label' => esc_html__( 'Bouton', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'button_show' => 'yes',
				],
			]
		);

		$this->start_controls_tabs( 'button_tabs' );

		// Tab Normal
		$this->start_controls_tab(
			'button_tab_normal',
			[
				'label' => esc_html__( 'Normal', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'button_color',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .nova-title-button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'selector' => '{{WRAPPER}} .nova-title-button',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'button_background',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-title-button',
			]
		);

		$this->end_controls_tab();

		// Tab Hover
		$this->start_controls_tab(
			'button_tab_hover',
			[
				'label' => esc_html__( 'Hover', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'button_color_hover',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-title-button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'button_background_hover',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-title-button:hover',
			]
		);

		$this->add_control(
			'button_border_color_hover',
			[
				'label' => esc_html__( 'Couleur de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-title-button:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'button_box_shadow_hover',
				'label' => esc_html__( 'Ombre de boîte', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-title-button:hover',
			]
		);

		$this->add_control(
			'button_transition',
			[
				'label' => esc_html__( 'Transition', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 3,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 0.3,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-title-button' => 'transition: color {{SIZE}}s ease, background-color {{SIZE}}s ease, border-color {{SIZE}}s ease, box-shadow {{SIZE}}s ease;',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'button_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-title-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-title-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'button_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-title-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'button_border',
				'selector' => '{{WRAPPER}} .nova-title-button',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .nova-title-button',
			]
		);

		$this->add_control(
			'button_align',
			[
				'label' => esc_html__( 'Alignement', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Gauche', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Centre', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Droite', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}} .nova-title-button-wrapper' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_icon_heading',
			[
				'label' => esc_html__( 'Icône', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'button_icon_size',
			[
				'label' => esc_html__( 'Taille icône (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 100,
						'step' => 1,
					],
				],
				'default' => [
					'size' => 16,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-title-button-icon' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-title-button-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_icon_spacing',
			[
				'label' => esc_html__( 'Espacement icône (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
						'step' => 1,
					],
				],
				'default' => [
					'size' => 8,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-title-button-icon.icon-before' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-title-button-icon.icon-after' => 'margin-left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Badge
		$this->start_controls_section(
			'section_style_badge',
			[
				'label' => esc_html__( 'Badge', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'badge_show' => 'yes',
				],
			]
		);

		$this->add_control(
			'badge_text_color',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .nova-title-badge-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'badge__typography',
				'selector' => '{{WRAPPER}} .nova-title-badge, {{WRAPPER}} .nova-title-badge .nova-title-badge-text, {{WRAPPER}} .nova-title-badge span, {{WRAPPER}} .nova-title-badge strong',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'badge_background',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-title-badge',
			]
		);

		$this->add_responsive_control(
			'badge_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-title-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'badge_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-title-badge' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'badge_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-title-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'badge_icon_spacing',
			[
				'label' => esc_html__( 'Espacement icône (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
						'step' => 1,
					],
				],
				'default' => [
					'size' => 8,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-title-badge' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'badge_icon_size',
			[
				'label' => esc_html__( 'Taille icône (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 100,
						'step' => 1,
					],
				],
				'default' => [
					'size' => 16,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-title-badge-icon' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-title-badge-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'badge_icon_color',
			[
				'label' => esc_html__( 'Couleur de l\'icône', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .nova-title-badge-icon' => 'color: {{VALUE}};',
				],
				'condition' => [
					'badge_icon[value]!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'badge_align_items',
			[
				'label' => esc_html__( 'Alignement vertical', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'center',
				'options' => [
					'flex-start' => esc_html__( 'Début', 'NOVA-addons' ),
					'flex-end' => esc_html__( 'Fin', 'NOVA-addons' ),
					'center' => esc_html__( 'Centre', 'NOVA-addons' ),
					'stretch' => esc_html__( 'Étirer', 'NOVA-addons' ),
					'baseline' => esc_html__( 'Ligne de base', 'NOVA-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} .nova-title-badge' => 'align-items: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Récupère la valeur numérique d'un contrôle Slider depuis un item du repeater.
	 * Compatible avec la structure Elementor (size/unit ou sizes/desktop/tablet/mobile).
	 *
	 * @param array  $item   Item du repeater.
	 * @param string $key    Clé du contrôle (ex: position_x, position_y).
	 * @param int    $idx    Index de l'item (non utilisé, pour compatibilité signature).
	 * @param string $device Device (desktop, tablet, mobile).
	 * @return float
	 */
	protected function get_icon_slider_size( $item, $key, $idx = 0, $device = 'desktop' ) {
		if ( ! is_array( $item ) ) {
			return 0;
		}
		
		// Dans un repeater Elementor, les valeurs responsive sont stockées avec des suffixes:
		// position_x (desktop), position_x_tablet, position_x_mobile
		$device_key = ( $device === 'desktop' ) ? $key : $key . '_' . $device;
		
		// Essayer la clé avec suffixe device
		if ( isset( $item[ $device_key ] ) ) {
			$data = is_array( $item[ $device_key ] ) ? $item[ $device_key ] : [];
			if ( isset( $data['size'] ) && $data['size'] !== '' ) {
				return floatval( $data['size'] );
			}
		}
		
		// Fallback sur desktop si le device n'a pas de valeur
		if ( $device !== 'desktop' && isset( $item[ $key ] ) ) {
			$data = is_array( $item[ $key ] ) ? $item[ $key ] : [];
			if ( isset( $data['size'] ) && $data['size'] !== '' ) {
				return floatval( $data['size'] );
			}
		}
		
		return 0;
	}

	/**
	 * Récupère l'unité d'un contrôle Slider depuis un item du repeater.
	 *
	 * @param array  $item   Item du repeater.
	 * @param string $key    Clé du contrôle.
	 * @param int    $idx    Index de l'item (non utilisé).
	 * @param string $device Device (desktop, tablet, mobile).
	 * @return string
	 */
	protected function get_icon_slider_unit( $item, $key, $idx = 0, $device = 'desktop' ) {
		if ( ! is_array( $item ) ) {
			return 'px';
		}
		
		// Dans un repeater Elementor, les valeurs responsive sont stockées avec des suffixes:
		// position_x (desktop), position_x_tablet, position_x_mobile
		$device_key = ( $device === 'desktop' ) ? $key : $key . '_' . $device;
		
		// Essayer la clé avec suffixe device
		if ( isset( $item[ $device_key ] ) ) {
			$data = is_array( $item[ $device_key ] ) ? $item[ $device_key ] : [];
			if ( ! empty( $data['unit'] ) ) {
				return $data['unit'];
			}
		}
		
		// Fallback sur desktop
		if ( $device !== 'desktop' && isset( $item[ $key ] ) ) {
			$data = is_array( $item[ $key ] ) ? $item[ $key ] : [];
			if ( ! empty( $data['unit'] ) ) {
				return $data['unit'];
			}
		}
		
		return 'px';
	}

	/**
	 * Affiche le widget sur le front-end.
	 */
	/**
	 * Traite le texte pour appliquer les styles aux mots spécifiés
	 */
	protected function process_styled_words( $text, $styled_words ) {
		if ( empty( $styled_words ) || empty( $text ) ) {
			return $text;
		}

		// Créer un tableau avec les mots et leurs données pour le tri
		$words_with_data = [];
		foreach ( $styled_words as $index => $word_data ) {
			if ( ! empty( $word_data['word_text'] ) ) {
				// Utiliser l'ID unique du repeater si disponible, sinon utiliser l'index
				$item_id = isset( $word_data['_id'] ) ? $word_data['_id'] : ( isset( $word_data['__id'] ) ? $word_data['__id'] : 'item-' . $index );
				$words_with_data[] = [
					'text' => $word_data['word_text'],
					'id' => $item_id,
					'data' => $word_data,
				];
			}
		}

		// Trier les mots par longueur décroissante pour éviter les remplacements partiels
		usort( $words_with_data, function( $a, $b ) {
			return strlen( $b['text'] ) - strlen( $a['text'] );
		} );

		// Remplacer chaque mot par un span stylisé avec styles inline
		foreach ( $words_with_data as $item ) {
			$word = $item['text'];
			$item_id = $item['id'];
			$word_data = $item['data'];
			
			// Construire les styles inline
			$inline_styles = [];
			
			// Résoudre la couleur de fond - priorité: __globals__ > valeur directe
			$background_color = '';
			$has_background_color = false;
			
			// D'abord vérifier si une couleur globale est définie
			if ( isset( $word_data['__globals__'] ) && ! empty( $word_data['__globals__']['word_background_color'] ) ) {
				$global_ref = $word_data['__globals__']['word_background_color'];
				if ( preg_match( '/id=([a-z0-9]+)/i', $global_ref, $matches ) ) {
					$global_id = $matches[1];
					$background_color = 'var(--e-global-color-' . esc_attr( $global_id ) . ')';
					$has_background_color = true;
				}
			}
			// Sinon utiliser la valeur directe si elle existe
			if ( empty( $background_color ) && ! empty( $word_data['word_background_color'] ) ) {
				$background_color = $word_data['word_background_color'];
				$has_background_color = true;
			}
			
			if ( $has_background_color && ! empty( $background_color ) ) {
				$inline_styles[] = 'background-color: ' . esc_attr( $background_color ) . ';';
			}
			
			// Résoudre la couleur du texte - priorité: __globals__ > valeur directe
			$text_color = '';
			$has_text_color = false;
			
			// D'abord vérifier si une couleur globale est définie
			if ( isset( $word_data['__globals__'] ) && ! empty( $word_data['__globals__']['word_text_color'] ) ) {
				$global_ref = $word_data['__globals__']['word_text_color'];
				if ( preg_match( '/id=([a-z0-9]+)/i', $global_ref, $matches ) ) {
					$global_id = $matches[1];
					$text_color = 'var(--e-global-color-' . esc_attr( $global_id ) . ')';
					$has_text_color = true;
				}
			}
			// Sinon utiliser la valeur directe si elle existe
			if ( empty( $text_color ) && ! empty( $word_data['word_text_color'] ) ) {
				$text_color = $word_data['word_text_color'];
				$has_text_color = true;
			}
			
			if ( $has_text_color && ! empty( $text_color ) ) {
				$inline_styles[] = 'color: ' . esc_attr( $text_color ) . ';';
			}
			
			// Padding
			if ( ! empty( $word_data['word_padding'] ) ) {
				$padding = $word_data['word_padding'];
				$unit = isset( $padding['unit'] ) ? $padding['unit'] : 'px';
				$top = isset( $padding['top'] ) ? $padding['top'] : '4';
				$right = isset( $padding['right'] ) ? $padding['right'] : '12';
				$bottom = isset( $padding['bottom'] ) ? $padding['bottom'] : '4';
				$left = isset( $padding['left'] ) ? $padding['left'] : '12';
				$inline_styles[] = 'padding: ' . esc_attr( $top . $unit . ' ' . $right . $unit . ' ' . $bottom . $unit . ' ' . $left . $unit ) . ';';
			}
			
			// Border radius
			if ( ! empty( $word_data['word_border_radius'] ) ) {
				$radius = $word_data['word_border_radius'];
				$unit = isset( $radius['unit'] ) ? $radius['unit'] : 'px';
				$top = isset( $radius['top'] ) ? $radius['top'] : '4';
				$right = isset( $radius['right'] ) ? $radius['right'] : '4';
				$bottom = isset( $radius['bottom'] ) ? $radius['bottom'] : '4';
				$left = isset( $radius['left'] ) ? $radius['left'] : '4';
				$inline_styles[] = 'border-radius: ' . esc_attr( $top . $unit . ' ' . $right . $unit . ' ' . $bottom . $unit . ' ' . $left . $unit ) . ';';
			}
			
			// Border - Group_Control_Border stocke les données différemment
			// Essayer toutes les variantes possibles
			$border_style = '';
			$border_width = [];
			$border_color = '';
			
			// Chercher toutes les clés qui contiennent "border" ou "color"
			$border_keys = [];
			$color_keys = [];
			foreach ( $word_data as $key => $value ) {
				if ( stripos( $key, 'border' ) !== false ) {
					$border_keys[ $key ] = $value;
				}
				if ( stripos( $key, 'color' ) !== false ) {
					$color_keys[ $key ] = $value;
				}
			}
			
			// Essayer différentes clés pour le style
			if ( isset( $word_data['word_border_border'] ) ) {
				$border_style = $word_data['word_border_border'];
			}
			
			// Essayer différentes clés pour la largeur
			if ( isset( $word_data['word_border_width'] ) ) {
				$border_width = $word_data['word_border_width'];
			}
			
			// Essayer différentes clés pour la couleur - Group_Control_Border peut utiliser différentes clés
			// Essayer d'abord les clés les plus probables
			if ( isset( $word_data['word_border_color'] ) && ! empty( $word_data['word_border_color'] ) ) {
				$border_color = $word_data['word_border_color'];
			} elseif ( isset( $word_data['word_border_border_color'] ) && ! empty( $word_data['word_border_border_color'] ) ) {
				$border_color = $word_data['word_border_border_color'];
			} elseif ( isset( $word_data['border_color'] ) && ! empty( $word_data['border_color'] ) ) {
				$border_color = $word_data['border_color'];
			} else {
				// Chercher dans toutes les clés color
				foreach ( $color_keys as $key => $value ) {
					if ( stripos( $key, 'border' ) !== false && ! empty( $value ) ) {
						$border_color = $value;
						break;
					}
				}
			}
			
			// Si border_color est vide mais qu'il y a une référence globale, essayer de la résoudre
			if ( empty( $border_color ) && isset( $word_data['__globals__'] ) && isset( $word_data['__globals__']['word_border_color'] ) ) {
				$global_ref = $word_data['__globals__']['word_border_color'];
				
				// Essayer de résoudre la couleur globale via Elementor
				// Utiliser get_settings_for_display() qui devrait résoudre les globals
				// Mais pour les repeaters, on doit le faire différemment
				// Vérifier si on peut obtenir la valeur depuis les settings complets
				if ( method_exists( $this, 'get_settings_for_display' ) ) {
					$all_settings = $this->get_settings_for_display();
					// Chercher dans les settings globaux du widget
					if ( isset( $all_settings['__globals__'] ) && isset( $all_settings['__globals__']['word_border_color'] ) ) {
						// Utiliser la méthode Elementor pour résoudre
						$global_color = \Elementor\Plugin::$instance->kits_manager->get_current_settings( 'custom_colors' );
						// Extraire l'ID de la référence globale
						if ( preg_match( '/id=([a-z0-9]+)/i', $global_ref, $matches ) ) {
							$global_id = $matches[1];
							
							// Essayer de récupérer la couleur depuis le kit
							if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->kits_manager ) {
								$kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
								if ( $kit ) {
									$kit_settings = $kit->get_settings();
									if ( isset( $kit_settings['custom_colors'] ) && is_array( $kit_settings['custom_colors'] ) ) {
										foreach ( $kit_settings['custom_colors'] as $custom_color ) {
											if ( isset( $custom_color['_id'] ) && $custom_color['_id'] === $global_id ) {
												$border_color = isset( $custom_color['color'] ) ? $custom_color['color'] : '';
												break;
											}
										}
									}
								}
							}
						}
					}
				}
				
				// Si toujours vide, utiliser une CSS variable que Elementor génère
				if ( empty( $border_color ) && preg_match( '/id=([a-z0-9]+)/i', $global_ref, $matches ) ) {
					$global_id = $matches[1];
					// Elementor génère des variables CSS pour les couleurs globales
					// Format: var(--e-global-color-{id})
					$border_color = 'var(--e-global-color-' . esc_attr( $global_id ) . ')';
				}
			}
			
			// Largeur de bordure
			if ( ! empty( $border_width ) && is_array( $border_width ) ) {
				$unit = isset( $border_width['unit'] ) ? $border_width['unit'] : 'px';
				$top = isset( $border_width['top'] ) && $border_width['top'] !== '' ? $border_width['top'] : '0';
				$right = isset( $border_width['right'] ) && $border_width['right'] !== '' ? $border_width['right'] : '0';
				$bottom = isset( $border_width['bottom'] ) && $border_width['bottom'] !== '' ? $border_width['bottom'] : '0';
				$left = isset( $border_width['left'] ) && $border_width['left'] !== '' ? $border_width['left'] : '0';
				
				// Ne pas ajouter border-width si toutes les valeurs sont 0
				if ( $top != '0' || $right != '0' || $bottom != '0' || $left != '0' ) {
					$border_width_style = 'border-width: ' . esc_attr( $top . $unit . ' ' . $right . $unit . ' ' . $bottom . $unit . ' ' . $left . $unit ) . ';';
					$inline_styles[] = $border_width_style;
				}
			}
			
			// Style de bordure
			if ( ! empty( $border_style ) && $border_style !== 'none' && $border_style !== '' ) {
				$border_style_css = 'border-style: ' . esc_attr( $border_style ) . ';';
				$inline_styles[] = $border_style_css;
			}
			
			// Couleur de bordure - toujours ajouter si définie
			if ( ! empty( $border_color ) ) {
				$border_color_css = 'border-color: ' . esc_attr( $border_color ) . ';';
				$inline_styles[] = $border_color_css;
			}
			
			// Rotation - Essayer différentes clés possibles
			// Note: Cette variable sera utilisée plus tard pour déterminer la rotation finale
			$rotation_data = null;
			$rotation_final_style = null; // Stocker le style final de rotation
			
			// Vérifier si l'animation est activée et si rotation est dans les propriétés à animer
			$animation_enabled_check = isset( $word_data['word_animation_enable'] ) && $word_data['word_animation_enable'] === 'yes';
			$animation_properties_check = isset( $word_data['word_animation_properties'] ) && is_array( $word_data['word_animation_properties'] ) ? $word_data['word_animation_properties'] : [];
			$rotation_is_animated = $animation_enabled_check && in_array( 'rotation', $animation_properties_check, true );
			
			if ( isset( $word_data['word_rotation'] ) ) {
				$rotation_data = $word_data['word_rotation'];
			} elseif ( isset( $word_data['rotation'] ) ) {
				$rotation_data = $word_data['rotation'];
			}
			
			if ( $rotation_data !== null ) {
				$rotation_value = null;
				$rotation_unit = 'deg';
				
				if ( is_array( $rotation_data ) ) {
					// Format: ['size' => 3, 'unit' => 'deg']
					if ( isset( $rotation_data['size'] ) ) {
						$rotation_value = floatval( $rotation_data['size'] );
						$rotation_unit = isset( $rotation_data['unit'] ) ? $rotation_data['unit'] : 'deg';
					} else {
						// Essayer la première valeur du tableau
						$first_value = reset( $rotation_data );
						if ( is_numeric( $first_value ) ) {
							$rotation_value = floatval( $first_value );
						}
					}
				} elseif ( is_numeric( $rotation_data ) ) {
					// Format: nombre direct
					$rotation_value = floatval( $rotation_data );
				} elseif ( is_string( $rotation_data ) && is_numeric( trim( $rotation_data ) ) ) {
					$rotation_value = floatval( trim( $rotation_data ) );
				}
				
				if ( $rotation_value !== null ) {
					// Créer le style de rotation finale
					$rotation_css = 'transform: rotate(' . esc_attr( $rotation_value . $rotation_unit ) . ') !important;';
					$rotation_final_style = $rotation_css; // Stocker pour l'animation
					
					// Si la rotation est animée, NE PAS l'ajouter dans les styles inline initiaux
					// Elle sera ajoutée dans les styles finaux seulement
					if ( ! $rotation_is_animated ) {
						// Si pas d'animation, ajouter la rotation dans les styles inline
						$inline_styles[] = $rotation_css;
					}
				}
			}
			
			// Box shadow - Générer le CSS box-shadow à partir des données
			$box_shadow_enabled = isset( $word_data['word_box_shadow_box_shadow_type'] ) && $word_data['word_box_shadow_box_shadow_type'] === 'yes';
			$box_shadow_data = isset( $word_data['word_box_shadow_box_shadow'] ) ? $word_data['word_box_shadow_box_shadow'] : null;
			
			if ( $box_shadow_enabled && ! empty( $box_shadow_data ) && is_array( $box_shadow_data ) ) {
				// Récupérer les valeurs du box-shadow
				$horizontal = isset( $box_shadow_data['horizontal'] ) && $box_shadow_data['horizontal'] !== '' ? floatval( $box_shadow_data['horizontal'] ) : 0;
				$vertical = isset( $box_shadow_data['vertical'] ) && $box_shadow_data['vertical'] !== '' ? floatval( $box_shadow_data['vertical'] ) : 0;
				$blur = isset( $box_shadow_data['blur'] ) && $box_shadow_data['blur'] !== '' ? floatval( $box_shadow_data['blur'] ) : 0;
				$spread = isset( $box_shadow_data['spread'] ) && $box_shadow_data['spread'] !== '' ? floatval( $box_shadow_data['spread'] ) : 0;
				$color = isset( $box_shadow_data['color'] ) && ! empty( $box_shadow_data['color'] ) ? $box_shadow_data['color'] : 'rgba(0,0,0,0.3)';
				
				// Construire le CSS box-shadow
				// Format: horizontal vertical blur spread color
				$unit = 'px'; // Unit par défaut pour box-shadow
				$box_shadow_css = sprintf(
					'box-shadow: %s%s %s%s %s%s %s%s %s;',
					esc_attr( $horizontal ),
					$unit,
					esc_attr( $vertical ),
					$unit,
					esc_attr( $blur ),
					$unit,
					esc_attr( $spread ),
					$unit,
					esc_attr( $color )
				);
				
				$inline_styles[] = $box_shadow_css;
			}
			
			$final_styles = implode( ' ', $inline_styles );
			
			$class = 'nova-styled-word nova-styled-word-' . esc_attr( $item_id );
			
			// Gestion de l'animation
			$animation_enabled = isset( $word_data['word_animation_enable'] ) && $word_data['word_animation_enable'] === 'yes';
			$data_attrs = '';
			$initial_styles = [];
			$final_styles_array = $inline_styles;
			$final_styles_to_apply = $inline_styles; // Styles finaux à appliquer (modifiés si animation activée)
			
			// Initialiser les variables d'animation à des valeurs par défaut
			$animation_properties = [];
			$animation_trigger = 'on_load';
			$animation_duration = 500;
			$animation_delay = 0;
			$animation_delay_scroll = 0;
			$animation_delay_refresh = 0;
			$animation_timing = 'ease';
			$initial_state = 'default';
			
			if ( $animation_enabled ) {
				$class .= ' nova-styled-word-animated';
				
				// Récupérer les paramètres d'animation (remplacer les valeurs par défaut)
				$animation_trigger = isset( $word_data['word_animation_trigger'] ) ? $word_data['word_animation_trigger'] : $animation_trigger;
				$animation_properties = isset( $word_data['word_animation_properties'] ) && is_array( $word_data['word_animation_properties'] ) ? $word_data['word_animation_properties'] : $animation_properties;
				$animation_duration = isset( $word_data['word_animation_duration'] ) ? intval( $word_data['word_animation_duration'] ) : $animation_duration;
				$animation_delay = isset( $word_data['word_animation_delay'] ) ? intval( $word_data['word_animation_delay'] ) : $animation_delay;
				$animation_delay_scroll = isset( $word_data['word_animation_delay_scroll'] ) ? intval( $word_data['word_animation_delay_scroll'] ) : $animation_delay_scroll;
				$animation_delay_refresh = isset( $word_data['word_animation_delay_refresh'] ) ? intval( $word_data['word_animation_delay_refresh'] ) : $animation_delay_refresh;
				$animation_timing = isset( $word_data['word_animation_timing'] ) ? $word_data['word_animation_timing'] : $animation_timing;
				$initial_state = isset( $word_data['word_animation_initial_state'] ) ? $word_data['word_animation_initial_state'] : $initial_state;
				
					// Créer les styles initiaux basés sur l'état initial
					if ( $initial_state === 'transparent' || $initial_state === 'default' ) {
						// État transparent ou default - masquer les propriétés à animer au départ
						$properties_to_remove = [];
						$rotation_final_value = null; // Stocker la valeur finale de rotation AVANT de la retirer
					
					// D'abord, extraire la valeur finale de rotation si elle existe
					if ( in_array( 'rotation', $animation_properties, true ) ) {
						// Utiliser directement rotation_final_style (qui contient la rotation finale avec !important)
						if ( ! empty( $rotation_final_style ) ) {
							$rotation_final_value = $rotation_final_style;
						} else {
							// Fallback : chercher dans inline_styles (au cas où)
							foreach ( $inline_styles as $style ) {
								if ( strpos( $style, 'transform:' ) !== false || strpos( $style, 'transform ' ) !== false ) {
									// Extraire la valeur de rotation (ex: "transform: rotate(-3deg) !important;")
									$rotation_final_value = trim( $style );
									break;
								}
							}
						}
					}
					
					foreach ( $animation_properties as $prop ) {
						// Utiliser les variables définies au début
						if ( $prop === 'background_color' && $has_background_color ) {
							$initial_styles[] = 'background-color: transparent;';
							$properties_to_remove[] = 'background-color:';
						}
						if ( $prop === 'text_color' && $has_text_color ) {
							$initial_styles[] = 'color: inherit;';
							$properties_to_remove[] = 'color:';
						}
						if ( $prop === 'padding' && ! empty( $word_data['word_padding'] ) ) {
							$initial_styles[] = 'padding: 0;';
							$properties_to_remove[] = 'padding:';
						}
						if ( $prop === 'border_radius' && ! empty( $word_data['word_border_radius'] ) ) {
							$initial_styles[] = 'border-radius: 0;';
							$properties_to_remove[] = 'border-radius:';
						}
						if ( $prop === 'border_width' ) {
							$initial_styles[] = 'border-width: 0;';
							$properties_to_remove[] = 'border-width:';
						}
						if ( $prop === 'border_color' ) {
							$initial_styles[] = 'border-color: transparent;';
							$properties_to_remove[] = 'border-color:';
						}
						if ( $prop === 'box_shadow' ) {
							$initial_styles[] = 'box-shadow: none;';
							$properties_to_remove[] = 'box-shadow:';
						}
						if ( $prop === 'rotation' ) {
							// Toujours ajouter la rotation initiale à 0 si rotation est dans les propriétés à animer
							// Utiliser !important pour forcer l'état initial et pouvoir être surchargé par l'animation
							$initial_styles[] = 'transform: rotate(0deg) !important;';
							$properties_to_remove[] = 'transform:';
						}
					}
					
					// Retirer les propriétés à animer des styles finaux et stocker les styles finaux dans un data-attribute
					$final_styles_filtered = [];
					foreach ( $inline_styles as $style ) {
						$should_keep = true;
						foreach ( $properties_to_remove as $prop_to_remove ) {
							if ( strpos( $style, $prop_to_remove ) !== false ) {
								$should_keep = false;
								break;
							}
						}
						if ( $should_keep ) {
							$final_styles_filtered[] = $style;
						}
					}
					
					// Pour la rotation, retirer TOUS les transform existants et ajouter le transform final correct
					if ( in_array( 'rotation', $animation_properties, true ) && ! empty( $rotation_final_value ) ) {
						// Retirer TOUS les transform existants de final_styles_filtered (au cas où il y en aurait)
						$final_styles_filtered = array_filter( $final_styles_filtered, function( $style ) {
							return strpos( $style, 'transform:' ) === false && strpos( $style, 'transform ' ) === false;
						} );
						$final_styles_filtered = array_values( $final_styles_filtered ); // Réindexer le tableau
						
						// S'assurer que la rotation finale a toujours !important
						if ( strpos( $rotation_final_value, '!important' ) === false ) {
							// Ajouter !important si pas déjà présent
							$rotation_final_value = str_replace( ';', ' !important;', trim( $rotation_final_value, '; ' ) ) . ';';
						}
						$final_styles_filtered[] = $rotation_final_value;
					}
					
					// Stocker les styles finaux dans un data-attribute pour les appliquer au déclenchement
					if ( ! empty( $final_styles_filtered ) ) {
						// Vérification finale : s'assurer qu'il n'y a qu'un seul transform dans les styles finaux
						$transform_count = 0;
						$last_transform_index = -1;
						foreach ( $final_styles_filtered as $index => $style ) {
							if ( strpos( $style, 'transform:' ) !== false || strpos( $style, 'transform ' ) !== false ) {
								$transform_count++;
								$last_transform_index = $index;
							}
						}
						
						// Si plusieurs transform, ne garder que le dernier
						if ( $transform_count > 1 && $last_transform_index >= 0 ) {
							$final_styles_filtered = array_filter( $final_styles_filtered, function( $style, $index ) use ( $last_transform_index ) {
								$is_transform = ( strpos( $style, 'transform:' ) !== false || strpos( $style, 'transform ' ) !== false );
								return ! $is_transform || $index === $last_transform_index;
							}, ARRAY_FILTER_USE_BOTH );
							$final_styles_filtered = array_values( $final_styles_filtered ); // Réindexer
						}
						
						$final_styles_string = implode( ' ', array_values( $final_styles_filtered ) );
						$final_styles_string = str_replace( '"', '&quot;', $final_styles_string );
						$data_attrs .= ' data-final-styles="' . esc_attr( $final_styles_string ) . '"';
					} else {
						// Si seulement la rotation est animée et qu'on n'a pas de styles finaux, ajouter quand même la rotation
						if ( count( $animation_properties ) === 1 && $animation_properties[0] === 'rotation' && ! empty( $rotation_final_value ) ) {
							// S'assurer que la rotation finale a toujours !important
							$rotation_final_string = $rotation_final_value;
							if ( strpos( $rotation_final_string, '!important' ) === false ) {
								// Ajouter !important si pas déjà présent
								$rotation_final_string = str_replace( ';', ' !important;', trim( $rotation_final_string, '; ' ) ) . ';';
							}
							$final_styles_string = str_replace( '"', '&quot;', $rotation_final_string );
							$data_attrs .= ' data-final-styles="' . esc_attr( $final_styles_string ) . '"';
						}
					}
				} else {
					// État personnalisé - créer des styles initiaux pour les propriétés à animer
					// Même avec un état personnalisé, on doit avoir un état initial pour que l'animation fonctionne
					$initial_styles_for_custom = [];
					$properties_to_remove_from_inline = [];
					
					// Pour chaque propriété à animer, créer un état initial
					// Utiliser les variables définies au début du foreach
					foreach ( $animation_properties as $prop ) {
						switch ( $prop ) {
							case 'background_color':
								if ( $has_background_color ) {
									$initial_styles_for_custom[] = 'background-color: transparent;';
									$properties_to_remove_from_inline[] = 'background-color:';
								}
								break;
							case 'text_color':
								if ( $has_text_color ) {
									$initial_styles_for_custom[] = 'color: inherit;';
									$properties_to_remove_from_inline[] = 'color:';
								}
								break;
							case 'padding':
								if ( ! empty( $word_data['word_padding'] ) ) {
									$initial_styles_for_custom[] = 'padding: 0;';
									$properties_to_remove_from_inline[] = 'padding:';
								}
								break;
							case 'border_radius':
								if ( ! empty( $word_data['word_border_radius'] ) ) {
									$initial_styles_for_custom[] = 'border-radius: 0;';
									$properties_to_remove_from_inline[] = 'border-radius:';
								}
								break;
							case 'border_width':
								$initial_styles_for_custom[] = 'border-width: 0;';
								$properties_to_remove_from_inline[] = 'border-width:';
								break;
							case 'border_color':
								$initial_styles_for_custom[] = 'border-color: transparent;';
								$properties_to_remove_from_inline[] = 'border-color:';
								break;
							case 'box_shadow':
								$initial_styles_for_custom[] = 'box-shadow: none;';
								$properties_to_remove_from_inline[] = 'box-shadow:';
								break;
							case 'rotation':
								// Pour la rotation, créer un état initial à 0deg
								// Utiliser !important pour forcer l'état initial et pouvoir être surchargé par l'animation
								$initial_styles_for_custom[] = 'transform: rotate(0deg) !important;';
								$properties_to_remove_from_inline[] = 'transform:';
								break;
						}
					}
					
					// Retirer les propriétés à animer des styles inline appliqués directement
					$final_styles_to_apply = [];
					foreach ( $inline_styles as $style ) {
						$should_keep = true;
						foreach ( $properties_to_remove_from_inline as $prop_to_remove ) {
							if ( strpos( $style, $prop_to_remove ) !== false ) {
								$should_keep = false;
								break;
							}
						}
						if ( $should_keep ) {
							$final_styles_to_apply[] = $style;
						}
					}
					
					// Pour la rotation, extraire la valeur finale
					$rotation_final_for_custom = null;
					if ( in_array( 'rotation', $animation_properties, true ) ) {
						foreach ( $inline_styles as $style ) {
							if ( strpos( $style, 'transform:' ) !== false || strpos( $style, 'transform ' ) !== false ) {
								$rotation_final_for_custom = trim( $style );
								break;
							}
						}
						// Si pas trouvé dans inline_styles mais rotation_final_style existe
						if ( empty( $rotation_final_for_custom ) && ! empty( $rotation_final_style ) ) {
							$rotation_final_for_custom = $rotation_final_style;
						}
					}
					
					// Initialiser $final_styles_for_custom comme un tableau vide
					$final_styles_for_custom = [];
					
					// Filtrer les inline_styles pour ne garder que les propriétés à animer
					foreach ( $inline_styles as $style ) {
						$should_add = false;
						foreach ( $animation_properties as $prop ) {
							switch ( $prop ) {
								case 'background_color':
									if ( strpos( $style, 'background-color:' ) !== false ) {
										$should_add = true;
										break 2;
									}
									break;
								case 'text_color':
									if ( strpos( $style, 'color:' ) !== false && strpos( $style, 'background-color:' ) === false ) {
										$should_add = true;
										break 2;
									}
									break;
								case 'padding':
									if ( strpos( $style, 'padding:' ) !== false ) {
										$should_add = true;
										break 2;
									}
									break;
								case 'border_radius':
									if ( strpos( $style, 'border-radius:' ) !== false ) {
										$should_add = true;
										break 2;
									}
									break;
								case 'border_width':
									if ( strpos( $style, 'border-width:' ) !== false ) {
										$should_add = true;
										break 2;
									}
									break;
								case 'border_color':
									if ( strpos( $style, 'border-color:' ) !== false ) {
										$should_add = true;
										break 2;
									}
									break;
								case 'box_shadow':
									if ( strpos( $style, 'box-shadow:' ) !== false ) {
										$should_add = true;
										break 2;
									}
									break;
								case 'rotation':
									// Rotation sera ajoutée séparément
									break;
							}
						}
						if ( $should_add ) {
							$final_styles_for_custom[] = $style;
						}
					}
					
					// Retirer TOUTES les rotations existantes de $final_styles_for_custom avant d'ajouter la bonne
					$final_styles_for_custom = array_filter( $final_styles_for_custom, function( $style ) {
						return strpos( $style, 'transform:' ) === false && strpos( $style, 'transform ' ) === false;
					} );
					$final_styles_for_custom = array_values( $final_styles_for_custom ); // Réindexer
					
					// Ajouter la rotation si elle existe (s'assurer qu'elle a !important)
					if ( ! empty( $rotation_final_for_custom ) ) {
						// S'assurer que la rotation finale a toujours !important
						if ( strpos( $rotation_final_for_custom, '!important' ) === false ) {
							// Ajouter !important si pas déjà présent
							$rotation_final_for_custom = str_replace( ';', ' !important;', trim( $rotation_final_for_custom, '; ' ) ) . ';';
						}
						$final_styles_for_custom[] = $rotation_final_for_custom;
					}
					
					// Stocker les styles initiaux dans data-initial-styles (pour l'état custom)
					if ( ! empty( $initial_styles_for_custom ) ) {
						$initial_styles_string = implode( ' ', array_values( $initial_styles_for_custom ) );
						$initial_styles_string = str_replace( '"', '&quot;', $initial_styles_string );
						$data_attrs .= ' data-initial-styles="' . esc_attr( $initial_styles_string ) . '"';
						
						// Utiliser les styles initiaux pour le style inline du span (au lieu des styles finaux)
						// Cela permettra à l'animation de fonctionner correctement
						$final_styles_array = $initial_styles_for_custom;
					} else {
						$final_styles_array = $final_styles_to_apply;
					}
					
					// Stocker les styles finaux dans data-final-styles
					if ( ! empty( $final_styles_for_custom ) ) {
						// Vérification finale : s'assurer qu'il n'y a qu'un seul transform dans les styles finaux
						$transform_count = 0;
						$last_transform_index = -1;
						foreach ( $final_styles_for_custom as $index => $style ) {
							if ( strpos( $style, 'transform:' ) !== false || strpos( $style, 'transform ' ) !== false ) {
								$transform_count++;
								$last_transform_index = $index;
							}
						}
						
						// Si plusieurs transform, ne garder que le dernier
						if ( $transform_count > 1 && $last_transform_index >= 0 ) {
							$final_styles_for_custom = array_filter( $final_styles_for_custom, function( $style, $index ) use ( $last_transform_index ) {
								$is_transform = ( strpos( $style, 'transform:' ) !== false || strpos( $style, 'transform ' ) !== false );
								return ! $is_transform || $index === $last_transform_index;
							}, ARRAY_FILTER_USE_BOTH );
							$final_styles_for_custom = array_values( $final_styles_for_custom ); // Réindexer
						}
						
						$final_styles_string = implode( ' ', array_values( $final_styles_for_custom ) );
						$final_styles_string = str_replace( '"', '&quot;', $final_styles_string );
						$data_attrs .= ' data-final-styles="' . esc_attr( $final_styles_string ) . '"';
					} elseif ( in_array( 'rotation', $animation_properties, true ) && ! empty( $rotation_final_for_custom ) ) {
						// Si seule la rotation est animée - s'assurer qu'elle a !important
						$rotation_final_string = $rotation_final_for_custom;
						if ( strpos( $rotation_final_string, '!important' ) === false ) {
							// Ajouter !important si pas déjà présent
							$rotation_final_string = str_replace( ';', ' !important;', trim( $rotation_final_string, '; ' ) ) . ';';
						}
						$final_styles_string = str_replace( '"', '&quot;', $rotation_final_string );
						$data_attrs .= ' data-final-styles="' . esc_attr( $final_styles_string ) . '"';
					}
				}
				
				// Ajouter les data-attributes pour l'animation
				$data_attrs .= ' data-animation-trigger="' . esc_attr( $animation_trigger ) . '"';
				$data_attrs .= ' data-animation-duration="' . esc_attr( $animation_duration ) . '"';
				$data_attrs .= ' data-animation-delay="' . esc_attr( $animation_delay ) . '"';
				$data_attrs .= ' data-animation-delay-scroll="' . esc_attr( $animation_delay_scroll ) . '"';
				$data_attrs .= ' data-animation-delay-refresh="' . esc_attr( $animation_delay_refresh ) . '"';
				$data_attrs .= ' data-animation-timing="' . esc_attr( $animation_timing ) . '"';
				$data_attrs .= ' data-animation-properties="' . esc_attr( implode( ',', $animation_properties ) ) . '"';
				
				// Si on_load, ajouter une classe pour déclencher l'animation
				if ( $animation_trigger === 'on_load' ) {
					$class .= ' nova-styled-word-animate-on-load';
				} elseif ( $animation_trigger === 'on_hover' ) {
					$class .= ' nova-styled-word-animate-on-hover';
				} elseif ( $animation_trigger === 'on_scroll' ) {
					$class .= ' nova-styled-word-animate-on-scroll';
				}
				
				// Ajouter les transitions CSS pour les propriétés animées
				$transition_properties = [];
				foreach ( $animation_properties as $prop ) {
					switch ( $prop ) {
						case 'background_color':
							$transition_properties[] = 'background-color';
							break;
						case 'text_color':
							$transition_properties[] = 'color';
							break;
						case 'padding':
							$transition_properties[] = 'padding';
							break;
						case 'border_radius':
							$transition_properties[] = 'border-radius';
							break;
						case 'border_width':
							$transition_properties[] = 'border-width';
							break;
						case 'border_color':
							$transition_properties[] = 'border-color';
							break;
						case 'box_shadow':
							$transition_properties[] = 'box-shadow';
							break;
						case 'rotation':
							$transition_properties[] = 'transform';
							break;
					}
				}
				
				if ( ! empty( $transition_properties ) ) {
					$transition_css = 'transition: ' . implode( ', ', $transition_properties ) . ' ' . esc_attr( $animation_duration ) . 'ms ' . esc_attr( $animation_timing ) . ';';
					if ( $animation_delay > 0 ) {
						$transition_css .= ' transition-delay: ' . esc_attr( $animation_delay ) . 'ms;';
					}
					// Ajouter la transition aux styles initiaux si on a un état initial défini
					if ( $initial_state === 'transparent' || $initial_state === 'default' ) {
						$initial_styles[] = $transition_css;
					} elseif ( $initial_state === 'custom' && ! empty( $initial_styles_for_custom ) ) {
						// Pour l'état custom, ajouter la transition aux styles initiaux
						$initial_styles_for_custom[] = $transition_css;
						// Mettre à jour data-initial-styles avec la transition
						$initial_styles_string = implode( ' ', array_values( $initial_styles_for_custom ) );
						$initial_styles_string = str_replace( '"', '&quot;', $initial_styles_string );
						// Retirer l'ancien data-initial-styles et le remplacer
						$data_attrs = preg_replace( '/ data-initial-styles="[^"]*"/', '', $data_attrs );
						$data_attrs .= ' data-initial-styles="' . esc_attr( $initial_styles_string ) . '"';
						// Mettre à jour final_styles_array aussi
						$final_styles_array = $initial_styles_for_custom;
					} else {
						$final_styles_to_apply[] = $transition_css;
					}
				}
				
				// Si état initial = transparent ou default, appliquer les styles initiaux au chargement
				if ( ( $initial_state === 'transparent' || $initial_state === 'default' ) && ! empty( $initial_styles ) ) {
					$initial_styles_string = implode( ' ', $initial_styles );
					$initial_styles_string = str_replace( '"', '&quot;', $initial_styles_string );
					$data_attrs .= ' data-initial-styles="' . esc_attr( $initial_styles_string ) . '"';
					// Utiliser les styles initiaux au lieu des styles finaux
					// Les styles initiaux contiennent déjà transform: rotate(0deg) !important; pour la rotation
					$final_styles_array = $initial_styles;
				} elseif ( $initial_state === 'custom' ) {
					// État personnalisé - pour l'animation, utiliser les styles initiaux créés
					// Ces styles initiaux incluent les propriétés à animer à leur état initial
					// + les autres propriétés non animées depuis $final_styles_to_apply
					if ( ! empty( $initial_styles_for_custom ) ) {
						// Combiner les styles initiaux (pour les propriétés animées) avec les autres styles
						$final_styles_array = array_merge( $final_styles_to_apply, $initial_styles_for_custom );
					} else {
						// Si pas de styles initiaux créés, utiliser les styles finaux normalement
						$final_styles_array = $inline_styles;
					}
				}
			}
			
			// Construire les styles finaux à appliquer
			// Si l'animation est activée et que rotation est animée, s'assurer que le style inline contient 0deg
			// et non la valeur finale (qui sera dans data-final-styles)
			if ( $animation_enabled && in_array( 'rotation', $animation_properties, true ) ) {
				// Si $final_styles_array contient déjà transform: rotate(0deg) !important; (dans $initial_styles),
				// on le garde. Sinon, on retire toute rotation et on ajoute 0deg
				$has_initial_rotation = false;
				foreach ( $final_styles_array as $index => $style ) {
					if ( ( strpos( $style, 'transform:' ) !== false || strpos( $style, 'transform ' ) !== false ) 
						&& strpos( $style, 'rotate(0deg)' ) !== false ) {
						$has_initial_rotation = true;
						break;
					}
				}
				
				if ( ! $has_initial_rotation ) {
					// Retirer toute rotation existante (qui serait la valeur finale)
					$final_styles_array = array_filter( $final_styles_array, function( $style ) {
						return strpos( $style, 'transform:' ) === false && strpos( $style, 'transform ' ) === false;
					} );
					$final_styles_array = array_values( $final_styles_array ); // Réindexer
					// Ajouter la rotation initiale à 0deg
					$final_styles_array[] = 'transform: rotate(0deg) !important;';
				}
			}
			
			$final_styles = implode( ' ', array_values( $final_styles_array ) );
			
			// Pour les styles CSS inline, on doit juste échapper les guillemets doubles dans les valeurs
			// Utiliser str_replace pour échapper uniquement les guillemets doubles dans les valeurs CSS
			if ( ! empty( $final_styles_array ) ) {
				// Échapper les guillemets doubles dans les valeurs CSS uniquement
				$escaped_styles = str_replace( '"', '&quot;', $final_styles );
				$style_attr = ' style="' . $escaped_styles . '"';
			} else {
				$style_attr = '';
			}
			
			// Échapper les caractères spéciaux pour la regex
			$escaped_word = preg_quote( $word, '/' );
			
			// Remplacer le mot par un span stylisé (insensible à la casse, une seule fois)
			$replacement = '<span class="' . esc_attr( $class ) . '"' . $style_attr . $data_attrs . '>' . esc_html( $word ) . '</span>';
			
			$text = preg_replace( '/' . $escaped_word . '/iu', $replacement, $text, 1 );
		}

		return $text;
	}

	/**
	 * Affiche le widget sur le front-end.
	 * Logs (erreur 500) : activer WP_DEBUG et WP_DEBUG_LOG dans wp-config.php, puis consulter wp-content/debug.log.
	 * Rechercher "[NOVA-Title]" pour voir jusqu'où le render() s'est exécuté.
	 */
	protected function render() {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		}
		$settings = $this->get_settings_for_display();
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		}

		$text_1 = isset( $settings['text_1'] ) ? $settings['text_1'] : '';
		$text_2 = isset( $settings['text_2'] ) ? $settings['text_2'] : '';
		
		// Styled words settings
		$styled_words_enable = ! empty( $settings['text_1_styled_words_enable'] ) && $settings['text_1_styled_words_enable'] === 'yes';
		$styled_words = ! empty( $settings['text_1_styled_words'] ) ? $settings['text_1_styled_words'] : [];
		
		// Traiter le texte 1 avec les mots stylisés
		if ( $styled_words_enable && ! empty( $styled_words ) ) {
			$text_1 = $this->process_styled_words( $text_1, $styled_words );
		}
		
		// Badge settings
		$badge_show = ! empty( $settings['badge_show'] ) && $settings['badge_show'] === 'yes';
		$badge_text = ! empty( $settings['badge_text'] ) ? $settings['badge_text'] : '';
		$badge_icon = ! empty( $settings['badge_icon'] ) ? $settings['badge_icon'] : [];
		$badge_has_icon = ! empty( $badge_icon ) && ! empty( $badge_icon['value'] );
		
		// Button settings
		$button_show = isset( $settings['button_show'] ) && $settings['button_show'] === 'yes';
		$button_position = isset( $settings['button_position'] ) ? $settings['button_position'] : 'after';
		$button_text = isset( $settings['button_text'] ) ? $settings['button_text'] : '';
		$button_link = isset( $settings['button_link'] ) ? $settings['button_link'] : [];
		$button_icon = ! empty( $settings['button_icon'] ) ? $settings['button_icon'] : [];
		$button_icon_position = isset( $settings['button_icon_position'] ) ? $settings['button_icon_position'] : 'after';
		$button_has_icon = ! empty( $button_icon ) && ! empty( $button_icon['value'] );
		
		// Build button link attributes
		$button_url = ! empty( $button_link['url'] ) ? esc_url( $button_link['url'] ) : '#';
		$button_link_attrs = '';
		if ( ! empty( $button_link['url'] ) ) {
			$this->add_link_attributes( 'button_link', $button_link );
			$button_link_attrs = $this->get_render_attribute_string( 'button_link' );
		}
		
		// Animation config
		$animation_enable = isset( $settings['animation_enable'] ) && $settings['animation_enable'] === 'yes';
		$animation_delay = isset( $settings['animation_delay'] ) ? intval( $settings['animation_delay'] ) : 100;
		$animation_duration = isset( $settings['animation_duration'] ) ? intval( $settings['animation_duration'] ) : 800;
		
		// Build animation config JSON
		$animation_translate_y = isset( $settings['animation_translate_y'] ) ? intval( $settings['animation_translate_y'] ) : 30;
		$animation_config = [
			'enable' => $animation_enable,
			'delay' => $animation_delay,
			'duration' => $animation_duration,
			'translateY' => $animation_translate_y,
		];

		// Icônes décoratives : config pour JS (position initiale + position au scroll) — uniquement pour les icônes rendues
		$title_icons_show = ! empty( $settings['title_icons_show'] ) && $settings['title_icons_show'] === 'yes';
		$title_icons_list  = ! empty( $settings['title_icons'] ) && is_array( $settings['title_icons'] ) ? $settings['title_icons'] : [];
		
		// Limiter le nombre d'icônes pour éviter les problèmes de mémoire
		if ( count( $title_icons_list ) > 50 ) {
			$title_icons_list = array_slice( $title_icons_list, 0, 50 );
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			}
		}
		
		$icons_config      = [];
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		}
		foreach ( $title_icons_list as $idx => $icon_item ) {
			if ( ! is_array( $icon_item ) ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				}
				continue;
			}
			
			// Vérifier le type d'icône (icon ou image)
			$icon_type = isset( $icon_item['icon_type'] ) ? $icon_item['icon_type'] : 'icon';
			$icon_value = isset( $icon_item['icon'] ) ? $icon_item['icon'] : [];
			$icon_image = isset( $icon_item['icon_image'] ) ? $icon_item['icon_image'] : [];
			
			// Vérifier si l'icône ou l'image est définie
			$has_icon = ( $icon_type === 'icon' && ! empty( $icon_value ) && ! empty( $icon_value['value'] ) );
			$has_image = ( $icon_type === 'image' && ! empty( $icon_image ) && ! empty( $icon_image['url'] ) );
			
			if ( ! $has_icon && ! $has_image ) {
				continue;
			}
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			}
			
			// Récupérer les valeurs pour chaque device
			$align_h = isset( $icon_item['align_h'] ) ? $icon_item['align_h'] : 'left';
			$align_v = isset( $icon_item['align_v'] ) ? $icon_item['align_v'] : 'top';
			$duration = isset( $icon_item['scroll_animation_duration'] ) ? intval( $icon_item['scroll_animation_duration'] ) : 800;
			
			// Desktop
			$pos_x_desktop = $this->get_icon_slider_size( $icon_item, 'position_x', $idx, 'desktop' );
			$pos_y_desktop = $this->get_icon_slider_size( $icon_item, 'position_y', $idx, 'desktop' );
			$unit_x_desktop = $this->get_icon_slider_unit( $icon_item, 'position_x', $idx, 'desktop' );
			$unit_y_desktop = $this->get_icon_slider_unit( $icon_item, 'position_y', $idx, 'desktop' );
			$scroll_x_desktop = $this->get_icon_slider_size( $icon_item, 'scroll_position_x', $idx, 'desktop' );
			$scroll_y_desktop = $this->get_icon_slider_size( $icon_item, 'scroll_position_y', $idx, 'desktop' );
			$scroll_unit_x_desktop = $this->get_icon_slider_unit( $icon_item, 'scroll_position_x', $idx, 'desktop' );
			$scroll_unit_y_desktop = $this->get_icon_slider_unit( $icon_item, 'scroll_position_y', $idx, 'desktop' );
			
			// Tablet
			$pos_x_tablet = $this->get_icon_slider_size( $icon_item, 'position_x', $idx, 'tablet' );
			$pos_y_tablet = $this->get_icon_slider_size( $icon_item, 'position_y', $idx, 'tablet' );
			$unit_x_tablet = $this->get_icon_slider_unit( $icon_item, 'position_x', $idx, 'tablet' );
			$unit_y_tablet = $this->get_icon_slider_unit( $icon_item, 'position_y', $idx, 'tablet' );
			$scroll_x_tablet = $this->get_icon_slider_size( $icon_item, 'scroll_position_x', $idx, 'tablet' );
			$scroll_y_tablet = $this->get_icon_slider_size( $icon_item, 'scroll_position_y', $idx, 'tablet' );
			$scroll_unit_x_tablet = $this->get_icon_slider_unit( $icon_item, 'scroll_position_x', $idx, 'tablet' );
			$scroll_unit_y_tablet = $this->get_icon_slider_unit( $icon_item, 'scroll_position_y', $idx, 'tablet' );
			
			// Mobile
			$pos_x_mobile = $this->get_icon_slider_size( $icon_item, 'position_x', $idx, 'mobile' );
			$pos_y_mobile = $this->get_icon_slider_size( $icon_item, 'position_y', $idx, 'mobile' );
			$unit_x_mobile = $this->get_icon_slider_unit( $icon_item, 'position_x', $idx, 'mobile' );
			$unit_y_mobile = $this->get_icon_slider_unit( $icon_item, 'position_y', $idx, 'mobile' );
			$scroll_x_mobile = $this->get_icon_slider_size( $icon_item, 'scroll_position_x', $idx, 'mobile' );
			$scroll_y_mobile = $this->get_icon_slider_size( $icon_item, 'scroll_position_y', $idx, 'mobile' );
			$scroll_unit_x_mobile = $this->get_icon_slider_unit( $icon_item, 'scroll_position_x', $idx, 'mobile' );
			$scroll_unit_y_mobile = $this->get_icon_slider_unit( $icon_item, 'scroll_position_y', $idx, 'mobile' );
			
			$icons_config[] = [
				'alignH' => $align_h,
				'alignV' => $align_v,
				'duration' => $duration,
				'desktop' => [
					'posX' => $pos_x_desktop,
					'posY' => $pos_y_desktop,
					'unitX' => $unit_x_desktop,
					'unitY' => $unit_y_desktop,
					'scrollX' => $scroll_x_desktop,
					'scrollY' => $scroll_y_desktop,
					'scrollUnitX' => $scroll_unit_x_desktop,
					'scrollUnitY' => $scroll_unit_y_desktop,
				],
				'tablet' => [
					'posX' => $pos_x_tablet,
					'posY' => $pos_y_tablet,
					'unitX' => $unit_x_tablet,
					'unitY' => $unit_y_tablet,
					'scrollX' => $scroll_x_tablet,
					'scrollY' => $scroll_y_tablet,
					'scrollUnitX' => $scroll_unit_x_tablet,
					'scrollUnitY' => $scroll_unit_y_tablet,
				],
				'mobile' => [
					'posX' => $pos_x_mobile,
					'posY' => $pos_y_mobile,
					'unitX' => $unit_x_mobile,
					'unitY' => $unit_y_mobile,
					'scrollX' => $scroll_x_mobile,
					'scrollY' => $scroll_y_mobile,
					'scrollUnitX' => $scroll_unit_x_mobile,
					'scrollUnitY' => $scroll_unit_y_mobile,
				],
			];
		}
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		}
		
		// Debug: Préparer les données pour JavaScript
		$debug_data = [
			'styled_words_enable' => $styled_words_enable,
			'styled_words_count' => count( $styled_words ),
			'styled_words' => $styled_words_enable ? $styled_words : [],
		];

		$data_title_icons_attr = '';
		$data_title_icons_trigger_attr = '';
		if ( $title_icons_show ) {
			// Extraire la valeur et l'unité du trigger avec protection contre les erreurs
			$trigger_data = isset( $settings['title_icons_scroll_trigger_top'] ) ? $settings['title_icons_scroll_trigger_top'] : null;
			$trigger_value = 82;
			$trigger_unit = 'px';
			
			// Vérifier que $trigger_data n'est pas une structure imbriquée complexe qui pourrait causer des problèmes
			if ( is_array( $trigger_data ) && count( $trigger_data ) < 20 ) {
				// Format Slider: ['size' => 82, 'unit' => 'px'] ou format responsive
				if ( isset( $trigger_data['size'] ) && is_numeric( $trigger_data['size'] ) ) {
					$trigger_value = floatval( $trigger_data['size'] );
				} elseif ( isset( $trigger_data['sizes'] ) && is_array( $trigger_data['sizes'] ) && isset( $trigger_data['sizes']['desktop'] ) ) {
					// Format responsive
					if ( isset( $trigger_data['sizes']['desktop']['size'] ) && is_numeric( $trigger_data['sizes']['desktop']['size'] ) ) {
						$trigger_value = floatval( $trigger_data['sizes']['desktop']['size'] );
					}
					if ( isset( $trigger_data['sizes']['desktop']['unit'] ) && ! empty( $trigger_data['sizes']['desktop']['unit'] ) ) {
						$trigger_unit = sanitize_text_field( $trigger_data['sizes']['desktop']['unit'] );
					}
				}
				
				if ( isset( $trigger_data['unit'] ) && ! empty( $trigger_data['unit'] ) ) {
					$trigger_unit = sanitize_text_field( $trigger_data['unit'] );
				}
			} elseif ( is_numeric( $trigger_data ) ) {
				// Format ancien (NUMBER): valeur directe
				$trigger_value = floatval( $trigger_data );
			}
			
			// Valider et limiter les valeurs
			$trigger_value = max( 0, min( 10000, $trigger_value ) ); // Entre 0 et 10000
			$trigger_unit = in_array( $trigger_unit, [ 'px', '%', 'vh' ], true ) ? $trigger_unit : 'px';
			
			$data_title_icons_trigger_attr = ' data-title-icons-trigger-value="' . esc_attr( $trigger_value ) . '"';
			$data_title_icons_trigger_attr .= ' data-title-icons-trigger-unit="' . esc_attr( $trigger_unit ) . '"';
			
			if ( ! empty( $icons_config ) && is_array( $icons_config ) ) {
				$icons_config_json = wp_json_encode( $icons_config );
				$data_title_icons_attr = ( $icons_config_json !== false ) ? ' data-title-icons-config="' . esc_attr( $icons_config_json ) . '"' : '';
				if ( $icons_config_json === false && defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				}
			}
		}
		?>
		<div class="nova-title-widget" data-animation-config="<?php echo esc_attr( wp_json_encode( $animation_config ) ); ?>" data-debug-styled-words="<?php echo esc_attr( wp_json_encode( $debug_data ) ); ?>"<?php echo $data_title_icons_attr . $data_title_icons_trigger_attr; ?>>
			<?php if ( $title_icons_show && ! empty( $title_icons_list ) ) : ?>
				<div class="nova-title-icons-wrapper" aria-hidden="true">
					<?php foreach ( $title_icons_list as $icon_idx => $icon_item ) :
						// Vérifier le type d'icône (icon ou image)
						$icon_type = isset( $icon_item['icon_type'] ) ? $icon_item['icon_type'] : 'icon';
						$icon_value = isset( $icon_item['icon'] ) ? $icon_item['icon'] : [];
						$icon_image = isset( $icon_item['icon_image'] ) ? $icon_item['icon_image'] : [];
						
						// Vérifier si l'icône ou l'image est définie
						$has_icon = ( $icon_type === 'icon' && ! empty( $icon_value ) && ! empty( $icon_value['value'] ) );
						$has_image = ( $icon_type === 'image' && ! empty( $icon_image ) && ! empty( $icon_image['url'] ) );
						
						if ( ! $has_icon && ! $has_image ) {
							continue;
						}
						
						$item_id = isset( $icon_item['_id'] ) ? $icon_item['_id'] : ( 'item-' . $icon_idx );
						// Position initiale : toujours en inline pour que l’éditeur mette à jour en temps réel (re-render du widget à chaque changement).
						$align_h = isset( $icon_item['align_h'] ) ? $icon_item['align_h'] : 'left';
						$align_v = isset( $icon_item['align_v'] ) ? $icon_item['align_v'] : 'top';
						$pos_x_desktop   = $this->get_icon_slider_size( $icon_item, 'position_x', $icon_idx, 'desktop' );
						$pos_y_desktop   = $this->get_icon_slider_size( $icon_item, 'position_y', $icon_idx, 'desktop' );
						$unit_x_desktop  = $this->get_icon_slider_unit( $icon_item, 'position_x', $icon_idx, 'desktop' );
						$unit_y_desktop  = $this->get_icon_slider_unit( $icon_item, 'position_y', $icon_idx, 'desktop' );
						
						$pos_x_tablet   = $this->get_icon_slider_size( $icon_item, 'position_x', $icon_idx, 'tablet' );
						$pos_y_tablet   = $this->get_icon_slider_size( $icon_item, 'position_y', $icon_idx, 'tablet' );
						$unit_x_tablet  = $this->get_icon_slider_unit( $icon_item, 'position_x', $icon_idx, 'tablet' );
						$unit_y_tablet  = $this->get_icon_slider_unit( $icon_item, 'position_y', $icon_idx, 'tablet' );
						
						$pos_x_mobile   = $this->get_icon_slider_size( $icon_item, 'position_x', $icon_idx, 'mobile' );
						$pos_y_mobile   = $this->get_icon_slider_size( $icon_item, 'position_y', $icon_idx, 'mobile' );
						$unit_x_mobile  = $this->get_icon_slider_unit( $icon_item, 'position_x', $icon_idx, 'mobile' );
						$unit_y_mobile  = $this->get_icon_slider_unit( $icon_item, 'position_y', $icon_idx, 'mobile' );
						$parts   = [];
						// Horizontal
						if ( $align_h === 'right' ) {
							$parts[] = 'left:auto;right:0';
						} elseif ( $align_h === 'center' ) {
							$parts[] = 'right:auto;left:50%';
						} else {
							$parts[] = 'right:auto;left:0';
						}
						// Vertical
						if ( $align_v === 'bottom' ) {
							$parts[] = 'top:auto;bottom:0';
						} elseif ( $align_v === 'center' ) {
							$parts[] = 'bottom:auto;top:50%';
						} else {
							$parts[] = 'bottom:auto;top:0';
						}
						// Transform : générer directement les valeurs inline pour un update live dans l'éditeur
						// En centre on ajoute -50% pour centrer l'élément, puis le décalage
						// Fonction helper pour calculer le transform
						$calculate_transform = function( $pos_x, $pos_y, $unit_x, $unit_y, $align_h, $align_v ) {
							if ( $align_h === 'center' ) {
								$tx = ( $pos_x != 0 ) ? 'calc(-50% + ' . $pos_x . $unit_x . ')' : '-50%';
							} else {
								$tx = $pos_x . $unit_x;
							}
							if ( $align_v === 'center' ) {
								$ty = ( $pos_y != 0 ) ? 'calc(-50% + ' . $pos_y . $unit_y . ')' : '-50%';
							} else {
								$ty = $pos_y . $unit_y;
							}
							return 'translate(' . $tx . ', ' . $ty . ')';
						};
						
						// Transform desktop (par défaut) - seulement pour le frontend
						// En mode éditeur, le JavaScript gère le transform pour permettre le responsive
						$is_editor_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();
						if ( ! $is_editor_mode ) {
							$transform_desktop = $calculate_transform( $pos_x_desktop, $pos_y_desktop, $unit_x_desktop, $unit_y_desktop, $align_h, $align_v );
							$parts[] = sprintf( 'transform: %s', esc_attr( $transform_desktop ) );
						}
						
						// Générer les CSS custom properties pour les valeurs responsive (toujours, pour JS et CSS)
						$parts[] = sprintf( '--nova-icon-tx-desktop: %s%s', $pos_x_desktop, $unit_x_desktop );
						$parts[] = sprintf( '--nova-icon-ty-desktop: %s%s', $pos_y_desktop, $unit_y_desktop );
						$parts[] = sprintf( '--nova-icon-tx-tablet: %s%s', $pos_x_tablet, $unit_x_tablet );
						$parts[] = sprintf( '--nova-icon-ty-tablet: %s%s', $pos_y_tablet, $unit_y_tablet );
						$parts[] = sprintf( '--nova-icon-tx-mobile: %s%s', $pos_x_mobile, $unit_x_mobile );
						$parts[] = sprintf( '--nova-icon-ty-mobile: %s%s', $pos_y_mobile, $unit_y_mobile );
						
						// Calculate image size for .nova-title-decoration-icon span (BEFORE rendering it)
						$img_style_parts = [];
						if ( $has_image ) {
							$w_type = isset( $icon_item['icon_image_width_type'] ) ? $icon_item['icon_image_width_type'] : 'custom';
							$h_type = isset( $icon_item['icon_image_height_type'] ) ? $icon_item['icon_image_height_type'] : 'auto';
							
							// Width on span - utiliser get_icon_slider_size pour chaque device
							if ( $w_type === 'auto' ) {
								$parts[] = 'width:auto';
							} elseif ( $w_type === 'custom' ) {
								$w_size_d = $this->get_icon_slider_size( $icon_item, 'icon_image_width', $icon_idx, 'desktop' );
								$w_unit_d = $this->get_icon_slider_unit( $icon_item, 'icon_image_width', $icon_idx, 'desktop' );
								$w_size_t = $this->get_icon_slider_size( $icon_item, 'icon_image_width', $icon_idx, 'tablet' );
								$w_unit_t = $this->get_icon_slider_unit( $icon_item, 'icon_image_width', $icon_idx, 'tablet' );
								$w_size_m = $this->get_icon_slider_size( $icon_item, 'icon_image_width', $icon_idx, 'mobile' );
								$w_unit_m = $this->get_icon_slider_unit( $icon_item, 'icon_image_width', $icon_idx, 'mobile' );
								
								if ( $w_size_d ) {
									$parts[] = 'width: var(--nova-icon-w-current, ' . $w_size_d . $w_unit_d . ')';
									$parts[] = '--nova-icon-w-desktop:' . $w_size_d . $w_unit_d;
									$parts[] = '--nova-icon-w-tablet:' . ( $w_size_t ? $w_size_t . $w_unit_t : $w_size_d . $w_unit_d );
									$parts[] = '--nova-icon-w-mobile:' . ( $w_size_m ? $w_size_m . $w_unit_m : $w_size_d . $w_unit_d );
									$img_style_parts[] = 'width:100%';
								}
							}
							
							// Height on span
							if ( $h_type === 'auto' ) {
								$parts[] = 'height:auto';
								$img_style_parts[] = 'height:auto';
							} elseif ( $h_type === 'custom' ) {
								$h_size_d = $this->get_icon_slider_size( $icon_item, 'icon_image_height', $icon_idx, 'desktop' );
								$h_unit_d = $this->get_icon_slider_unit( $icon_item, 'icon_image_height', $icon_idx, 'desktop' );
								$h_size_t = $this->get_icon_slider_size( $icon_item, 'icon_image_height', $icon_idx, 'tablet' );
								$h_unit_t = $this->get_icon_slider_unit( $icon_item, 'icon_image_height', $icon_idx, 'tablet' );
								$h_size_m = $this->get_icon_slider_size( $icon_item, 'icon_image_height', $icon_idx, 'mobile' );
								$h_unit_m = $this->get_icon_slider_unit( $icon_item, 'icon_image_height', $icon_idx, 'mobile' );
								
								if ( $h_size_d ) {
									$parts[] = 'height: var(--nova-icon-h-current, ' . $h_size_d . $h_unit_d . ')';
									$parts[] = '--nova-icon-h-desktop:' . $h_size_d . $h_unit_d;
									$parts[] = '--nova-icon-h-tablet:' . ( $h_size_t ? $h_size_t . $h_unit_t : $h_size_d . $h_unit_d );
									$parts[] = '--nova-icon-h-mobile:' . ( $h_size_m ? $h_size_m . $h_unit_m : $h_size_d . $h_unit_d );
									$img_style_parts[] = 'height:100%';
								}
							}
						}
						
						$style = implode( '; ', $parts );
					?>
						<span class="nova-title-decoration-icon nova-title-decoration-icon-<?php echo esc_attr( $item_id ); ?> elementor-repeater-item-<?php echo esc_attr( $item_id ); ?>" style="<?php echo esc_attr( $style ); ?>">
							<span class="nova-title-decoration-icon-inner">
								<?php if ( $has_icon ) : ?>
									<?php Icons_Manager::render_icon( $icon_value, [ 'aria-hidden' => 'true' ] ); ?>
								<?php elseif ( $has_image ) : ?>
								<?php
								$img_style_attr = ! empty( $img_style_parts ) ? ' style="' . esc_attr( implode( '; ', $img_style_parts ) ) . '"' : '';
								?>
								<img src="<?php echo esc_url( $icon_image['url'] ); ?>" alt="<?php echo esc_attr__( 'Icône décorative', 'NOVA-addons' ); ?>"<?php echo $img_style_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
							<?php endif; ?>
							</span>
						</span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php if ( $button_show && $button_position === 'before' && ( ! empty( $button_text ) || $button_has_icon ) ) : ?>
				<div class="nova-title-button-wrapper">
					<a href="<?php echo esc_url( $button_url ); ?>" <?php echo $button_link_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="nova-title-button">
						<?php if ( $button_has_icon && $button_icon_position === 'before' ) : ?>
							<span class="nova-title-button-icon icon-before">
								<?php Icons_Manager::render_icon( $button_icon, [ 'aria-hidden' => 'true' ] ); ?>
							</span>
						<?php endif; ?>
						<?php if ( ! empty( $button_text ) ) : ?>
							<span class="nova-title-button-text"><?php echo esc_html( $button_text ); ?></span>
						<?php endif; ?>
						<?php if ( $button_has_icon && $button_icon_position === 'after' ) : ?>
							<span class="nova-title-button-icon icon-after">
								<?php Icons_Manager::render_icon( $button_icon, [ 'aria-hidden' => 'true' ] ); ?>
							</span>
						<?php endif; ?>
					</a>
				</div>
			<?php endif; ?>
			
			<div class="nova-title-overlay-content">
				<div class="nova-title-left-group">
					<?php if ( $badge_show ) : ?>
						<div class="nova-title-badge">
							<?php if ( $badge_has_icon ) : ?>
								<span class="nova-title-badge-icon">
									<?php Icons_Manager::render_icon( $badge_icon, [ 'aria-hidden' => 'true' ] ); ?>
								</span>
							<?php endif; ?>
							<?php if ( ! empty( $badge_text ) ) : ?>
								<span class="nova-title-badge-text"><?php echo esc_html( $badge_text ); ?></span>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					
					<?php if ( ! empty( $text_1 ) ) : ?>
						<div class="nova-title-text nova-title-text-left nova-title-text-1">
							<?php 
							// Le HTML est déjà sécurisé via esc_attr() et esc_html() dans process_styled_words()
							// wp_kses() filtre le contenu de l'attribut style et supprime transform et !important
							// Utiliser directement le HTML sécurisé sans wp_kses() pour préserver les styles CSS
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo $text_1; 
							?>
						</div>
					<?php endif; ?>
				</div>
				
				
				<?php if ( ! empty( $text_2 ) ) : ?>
					<div class="nova-title-text nova-title-text-right">
						<div class="nova-title-text nova-title-text-2">
							<?php echo wp_kses_post( $text_2 ); ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
			
			<?php if ( $button_show && $button_position === 'after' && ( ! empty( $button_text ) || $button_has_icon ) ) : ?>
				<div class="nova-title-button-wrapper">
					<a href="<?php echo esc_url( $button_url ); ?>" <?php echo $button_link_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="nova-title-button">
						<?php if ( $button_has_icon && $button_icon_position === 'before' ) : ?>
							<span class="nova-title-button-icon icon-before">
								<?php Icons_Manager::render_icon( $button_icon, [ 'aria-hidden' => 'true' ] ); ?>
							</span>
						<?php endif; ?>
						<?php if ( ! empty( $button_text ) ) : ?>
							<span class="nova-title-button-text"><?php echo esc_html( $button_text ); ?></span>
						<?php endif; ?>
						<?php if ( $button_has_icon && $button_icon_position === 'after' ) : ?>
							<span class="nova-title-button-icon icon-after">
								<?php Icons_Manager::render_icon( $button_icon, [ 'aria-hidden' => 'true' ] ); ?>
							</span>
						<?php endif; ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
		<script>
		// Script de debug NOVA Title désactivé pour éviter le bruit dans la console.
		</script>
		<?php
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		}
	}
}

