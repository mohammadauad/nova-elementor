<?php
namespace Nova_Addons_Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Elementor\Widget_Base;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Widget NOVA Stacking Cards 2.
 * Two-column layout with icon+text+button on left, image on right.
 * Cards don't take full screen width.
 */
class Stacking_Cards_2_Widget extends Widget_Base {

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'nova-stacking-cards-2';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'NOVA Stacking Cards 2', 'NOVA-addons' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-posts-grid';
	}

	/**
	 * Widget categories.
	 *
	 * @return array
	 */
	public function get_categories() {
		return [ 'NOVA-addons' ];
	}

	/**
	 * Keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return [ 'stack', 'cards', 'animation', 'gsap', 'scroll', 'sticky', 'NOVA', 'two-column' ];
	}

	/**
	 * Style dependencies.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		// Always load main styles.
		// Loading NOVA Title assets conditionnellement ici peut créer des boucles internes
		// avec get_settings_for_display() → get_style_depends().
		// Les assets de NOVA Title sont donc gérés par le widget NOVA Title lui‑même.
		return [ 'nova-stacking-cards-2-style' ];
	}

	/**
	 * Script dependencies.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		// Même logique que pour les styles : éviter d'appeler get_settings_for_display()
		// ici pour ne pas provoquer de récursion avec l'initialisation Elementor.
		return [ 'gsap', 'gsap-scrolltrigger', 'nova-stacking-cards-2-script' ];
	}

	/**
	 * Register widget controls.
	 *
	 * @return void
	 */
	protected function register_controls() {

		/**
		 * Layout & container settings.
		 */
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Mise en page', 'NOVA-addons' ),
			]
		);

		$this->add_responsive_control(
			'container_width',
			[
				'label' => esc_html__( 'Largeur du conteneur', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range' => [
					'px' => [ 'min' => 600, 'max' => 1600 ],
					'%'  => [ 'min' => 50,  'max' => 100 ],
					'vw' => [ 'min' => 50,  'max' => 100 ],
				],
				'default' => [
					'size' => 1200,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-cards-2' => '--container-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_height',
			[
				'label' => esc_html__( 'Hauteur des cartes', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vh', 'custom' ],
				'range' => [
					'px' => [ 'min' => 300, 'max' => 800 ],
					'vh' => [ 'min' => 30,  'max' => 90 ],
				],
				'default' => [
					'size' => 500,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-cards-2' => '--card-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'columns_ratio',
			[
				'label' => esc_html__( 'Largeur colonne contenu', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px' ],
				'range' => [
					'%' => [ 'min' => 20, 'max' => 80 ],
					'px' => [ 'min' => 100, 'max' => 800 ],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__content' => 'flex-basis: {{SIZE}}{{UNIT}}; flex-shrink: 0;',
				],
			]
		);

		$this->add_responsive_control(
			'card_gap',
			[
				'label' => esc_html__( 'Espacement entre les colonnes', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 100 ],
					'em' => [ 'min' => 0, 'max' => 6 ],
				],
				'default' => [
					'size' => 40,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__inner' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'card_radius',
			[
				'label' => esc_html__( 'Arrondi des cartes', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 60 ],
					'%'  => [ 'min' => 0, 'max' => 50 ],
				],
				'default' => [
					'size' => 20,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2' => '--card-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'image_radius',
			[
				'label' => esc_html__( 'Arrondi des images', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 60 ],
					'%'  => [ 'min' => 0, 'max' => 50 ],
				],
				'default' => [
					'size' => 16,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__image' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_padding',
			[
				'label' => esc_html__( 'Padding des cartes', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'default' => [
					'top' => '40',
					'right' => '40',
					'bottom' => '40',
					'left' => '40',
					'unit' => 'px',
				],
			]
		);

		$this->add_control(
			'reverse_layout',
			[
				'label' => esc_html__( 'Inverser les colonnes', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__inner' => 'flex-direction: row-reverse;',
				],
			]
		);

		$this->add_control(
			'alternate_layout',
			[
				'label' => esc_html__( 'Alterner les colonnes', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => '',
				'description' => esc_html__( 'Alterne la position de l\'image entre gauche et droite pour chaque carte.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'stacked_offset_enable',
			[
				'label' => esc_html__( 'Décalage empilé (stacking)', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => '',
				'description' => esc_html__( 'Empile les cartes avec un décalage vertical (1ère carte top 0, 2e top + décalage, 3e top + 2× décalage…). Le bloc est centré verticalement en tenant compte du décalage.', 'NOVA-addons' ),
			]
		);

		$this->add_responsive_control(
			'stacked_offset',
			[
				'label' => esc_html__( 'Décalage vertical entre les cartes', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 80 ],
					'em' => [ 'min' => 0, 'max' => 5 ],
				],
				'default' => [
					'size' => 20,
					'unit' => 'px',
				],
				'condition' => [
					'stacked_offset_enable' => 'yes',
				],
				'description' => esc_html__( 'Espace en Y entre chaque carte (ex. 20px : 1ère à 0, 2e à 20px, 3e à 40px…).', 'NOVA-addons' ),
			]
		);

		$this->end_controls_section();

		/**
		 * Cards repeater.
		 */
		$this->start_controls_section(
			'section_cards',
			[
				'label' => esc_html__( 'Cartes', 'NOVA-addons' ),
			]
		);

		/**
		 * Option to replace first card with NOVA Title widget content.
		 */
		$this->add_control(
			'use_nova_title_for_first_card',
			[
				'label' => esc_html__( 'Remplacer la première carte par un widget NOVA Title', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => '',
				'description' => esc_html__( 'Quand activé, la première carte sera complètement remplacée par le contenu d\'un widget NOVA Title sélectionné.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'selected_nova_title_widget_id',
			[
				'label' => esc_html__( 'Sélectionner un widget NOVA Title', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => '',
				'options' => $this->get_nova_title_widgets_options_safe(),
				'condition' => [
					'use_nova_title_for_first_card' => 'yes',
				],
				'description' => esc_html__( 'Choisissez le widget NOVA Title à afficher dans la première carte. Le widget sélectionné sera automatiquement caché sur la page. Rechargez la page si les widgets ne s\'affichent pas.', 'NOVA-addons' ),
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'card_icon_heading',
			[
				'label' => esc_html__( 'Icône', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
			]
		);

		$repeater->add_control(
			'card_icon_type',
			[
				'label' => esc_html__( 'Type d\'icône', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options' => [
					'icon' => [
						'title' => esc_html__( 'Icône', 'NOVA-addons' ),
						'icon'  => 'eicon-star',
					],
					'image' => [
						'title' => esc_html__( 'Image', 'NOVA-addons' ),
						'icon'  => 'eicon-image-bold',
					],
					'none' => [
						'title' => esc_html__( 'Aucun', 'NOVA-addons' ),
						'icon'  => 'eicon-ban',
					],
				],
				'default' => 'icon',
			]
		);

		$repeater->add_control(
			'card_icon',
			[
				'label' => esc_html__( 'Icône (bibliothèque)', 'NOVA-addons' ),
				'type' => Controls_Manager::ICONS,
				'condition' => [
					'card_icon_type' => 'icon',
				],
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'card_icon_image',
			[
				'label' => esc_html__( 'Image icône', 'NOVA-addons' ),
				'type' => Controls_Manager::MEDIA,
				'media_types' => [ 'image', 'svg' ],
				'condition' => [
					'card_icon_type' => 'image',
				],
			]
		);

		$repeater->add_control(
			'card_content_heading',
			[
				'label' => esc_html__( 'Contenu', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$repeater->add_control(
			'card_title',
			[
				'label' => esc_html__( 'Titre', 'NOVA-addons' ),
				'type' => Controls_Manager::WYSIWYG,
				'default' => esc_html__( 'Titre de la carte', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'card_description',
			[
				'label' => esc_html__( 'Description', 'NOVA-addons' ),
				'type' => Controls_Manager::WYSIWYG,
				'default' => esc_html__( 'Ajoutez ici un texte descriptif pour cette carte.', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'card_button_heading',
			[
				'label' => esc_html__( 'Bouton', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$repeater->add_control(
			'card_button_text',
			[
				'label' => esc_html__( 'Texte du bouton', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'En savoir plus', 'NOVA-addons' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'card_button_link',
			[
				'label' => esc_html__( 'Lien du bouton', 'NOVA-addons' ),
				'type' => Controls_Manager::URL,
				'placeholder' => 'https://example.com',
				'default' => [
					'url' => '#',
				],
			]
		);

		$repeater->add_control(
			'card_button_icon',
			[
				'label' => esc_html__( 'Icône du bouton', 'NOVA-addons' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-arrow-right',
					'library' => 'fa-solid',
				],
				'skin' => 'inline',
				'label_block' => false,
			]
		);

		$repeater->add_control(
			'card_button_icon_position',
			[
				'label' => esc_html__( 'Position de l\'icône', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'before',
				'options' => [
					'before' => esc_html__( 'Avant le texte', 'NOVA-addons' ),
					'after' => esc_html__( 'Après le texte', 'NOVA-addons' ),
				],
			]
		);

		$repeater->add_control(
			'card_media_heading',
			[
				'label' => esc_html__( 'Image', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$repeater->add_control(
			'card_media',
			[
				'label' => esc_html__( 'Image de la carte', 'NOVA-addons' ),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater->add_control(
			'card_style_heading',
			[
				'label' => esc_html__( 'Style de la carte', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$repeater->add_control(
			'card_background_color',
			[
				'label' => esc_html__( 'Couleur de fond', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}.nova-stacking-card-2' => 'background-color: {{VALUE}};',
				],
			]
		);

		$repeater->add_control(
			'card_text_color',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#1a1a2e',
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}.nova-stacking-card-2' => 'color: {{VALUE}};',
					'{{WRAPPER}} {{CURRENT_ITEM}}.nova-stacking-card-2 .nova-stacking-card-2__title *' => 'color: {{VALUE}};',
					'{{WRAPPER}} {{CURRENT_ITEM}}.nova-stacking-card-2 .nova-stacking-card-2__description' => 'color: {{VALUE}};',
					'{{WRAPPER}} {{CURRENT_ITEM}}.nova-stacking-card-2 .nova-stacking-card-2__description p' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'cards',
			[
				'label' => esc_html__( 'Cartes', 'NOVA-addons' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'card_title' => esc_html__( 'Création de site web', 'NOVA-addons' ),
						'card_description' => esc_html__( 'Nous créons des sites web modernes et performants adaptés à vos besoins.', 'NOVA-addons' ),
						'card_button_text' => esc_html__( 'Découvrir', 'NOVA-addons' ),
						'card_background_color' => '#FEF9E7',
					],
					[
						'card_title' => esc_html__( 'Infographie et Webdesign', 'NOVA-addons' ),
						'card_description' => esc_html__( 'Des designs créatifs et des infographies impactantes pour votre marque.', 'NOVA-addons' ),
						'card_button_text' => esc_html__( 'Explorer', 'NOVA-addons' ),
						'card_background_color' => '#FCE4EC',
					],
					[
						'card_title' => esc_html__( 'Webmarketing', 'NOVA-addons' ),
						'card_description' => esc_html__( 'Stratégies de marketing digital pour augmenter votre visibilité.', 'NOVA-addons' ),
						'card_button_text' => esc_html__( 'En savoir plus', 'NOVA-addons' ),
						'card_background_color' => '#E8F5E9',
					],
					[
						'card_title' => esc_html__( 'Maintenance et Support', 'NOVA-addons' ),
						'card_description' => esc_html__( 'Un support technique réactif pour maintenir votre site en parfait état.', 'NOVA-addons' ),
						'card_button_text' => esc_html__( 'Contactez-nous', 'NOVA-addons' ),
						'card_background_color' => '#E3F2FD',
					],
				],
				'title_field' => '{{{ card_title.replace(/<[^>]*>/g, "") }}}',
			]
		);

		$this->end_controls_section();

		/**
		 * Style - Icon.
		 */
		$this->start_controls_section(
			'section_style_icon',
			[
				'label' => esc_html__( 'Icône', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label' => esc_html__( 'Taille', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [ 'min' => 20, 'max' => 120 ],
				],
				'default' => [
					'size' => 48,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__icon' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-stacking-card-2__icon img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#1a1a2e',
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .nova-stacking-card-2__icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_spacing',
			[
				'label' => esc_html__( 'Espacement sous l\'icône', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 60 ],
				],
				'default' => [
					'size' => 20,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__icon' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/**
		 * Style - Title.
		 */
		$this->start_controls_section(
			'section_style_title',
			[
				'label' => esc_html__( 'Titre', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .nova-stacking-card-2__title *',
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'title_spacing',
			[
				'label' => esc_html__( 'Espacement sous le titre', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 60 ],
				],
				'default' => [
					'size' => 16,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/**
		 * Style - Description.
		 */
		$this->start_controls_section(
			'section_style_description',
			[
				'label' => esc_html__( 'Description', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'description_typography',
				'selector' => '{{WRAPPER}} .nova-stacking-card-2__description',
			]
		);

		$this->add_control(
			'description_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__description' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'description_spacing',
			[
				'label' => esc_html__( 'Espacement sous la description', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 60 ],
				],
				'default' => [
					'size' => 24,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__description' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/**
		 * Style - Content Layout.
		 */
		$this->start_controls_section(
			'section_style_content_layout',
			[
				'label' => esc_html__( 'Layout Contenu', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'content_heading_main',
			[
				'label' => esc_html__( 'Zone Contenu Principale', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_responsive_control(
			'content_display',
			[
				'label' => esc_html__( 'Display', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'flex',
				'options' => [
					'flex' => 'Flex',
					'block' => 'Block',
					'grid' => 'Grid',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__content' => 'display: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_flex_direction',
			[
				'label' => esc_html__( 'Flex Direction', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'column',
				'options' => [
					'row' => 'Row',
					'row-reverse' => 'Row Reverse',
					'column' => 'Column',
					'column-reverse' => 'Column Reverse',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__content' => 'flex-direction: {{VALUE}};',
				],
				'condition' => [
					'content_display' => 'flex',
				],
			]
		);

		$this->add_responsive_control(
			'content_justify_content',
			[
				'label' => esc_html__( 'Justify Content', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'center',
				'options' => [
					'flex-start' => 'Start',
					'center' => 'Center',
					'flex-end' => 'End',
					'space-between' => 'Space Between',
					'space-around' => 'Space Around',
					'space-evenly' => 'Space Evenly',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__content' => 'justify-content: {{VALUE}};',
				],
				'condition' => [
					'content_display' => [ 'flex', 'grid' ],
				],
			]
		);

		$this->add_responsive_control(
			'content_align_items',
			[
				'label' => esc_html__( 'Align Items', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'flex-start',
				'options' => [
					'flex-start' => 'Start',
					'center' => 'Center',
					'flex-end' => 'End',
					'stretch' => 'Stretch',
					'baseline' => 'Baseline',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__content' => 'align-items: {{VALUE}};',
				],
				'condition' => [
					'content_display' => [ 'flex', 'grid' ],
				],
			]
		);

		$this->add_responsive_control(
			'content_flex_wrap',
			[
				'label' => esc_html__( 'Flex Wrap', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'nowrap',
				'options' => [
					'nowrap' => 'No Wrap',
					'wrap' => 'Wrap',
					'wrap-reverse' => 'Wrap Reverse',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__content' => 'flex-wrap: {{VALUE}};',
				],
				'condition' => [
					'content_display' => 'flex',
				],
			]
		);

		$this->add_responsive_control(
			'content_gap',
			[
				'label' => esc_html__( 'Gap', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 100 ],
					'em' => [ 'min' => 0, 'max' => 10 ],
					'rem' => [ 'min' => 0, 'max' => 10 ],
					'%' => [ 'min' => 0, 'max' => 100 ],
				],
				'default' => [
					'size' => 16,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__content' => 'gap: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'content_display' => [ 'flex', 'grid' ],
				],
			]
		);

		$this->add_responsive_control(
			'content_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'content_heading_2',
			[
				'label' => esc_html__( 'Zone Contenu Secondaire', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'content_2_display',
			[
				'label' => esc_html__( 'Display', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'flex',
				'options' => [
					'flex' => 'Flex',
					'block' => 'Block',
					'grid' => 'Grid',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__content_2' => 'display: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_2_flex_direction',
			[
				'label' => esc_html__( 'Flex Direction', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'column',
				'options' => [
					'row' => 'Row',
					'row-reverse' => 'Row Reverse',
					'column' => 'Column',
					'column-reverse' => 'Column Reverse',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__content_2' => 'flex-direction: {{VALUE}};',
				],
				'condition' => [
					'content_2_display' => 'flex',
				],
			]
		);

		$this->add_responsive_control(
			'content_2_justify_content',
			[
				'label' => esc_html__( 'Justify Content', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'flex-start',
				'options' => [
					'flex-start' => 'Start',
					'center' => 'Center',
					'flex-end' => 'End',
					'space-between' => 'Space Between',
					'space-around' => 'Space Around',
					'space-evenly' => 'Space Evenly',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__content_2' => 'justify-content: {{VALUE}};',
				],
				'condition' => [
					'content_2_display' => [ 'flex', 'grid' ],
				],
			]
		);

		$this->add_responsive_control(
			'content_2_align_items',
			[
				'label' => esc_html__( 'Align Items', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'flex-start',
				'options' => [
					'flex-start' => 'Start',
					'center' => 'Center',
					'flex-end' => 'End',
					'stretch' => 'Stretch',
					'baseline' => 'Baseline',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__content_2' => 'align-items: {{VALUE}};',
				],
				'condition' => [
					'content_2_display' => [ 'flex', 'grid' ],
				],
			]
		);

		$this->add_responsive_control(
			'content_2_flex_wrap',
			[
				'label' => esc_html__( 'Flex Wrap', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'nowrap',
				'options' => [
					'nowrap' => 'No Wrap',
					'wrap' => 'Wrap',
					'wrap-reverse' => 'Wrap Reverse',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__content_2' => 'flex-wrap: {{VALUE}};',
				],
				'condition' => [
					'content_2_display' => 'flex',
				],
			]
		);

		$this->add_responsive_control(
			'content_2_gap',
			[
				'label' => esc_html__( 'Gap', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 100 ],
					'em' => [ 'min' => 0, 'max' => 10 ],
					'rem' => [ 'min' => 0, 'max' => 10 ],
					'%' => [ 'min' => 0, 'max' => 100 ],
				],
				'default' => [
					'size' => 16,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__content_2' => 'gap: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'content_2_display' => [ 'flex', 'grid' ],
				],
			]
		);

		$this->add_responsive_control(
			'content_2_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__content_2' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/**
		 * Style - Button.
		 */
		$this->start_controls_section(
			'section_style_button',
			[
				'label' => esc_html__( 'Bouton', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'selector' => '{{WRAPPER}} .nova-stacking-card-2__button',
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => esc_html__( 'Normal', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_background_color',
			[
				'label' => esc_html__( 'Couleur de fond', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#1a1a2e',
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__button' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => esc_html__( 'Survol', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'button_text_color_hover',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_background_color_hover',
			[
				'label' => esc_html__( 'Couleur de fond', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__button:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_border_color_hover',
			[
				'label' => esc_html__( 'Couleur de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__button:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'button_box_shadow_hover',
				'label' => esc_html__( 'Ombre au survol', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-stacking-card-2__button:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'button_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'default' => [
					'top' => '14',
					'right' => '28',
					'bottom' => '14',
					'left' => '28',
					'unit' => 'px',
				],
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'button_border_radius',
			[
				'label' => esc_html__( 'Arrondi', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'default' => [
					'top' => '50',
					'right' => '50',
					'bottom' => '50',
					'left' => '50',
					'unit' => 'px',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'button_border',
				'label' => esc_html__( 'Bordure', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-stacking-card-2__button',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'button_box_shadow',
				'label' => esc_html__( 'Ombre', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-stacking-card-2__button',
			]
		);

		$this->add_control(
			'button_icon_heading',
			[
				'label' => esc_html__( 'Icône du bouton', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'button_icon_size',
			[
				'label' => esc_html__( 'Taille de l\'icône', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [ 'min' => 10, 'max' => 100 ],
					'em' => [ 'min' => 0.5, 'max' => 5 ],
				],
				'default' => [
					'size' => 42,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__button-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-stacking-card-2__button-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-stacking-card-2__button-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_icon_spacing',
			[
				'label' => esc_html__( 'Espacement icône', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 50 ],
					'em' => [ 'min' => 0, 'max' => 3 ],
				],
				'default' => [
					'size' => 12,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__button' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'button_icon_color',
			[
				'label' => esc_html__( 'Couleur de l\'icône', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__button-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .nova-stacking-card-2__button-icon svg' => 'fill: {{VALUE}};',
					'{{WRAPPER}} .nova-stacking-card-2__button-icon svg path' => 'stroke: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_icon_background',
			[
				'label' => esc_html__( 'Fond de l\'icône', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__button-icon' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .nova-stacking-card-2__button-icon svg rect:first-child' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_icon_border_radius',
			[
				'label' => esc_html__( 'Arrondi de l\'icône', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 100 ],
					'%' => [ 'min' => 0, 'max' => 50 ],
				],
				'default' => [
					'size' => 50,
					'unit' => '%',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__button-icon' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/**
		 * Style - Media (Image).
		 */
		$this->start_controls_section(
			'section_style_media',
			[
				'label' => esc_html__( 'Média (Image)', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'media_width',
			[
				'label' => esc_html__( 'Largeur', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw', 'custom' ],
				'range' => [
					'px' => [ 'min' => 50, 'max' => 800 ],
					'%' => [ 'min' => 10, 'max' => 100 ],
					'vw' => [ 'min' => 10, 'max' => 100 ],
				],
				'default' => [
					'unit' => '%',
					'size' => 100,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__image' => 'width: 100%; max-width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-stacking-card-2__media' => 'width: 100%; max-width: {{SIZE}}{{UNIT}};',

				],
			]
		);

		$this->add_responsive_control(
			'media_height',
			[
				'label' => esc_html__( 'Hauteur', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vh', 'custom' ],
				'range' => [
					'px' => [ 'min' => 50, 'max' => 800 ],
					'%' => [ 'min' => 10, 'max' => 100 ],
					'vh' => [ 'min' => 10, 'max' => 100 ],
				],
				'default' => [
					'unit' => 'custom',
					'size' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__image' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'media_aspect_ratio',
			[
				'label' => esc_html__( 'Ratio d\'aspect', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'' => esc_html__( 'Défaut', 'NOVA-addons' ),
					'1/1' => '1:1 (Carré)',
					'4/3' => '4:3',
					'3/2' => '3:2',
					'16/9' => '16:9',
					'21/9' => '21:9 (Cinéma)',
					'3/4' => '3:4 (Portrait)',
					'2/3' => '2:3 (Portrait)',
					'9/16' => '9:16 (Portrait)',
					'custom' => esc_html__( 'Personnalisé', 'NOVA-addons' ),
				],
				'default' => '',
				'selectors_dictionary' => [
					'custom' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__image' => 'aspect-ratio: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'media_aspect_ratio_custom',
			[
				'label' => esc_html__( 'Ratio personnalisé', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => '16/9',
				'description' => esc_html__( 'Entrez un ratio comme "16/9", "4/3" ou "1.5"', 'NOVA-addons' ),
				'condition' => [
					'media_aspect_ratio' => 'custom',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__image' => 'aspect-ratio: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'media_border_radius',
			[
				'label' => esc_html__( 'Arrondi', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default' => [
					'top' => '16',
					'right' => '16',
					'bottom' => '16',
					'left' => '16',
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__media' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .nova-stacking-card-2__image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'media_object_fit',
			[
				'label' => esc_html__( 'Ajustement de l\'image', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'cover' => esc_html__( 'Couvrir', 'NOVA-addons' ),
					'contain' => esc_html__( 'Contenir', 'NOVA-addons' ),
					'fill' => esc_html__( 'Remplir', 'NOVA-addons' ),
					'none' => esc_html__( 'Aucun', 'NOVA-addons' ),
				],
				'default' => 'cover',
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__image' => 'object-fit: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'media_object_position',
			[
				'label' => esc_html__( 'Position de l\'image', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'center center' => esc_html__( 'Centre', 'NOVA-addons' ),
					'top center' => esc_html__( 'Haut', 'NOVA-addons' ),
					'bottom center' => esc_html__( 'Bas', 'NOVA-addons' ),
					'left center' => esc_html__( 'Gauche', 'NOVA-addons' ),
					'right center' => esc_html__( 'Droite', 'NOVA-addons' ),
					'top left' => esc_html__( 'Haut gauche', 'NOVA-addons' ),
					'top right' => esc_html__( 'Haut droite', 'NOVA-addons' ),
					'bottom left' => esc_html__( 'Bas gauche', 'NOVA-addons' ),
					'bottom right' => esc_html__( 'Bas droite', 'NOVA-addons' ),
				],
				'default' => 'center center',
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2__image' => 'object-position: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		/**
		 * Style - Card Shadow.
		 */
		$this->start_controls_section(
			'section_style_card',
			[
				'label' => esc_html__( 'Carte', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'card_box_shadow',
				'selector' => '{{WRAPPER}} .nova-stacking-card-2',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'card_border',
				'selector' => '{{WRAPPER}} .nova-stacking-card-2',
			]
		);

		$this->end_controls_section();

		/**
		 * Animation settings.
		 */
		$this->start_controls_section(
			'section_animation',
			[
				'label' => esc_html__( 'Animation', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_ADVANCED,
			]
		);

		$this->add_control(
			'animation_scrub',
			[
				'label' => esc_html__( 'Fluidité du scroll (scrub)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0.1,
						'max' => 2,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 0.5,
				],
				'description' => esc_html__( 'Plus la valeur est basse, plus l\'animation est réactive.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'animation_scale_down',
			[
				'label' => esc_html__( 'Échelle de réduction', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0.8,
						'max' => 1,
						'step' => 0.01,
					],
				],
				'default' => [
					'size' => 0.92,
				],
				'description' => esc_html__( 'Échelle appliquée aux cartes précédentes lors du scroll.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'animation_blur',
			[
				'label' => esc_html__( 'Flou des cartes précédentes', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 10,
					],
				],
				'default' => [
					'size' => 4,
				],
				'description' => esc_html__( 'Intensité du flou sur les cartes passées.', 'NOVA-addons' ),
			]
		);

		$this->add_responsive_control(
			'card_spacing',
			[
				'label' => esc_html__( 'Espacement entre les cartes', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 100 ],
				],
				'default' => [
					'size' => 20,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'sticky_top',
			[
				'label' => esc_html__( 'Position sticky (top)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 200 ],
				],
				'default' => [
					'size' => 80,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-stacking-card-2' => '--nova-card-sticky-top: {{SIZE}}{{UNIT}};',
				],
				'description' => esc_html__( 'Distance du haut de l\'écran où les cartes se fixent.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'animation_debug_markers',
			[
				'label' => esc_html__( 'Afficher les marqueurs ScrollTrigger', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Get all NOVA Title widgets from the current page/document (safe version for register_controls).
	 *
	 * @return array Array of widget options [id => label]
	 */
	protected function get_nova_title_widgets_options_safe() {
		$options = [
			'' => esc_html__( '— Sélectionner —', 'NOVA-addons' ),
		];

		// Early return if Elementor is not fully loaded
		if ( ! class_exists( '\Elementor\Plugin' ) || ! \Elementor\Plugin::$instance ) {
			return $options;
		}

		try {
			// Get current document/post ID
			$post_id = get_the_ID();
			
			// Try multiple methods to get post ID
			if ( ! $post_id ) {
				// Method 1: From Elementor editor
				if ( isset( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$post_id = intval( $_GET['post'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				}
			}

			if ( ! $post_id ) {
				// Method 2: From Elementor preview/editor
				if ( isset( $_REQUEST['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$post_id = intval( $_REQUEST['elementor-preview'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				}
			}

			if ( ! $post_id ) {
				// Method 3: From global $post
				global $post;
				if ( isset( $post->ID ) ) {
					$post_id = $post->ID;
				}
			}

			if ( ! $post_id ) {
				return $options;
			}

			// Check if documents manager is available
			if ( ! \Elementor\Plugin::$instance->documents ) {
				return $options;
			}

			// Get Elementor document (use false to avoid errors if document doesn't exist)
			$document = \Elementor\Plugin::$instance->documents->get( $post_id, false );
			if ( ! $document || ! $document->is_built_with_elementor() ) {
				return $options;
			}

			// Get all elements from the document
			$elements_data = $document->get_elements_data();
			if ( empty( $elements_data ) || ! is_array( $elements_data ) ) {
				return $options;
			}

			// Recursively find all NOVA Title widgets
			$this->find_nova_title_widgets( $elements_data, $options );

		} catch ( \Exception $e ) {
			// Silently fail and return default options
			// This prevents errors during widget registration
			return $options;
		}

		return $options;
	}

	/**
	 * Get all NOVA Title widgets from the current page/document.
	 *
	 * @return array Array of widget options [id => label]
	 */
	protected function get_nova_title_widgets_options() {
		return $this->get_nova_title_widgets_options_safe();
	}

	/**
	 * Recursively find NOVA Title widgets in elements data.
	 *
	 * @param array $elements Elements data array.
	 * @param array &$options Options array to populate.
	 * @return void
	 */
	protected function find_nova_title_widgets( $elements, &$options ) {
		if ( ! is_array( $elements ) ) {
			return;
		}

		foreach ( $elements as $element ) {
			// Check if this is a widget
			if ( isset( $element['widgetType'] ) && 'nova-title' === $element['widgetType'] ) {
				// Get widget ID
				$widget_id = isset( $element['id'] ) ? $element['id'] : '';
				if ( empty( $widget_id ) ) {
					continue;
				}

				// Try to get a label from widget settings
				$label = esc_html__( 'NOVA Title', 'NOVA-addons' ) . ' #' . substr( $widget_id, 0, 8 );
				
				// Try to get text_1 or text_2 for a better label
				if ( isset( $element['settings']['text_1'] ) && ! empty( $element['settings']['text_1'] ) ) {
					$text_preview = wp_strip_all_tags( $element['settings']['text_1'] );
					$text_preview = mb_substr( $text_preview, 0, 50 );
					if ( ! empty( $text_preview ) ) {
						$label = $text_preview . '...';
					}
				} elseif ( isset( $element['settings']['text_2'] ) && ! empty( $element['settings']['text_2'] ) ) {
					$text_preview = wp_strip_all_tags( $element['settings']['text_2'] );
					$text_preview = mb_substr( $text_preview, 0, 50 );
					if ( ! empty( $text_preview ) ) {
						$label = $text_preview . '...';
					}
				}

				$options[ $widget_id ] = $label;
			}

			// Recursively search in elements (for columns, sections, etc.)
			if ( isset( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$this->find_nova_title_widgets( $element['elements'], $options );
			}
		}
	}

	/**
	 * Render NOVA Title widget by ID.
	 *
	 * @param string $widget_id Widget ID.
	 * @return string Rendered HTML or empty string.
	 */
	protected function render_nova_title_widget( $widget_id ) {
		if ( empty( $widget_id ) ) {
			return '';
		}

		// Get current document/post ID
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return '';
		}

		// Get Elementor document
		$document = Plugin::$instance->documents->get( $post_id );
		if ( ! $document || ! $document->is_built_with_elementor() ) {
			return '';
		}

		// Get all elements from the document
		$elements_data = $document->get_elements_data();
		if ( empty( $elements_data ) ) {
			return '';
		}

		// Find the widget by ID
		$widget_data = $this->find_widget_by_id( $elements_data, $widget_id );
		if ( empty( $widget_data ) ) {
			return '';
		}

		// Create widget instance and render
		try {
			$widget_instance = Plugin::$instance->elements_manager->create_element_instance( $widget_data );
			if ( ! $widget_instance ) {
				return '';
			}

			ob_start();
			$widget_instance->render_content();
			$html = ob_get_clean();

			return $html;
		} catch ( \Exception $e ) {
			return '';
		}
	}

	/**
	 * Find widget data by ID recursively.
	 *
	 * @param array $elements Elements data array.
	 * @param string $widget_id Widget ID to find.
	 * @return array|false Widget data or false if not found.
	 */
	protected function find_widget_by_id( $elements, $widget_id ) {
		if ( ! is_array( $elements ) ) {
			return false;
		}

		foreach ( $elements as $element ) {
			if ( isset( $element['id'] ) && $element['id'] === $widget_id ) {
				return $element;
			}

			// Recursively search in elements
			if ( isset( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$found = $this->find_widget_by_id( $element['elements'], $widget_id );
				if ( false !== $found ) {
					return $found;
				}
			}
		}

		return false;
	}

	/**
	 * Render widget output.
	 *
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$cards = isset( $settings['cards'] ) && is_array( $settings['cards'] ) ? $settings['cards'] : [];
		if ( empty( $cards ) ) {
			return;
		}

		$wrapper_id = 'nova-stacking-cards-2-' . wp_unique_id();

		// Animation settings
		$scrub     = isset( $settings['animation_scrub']['size'] ) ? (float) $settings['animation_scrub']['size'] : 1.2;
		$debug     = isset( $settings['animation_debug_markers'] ) && 'yes' === $settings['animation_debug_markers'];
		$alternate = isset( $settings['alternate_layout'] ) && 'yes' === $settings['alternate_layout'];

		// Check if first card should be replaced with NOVA Title widget
		$use_nova_title_for_first_card = ! empty( $settings['use_nova_title_for_first_card'] ) && 'yes' === $settings['use_nova_title_for_first_card'];
		$selected_nova_title_widget_id = ! empty( $settings['selected_nova_title_widget_id'] ) ? $settings['selected_nova_title_widget_id'] : '';
		$nova_title_html = '';

		if ( $use_nova_title_for_first_card && ! empty( $selected_nova_title_widget_id ) ) {
			$nova_title_html = $this->render_nova_title_widget( $selected_nova_title_widget_id );
		}

		// Visual card count (used for data-attribute only)
		$visual_cards_count = count( $cards );
		if ( $use_nova_title_for_first_card && ! empty( $nova_title_html ) ) {
			$visual_cards_count++;
		}

		// Stacked offset (stacking cards with vertical offset and vertical centering)
		$stacked = ! empty( $settings['stacked_offset_enable'] ) && 'yes' === $settings['stacked_offset_enable'];
		$stack_offset_size   = $stacked && isset( $settings['stacked_offset']['size'] ) ? (float) $settings['stacked_offset']['size'] : 20;
		$stack_offset_unit   = $stacked && isset( $settings['stacked_offset']['unit'] ) ? $settings['stacked_offset']['unit'] : 'px';
		// Valeurs responsive (suffixes Elementor pour repeater/responsive controls)
		$stack_offset_size_t = $stacked && isset( $settings['stacked_offset_tablet']['size'] ) && $settings['stacked_offset_tablet']['size'] !== '' ? (float) $settings['stacked_offset_tablet']['size'] : $stack_offset_size;
		$stack_offset_unit_t = $stacked && isset( $settings['stacked_offset_tablet']['unit'] ) && $settings['stacked_offset_tablet']['unit'] !== '' ? $settings['stacked_offset_tablet']['unit'] : $stack_offset_unit;
		$stack_offset_size_m = $stacked && isset( $settings['stacked_offset_mobile']['size'] ) && $settings['stacked_offset_mobile']['size'] !== '' ? (float) $settings['stacked_offset_mobile']['size'] : $stack_offset_size;
		$stack_offset_unit_m = $stacked && isset( $settings['stacked_offset_mobile']['unit'] ) && $settings['stacked_offset_mobile']['unit'] !== '' ? $settings['stacked_offset_mobile']['unit'] : $stack_offset_unit;

		// Sticky top — position finale des cartes lors du scroll (desktop/tablet/mobile)
		$sticky_top_d = isset( $settings['sticky_top']['size'] ) && $settings['sticky_top']['size'] !== '' ? (float) $settings['sticky_top']['size'] : 80;
		$sticky_top_t = isset( $settings['sticky_top_tablet']['size'] ) && $settings['sticky_top_tablet']['size'] !== '' ? (float) $settings['sticky_top_tablet']['size'] : $sticky_top_d;
		$sticky_top_m = isset( $settings['sticky_top_mobile']['size'] ) && $settings['sticky_top_mobile']['size'] !== '' ? (float) $settings['sticky_top_mobile']['size'] : $sticky_top_d;

		$holder_stacked_style = '';
		if ( $stacked && $visual_cards_count > 0 ) {
			$holder_stacked_style = sprintf(
				' style="--stack-offset-d: %1$s%2$s; --stack-offset-t: %3$s%4$s; --stack-offset-m: %5$s%6$s; --stack-cards-count: %7$d;"',
				esc_attr( $stack_offset_size ),
				esc_attr( $stack_offset_unit ),
				esc_attr( $stack_offset_size_t ),
				esc_attr( $stack_offset_unit_t ),
				esc_attr( $stack_offset_size_m ),
				esc_attr( $stack_offset_unit_m ),
				(int) $visual_cards_count
			);
		}

		?>
		<?php if ( $use_nova_title_for_first_card && ! empty( $selected_nova_title_widget_id ) ) : ?>
			<style>
				/* Hide the selected NOVA Title widget */
				.elementor-element-<?php echo esc_attr( $selected_nova_title_widget_id ); ?>,
				.elementor-element[data-id="<?php echo esc_attr( $selected_nova_title_widget_id ); ?>"] {
					display: none !important;
					visibility: hidden !important;
					opacity: 0 !important;
					height: 0 !important;
					overflow: hidden !important;
				}
			</style>
		<?php endif; ?>
		<div class="nova-stacking-cards-2-wrapper" id="<?php echo esc_attr( $wrapper_id ); ?>">
			<div
				class="nova-stacking-cards-2<?php echo $stacked ? ' nova-stacking-cards-2--stacked' : ''; ?>"
				data-card-count="<?php echo esc_attr( $visual_cards_count ); ?>"
				data-scrub="<?php echo esc_attr( $scrub ); ?>"
				data-step-duration="0.9"
				data-scroll-multiplier="1.1"
				data-debug="<?php echo esc_attr( $debug ? '1' : '0' ); ?>"
				data-stacked="<?php echo esc_attr( $stacked ? '1' : '0' ); ?>"
				data-stack-offset="<?php echo esc_attr( $stacked ? $stack_offset_size : 0 ); ?>"
				data-stack-offset-unit="<?php echo esc_attr( $stacked ? $stack_offset_unit : 'px' ); ?>"
				data-stack-offset-tablet="<?php echo esc_attr( $stacked ? $stack_offset_size_t : 0 ); ?>"
				data-stack-offset-unit-tablet="<?php echo esc_attr( $stacked ? $stack_offset_unit_t : 'px' ); ?>"
				data-stack-offset-mobile="<?php echo esc_attr( $stacked ? $stack_offset_size_m : 0 ); ?>"
				data-stack-offset-unit-mobile="<?php echo esc_attr( $stacked ? $stack_offset_unit_m : 'px' ); ?>"
				data-sticky-top="<?php echo esc_attr( $sticky_top_d ); ?>"
				data-sticky-top-tablet="<?php echo esc_attr( $sticky_top_t ); ?>"
				data-sticky-top-mobile="<?php echo esc_attr( $sticky_top_m ); ?>"
			>
				<div class="nova-stacking-cards-2__holder"<?php echo $holder_stacked_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php
				// If option enabled, inject an extra first card with NOVA Title content,
				// but keep all original cards after it.
				if ( $use_nova_title_for_first_card && ! empty( $nova_title_html ) ) {
					$nova_card_class = [
						'nova-stacking-card-2',
						'nova-stacking-card-2--index-1',
						'nova-stacking-card-2--nova-title-replaced',
						'is-active',
					];
					$nova_card_style = $stacked ? ' style="top: 0;"' : '';
					?>
					<article
						class="<?php echo esc_attr( implode( ' ', $nova_card_class ) ); ?>"
						data-card-index="0"<?php echo $nova_card_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					>
						<div class="nova-stacking-card-2__inner nova-stacking-card-2__inner--nova-title">
							<div class="nova-stacking-card-2__nova-title-content">
								<!-- Wrap in elementor-widget-nova-title to ensure styles apply -->
								<div class="elementor-widget elementor-widget-nova-title" data-id="<?php echo esc_attr( $selected_nova_title_widget_id ); ?>">
									<div class="elementor-widget-container">
										<?php echo $nova_title_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</div>
								</div>
							</div>
						</div>
					</article>
					<?php
					// Add script to reinitialize NOVA Title widget after injection
					?>
					<script>
					(function($) {
						'use strict';
						function initInjectedNovaTitle() {
							// Find the injected NOVA Title widget
							var $wrapper = $('#<?php echo esc_js( $wrapper_id ); ?>');
							var $injectedWidget = $wrapper.find('.elementor-widget-nova-title[data-id="<?php echo esc_js( $selected_nova_title_widget_id ); ?>"] .nova-title-widget');
							
							if ($injectedWidget.length > 0 && typeof window.NOVATitle !== 'undefined') {
								// Reset initialization flag and reinitialize
								$injectedWidget.data('nova-title-initialized', false);
								window.NOVATitle.initInstance($injectedWidget);
								
								// Also try to initialize styled words animations if needed
								if (window.NOVATitle.initStyledWordsAnimations) {
									var $widgetContainer = $injectedWidget.closest('.elementor-widget-nova-title');
									if ($widgetContainer.length) {
										window.NOVATitle.initStyledWordsAnimations($widgetContainer);
									}
								}
							}
						}
						
						// Try multiple times to ensure scripts are loaded
						$(document).ready(function() {
							// Try immediately
							setTimeout(initInjectedNovaTitle, 100);
							// Try after a delay
							setTimeout(initInjectedNovaTitle, 500);
							// Try after window load
							$(window).on('load', function() {
								setTimeout(initInjectedNovaTitle, 200);
							});
						});
						
						// Also try when Elementor frontend is ready
						if (typeof elementorFrontend !== 'undefined') {
							$(window).on('elementor/frontend/init', function() {
								setTimeout(initInjectedNovaTitle, 200);
							});
						}
					})(jQuery);
					</script>
					<?php
				}

				foreach ( $cards as $index => $card ) :
					// Normal card rendering for all cards
					$card_icon_type = isset( $card['card_icon_type'] ) ? $card['card_icon_type'] : 'icon';

					$card_class = [
						'nova-stacking-card-2',
						'nova-stacking-card-2--index-' . ( $index + 1 ),
					];
					if ( ! empty( $card['_id'] ) ) {
						$card_class[] = 'elementor-repeater-item-' . esc_attr( $card['_id'] );
					}
					// Only make the first repeater card active if we do NOT have a NOVA Title card injected.
					if ( 0 === $index && ! ( $use_nova_title_for_first_card && ! empty( $nova_title_html ) ) ) {
						$card_class[] = 'is-active';
					}
					if ( $alternate && ( $index % 2 === 1 ) ) {
						$card_class[] = 'nova-stacking-card-2--reversed';
					}

					// Stacked offset: géré par GSAP via data-stack-offset (plus de top CSS inline)
					$stack_index    = ( $use_nova_title_for_first_card && ! empty( $nova_title_html ) ) ? $index + 1 : $index;
					$card_top_style = ''; // GSAP gère le décalage via y: index * stackOffset

					// Image HTML
					$image_html = '';
					if ( ! empty( $card['card_media']['id'] ) ) {
						$image_html = wp_get_attachment_image(
							intval( $card['card_media']['id'] ),
							'large',
							false,
							[
								'class' => 'nova-stacking-card-2__image',
								'loading' => $index === 0 ? 'eager' : 'lazy',
							]
						);
					} elseif ( ! empty( $card['card_media']['url'] ) ) {
						$image_url = esc_url( $card['card_media']['url'] );
						$image_html = '<img class="nova-stacking-card-2__image" src="' . $image_url . '" alt="" loading="' . ( 0 === $index ? 'eager' : 'lazy' ) . '"/>';
					}

					// Icon HTML
					$icon_html = '';
					if ( 'icon' === $card_icon_type && ! empty( $card['card_icon']['value'] ) ) {
						$icon_value = $card['card_icon']['value'];
						if ( is_array( $icon_value ) && ! empty( $icon_value['url'] ) ) {
							$icon_html = sprintf( '<img src="%1$s" alt="" />', esc_url( $icon_value['url'] ) );
						} else {
							ob_start();
							Icons_Manager::render_icon( $card['card_icon'], [ 'aria-hidden' => 'true' ] );
							$icon_html = ob_get_clean();
						}
					} elseif ( 'image' === $card_icon_type && ! empty( $card['card_icon_image']['url'] ) ) {
						$icon_html = sprintf( '<img src="%1$s" alt="" />', esc_url( $card['card_icon_image']['url'] ) );
					}

					// Button
					$button_text = isset( $card['card_button_text'] ) ? $card['card_button_text'] : '';
					$button_link = isset( $card['card_button_link'] ) ? $card['card_button_link'] : [];
					$button_icon = isset( $card['card_button_icon'] ) ? $card['card_button_icon'] : [];
					$button_icon_position = isset( $card['card_button_icon_position'] ) ? $card['card_button_icon_position'] : 'before';
					$button_key  = 'stacking-card-2-button-' . $index;
					$has_button  = ! empty( $button_text ) && ! empty( $button_link['url'] );

					// Button icon HTML
					$button_icon_html = '';
					if ( ! empty( $button_icon['value'] ) ) {
						$icon_value = $button_icon['value'];
						if ( is_array( $icon_value ) && ! empty( $icon_value['url'] ) ) {
							$button_icon_html = sprintf( '<img src="%1$s" alt="" />', esc_url( $icon_value['url'] ) );
						} else {
							ob_start();
							Icons_Manager::render_icon( $button_icon, [ 'aria-hidden' => 'true' ] );
							$button_icon_html = ob_get_clean();
						}
					}

					if ( $has_button ) {
						$this->add_render_attribute( $button_key, 'class', 'nova-stacking-card-2__button' );
						$this->add_link_attributes( $button_key, $button_link );
					}
					?>
					<article
						class="<?php echo esc_attr( implode( ' ', $card_class ) ); ?>"
						data-card-index="<?php echo esc_attr( $index ); ?>"<?php echo $card_top_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					>
						<div class="nova-stacking-card-2__inner">
							<div class="nova-stacking-card-2__content">
								<?php if ( ! empty( $icon_html ) ) : ?>
									<div class="nova-stacking-card-2__icon">
										<?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</div>
								<?php endif; ?>

								<?php if ( ! empty( $card['card_title'] ) ) : ?>
									<div class="nova-stacking-card-2__title">
										<?php echo wp_kses_post( $card['card_title'] ); ?>
									</div>
								<?php endif; ?>

								<div class="nova-stacking-card-2__content_2">
									<?php if ( ! empty( $card['card_description'] ) ) : ?>
										<div class="nova-stacking-card-2__description">
											<?php echo wp_kses_post( $card['card_description'] ); ?>
										</div>
									<?php endif; ?>

									<?php if ( $has_button ) : ?>
										<a <?php echo $this->get_render_attribute_string( $button_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
											<?php if ( ! empty( $button_icon_html ) && 'before' === $button_icon_position ) : ?>
												<span class="nova-stacking-card-2__button-icon icon-before">
													<?php echo $button_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
												</span>
											<?php endif; ?>
											<span class="nova-stacking-card-2__button-text"><?php echo esc_html( $button_text ); ?></span>
											<?php if ( ! empty( $button_icon_html ) && 'after' === $button_icon_position ) : ?>
												<span class="nova-stacking-card-2__button-icon icon-after">
													<?php echo $button_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
												</span>
											<?php endif; ?>
										</a>
									<?php endif; ?>
								</div>
								
							</div>

							<div class="nova-stacking-card-2__media">
								<?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
						</div>
					</article>
					<?php
				endforeach;
				?>
				</div><!-- /.nova-stacking-cards-2__holder -->
			</div>
		</div>
		<?php
	}
}
