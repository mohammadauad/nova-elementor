<?php
/**
 * NOVA Filters Widget
 *
 * Builds a fully configurable GET form (filter / search) with multiple field
 * types, labels, layout, validation and pro styling controls.
 *
 * @package Nova Addons
 */

namespace Nova_Addons_Elementor;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Repeater;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Group_Control_Background;
use \Elementor\Icons_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Filters_Widget extends Widget_Base {

	public function get_name() {
		return 'nova-filters';
	}

	public function get_title() {
		return esc_html__( 'NOVA Filters', NOVA_ADDONS_TEXT_DOMAIN );
	}

	public function get_icon() {
		return 'eicon-filter';
	}

	public function get_categories() {
		return [ 'nova-addons' ];
	}

	public function get_keywords() {
		return [ 'filter', 'filters', 'form', 'search', 'get', 'query', 'nova' ];
	}

	public function get_style_depends() {
		return [ 'nova-filters-style' ];
	}

	public function get_script_depends() {
		return [ 'nova-filters-script' ];
	}

	/* ---------------------------------------------------------------------
	 * CONTROLS
	 * ------------------------------------------------------------------ */

	protected function register_controls() {
		$this->register_form_section();
		$this->register_fields_section();
		$this->register_submit_section();

		$this->register_container_style();
		$this->register_label_style();
		$this->register_input_style();
		$this->register_help_style();
		$this->register_submit_style();
		$this->register_reset_style();
	}

	/* ----- CONTENT : Form Settings ----- */
	private function register_form_section() {
		$this->start_controls_section(
			'section_form_settings',
			[
				'label' => esc_html__( 'Form Settings', NOVA_ADDONS_TEXT_DOMAIN ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'form_action',
			[
				'label'       => esc_html__( 'Form action URL', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'        => Controls_Manager::URL,
				'placeholder' => esc_url( home_url( '/' ) ),
				'description' => esc_html__( 'Leave empty to submit to the current page.', NOVA_ADDONS_TEXT_DOMAIN ),
				'show_external' => false,
				'options'     => [ 'url_params' => false, 'nofollow' => false, 'custom_attributes' => false ],
			]
		);

		$this->add_control(
			'form_id_attr',
			[
				'label'       => esc_html__( 'Form HTML ID', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'        => Controls_Manager::TEXT,
				'description' => esc_html__( 'Optional. Used in CSS / JS selectors.', NOVA_ADDONS_TEXT_DOMAIN ),
			]
		);

		$this->add_control(
			'preserve_query',
			[
				'label'        => esc_html__( 'Preserve other query params', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'         => Controls_Manager::SWITCHER,
				'description'  => esc_html__( 'Append existing URL params as hidden inputs (so they survive the GET submit).', NOVA_ADDONS_TEXT_DOMAIN ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'submit_on_change',
			[
				'label'        => esc_html__( 'Auto-submit on change', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'         => Controls_Manager::SWITCHER,
				'description'  => esc_html__( 'Submit the form automatically when a select / radio / checkbox changes.', NOVA_ADDONS_TEXT_DOMAIN ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label'   => esc_html__( 'Columns', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'    => Controls_Manager::SELECT,
				'default' => '12',
				'options' => [
					'1'  => '1',
					'2'  => '2',
					'3'  => '3',
					'4'  => '4',
					'6'  => '6',
					'12' => esc_html__( 'Auto (grid 12 / field width)', NOVA_ADDONS_TEXT_DOMAIN ),
				],
				'selectors' => [
					'{{WRAPPER}} .nova-filters-fields' => '--nova-filters-cols: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'gap',
			[
				'label'      => esc_html__( 'Gap between fields', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'rem' ],
				'range'      => [
					'px'  => [ 'min' => 0, 'max' => 80 ],
					'rem' => [ 'min' => 0, 'max' => 5, 'step' => 0.1 ],
				],
				'default'    => [ 'unit' => 'px', 'size' => 16 ],
				'selectors'  => [
					'{{WRAPPER}} .nova-filters-fields' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/* ----- CONTENT : Fields ----- */
	private function register_fields_section() {
		$this->start_controls_section(
			'section_fields',
			[
				'label' => esc_html__( 'Fields', NOVA_ADDONS_TEXT_DOMAIN ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'field_type',
			[
				'label'   => esc_html__( 'Field type', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'text',
				'options' => [
					'text'           => esc_html__( 'Text', NOVA_ADDONS_TEXT_DOMAIN ),
					'search'         => esc_html__( 'Search', NOVA_ADDONS_TEXT_DOMAIN ),
					'email'          => esc_html__( 'Email', NOVA_ADDONS_TEXT_DOMAIN ),
					'tel'            => esc_html__( 'Tel', NOVA_ADDONS_TEXT_DOMAIN ),
					'url'            => esc_html__( 'URL', NOVA_ADDONS_TEXT_DOMAIN ),
					'number'         => esc_html__( 'Number', NOVA_ADDONS_TEXT_DOMAIN ),
					'range'          => esc_html__( 'Range', NOVA_ADDONS_TEXT_DOMAIN ),
					'date'           => esc_html__( 'Date', NOVA_ADDONS_TEXT_DOMAIN ),
					'time'           => esc_html__( 'Time', NOVA_ADDONS_TEXT_DOMAIN ),
					'datetime-local' => esc_html__( 'Date + time', NOVA_ADDONS_TEXT_DOMAIN ),
					'textarea'       => esc_html__( 'Textarea', NOVA_ADDONS_TEXT_DOMAIN ),
					'select'         => esc_html__( 'Select (dropdown)', NOVA_ADDONS_TEXT_DOMAIN ),
					'multiselect'    => esc_html__( 'Multi-select', NOVA_ADDONS_TEXT_DOMAIN ),
					'radio'          => esc_html__( 'Radio group', NOVA_ADDONS_TEXT_DOMAIN ),
					'checkbox-group' => esc_html__( 'Checkbox group', NOVA_ADDONS_TEXT_DOMAIN ),
					'checkbox'       => esc_html__( 'Single checkbox', NOVA_ADDONS_TEXT_DOMAIN ),
					'hidden'         => esc_html__( 'Hidden', NOVA_ADDONS_TEXT_DOMAIN ),
				],
			]
		);

		$repeater->add_control(
			'field_name',
			[
				'label'       => esc_html__( 'Field name (URL param)', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'q',
				'description' => esc_html__( 'Used as the GET key in the URL (e.g. ?q=value).', NOVA_ADDONS_TEXT_DOMAIN ),
				'placeholder' => 'name',
			]
		);

		$repeater->add_control(
			'field_label',
			[
				'label'   => esc_html__( 'Label', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Field label', NOVA_ADDONS_TEXT_DOMAIN ),
			]
		);

		$repeater->add_control(
			'field_placeholder',
			[
				'label'     => esc_html__( 'Placeholder', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => Controls_Manager::TEXT,
				'condition' => [
					'field_type!' => [ 'select', 'multiselect', 'radio', 'checkbox-group', 'checkbox', 'hidden' ],
				],
			]
		);

		$repeater->add_control(
			'field_default',
			[
				'label'       => esc_html__( 'Default value', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'        => Controls_Manager::TEXT,
				'description' => esc_html__( 'Pre-filled value if the URL does not contain this param yet.', NOVA_ADDONS_TEXT_DOMAIN ),
				'condition'   => [
					'field_type!' => [ 'checkbox', 'checkbox-group', 'multiselect' ],
				],
			]
		);

		$repeater->add_control(
			'field_help',
			[
				'label' => esc_html__( 'Help text', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'  => Controls_Manager::TEXTAREA,
				'rows'  => 2,
			]
		);

		$repeater->add_control(
			'field_required',
			[
				'label'        => esc_html__( 'Required', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'condition'    => [
					'field_type!' => [ 'hidden' ],
				],
			]
		);

		$repeater->add_responsive_control(
			'field_width',
			[
				'label'   => esc_html__( 'Field width (1-12 grid units)', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'    => Controls_Manager::SELECT,
				'default' => '12',
				'options' => [
					'12' => esc_html__( 'Full (12/12)', NOVA_ADDONS_TEXT_DOMAIN ),
					'6'  => esc_html__( 'Half (6/12)', NOVA_ADDONS_TEXT_DOMAIN ),
					'4'  => esc_html__( 'Third (4/12)', NOVA_ADDONS_TEXT_DOMAIN ),
					'3'  => esc_html__( 'Quarter (3/12)', NOVA_ADDONS_TEXT_DOMAIN ),
					'8'  => esc_html__( 'Two-thirds (8/12)', NOVA_ADDONS_TEXT_DOMAIN ),
					'9'  => esc_html__( 'Three-quarters (9/12)', NOVA_ADDONS_TEXT_DOMAIN ),
				],
			]
		);

		$repeater->add_control(
			'field_options',
			[
				'label'       => esc_html__( 'Options', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 6,
				'description' => esc_html__( 'One option per line. Format: value|Label (the part after the pipe is shown to the user).', NOVA_ADDONS_TEXT_DOMAIN ),
				'placeholder' => "all|All\nnew|Newest\nold|Oldest",
				'default'     => '',
				'condition'   => [
					'field_type' => [ 'select', 'multiselect', 'radio', 'checkbox-group' ],
				],
			]
		);

		$repeater->add_control(
			'field_min',
			[
				'label'     => esc_html__( 'Min', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => Controls_Manager::NUMBER,
				'condition' => [ 'field_type' => [ 'number', 'range' ] ],
			]
		);

		$repeater->add_control(
			'field_max',
			[
				'label'     => esc_html__( 'Max', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => Controls_Manager::NUMBER,
				'condition' => [ 'field_type' => [ 'number', 'range' ] ],
			]
		);

		$repeater->add_control(
			'field_step',
			[
				'label'     => esc_html__( 'Step', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => Controls_Manager::NUMBER,
				'condition' => [ 'field_type' => [ 'number', 'range' ] ],
			]
		);

		$repeater->add_control(
			'field_rows',
			[
				'label'     => esc_html__( 'Rows', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 4,
				'condition' => [ 'field_type' => 'textarea' ],
			]
		);

		$repeater->add_control(
			'field_checkbox_label',
			[
				'label'     => esc_html__( 'Checkbox text', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => Controls_Manager::TEXT,
				'condition' => [ 'field_type' => 'checkbox' ],
				'description' => esc_html__( 'Text displayed next to the checkbox (different from the field label above).', NOVA_ADDONS_TEXT_DOMAIN ),
			]
		);

		$repeater->add_control(
			'field_checkbox_value',
			[
				'label'     => esc_html__( 'Checked value', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '1',
				'condition' => [ 'field_type' => 'checkbox' ],
			]
		);

		$repeater->add_control(
			'field_extra_class',
			[
				'label'   => esc_html__( 'Extra wrapper CSS class', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'    => Controls_Manager::TEXT,
			]
		);

		$this->add_control(
			'fields',
			[
				'label'       => esc_html__( 'Fields', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ field_label || field_name }}} ({{ field_type }})',
				'default'     => [
					[
						'field_type'  => 'search',
						'field_name'  => 's',
						'field_label' => esc_html__( 'Search', NOVA_ADDONS_TEXT_DOMAIN ),
						'field_placeholder' => esc_html__( 'Search…', NOVA_ADDONS_TEXT_DOMAIN ),
						'field_width' => '12',
					],
				],
			]
		);

		$this->end_controls_section();
	}

	/* ----- CONTENT : Submit / Reset ----- */
	private function register_submit_section() {
		$this->start_controls_section(
			'section_submit',
			[
				'label' => esc_html__( 'Submit & Reset', NOVA_ADDONS_TEXT_DOMAIN ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_submit',
			[
				'label'        => esc_html__( 'Show submit button', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'submit_text',
			[
				'label'     => esc_html__( 'Submit label', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Filter', NOVA_ADDONS_TEXT_DOMAIN ),
				'condition' => [ 'show_submit' => 'yes' ],
			]
		);

		$this->add_control(
			'submit_icon',
			[
				'label'     => esc_html__( 'Submit icon', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => Controls_Manager::ICONS,
				'skin'      => 'inline',
				'condition' => [ 'show_submit' => 'yes' ],
			]
		);

		$this->add_control(
			'submit_icon_position',
			[
				'label'   => esc_html__( 'Icon position', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'    => Controls_Manager::CHOOSE,
				'default' => 'after',
				'options' => [
					'before' => [
						'title' => esc_html__( 'Before', NOVA_ADDONS_TEXT_DOMAIN ),
						'icon'  => 'eicon-h-align-left',
					],
					'after' => [
						'title' => esc_html__( 'After', NOVA_ADDONS_TEXT_DOMAIN ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'condition' => [
					'show_submit'    => 'yes',
					'submit_icon[value]!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'submit_width',
			[
				'label'   => esc_html__( 'Submit width', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'auto',
				'options' => [
					'auto' => esc_html__( 'Auto', NOVA_ADDONS_TEXT_DOMAIN ),
					'full' => esc_html__( 'Full row', NOVA_ADDONS_TEXT_DOMAIN ),
					'half' => esc_html__( 'Half row', NOVA_ADDONS_TEXT_DOMAIN ),
				],
				'condition' => [ 'show_submit' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'actions_align',
			[
				'label'   => esc_html__( 'Actions alignment', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'    => Controls_Manager::CHOOSE,
				'default' => 'flex-start',
				'options' => [
					'flex-start'    => [ 'title' => esc_html__( 'Left', NOVA_ADDONS_TEXT_DOMAIN ),  'icon' => 'eicon-text-align-left' ],
					'center'        => [ 'title' => esc_html__( 'Center', NOVA_ADDONS_TEXT_DOMAIN ),'icon' => 'eicon-text-align-center' ],
					'flex-end'      => [ 'title' => esc_html__( 'Right', NOVA_ADDONS_TEXT_DOMAIN ), 'icon' => 'eicon-text-align-right' ],
					'space-between' => [ 'title' => esc_html__( 'Stretch', NOVA_ADDONS_TEXT_DOMAIN ),'icon' => 'eicon-h-align-stretch' ],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-filters-actions' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'show_reset',
			[
				'label'        => esc_html__( 'Show reset button', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'reset_text',
			[
				'label'     => esc_html__( 'Reset label', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Reset', NOVA_ADDONS_TEXT_DOMAIN ),
				'condition' => [ 'show_reset' => 'yes' ],
			]
		);

		$this->end_controls_section();
	}

	/* ----- STYLE : Container ----- */
	private function register_container_style() {
		$this->start_controls_section(
			'section_style_container',
			[
				'label' => esc_html__( 'Container', NOVA_ADDONS_TEXT_DOMAIN ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'container_background',
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-filters-form',
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label'      => esc_html__( 'Padding', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .nova-filters-form' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'container_border',
				'selector' => '{{WRAPPER}} .nova-filters-form',
			]
		);

		$this->add_responsive_control(
			'container_radius',
			[
				'label'      => esc_html__( 'Border radius', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .nova-filters-form' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'container_shadow',
				'selector' => '{{WRAPPER}} .nova-filters-form',
			]
		);

		$this->end_controls_section();
	}

	/* ----- STYLE : Labels ----- */
	private function register_label_style() {
		$this->start_controls_section(
			'section_style_labels',
			[
				'label' => esc_html__( 'Labels', NOVA_ADDONS_TEXT_DOMAIN ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'show_labels',
			[
				'label'        => esc_html__( 'Show labels', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'selectors_dictionary' => [
					''    => 'display: none;',
					'yes' => 'display: inline-flex;',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-filter-label' => '{{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'label_typography',
				'selector' => '{{WRAPPER}} .nova-filter-label',
			]
		);

		$this->add_control(
			'label_color',
			[
				'label'     => esc_html__( 'Color', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-filter-label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'label_required_color',
			[
				'label'     => esc_html__( 'Required asterisk color', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#d92121',
				'selectors' => [
					'{{WRAPPER}} .nova-filter-required' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'label_gap',
			[
				'label'      => esc_html__( 'Label spacing', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
				'default'    => [ 'unit' => 'px', 'size' => 6 ],
				'selectors'  => [
					'{{WRAPPER}} .nova-filter-field' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/* ----- STYLE : Inputs ----- */
	private function register_input_style() {
		$this->start_controls_section(
			'section_style_inputs',
			[
				'label' => esc_html__( 'Inputs', NOVA_ADDONS_TEXT_DOMAIN ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'input_typography',
				'selector' => '{{WRAPPER}} .nova-filter-input, {{WRAPPER}} .nova-filter-select, {{WRAPPER}} .nova-filter-textarea',
			]
		);

		$this->add_control(
			'input_text_color',
			[
				'label'     => esc_html__( 'Text color', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-filter-input, {{WRAPPER}} .nova-filter-select, {{WRAPPER}} .nova-filter-textarea' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'input_placeholder_color',
			[
				'label'     => esc_html__( 'Placeholder color', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-filter-input::placeholder, {{WRAPPER}} .nova-filter-textarea::placeholder' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'input_background',
			[
				'label'     => esc_html__( 'Background', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-filter-input, {{WRAPPER}} .nova-filter-select, {{WRAPPER}} .nova-filter-textarea' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'input_border',
				'selector' => '{{WRAPPER}} .nova-filter-input, {{WRAPPER}} .nova-filter-select, {{WRAPPER}} .nova-filter-textarea',
			]
		);

		$this->add_responsive_control(
			'input_radius',
			[
				'label'      => esc_html__( 'Border radius', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .nova-filter-input, {{WRAPPER}} .nova-filter-select, {{WRAPPER}} .nova-filter-textarea' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'input_padding',
			[
				'label'      => esc_html__( 'Padding', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'default'    => [ 'top' => '10', 'right' => '14', 'bottom' => '10', 'left' => '14', 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .nova-filter-input, {{WRAPPER}} .nova-filter-select, {{WRAPPER}} .nova-filter-textarea' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'input_focus_heading',
			[
				'label'     => esc_html__( 'Focus', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'input_focus_border',
			[
				'label'     => esc_html__( 'Border color (focus)', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-filter-input:focus, {{WRAPPER}} .nova-filter-select:focus, {{WRAPPER}} .nova-filter-textarea:focus' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'input_focus_shadow',
			[
				'label'     => esc_html__( 'Focus ring color', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-filter-input:focus, {{WRAPPER}} .nova-filter-select:focus, {{WRAPPER}} .nova-filter-textarea:focus' => 'box-shadow: 0 0 0 3px {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/* ----- STYLE : Help text ----- */
	private function register_help_style() {
		$this->start_controls_section(
			'section_style_help',
			[
				'label' => esc_html__( 'Help text', NOVA_ADDONS_TEXT_DOMAIN ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'help_typography',
				'selector' => '{{WRAPPER}} .nova-filter-help',
			]
		);

		$this->add_control(
			'help_color',
			[
				'label'     => esc_html__( 'Color', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-filter-help' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/* ----- STYLE : Submit button ----- */
	private function register_submit_style() {
		$this->start_controls_section(
			'section_style_submit',
			[
				'label'     => esc_html__( 'Submit button', NOVA_ADDONS_TEXT_DOMAIN ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_submit' => 'yes' ],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'submit_typography',
				'selector' => '{{WRAPPER}} .nova-filters-submit',
			]
		);

		$this->start_controls_tabs( 'submit_states' );

		$this->start_controls_tab(
			'submit_normal',
			[ 'label' => esc_html__( 'Normal', NOVA_ADDONS_TEXT_DOMAIN ) ]
		);

		$this->add_control(
			'submit_color',
			[
				'label'     => esc_html__( 'Text color', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .nova-filters-submit' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'submit_background',
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-filters-submit',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'submit_border',
				'selector' => '{{WRAPPER}} .nova-filters-submit',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'submit_hover',
			[ 'label' => esc_html__( 'Hover', NOVA_ADDONS_TEXT_DOMAIN ) ]
		);

		$this->add_control(
			'submit_color_hover',
			[
				'label'     => esc_html__( 'Text color', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-filters-submit:hover, {{WRAPPER}} .nova-filters-submit:focus' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'submit_background_hover',
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-filters-submit:hover, {{WRAPPER}} .nova-filters-submit:focus',
			]
		);

		$this->add_control(
			'submit_border_color_hover',
			[
				'label'     => esc_html__( 'Border color', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-filters-submit:hover, {{WRAPPER}} .nova-filters-submit:focus' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'submit_radius',
			[
				'label'      => esc_html__( 'Border radius', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .nova-filters-submit' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'submit_padding',
			[
				'label'      => esc_html__( 'Padding', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'default'    => [ 'top' => '12', 'right' => '24', 'bottom' => '12', 'left' => '24', 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .nova-filters-submit' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'submit_icon_size',
			[
				'label'     => esc_html__( 'Icon size', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => Controls_Manager::SLIDER,
				'size_units'=> [ 'px' ],
				'range'     => [ 'px' => [ 'min' => 6, 'max' => 48 ] ],
				'selectors' => [
					'{{WRAPPER}} .nova-filters-submit .nova-filters-submit-icon i, {{WRAPPER}} .nova-filters-submit .nova-filters-submit-icon svg' => 'font-size: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'submit_icon_gap',
			[
				'label'     => esc_html__( 'Icon spacing', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => Controls_Manager::SLIDER,
				'size_units'=> [ 'px' ],
				'range'     => [ 'px' => [ 'min' => 0, 'max' => 30 ] ],
				'selectors' => [
					'{{WRAPPER}} .nova-filters-submit' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/* ----- STYLE : Reset button ----- */
	private function register_reset_style() {
		$this->start_controls_section(
			'section_style_reset',
			[
				'label'     => esc_html__( 'Reset button', NOVA_ADDONS_TEXT_DOMAIN ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_reset' => 'yes' ],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'reset_typography',
				'selector' => '{{WRAPPER}} .nova-filters-reset',
			]
		);

		$this->add_control(
			'reset_color',
			[
				'label'     => esc_html__( 'Text color', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .nova-filters-reset' => 'color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'reset_color_hover',
			[
				'label'     => esc_html__( 'Hover color', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .nova-filters-reset:hover, {{WRAPPER}} .nova-filters-reset:focus' => 'color: {{VALUE}};' ],
			]
		);

		$this->add_responsive_control(
			'reset_padding',
			[
				'label'      => esc_html__( 'Padding', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .nova-filters-reset' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * RENDER
	 * ------------------------------------------------------------------ */

	protected function render() {
		$s = $this->get_settings_for_display();

		$action_url = ! empty( $s['form_action']['url'] ) ? esc_url( $s['form_action']['url'] ) : '';
		$form_id    = ! empty( $s['form_id_attr'] ) ? sanitize_html_class( $s['form_id_attr'] ) : '';

		$preserve_query   = ! empty( $s['preserve_query'] ) && $s['preserve_query'] === 'yes';
		$submit_on_change = ! empty( $s['submit_on_change'] ) && $s['submit_on_change'] === 'yes';

		// Build the list of field names so we know which params to skip when preserving.
		$field_names = [];
		if ( ! empty( $s['fields'] ) && is_array( $s['fields'] ) ) {
			foreach ( $s['fields'] as $f ) {
				if ( ! empty( $f['field_name'] ) ) {
					$field_names[] = $f['field_name'];
				}
			}
		}

		$form_classes = [ 'nova-filters-form' ];
		if ( $submit_on_change ) {
			$form_classes[] = 'nova-filters--submit-on-change';
		}

		$form_attrs  = 'method="get"';
		$form_attrs .= ' class="' . esc_attr( implode( ' ', $form_classes ) ) . '"';
		if ( $action_url )    $form_attrs .= ' action="' . esc_url( $action_url ) . '"';
		if ( $form_id )       $form_attrs .= ' id="' . esc_attr( $form_id ) . '"';
		if ( $submit_on_change ) $form_attrs .= ' data-submit-on-change="1"';

		?>
		<form <?php echo $form_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

			<?php if ( $preserve_query ) : ?>
				<?php $this->render_preserved_query_inputs( $field_names, $action_url ); ?>
			<?php endif; ?>

			<div class="nova-filters-fields">
				<?php
				if ( ! empty( $s['fields'] ) && is_array( $s['fields'] ) ) {
					foreach ( $s['fields'] as $field ) {
						$this->render_field( $field );
					}
				}
				?>
			</div>

			<?php if ( ( ! empty( $s['show_submit'] ) && $s['show_submit'] === 'yes' ) || ( ! empty( $s['show_reset'] ) && $s['show_reset'] === 'yes' ) ) : ?>
				<div class="nova-filters-actions">

					<?php if ( ! empty( $s['show_reset'] ) && $s['show_reset'] === 'yes' ) : ?>
						<button type="button" class="nova-filters-reset" data-nova-filters-reset>
							<?php echo esc_html( ! empty( $s['reset_text'] ) ? $s['reset_text'] : __( 'Reset', NOVA_ADDONS_TEXT_DOMAIN ) ); ?>
						</button>
					<?php endif; ?>

					<?php if ( ! empty( $s['show_submit'] ) && $s['show_submit'] === 'yes' ) :
						$submit_classes = [ 'nova-filters-submit' ];
						$width = ! empty( $s['submit_width'] ) ? $s['submit_width'] : 'auto';
						$submit_classes[] = 'nova-filters-submit--' . sanitize_html_class( $width );

						$icon_position = ! empty( $s['submit_icon_position'] ) ? $s['submit_icon_position'] : 'after';
						$has_icon      = ! empty( $s['submit_icon']['value'] );
						$submit_label  = ! empty( $s['submit_text'] ) ? $s['submit_text'] : __( 'Filter', NOVA_ADDONS_TEXT_DOMAIN );
					?>
						<button type="submit" class="<?php echo esc_attr( implode( ' ', $submit_classes ) ); ?>">
							<?php if ( $has_icon && $icon_position === 'before' ) : ?>
								<span class="nova-filters-submit-icon"><?php Icons_Manager::render_icon( $s['submit_icon'], [ 'aria-hidden' => 'true' ] ); ?></span>
							<?php endif; ?>
							<span class="nova-filters-submit-text"><?php echo esc_html( $submit_label ); ?></span>
							<?php if ( $has_icon && $icon_position === 'after' ) : ?>
								<span class="nova-filters-submit-icon"><?php Icons_Manager::render_icon( $s['submit_icon'], [ 'aria-hidden' => 'true' ] ); ?></span>
							<?php endif; ?>
						</button>
					<?php endif; ?>

				</div>
			<?php endif; ?>

		</form>
		<?php
	}

	/**
	 * Render hidden inputs for every existing GET param that is not handled
	 * by one of the form fields, so submitting preserves them.
	 */
	private function render_preserved_query_inputs( $field_names, $action_url ) {
		// If a custom action URL was set, take its baseline query string. Otherwise current request.
		$source = [];
		if ( $action_url ) {
			$parsed = wp_parse_url( $action_url );
			if ( ! empty( $parsed['query'] ) ) {
				parse_str( $parsed['query'], $source );
			}
		}
		// Merge with current GET so we don't drop active filters from another widget on the same page.
		if ( ! empty( $_GET ) && is_array( $_GET ) ) {
			$source = array_merge( $source, wp_unslash( $_GET ) );
		}
		if ( empty( $source ) ) {
			return;
		}
		foreach ( $source as $key => $value ) {
			if ( in_array( (string) $key, array_map( 'strval', $field_names ), true ) ) {
				continue;
			}
			if ( is_array( $value ) ) {
				foreach ( $value as $vk => $v ) {
					printf(
						'<input type="hidden" name="%s[%s]" value="%s">',
						esc_attr( (string) $key ),
						esc_attr( (string) $vk ),
						esc_attr( (string) $v )
					);
				}
			} else {
				printf(
					'<input type="hidden" name="%s" value="%s">',
					esc_attr( (string) $key ),
					esc_attr( (string) $value )
				);
			}
		}
	}

	/**
	 * Render a single field block.
	 */
	private function render_field( $field ) {
		$type    = ! empty( $field['field_type'] ) ? $field['field_type'] : 'text';
		$name    = ! empty( $field['field_name'] ) ? $field['field_name'] : '';
		if ( $name === '' ) {
			return; // Skip mis-configured fields without a name.
		}

		$label       = isset( $field['field_label'] ) ? $field['field_label'] : '';
		$placeholder = isset( $field['field_placeholder'] ) ? $field['field_placeholder'] : '';
		$default     = isset( $field['field_default'] ) ? $field['field_default'] : '';
		$help        = isset( $field['field_help'] ) ? $field['field_help'] : '';
		$required    = ! empty( $field['field_required'] ) && $field['field_required'] === 'yes';
		$width       = isset( $field['field_width'] ) ? (int) $field['field_width'] : 12;
		$extra_class = isset( $field['field_extra_class'] ) ? trim( (string) $field['field_extra_class'] ) : '';

		$min  = isset( $field['field_min'] ) && $field['field_min'] !== '' ? (string) $field['field_min'] : null;
		$max  = isset( $field['field_max'] ) && $field['field_max'] !== '' ? (string) $field['field_max'] : null;
		$step = isset( $field['field_step'] ) && $field['field_step'] !== '' ? (string) $field['field_step'] : null;

		$current_value = $this->get_current_value( $name, $default );

		$id_attr = sanitize_html_class( 'nova-filter-' . $name . '-' . substr( md5( $name . $type . wp_rand() ), 0, 6 ) );

		// Field wrapper.
		$wrapper_classes = [
			'nova-filter-field',
			'nova-filter-field--' . sanitize_html_class( $type ),
			'nova-filter-field--w-' . $width,
		];
		if ( $extra_class ) {
			$wrapper_classes[] = $extra_class;
		}

		$wrapper_classes_str = esc_attr( implode( ' ', $wrapper_classes ) );

		if ( $type === 'hidden' ) {
			printf(
				'<input type="hidden" name="%s" value="%s">',
				esc_attr( $name ),
				esc_attr( (string) $current_value )
			);
			return;
		}

		echo '<div class="' . $wrapper_classes_str . '" data-nova-field-name="' . esc_attr( $name ) . '">';

		// Label.
		if ( $label !== '' && $type !== 'checkbox' ) {
			echo '<label class="nova-filter-label" for="' . esc_attr( $id_attr ) . '">';
			echo esc_html( $label );
			if ( $required ) {
				echo ' <span class="nova-filter-required" aria-hidden="true">*</span>';
			}
			echo '</label>';
		}

		// Field.
		switch ( $type ) {
			case 'textarea':
				$rows = isset( $field['field_rows'] ) && (int) $field['field_rows'] > 0 ? (int) $field['field_rows'] : 4;
				printf(
					'<textarea class="nova-filter-textarea" name="%s" id="%s" rows="%d" placeholder="%s"%s>%s</textarea>',
					esc_attr( $name ),
					esc_attr( $id_attr ),
					$rows,
					esc_attr( $placeholder ),
					$required ? ' required' : '',
					esc_textarea( (string) $current_value )
				);
				break;

			case 'select':
			case 'multiselect':
				$is_multi = ( $type === 'multiselect' );
				$opts     = $this->parse_options_list( isset( $field['field_options'] ) ? $field['field_options'] : '' );
				$multi_attr = $is_multi ? ' multiple' : '';
				$name_attr  = $is_multi ? $name . '[]' : $name;

				printf(
					'<select class="nova-filter-select" name="%s" id="%s"%s%s>',
					esc_attr( $name_attr ),
					esc_attr( $id_attr ),
					$multi_attr,
					$required ? ' required' : ''
				);
				if ( ! $is_multi && $placeholder !== '' ) {
					printf( '<option value="">%s</option>', esc_html( $placeholder ) );
				}
				foreach ( $opts as $opt ) {
					$selected = $is_multi
						? in_array( $opt['value'], (array) $current_value, true )
						: ( (string) $current_value === (string) $opt['value'] );
					printf(
						'<option value="%s"%s>%s</option>',
						esc_attr( $opt['value'] ),
						$selected ? ' selected' : '',
						esc_html( $opt['label'] )
					);
				}
				echo '</select>';
				break;

			case 'radio':
				$opts = $this->parse_options_list( isset( $field['field_options'] ) ? $field['field_options'] : '' );
				echo '<div class="nova-filter-radio-group" role="radiogroup">';
				foreach ( $opts as $idx => $opt ) {
					$opt_id = $id_attr . '-' . $idx;
					$checked = ( (string) $current_value === (string) $opt['value'] );
					printf(
						'<label class="nova-filter-radio" for="%s"><input type="radio" name="%s" id="%s" value="%s"%s%s><span class="nova-filter-radio-label">%s</span></label>',
						esc_attr( $opt_id ),
						esc_attr( $name ),
						esc_attr( $opt_id ),
						esc_attr( $opt['value'] ),
						$checked ? ' checked' : '',
						$required ? ' required' : '',
						esc_html( $opt['label'] )
					);
				}
				echo '</div>';
				break;

			case 'checkbox-group':
				$opts = $this->parse_options_list( isset( $field['field_options'] ) ? $field['field_options'] : '' );
				$values = (array) $current_value;
				echo '<div class="nova-filter-checkbox-group">';
				foreach ( $opts as $idx => $opt ) {
					$opt_id = $id_attr . '-' . $idx;
					$checked = in_array( (string) $opt['value'], array_map( 'strval', $values ), true );
					printf(
						'<label class="nova-filter-checkbox" for="%s"><input type="checkbox" name="%s[]" id="%s" value="%s"%s><span class="nova-filter-checkbox-label">%s</span></label>',
						esc_attr( $opt_id ),
						esc_attr( $name ),
						esc_attr( $opt_id ),
						esc_attr( $opt['value'] ),
						$checked ? ' checked' : '',
						esc_html( $opt['label'] )
					);
				}
				echo '</div>';
				break;

			case 'checkbox':
				$checkbox_label = isset( $field['field_checkbox_label'] ) ? $field['field_checkbox_label'] : $label;
				$checkbox_value = isset( $field['field_checkbox_value'] ) && $field['field_checkbox_value'] !== '' ? $field['field_checkbox_value'] : '1';
				$checked = ( (string) $current_value === (string) $checkbox_value );
				printf(
					'<label class="nova-filter-checkbox" for="%s"><input type="checkbox" name="%s" id="%s" value="%s"%s%s><span class="nova-filter-checkbox-label">%s</span>%s</label>',
					esc_attr( $id_attr ),
					esc_attr( $name ),
					esc_attr( $id_attr ),
					esc_attr( $checkbox_value ),
					$checked ? ' checked' : '',
					$required ? ' required' : '',
					esc_html( $checkbox_label ),
					$required ? ' <span class="nova-filter-required" aria-hidden="true">*</span>' : ''
				);
				break;

			case 'range':
				$attrs  = $min !== null ? ' min="' . esc_attr( $min ) . '"' : '';
				$attrs .= $max !== null ? ' max="' . esc_attr( $max ) . '"' : '';
				$attrs .= $step !== null ? ' step="' . esc_attr( $step ) . '"' : '';
				printf(
					'<input type="range" class="nova-filter-input nova-filter-input--range" name="%s" id="%s" value="%s"%s%s>',
					esc_attr( $name ),
					esc_attr( $id_attr ),
					esc_attr( (string) $current_value ),
					$attrs,
					$required ? ' required' : ''
				);
				break;

			default:
				// text, search, email, tel, url, number, date, time, datetime-local
				$attrs = '';
				if ( in_array( $type, [ 'number' ], true ) ) {
					if ( $min !== null ) {
						$attrs .= ' min="' . esc_attr( $min ) . '"';
					}
					if ( $max !== null ) {
						$attrs .= ' max="' . esc_attr( $max ) . '"';
					}
					if ( $step !== null ) {
						$attrs .= ' step="' . esc_attr( $step ) . '"';
					}
				}
				printf(
					'<input type="%s" class="nova-filter-input" name="%s" id="%s" value="%s" placeholder="%s"%s%s>',
					esc_attr( $type ),
					esc_attr( $name ),
					esc_attr( $id_attr ),
					esc_attr( (string) $current_value ),
					esc_attr( $placeholder ),
					$attrs,
					$required ? ' required' : ''
				);
				break;
		}

		if ( $help !== '' ) {
			echo '<small class="nova-filter-help">' . wp_kses_post( $help ) . '</small>';
		}

		echo '</div>';
	}

	/**
	 * Return the current value for a field name : URL param if present, otherwise the configured default.
	 */
	private function get_current_value( $name, $default ) {
		if ( isset( $_GET[ $name ] ) ) {
			$raw = wp_unslash( $_GET[ $name ] );
			if ( is_array( $raw ) ) {
				return array_map( 'sanitize_text_field', $raw );
			}
			return sanitize_text_field( $raw );
		}
		return $default;
	}

	/**
	 * Parse "value|Label" lines into an array of { value, label }.
	 */
	private function parse_options_list( $raw ) {
		$out = [];
		if ( ! is_string( $raw ) || trim( $raw ) === '' ) {
			return $out;
		}
		$lines = preg_split( '/\r\n|\r|\n/', $raw );
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( $line === '' ) {
				continue;
			}
			if ( strpos( $line, '|' ) !== false ) {
				list( $value, $label ) = array_map( 'trim', explode( '|', $line, 2 ) );
			} else {
				$value = $label = $line;
			}
			$out[] = [
				'value' => $value,
				'label' => $label === '' ? $value : $label,
			];
		}
		return $out;
	}
}
