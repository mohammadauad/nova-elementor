<?php
/**
 * NOVA Addons - Settings Page
 * Admin settings page pour configurer les animations et comportements du plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Nova_Addons_Settings {

	/**
	 * Initialize settings screen
	 */
	public static function init() {
		add_action( 'admin_menu', [ __CLASS__, 'add_admin_menu' ] );
		add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
		// Inject CSS variables with settings values
		add_action( 'wp_head', [ __CLASS__, 'inject_icon_menu_css_variables' ] );
	}

	/**
	 * Add admin menu
	 */
	public static function add_admin_menu() {
		add_menu_page(
			esc_html__( 'NOVA Addons Settings', 'NOVA-addons' ),
			esc_html__( 'NOVA Addons', 'NOVA-addons' ),
			'manage_options',
			'NOVA-addons-settings',
			[ __CLASS__, 'render_settings_page' ],
			'dashicons-star-filled',
			99
		);
	}

	/**
	 * Enqueue admin styles
	 */
	public static function enqueue_admin_styles( $hook ) {
		if ( 'toplevel_page_NOVA-addons-settings' !== $hook ) {
			return;
		}
		
		wp_enqueue_style(
			'NOVA-admin-settings',
			plugin_dir_url( __FILE__ ) . '../assets/css/admin-settings.css',
			[],
			'1.0.0'
		);
	}

	/**
	 * Register all settings
	 */
	public static function register_settings() {
		// Page Loader Settings
		register_setting( 'NOVA_addons_settings', 'NOVA_loader_enabled', [
			'sanitize_callback' => function( $value ) {
				return ( $value === 'yes' ) ? 'yes' : 'no';
			},
			'default' => 'yes'
		] );
		register_setting( 'NOVA_addons_settings', 'NOVA_loader_color_1' );
		register_setting( 'NOVA_addons_settings', 'NOVA_loader_color_2' );
		register_setting( 'NOVA_addons_settings', 'NOVA_loader_phase2_duration' );
		register_setting( 'NOVA_addons_settings', 'NOVA_loader_phase2_delay' );
		register_setting( 'NOVA_addons_settings', 'NOVA_loader_phase3_duration' );
		register_setting( 'NOVA_addons_settings', 'NOVA_loader_anim_delay' );

		// Icon Menu Animation Settings
		register_setting( 'NOVA_addons_settings', 'NOVA_icon_menu_hover_duration' );
		register_setting( 'NOVA_addons_settings', 'NOVA_icon_menu_hover_easing' );
		register_setting( 'NOVA_addons_settings', 'NOVA_icon_menu_submenu_duration' );
		register_setting(
			'NOVA_addons_settings',
			'NOVA_icon_menu_hover_enabled',
			[
				'sanitize_callback' => function( $value ) {
					return ( $value === 'yes' ) ? 'yes' : 'no';
				},
				'default' => 'yes'
			]
		);

		// Page Loader Section
		add_settings_section(
			'NOVA_loader_section',
			esc_html__( 'Page Loader Animation', 'NOVA-addons' ),
			[ __CLASS__, 'loader_section_callback' ],
			'NOVA_addons_settings'
		);

		add_settings_field(
			'NOVA_loader_enabled',
			esc_html__( 'Enable Page Loader', 'NOVA-addons' ),
			[ __CLASS__, 'loader_enabled_field_callback' ],
			'NOVA_addons_settings',
			'NOVA_loader_section'
		);

		add_settings_field(
			'NOVA_loader_color_1',
			esc_html__( 'Primary Color (Phase 1)', 'NOVA-addons' ),
			[ __CLASS__, 'loader_color_1_field_callback' ],
			'NOVA_addons_settings',
			'NOVA_loader_section'
		);

		add_settings_field(
			'NOVA_loader_color_2',
			esc_html__( 'Secondary Color (Phase 2-3)', 'NOVA-addons' ),
			[ __CLASS__, 'loader_color_2_field_callback' ],
			'NOVA_addons_settings',
			'NOVA_loader_section'
		);

		add_settings_field(
			'NOVA_loader_phase2_delay',
			esc_html__( 'Phase 2 Delay (seconds)', 'NOVA-addons' ),
			[ __CLASS__, 'loader_phase2_delay_field_callback' ],
			'NOVA_addons_settings',
			'NOVA_loader_section'
		);

		add_settings_field(
			'NOVA_loader_phase2_duration',
			esc_html__( 'Phase 2 Duration (seconds)', 'NOVA-addons' ),
			[ __CLASS__, 'loader_phase2_duration_field_callback' ],
			'NOVA_addons_settings',
			'NOVA_loader_section'
		);

		add_settings_field(
			'NOVA_loader_phase3_duration',
			esc_html__( 'Phase 3 Duration (seconds)', 'NOVA-addons' ),
			[ __CLASS__, 'loader_phase3_duration_field_callback' ],
			'NOVA_addons_settings',
			'NOVA_loader_section'
		);

		add_settings_field(
			'NOVA_loader_anim_delay',
			esc_html__( 'Animation Delay (milliseconds)', 'NOVA-addons' ),
			[ __CLASS__, 'anim_delay_field_callback' ],
			'NOVA_addons_settings',
			'NOVA_loader_section'
		);

		// Icon Menu Animation Section
		add_settings_section(
			'NOVA_icon_menu_section',
			esc_html__( 'Icon Menu Animation', 'NOVA-addons' ),
			[ __CLASS__, 'icon_menu_section_callback' ],
			'NOVA_addons_settings'
		);

		add_settings_field(
			'NOVA_icon_menu_hover_duration',
			esc_html__( 'Hover Animation Duration', 'NOVA-addons' ),
			[ __CLASS__, 'icon_menu_hover_duration_callback' ],
			'NOVA_addons_settings',
			'NOVA_icon_menu_section'
		);

		add_settings_field(
			'NOVA_icon_menu_hover_easing',
			esc_html__( 'Hover Animation Easing', 'NOVA-addons' ),
			[ __CLASS__, 'icon_menu_hover_easing_callback' ],
			'NOVA_addons_settings',
			'NOVA_icon_menu_section'
		);

		add_settings_field(
			'NOVA_icon_menu_submenu_duration',
			esc_html__( 'Submenu Animation Duration', 'NOVA-addons' ),
			[ __CLASS__, 'icon_menu_submenu_duration_callback' ],
			'NOVA_addons_settings',
			'NOVA_icon_menu_section'
		);

		add_settings_field(
			'NOVA_icon_menu_hover_enabled',
			esc_html__( 'Enable Hover Animation', 'NOVA-addons' ),
			[ __CLASS__, 'icon_menu_hover_enabled_callback' ],
			'NOVA_addons_settings',
			'NOVA_icon_menu_section'
		);
	}

	/**
	 * Page Loader section callback
	 */
	public static function loader_section_callback() {
		echo wp_kses_post( __( '⚙️ Configure the page loader animation that appears on every page load. You can enable or disable the animation using the option below.', 'NOVA-addons' ) );
	}

	/**
	 * Icon Menu section callback
	 */
	public static function icon_menu_section_callback() {
		echo wp_kses_post( __( '🎨 Customize animation behavior for Icon Menu hover effects and submenus.', 'NOVA-addons' ) );
	}

	/**
	 * Primary Color (Phase 1) field
	 */
	public static function loader_color_1_field_callback() {
		$value = get_option( 'NOVA_loader_color_1', 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' );
		?>
		<input type="text" name="NOVA_loader_color_1" value="<?php echo esc_attr( $value ); ?>" class="NOVA-color-field" placeholder="#667eea or linear-gradient(...)">
		<p class="description">
			<?php esc_html_e( 'Primary color/gradient displayed at the start (Phase 1)', 'NOVA-addons' ); ?><br>
			<strong><?php esc_html_e( 'Examples:', 'NOVA-addons' ); ?></strong> 
			<code>#667eea</code> | 
			<code>linear-gradient(135deg, #667eea 0%, #764ba2 100%)</code> | 
			<code>rgb(102, 126, 234)</code>
		</p>
		<?php
	}

	/**
	 * Secondary Color (Phase 2-3) field
	 */
	public static function loader_color_2_field_callback() {
		$value = get_option( 'NOVA_loader_color_2', '#DB002B' );
		?>
		<input type="text" name="NOVA_loader_color_2" value="<?php echo esc_attr( $value ); ?>" class="NOVA-color-field" placeholder="#DB002B">
		<p class="description">
			<?php esc_html_e( 'Secondary color displayed during Phase 2 and 3 (overlay animation)', 'NOVA-addons' ); ?><br>
			<strong><?php esc_html_e( 'Examples:', 'NOVA-addons' ); ?></strong> 
			<code>#DB002B</code> | 
			<code>rgb(219, 0, 43)</code> | 
			<code>rgba(219, 0, 43, 0.95)</code>
		</p>
		<?php
	}

	/**
	 * Phase 2 Delay field
	 */
	public static function loader_phase2_delay_field_callback() {
		$value = get_option( 'NOVA_loader_phase2_delay', 0.1 );
		?>
		<div class="NOVA-input-group">
			<input type="number" name="NOVA_loader_phase2_delay" value="<?php echo esc_attr( $value ); ?>" step="0.1" min="0" max="3" class="NOVA-duration-input">
			<span class="NOVA-input-suffix">seconds</span>
		</div>
		<p class="description">
			<?php esc_html_e( 'Delay before secondary color starts animating in (0 - 3 seconds)', 'NOVA-addons' ); ?>
		</p>
		<?php
	}

	/**
	 * Phase 2 Duration field
	 */
	public static function loader_phase2_duration_field_callback() {
		$value = get_option( 'NOVA_loader_phase2_duration', 0.3 );
		?>
		<div class="NOVA-input-group">
			<input type="number" name="NOVA_loader_phase2_duration" value="<?php echo esc_attr( $value ); ?>" step="0.1" min="0.1" max="5" class="NOVA-duration-input">
			<span class="NOVA-input-suffix">seconds</span>
		</div>
		<p class="description">
			<?php esc_html_e( 'How long secondary color takes to expand from height 0 to full (0.1 - 5 seconds)', 'NOVA-addons' ); ?>
		</p>
		<?php
	}

	/**
	 * Phase 3 Duration field
	 */
	public static function loader_phase3_duration_field_callback() {
		$value = get_option( 'NOVA_loader_phase3_duration', 1.0 );
		?>
		<div class="NOVA-input-group">
			<input type="number" name="NOVA_loader_phase3_duration" value="<?php echo esc_attr( $value ); ?>" step="0.1" min="0.1" max="5" class="NOVA-duration-input">
			<span class="NOVA-input-suffix">seconds</span>
		</div>
		<p class="description">
			<?php esc_html_e( 'How long secondary color takes to contract from full to height 0 (0.1 - 5 seconds)', 'NOVA-addons' ); ?>
		</p>
		<?php
	}

	/**
	 * Page Loader animation delay field
	 */
	public static function anim_delay_field_callback() {
		$value = get_option( 'NOVA_loader_anim_delay', 0 );
		?>
		<div class="NOVA-input-group">
			<input type="number" name="NOVA_loader_anim_delay" value="<?php echo esc_attr( $value ); ?>" step="100" min="0" max="5000" class="NOVA-delay-input">
			<span class="NOVA-input-suffix">ms</span>
		</div>
		<p class="description">
			<?php esc_html_e( 'Delay before animation starts (0 - 5000 milliseconds). 0 = immediate', 'NOVA-addons' ); ?>
		</p>
		<?php
	}

	/**
	 * Page Loader enabled field
	 */
	public static function loader_enabled_field_callback() {
		$value = get_option( 'NOVA_loader_enabled', 'yes' );
		?>
		<!-- Hidden input to always submit 'no' if unchecked -->
		<input type="hidden" name="NOVA_loader_enabled" value="no">
		<label for="NOVA_loader_enabled">
			<input type="checkbox" name="NOVA_loader_enabled" id="NOVA_loader_enabled" value="yes" <?php checked( $value, 'yes' ); ?>>
			<?php esc_html_e( 'Enable page loader animation on every page load', 'NOVA-addons' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'Check this box to enable the page loader animation. Uncheck to disable it completely.', 'NOVA-addons' ); ?>
		</p>
		<?php
	}

	/**
	 * Icon Menu hover duration field
	 */
	public static function icon_menu_hover_duration_callback() {
		$value = get_option( 'NOVA_icon_menu_hover_duration', 1.0 );
		?>
		<div class="NOVA-input-group">
			<input type="number" name="NOVA_icon_menu_hover_duration" value="<?php echo esc_attr( $value ); ?>" step="0.1" min="0.1" max="5" class="NOVA-duration-input">
			<span class="NOVA-input-suffix">seconds</span>
		</div>
		<p class="description">
			<?php esc_html_e( 'Time for menu items to highlight on hover (0.1 - 5 seconds). Higher = slower, smoother animation', 'NOVA-addons' ); ?>
		</p>
		<?php
	}

	/**
	 * Icon Menu hover easing field
	 */
	public static function icon_menu_hover_easing_callback() {
		$value = get_option( 'NOVA_icon_menu_hover_easing', 'ease' );
		$options = [
			'linear'       => 'Linear (constant speed)',
			'ease'         => 'Ease (smooth, default)',
			'ease-in'      => 'Ease In (slow start)',
			'ease-out'     => 'Ease Out (slow end)',
			'ease-in-out'  => 'Ease In Out (smooth both)',
		];
		?>
		<select name="NOVA_icon_menu_hover_easing" class="NOVA-select">
			<?php foreach ( $options as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description">
			<?php esc_html_e( 'Animation easing function for smoother or snappier transitions', 'NOVA-addons' ); ?>
		</p>
		<?php
	}

	/**
	 * Icon Menu submenu duration field
	 */
	public static function icon_menu_submenu_duration_callback() {
		$value = get_option( 'NOVA_icon_menu_submenu_duration', 0.3 );
		?>
		<div class="NOVA-input-group">
			<input type="number" name="NOVA_icon_menu_submenu_duration" value="<?php echo esc_attr( $value ); ?>" step="0.1" min="0.1" max="5" class="NOVA-duration-input">
			<span class="NOVA-input-suffix">seconds</span>
		</div>
		<p class="description">
			<?php esc_html_e( 'Time for submenus to fade in (0.1 - 5 seconds). Higher = slower, smoother animation', 'NOVA-addons' ); ?>
		</p>
		<?php
	}

	/**
	 * Icon Menu hover enabled field
	 */
	public static function icon_menu_hover_enabled_callback() {
		$value = get_option( 'NOVA_icon_menu_hover_enabled', 'yes' );
		?>
		<!-- Hidden input to always submit 'no' if unchecked -->
		<input type="hidden" name="NOVA_icon_menu_hover_enabled" value="no">
		<label for="NOVA_icon_menu_hover_enabled">
			<input type="checkbox" name="NOVA_icon_menu_hover_enabled" id="NOVA_icon_menu_hover_enabled" value="yes" <?php checked( $value, 'yes' ); ?>>
			<?php esc_html_e( 'Enable hover animation for Icon Menu', 'NOVA-addons' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'Check this box to enable hover animation for the Icon Menu. Uncheck to disable.', 'NOVA-addons' ); ?>
		</p>
		<?php
	}

	/**
	 * Inject Icon Menu CSS variables based on settings
	 */
	public static function inject_icon_menu_css_variables() {
		// Get settings
		$hover_duration = get_option( 'NOVA_icon_menu_hover_duration', 1.0 );
		$hover_easing = get_option( 'NOVA_icon_menu_hover_easing', 'ease' );
		$submenu_duration = get_option( 'NOVA_icon_menu_submenu_duration', 0.3 );
		$hover_enabled = get_option( 'NOVA_icon_menu_hover_enabled', 'yes' );
		
		// Sanitize values
		$hover_duration = floatval( $hover_duration );
		$submenu_duration = floatval( $submenu_duration );
		$hover_easing = sanitize_text_field( $hover_easing );
		
		?>
		<style id="nova-header-menu-animation">
			/* ========================================
			   CRITICAL: ALWAYS force #top-header-2 width to auto
			======================================== */
			#top-header-2 {
				width: auto !important;
				overflow: visible !important;
			}
			
			<?php if ( $hover_enabled === 'yes' ) : ?>
				/* When hover animation is ENABLED - Use CSS variables */
				#top-header-2 {
					--nova-hover-duration: <?php echo esc_attr( $hover_duration ); ?>s;
					--nova-hover-easing: <?php echo esc_attr( $hover_easing ); ?>;
					--nova-submenu-duration: <?php echo esc_attr( $submenu_duration ); ?>s;
				}
			<?php else : ?>
				/* When hover animation is DISABLED */
				/* Remove ALL hover effects */
				#top-header-2 a:hover {
					background-color: transparent !important;
					transform: none !important;
					opacity: 1 !important;
				}
				
				#top-header-2 li:hover {
					background-color: transparent !important;
				}
				
				#top-header-2 li:hover > a {
					background-color: transparent !important;
					transform: none !important;
					opacity: 1 !important;
				}
				
				/* Remove ALL animations */
				#top-header-2,
				#top-header-2 a,
				#top-header-2 li,
				#top-header-2 .sub-menu {
					animation: none !important;
					transition: none !important;
				}
				
				/* Keep submenu visible */
				#top-header-2 .sub-menu {
					display: block !important;
					opacity: 1 !important;
					visibility: visible !important;
					width: auto !important;
					max-height: none !important;
					overflow: visible !important;
				}
				
				#top-header-2 li:hover > .sub-menu {
					display: block !important;
				}
			<?php endif; ?>
		</style>
		<?php
	}

	/**
	 * Render settings page with professional layout
	 */
	public static function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'NOVA-addons' ) );
		}
		?>
		<div class="wrap NOVA-settings-wrapper">
			<!-- Header -->
			<div class="NOVA-settings-header">
				<div class="NOVA-header-content">
					<h1>🌟 <?php esc_html_e( 'NOVA Addons Settings', 'NOVA-addons' ); ?></h1>
					<p class="NOVA-subtitle"><?php esc_html_e( 'Configure animations and behaviors for your NOVA widgets', 'NOVA-addons' ); ?></p>
				</div>
			</div>

			<!-- Main Content -->
			<div class="NOVA-settings-container">
				<!-- Settings Form -->
				<div class="NOVA-settings-form">
					<form method="post" action="options.php" class="NOVA-form">
						<?php
						settings_fields( 'NOVA_addons_settings' );
						do_settings_sections( 'NOVA_addons_settings' );
						submit_button( esc_html__( 'Save Settings', 'NOVA-addons' ), 'primary large' );
						?>
					</form>
				</div>

				<!-- Sidebar with Preview -->
				<div class="NOVA-settings-sidebar">
					<!-- Page Loader Preview -->
					<div class="NOVA-preview-card">
						<h3><?php esc_html_e( '📱 Page Loader Preview', 'NOVA-addons' ); ?></h3>
						<div class="NOVA-loader-preview" style="background: <?php echo esc_attr( get_option( 'NOVA_loader_color_1', 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' ) ); ?>">
							<span><?php esc_html_e( 'This is how your page loader background will look', 'NOVA-addons' ); ?></span>
						</div>
						<p class="NOVA-preview-info">
							<?php esc_html_e( 'Duration:', 'NOVA-addons' ); ?> 
							<strong><?php echo esc_html( get_option( 'NOVA_loader_anim_delay', 0 ) ); ?>ms</strong>
						</p>
					</div>

					<!-- Information Card -->
					<div class="NOVA-info-card">
						<h3><?php esc_html_e( 'ℹ️ Information', 'NOVA-addons' ); ?></h3>
						<ul class="NOVA-info-list">
							<li><?php esc_html_e( '✅ Page Loader shows on every page load', 'NOVA-addons' ); ?></li>
							<li><?php esc_html_e( '✅ Icon Menu animations apply instantly', 'NOVA-addons' ); ?></li>
							<li><?php esc_html_e( '✅ All changes are saved immediately', 'NOVA-addons' ); ?></li>
							<li><?php esc_html_e( '💡 Clear cache to see updates', 'NOVA-addons' ); ?></li>
						</ul>
					</div>

					<!-- Support Card -->
					<div class="NOVA-support-card">
						<h3><?php esc_html_e( '📚 Need Help?', 'NOVA-addons' ); ?></h3>
						<p><?php esc_html_e( 'Check the plugin documentation for more information.', 'NOVA-addons' ); ?></p>
						<a href="https://NOVA-addons.com/docs" target="_blank" class="button button-secondary">
							<?php esc_html_e( 'View Documentation', 'NOVA-addons' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>

		<style>
			.NOVA-settings-wrapper {
				background: #f5f5f5;
				margin: -20px -20px 0 -20px;
				padding: 20px;
			}

			.NOVA-settings-header {
				background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
				color: white;
				padding: 40px;
				border-radius: 8px;
				margin-bottom: 30px;
				box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
			}

			.NOVA-settings-header h1 {
				margin: 0;
				font-size: 32px;
				color: white;
			}

			.NOVA-subtitle {
				margin: 10px 0 0 0;
				font-size: 14px;
				opacity: 0.9;
			}

			.NOVA-settings-container {
				display: grid;
				grid-template-columns: 1fr 320px;
				gap: 20px;
			}

			.NOVA-settings-form {
				background: white;
				padding: 30px;
				border-radius: 8px;
				box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
			}

			.NOVA-form .settings-section {
				margin-bottom: 30px;
				padding-bottom: 30px;
				border-bottom: 1px solid #eee;
			}

			.NOVA-form .settings-section:last-child {
				border-bottom: none;
			}

			.NOVA-form h2 {
				font-size: 18px;
				color: #333;
				margin: 0 0 10px 0;
			}

			.NOVA-form .form-table {
				margin-top: 15px;
			}

			.NOVA-form .form-table th {
				font-weight: 600;
				padding: 15px 0;
				text-align: left;
			}

			.NOVA-form .form-table td {
				padding: 15px 0;
			}

			.NOVA-color-field,
			.NOVA-duration-input,
			.NOVA-delay-input,
			.NOVA-select {
				padding: 8px 12px;
				border: 1px solid #ccc;
				border-radius: 4px;
				font-size: 13px;
			}

			.NOVA-color-field {
				width: 100%;
				max-width: 400px;
			}

			.NOVA-duration-input,
			.NOVA-delay-input {
				width: 100px;
			}

			.NOVA-select {
				min-width: 100%;
			}

			.NOVA-input-group {
				display: flex;
				align-items: center;
				gap: 8px;
			}

			.NOVA-input-suffix {
				font-size: 13px;
				color: #666;
				min-width: 60px;
			}

			.NOVA-form .description {
				margin: 8px 0 0 0;
				font-size: 12px;
				color: #666;
			}

			.NOVA-form .description code {
				background: #f5f5f5;
				padding: 2px 4px;
				border-radius: 3px;
				font-size: 11px;
			}

			/* Sidebar */
			.NOVA-settings-sidebar {
				display: flex;
				flex-direction: column;
				gap: 20px;
			}

			.NOVA-preview-card,
			.NOVA-info-card,
			.NOVA-support-card {
				background: white;
				padding: 20px;
				border-radius: 8px;
				box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
			}

			.NOVA-preview-card h3,
			.NOVA-info-card h3,
			.NOVA-support-card h3 {
				margin: 0 0 15px 0;
				font-size: 14px;
				font-weight: 600;
				color: #333;
			}

			.NOVA-loader-preview {
				height: 120px;
				border-radius: 6px;
				display: flex;
				align-items: center;
				justify-content: center;
				color: white;
				font-size: 12px;
				text-align: center;
				padding: 15px;
				margin-bottom: 15px;
				font-weight: 500;
				box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
			}

			.NOVA-preview-info {
				margin: 0;
				font-size: 12px;
				color: #666;
			}

			.NOVA-info-list {
				list-style: none;
				margin: 0;
				padding: 0;
				font-size: 13px;
			}

			.NOVA-info-list li {
				padding: 8px 0;
				color: #666;
			}

			.NOVA-support-card p {
				margin: 0 0 15px 0;
				font-size: 12px;
				color: #666;
			}

			.NOVA-support-card .button {
				width: 100%;
				text-align: center;
			}

			/* Responsive */
			@media (max-width: 768px) {
				.NOVA-settings-container {
					grid-template-columns: 1fr;
				}

				.NOVA-settings-header {
					padding: 30px;
				}

				.NOVA-settings-header h1 {
					font-size: 24px;
				}

				.NOVA-settings-form {
					padding: 20px;
				}
			}
		</style>
		<?php
	}
}

// Initialize
Nova_Addons_Settings::init();






