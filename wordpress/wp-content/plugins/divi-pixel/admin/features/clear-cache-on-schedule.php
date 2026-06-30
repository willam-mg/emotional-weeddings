<?php

namespace DiviPixel;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('DIPI_Cache_Clearance')) {
    class DIPI_Cache_Clearance
    {
        private static $_instance;

        private $hook = 'dipi_clear_cache';
        public static function instance()
        {
            if (self::$_instance == null) {
                self::$_instance = new self();
                self::$_instance->init();
            }

            return self::$_instance;
        }

        private function init()
        {
            $enable_clear_cache_on_wp_hook = DIPI_Settings::get_option('enable_clear_cache_on_wp_hook');
            if ($enable_clear_cache_on_wp_hook) {
                $this->clear_on_wp_hooks();
            }

            add_action('init', [$this, 'maybe_schedule_auto_clear_cache']);
            add_action($this->hook, [$this, 'clear_et_cache']);
        }

        public function clear_on_wp_hooks()
        {
            $clear_wp_hooks = DIPI_Settings::get_option('clear_cache_on_wp_hook');
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
            // Check Nonce
            if (isset($_POST['et_fb_helper_nonce']) && !wp_verify_nonce(sanitize_text_field($_POST['et_fb_helper_nonce']), 'et_fb_update_helper_assets_nonce')) {
                return;
            }
            // Update Cache
            if (isset($_POST['action']) && 'et_fb_update_builder_assets' === sanitize_text_field($_POST['action'])) {
                $this->clear_et_cache();
            }
        }

        public function maybe_schedule_auto_clear_cache()
        {
            $clear_cache_on_schedule = DIPI_Settings::get_option('enable_clear_cache_on_schedule');
            if ($clear_cache_on_schedule) {
                $clear_cache_schedule = DIPI_Settings::get_option('clear_cache_schedule');
                $clear_cache_schedule ? $clear_cache_schedule : 'daily';
                if (!wp_next_scheduled($this->hook)) {
                    wp_schedule_event(time(), $clear_cache_schedule, $this->hook);
                }
            } else {
                if (wp_next_scheduled($this->hook)) {
                    wp_clear_scheduled_hook($this->hook);
                }
            }
        }

        public function clear_et_cache()
        {
            if (class_exists('ET_Core_PageResource')) {
                \ET_Core_PageResource::remove_static_resources('all', 'all');
            }
        }
    }
}

DIPI_Cache_Clearance::instance();