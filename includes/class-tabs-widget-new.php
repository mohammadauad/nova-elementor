<?php
// Version simplifiée avec un seul panneau et contenu dynamique
protected function render_single_panel()
{
    $settings = $this->get_settings_for_display();
    $tabs = $settings['tabs'];
    $active = max(1, (int) $settings['active_tab']) - 1;
    $widget_id = $this->get_id();
    $progress_enabled = ! empty( $settings['tabs_progress_bar'] ) && $settings['tabs_progress_bar'] === 'yes';

    if (empty($tabs))
        return;

    // Préparer les données des tabs pour le JS
    $tabs_data = [];
    foreach ($tabs as $i => $tab) {
        $tab_data = [
            'index' => $i,
            'title' => $tab['tab_title'],
            'icon' => !empty($tab['tab_icon']['value']) ? $tab['tab_icon'] : null,
            'text1' => !empty($tab['content_text_1']) ? wp_kses_post($tab['content_text_1']) : '',
            'text2' => !empty($tab['content_text_2']) ? wp_kses_post($tab['content_text_2']) : '',
            'link_url' => !empty($tab['content_link']['url']) ? $tab['content_link']['url'] : '',
            'link_text' => !empty($tab['content_link_text']) ? $tab['content_link_text'] : '',
            'images' => [],
            'content_icon' => !empty($tab['content_icon']['value']) ? $tab['content_icon'] : null,
        ];

        // Images
        if (!empty($tab['content_image'])) {
            foreach ($tab['content_image'] as $img) {
                if (!empty($img['url'])) {
                    $caption = '';
                    if (!empty($img['id'])) {
                        $attachment = get_post($img['id']);
                        if ($attachment) {
                            $caption = trim($attachment->post_excerpt);
                            if (empty($caption)) {
                                $caption = trim($attachment->post_title);
                            }
                        }
                    }
                    $tab_data['images'][] = [
                        'url' => $img['url'],
                        'caption' => $caption,
                    ];
                }
            }
        }

        $tabs_data[] = $tab_data;
    }

    // Contenu fixe (une seule fois)
    $fixed_text_html = '';
    if ($settings['fixed_text_source'] === 'nova_title' && !empty($settings['selected_nova_title_widget_id'])) {
        $nova_title_id = $settings['selected_nova_title_widget_id'];
        $html = $this->render_nova_title_widget($nova_title_id);
        if (!empty($html)) {
            $html = '<div class="elementor-widget elementor-widget-nova-title" data-id="' . esc_attr($nova_title_id) . '"><div class="elementor-widget-container">' . $html . '</div></div>';
        }
        $fixed_text_html = '<div class="nova-tabs-fixed-text">' . $html . '</div>';
    } else if ($settings['fixed_text_source'] === 'elementor_template' && !empty($settings['selected_elementor_template_id'])) {
        $template_id = (int) $settings['selected_elementor_template_id'];
        $html = '';
        try {
            if (class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance && \Elementor\Plugin::$instance->frontend) {
                $html = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display($template_id);
            }
        } catch (\Exception $e) {
            $html = '';
        }
        if (!empty($html)) {
            $fixed_text_html = '<div class="nova-tabs-fixed-text nova-tabs-fixed-text--template" data-template-id="' . esc_attr($template_id) . '">' . $html . '</div>';
        }
    } else if (!empty($settings['global_fixed_text'])) {
        $fixed_text_html = '<div class="nova-tabs-fixed-text">' . wp_kses_post($settings['global_fixed_text']) . '</div>';
    }

    $tabs_autoplay = ! empty( $settings['tabs_autoplay'] ) && $settings['tabs_autoplay'] === 'yes';
    $tabs_autoplay_delay = isset( $settings['tabs_autoplay_delay'] ) && is_numeric( $settings['tabs_autoplay_delay'] ) ? (int) $settings['tabs_autoplay_delay'] : 5000;
    $tabs_autoplay_delay = max( 500, $tabs_autoplay_delay );
    $tabs_autoplay_loop = ! empty( $settings['tabs_autoplay_loop'] ) && $settings['tabs_autoplay_loop'] === 'yes';
    ?>
    <div class="nova-tabs-widget nova-tabs-dynamic"
        id="nova-tabs-<?php echo esc_attr($widget_id); ?>"
        data-tabs-autoplay="<?php echo $tabs_autoplay ? '1' : '0'; ?>"
        data-tabs-autoplay-delay="<?php echo esc_attr( $tabs_autoplay_delay ); ?>"
        data-tabs-autoplay-loop="<?php echo $tabs_autoplay_loop ? '1' : '0'; ?>"
        data-tabs-progress="<?php echo $progress_enabled ? '1' : '0'; ?>"
        data-active-tab="<?php echo esc_attr($active); ?>"
        data-tabs-json="<?php echo esc_attr(json_encode($tabs_data)); ?>">

        <!-- Navigation -->
        <div class="nova-tabs-nav" role="tablist">
            <?php foreach ($tabs as $i => $tab):
                $is_active = ($i === $active);
                ?>
                <button class="nova-tab-btn<?php echo $is_active ? ' nova-tab-active' : ''; ?>" role="tab"
                    aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
                    data-tab="<?php echo esc_attr($i); ?>">
                    <?php if (!empty($tab['tab_icon']['value'])): ?>
                        <span class="nova-tab-btn-icon">
                            <?php Icons_Manager::render_icon($tab['tab_icon'], ['aria-hidden' => 'true']); ?>
                        </span>
                    <?php endif; ?>
                    <span class="nova-tab-btn-text"><?php echo esc_html($tab['tab_title']); ?></span>
                    <?php if ( $progress_enabled ) : ?>
                        <span class="nova-tab-progress" aria-hidden="true"><span class="nova-tab-progress-fill" aria-hidden="true"></span></span>
                    <?php endif; ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Body: Fixed + Single Panel -->
        <div class="nova-tabs-body<?php echo ($settings['panel_layout_type'] === '2_col' && !empty($fixed_text_html)) ? ' nova-tabs-has-fixed' : ''; ?>">
            <?php if (!empty($fixed_text_html)) : ?>
                <?php echo $fixed_text_html; ?>
            <?php endif; ?>

            <!-- Single Dynamic Panel -->
            <div class="nova-tabs-content">
                <div class="nova-tab-panel nova-tab-panel-active" role="tabpanel">
                    <div class="nova-tab-panel-inner" <?php echo ($settings['panel_layout_type'] === '2_col') ? 'style="display: flex; flex-direction: row;"' : ''; ?>>
                        <!-- Content injected by JS -->
                    </div>
                </div>
            </div>
        </div>

    </div>
    <?php
}
