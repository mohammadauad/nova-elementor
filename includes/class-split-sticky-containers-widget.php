<?php
namespace Nova_Addons_Elementor;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Widget NOVA Split Sticky Containers.
 *
 * Mise en page à deux colonnes basée sur des modèles Elementor,
 * avec possibilité de rendre l'une des deux colonnes sticky
 * pendant le scroll (par rapport à l'autre).
 *
 * Chaque colonne affiche un modèle Elementor complet, ce qui permet
 * d'y placer n'importe quels widgets / conteneurs.
 */
class Split_Sticky_Containers_Widget extends Widget_Base {

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'nova-split-sticky-containers';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'NOVA Split Sticky (2 conteneurs)', 'NOVA-addons' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-columns';
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
	 * Style dependencies.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return [ 'nova-split-sticky-containers-style' ];
	}

	/**
	 * Register widget controls.
	 *
	 * @return void
	 */
	protected function register_controls() {

		/**
		 * Content templates.
		 */
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Contenu', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'left_template',
			[
				'label' => esc_html__( 'Modèle colonne gauche', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => $this->get_elementor_templates_options(),
				'default' => '',
				'description' => esc_html__( 'Choisissez un modèle Elementor (section / page) qui sera affiché dans la colonne gauche.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'right_template',
			[
				'label' => esc_html__( 'Modèle colonne droite', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => $this->get_elementor_templates_options(),
				'default' => '',
				'description' => esc_html__( 'Choisissez un modèle Elementor (section / page) qui sera affiché dans la colonne droite.', 'NOVA-addons' ),
			]
		);

		$this->end_controls_section();

		/**
		 * Layout & sticky behavior.
		 */
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Mise en page & sticky', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'sticky_side',
			[
				'label' => esc_html__( 'Colonne sticky', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Gauche', 'NOVA-addons' ),
						'icon'  => 'eicon-h-align-left',
					],
					'right' => [
						'title' => esc_html__( 'Droite', 'NOVA-addons' ),
						'icon'  => 'eicon-h-align-right',
					],
					'none' => [
						'title' => esc_html__( 'Aucune', 'NOVA-addons' ),
						'icon'  => 'eicon-ban',
					],
				],
				'default' => 'left',
				'description' => esc_html__( 'Choisissez quelle colonne doit rester fixe (sticky) pendant le scroll par rapport à l’autre.', 'NOVA-addons' ),
			]
		);

		$this->add_responsive_control(
			'sticky_offset',
			[
				'label' => esc_html__( 'Décalage sticky (top)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
				],
				'default' => [
					'size' => 80,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-split-sticky-containers' => '--nova-split-sticky-top: {{SIZE}}{{UNIT}};',
				],
				'description' => esc_html__( 'Distance depuis le haut de la fenêtre (par exemple sous le header fixe).', 'NOVA-addons' ),
			]
		);

		$this->add_responsive_control(
			'columns_gap',
			[
				'label' => esc_html__( 'Espacement entre colonnes', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 120,
					],
				],
				'default' => [
					'size' => 40,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-split-sticky-containers' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Get Elementor templates (sections / pages) as options.
	 *
	 * @return array
	 */
	protected function get_elementor_templates_options() {
		$options = [
			'' => esc_html__( '— Aucun —', 'NOVA-addons' ),
		];

		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return $options;
		}

		$templates = Plugin::$instance->templates_manager->get_source( 'local' )->get_items();
		if ( ! empty( $templates ) && is_array( $templates ) ) {
			foreach ( $templates as $template ) {
				if ( empty( $template['template_id'] ) ) {
					continue;
				}
				$options[ $template['template_id'] ] = $template['title'] . ' (ID ' . $template['template_id'] . ')';
			}
		}

		return $options;
	}

	/**
	 * Render widget.
	 *
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$left_template_id  = ! empty( $settings['left_template'] ) ? intval( $settings['left_template'] ) : 0;
		$right_template_id = ! empty( $settings['right_template'] ) ? intval( $settings['right_template'] ) : 0;

		if ( ! $left_template_id && ! $right_template_id ) {
			return;
		}

		$sticky_side = isset( $settings['sticky_side'] ) ? $settings['sticky_side'] : 'left';
		if ( ! in_array( $sticky_side, [ 'left', 'right', 'none' ], true ) ) {
			$sticky_side = 'left';
		}

		$wrapper_classes = [
			'nova-split-sticky-containers',
			'nova-split-sticky-containers--sticky-' . $sticky_side,
		];

		?>
		<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
			<div class="nova-split-sticky-containers__column nova-split-sticky-containers__column--left">
				<?php
				if ( $left_template_id ) {
					echo Plugin::$instance->frontend->get_builder_content_for_display( $left_template_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
			</div>
			<div class="nova-split-sticky-containers__column nova-split-sticky-containers__column--right">
				<?php
				if ( $right_template_id ) {
					echo Plugin::$instance->frontend->get_builder_content_for_display( $right_template_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
			</div>
		</div>
		<?php
	}
}

