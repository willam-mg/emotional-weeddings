<?php
namespace DiviPixel;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

if (!class_exists('DIPI_ClearDiviCache')) {
    class DIPI_ClearDiviCache
    {
        private static $instance = null;
        private $hook = 'dipi_clear_cache';

        private function __construct()
        {
        }

        public static function instance()
        {
            if (self::$instance === null) {
                self::$instance = new self();
                self::$instance->init();
            }
            return self::$instance;
        }

        private function init()
        {
            if (DIPI_Settings::get_option('show_clear_divi_cache_in_adminbar')) {
                add_action('admin_bar_menu', [$this, 'add_admin_bar_menu'], 999);
                add_action('admin_footer', [$this, 'enqueue_admin_scripts']);
                add_action('wp_footer', [$this, 'enqueue_admin_scripts']);
                add_action('wp_ajax_dipi_clear_static_css', [$this, 'handle_ajax_request']);
            }

            add_action('init', [$this, 'maybe_schedule_auto_clear_cache']);
            add_action($this->hook, [$this, 'clear_et_cache']);

            if (DIPI_Settings::get_option('enable_clear_cache_on_wp_hook')) {
                $this->clear_on_wp_hooks();
            }
        }

        public function add_admin_bar_menu($admin_bar)
        {
            if (
                !DIPI_Settings::get_option('show_clear_divi_cache_in_adminbar') ||
                (DIPI_Settings::get_option('show_clear_divi_cache_in_adminbar_only_admin') && !current_user_can('administrator'))
            ) {
                return;
            }

            $admin_bar->add_menu([
                'id' => 'dipi_csc',
                'title' => sprintf(
                    '<span class="ab-icon"></span><span class="ab-label">%s</span>',
                    esc_html__('Clear Divi Cache', 'dipi-divi-pixel')
                ),
                'href' => '',
                'meta' => [
                    'title' => '',
                ],
            ]);

            $admin_bar->add_menu([
                'id' => 'dipi_clear_static_css',
                'parent' => 'dipi_csc',
                'title' => sprintf(
                    '<span data-wpnonce="%1$s">%2$s</span>',
                    wp_create_nonce('dipi_clear_static_css'),
                    esc_html__('Clear Static CSS File Generation', 'dipi-divi-pixel')
                ),
                'href' => 'javascript:void(0)',
            ]);

            $admin_bar->add_menu([
                'id' => 'dipi_csc_clear_local_storage',
                'parent' => 'dipi_csc',
                'title' => esc_html__('Clear Local Storage', 'dipi-divi-pixel'),
                'href' => 'javascript:void(0)',
            ]);
        }

        public function enqueue_admin_scripts()
        {
            // Only load for logged-in users
            if (!is_user_logged_in()) {
                return;
            }

            // If "only admin" option is enabled, check if user is administrator
            if (
                DIPI_Settings::get_option('show_clear_divi_cache_in_adminbar_only_admin') &&
                !current_user_can('administrator')
            ) {
                return;
            }

            wp_enqueue_script('DIPI_ClearDiviCache', plugins_url('dist/admin/js/ClearDiviCache.min.js', constant('DIPI_PLUGIN_FILE')), ['jquery'], constant('DIPI_VERSION'), true);

            wp_localize_script(
                'DIPI_ClearDiviCache',
                'dipi_cache_data',
                array(
                    'adminAjaxURL' => esc_url(admin_url('admin-ajax.php')),
                    'isAdmin' => is_admin(),
                )
            );
        }

        public function handle_ajax_request()
        {
            if (check_ajax_referer('dipi_clear_static_css', '_wpnonce')) {
                $this->clear_et_cache();
                wp_send_json_success(esc_html__('The static CSS file generation has been cleared!', 'dipi-divi-pixel'));
            } else {
                wp_send_json_error(esc_html__('Invalid nonce.', 'dipi-divi-pixel'), 403);
            }
        }

        public function clear_on_wp_hooks()
        {
            $clear_wp_hooks = DIPI_Settings::get_option('clear_cache_on_wp_hook');
            if(!is_array($clear_wp_hooks))
                return;
            if (in_array('after_plugin_update', $clear_wp_hooks) || in_array('after_theme_update', $clear_wp_hooks)) {
                add_action('upgrader_process_complete', [$this, 'clear_et_cache'], 999, 2);
            }
            if (in_array('after_vb_exits', $clear_wp_hooks)) {
                add_action('init', [$this, 'maybe_divi_builder_exit'], 999);
            }
            if (in_array('after_post_save', $clear_wp_hooks)) {
                add_action('save_post', [$this, 'clear_et_cache'], 999, 2);
            }
        }

        public function maybe_divi_builder_exit()
        {
            if (
                isset($_POST['et_fb_helper_nonce']) && wp_verify_nonce(sanitize_text_field($_POST['et_fb_helper_nonce']), 'et_fb_update_helper_assets_nonce') &&
                isset($_POST['action']) && 'et_fb_update_builder_assets' === sanitize_text_field($_POST['action'])
            ) {
                $this->clear_et_cache();
            }
        }

        public function maybe_schedule_auto_clear_cache()
        {
            $clear_cache_on_schedule = DIPI_Settings::get_option('enable_clear_cache_on_schedule');
            $clear_cache_schedule = DIPI_Settings::get_option('clear_cache_schedule') ?: 'daily';

            if ($clear_cache_on_schedule) {
                if (!wp_next_scheduled($this->hook)) {
                    wp_schedule_event(time(), $clear_cache_schedule, $this->hook);
                }
            } else {
                wp_clear_scheduled_hook($this->hook);
            }
        }

        public function clear_et_cache()
        {
            if (class_exists('ET_Core_PageResource')) {
                \ET_Core_PageResource::remove_static_resources('all', 'all');
            }
        }
    }

    DIPI_ClearDiviCache::instance();
}
