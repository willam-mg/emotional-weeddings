<?php
namespace DiviPixel;

/**
 * The DIPI_Settings class is a utility class which declares all the setings fields and provides utility functions to
 * access those settings throughout the plugin.
 */
if (!class_exists('DIPI_Settings')) {
    class DIPI_Settings
    {
        private static $isEDD = false;
        private static $instance = null;
        private static $settings_prefix = 'dipi_';

        // Internal caches to reduce processing time
        private $tabs;
        private $sections;
        private $toggles;
        private $fields;
        private $pages;
        private $menus;
        private $schedules;
        private $popups;

        /**
         * Settings instance
         *
         * @since 1.6.0
         * @return DIPI_Settings
         */
        public static function instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }

          
            return self::$instance;
            
        }

        public static function settings_prefix()
        {
            return self::$settings_prefix;
        }

        public static function is_usable_license()
        {
            if (self::$isEDD) {
                $license_status = self::get_option('license_status');
                return $license_status === 'valid' || $license_status === 'expired';
            } else {
                return true;
            }
        }

        public static function admin_url($tab, $section = '', $toggle = '')
        {
            $args = [
                'dipi_tab' => $tab,
            ];

            if ($section != '') {
                $args['dipi_section'] = $section;
            }

            if ($toggle != 'dipi_toggle') {
                $args['dipi_toggle'] = $toggle;
            }

            return add_query_arg($args, admin_url('admin.php?page=divi_pixel_options'));
        }

        /**
         * Default function for retrieving Divi Pixel settings from the database
         *
         * This function uses WordPress get_option() to retrieve the Divi Pixel settings in an
         * unified way. Settings might be transformed and be different from whats stored inside
         * the database. Therefore if not using this function (which should almost alawys be used).
         * You have to apply possible transformations by yourself, for example transforming on/off
         * into true/false.
         *
         * Special transformations are done to settings fields of the following types:
         * - checkbox: on/off is transformed into true/false values which can directly be used in if
         *  statements
         * - everything else: return the value stored in the DB. If no value is stored, returns the
         *  default value. If no default value is defined, returns false
         * @param string $option The option to load.
         * @return mixed Option in an usable way. False if neither option nor default value is set
         */
        public static function get_option($option)
        {
            // Load fields and check if $option exists
            $fields = self::instance()->get_fields();
            if (!isset($fields[$option])) {
                dipi_info("DIPI_Settings::get_option() - Unknown Setting: " . $option);
                return false;
            }

            // Load value of $option
            $prefix = self::settings_prefix();
            $value = get_option("{$prefix}{$option}");
            $default = isset($fields[$option]['default']) ? $fields[$option]['default'] : null;
            if($default instanceof \Closure)
                $default = $default();

            switch ($fields[$option]['type']) {
                // Convert on/off to boolean value
                case 'checkbox':
                    if ('on' === $value) {
                        return true;
                    } else if ('off' === $value) {
                        return false;
                    } else if (!is_null($default)) {
                        return 'on' === $default;
                    } else {
                        return false;
                    }
                case 'select2':
                    if (!empty($value)) {
                        return $value;
                    } else if (!is_null($default)) {
                        return $default;
                    } else {
                        return [];
                    }
                default:
                    // By default, if $value is empty, return default if default is not null
                    if (!empty($value)) {
                        return $value;
                    } else if (!is_null($default)) {
                        return $default;
                    } else {
                        return false;
                    }
            }
        }

        public static function update_option($option, $value)
        {
            return update_option(self::settings_prefix() . $option, $value);
        }

        /**
         * Globale Getter
         */

        public static function get_mobile_menu_breakpoint()
        {
            if (!self::get_option('custom_breakpoints')) {
                return 980;
            }

            $breakpoint_mobile = self::get_option('breakpoint_mobile');
            if (!$breakpoint_mobile || !is_numeric($breakpoint_mobile)) {
                return 980;
            }

            return intval($breakpoint_mobile);
        }

        public function get_tabs()
        {
            if (null === $this->tabs) {
                $this->tabs = $this->create_tabs();
            }
            return $this->tabs;
        }

        public function get_sections()
        {
            if (null === $this->sections) {
                $this->sections = $this->create_sections();
            }
            return $this->sections;
        }

        public function get_toggles()
        {
            if (null === $this->toggles) {
                $this->toggles = $this->create_toggles();
            }
            return $this->toggles;
        }

        public function get_fields()
        {
            if (null === $this->fields) {
                $this->fields = $this->create_fields();
            }
            return $this->fields;
        }

        // FIXME: Wo wird das hier überhaupt benutzt?
        private function open_customize_url($type, $panel, $url)
        {
            $args = array(
                "autofocus[$type]" => $panel,
                'url' => rawurlencode(esc_url($url)),
            );
            return add_query_arg($args, admin_url('customize.php'));
        }

        private function create_tabs()
        {
            if (!self::is_usable_license()) {
                return [
                    'settings' => [
                        'label' => fn() => esc_html__('Settings', 'dipi-divi-pixel'),
                        'priority' => 70,
                        'icon_class' => 'dp-settings',
                    ],
                ];
            }

            return [
                'general' => [
                    'label' => fn() => esc_html__('General', 'dipi-divi-pixel'),
                    'priority' => 10,
                    'icon_class' => 'dp-settings',
                ],
                'blog' => [
                    'label' => fn() => esc_html__('Blog', 'dipi-divi-pixel'),
                    'priority' => 20,
                    'icon_class' => 'dp-blog',
                ],
                'social_media' => [
                    'label' => fn() => esc_html__('Social Media', 'dipi-divi-pixel'),
                    'priority' => 80,
                    'icon_class' => 'dp-share',
                ],
                'mobile' => [
                    'label' => fn() => esc_html__('Mobile', 'dipi-divi-pixel'),
                    'priority' => 30,
                    'icon_class' => 'dp-devices',
                ],
                'modules' => [
                    'label' => fn() => esc_html__('Modules', 'dipi-divi-pixel'),
                    'priority' => 40,
                    'icon_class' => 'dp-switches',
                ],
                'injector' => [
                    'label' => fn() => esc_html__('Layout Injector', 'dipi-divi-pixel'),
                    'priority' => 50,
                    'icon_class' => 'dp-layers',
                ],
                'settings' => [
                    'label' => fn() => esc_html__('Settings', 'dipi-divi-pixel'),
                    'priority' => 70,
                    'icon_class' => 'dp-settings',
                ],
                'import_export' => [
                    'label' => fn() => esc_html__('Import Export', 'dipi-divi-pixel'),
                    'priority' => 80,
                    'icon_class' => 'dp-settings',
                ],
            ];
        }

        private function create_sections()
        {
            if (!self::is_usable_license()) {
                return [
                    'settings_general' => [
                        'label' => fn() => esc_html__('General Settings', 'dipi-divi-pixel'),
                        'priority' => 10,
                        'tab' => 'settings',
                    ],
                ];
            }
            return [
                'general' => [
                    'label' => fn() => esc_html__('General Settings', 'dipi-divi-pixel'),
                    'priority' => 10,
                    'tab' => 'general',
                ],
                'header_navigation' => [
                    'label' => fn() => esc_html__('Header & Navigation', 'dipi-divi-pixel'),
                    'priority' => 30,
                    'tab' => 'general',
                ],
                'footer' => [
                    'label' => fn() => esc_html__('Footer', 'dipi-divi-pixel'),
                    'priority' => 40,
                    'tab' => 'general',
                ],
                'blog_general' => [
                    'label' => fn() => esc_html__('General Setting', 'dipi-divi-pixel'),
                    'priority' => 10,
                    'tab' => 'blog',
                ],
                'mobile_general' => [
                    'label' => fn() => esc_html__('General', 'dipi-divi-pixel'),
                    'priority' => 10,
                    'tab' => 'mobile',
                ],
                'mobile_menu' => [
                    'label' => fn() => esc_html__('Mobile Menu', 'dipi-divi-pixel'),
                    'priority' => 20,
                    'tab' => 'mobile',
                ],
                'popup_maker' => [
                    'label' => fn() => esc_html__('Popup Maker', 'dipi-divi-pixel'),
                    'priority' => 10,
                    'tab' => 'modules',
                ],
                'custom_modules' => [
                    'label' => fn() => esc_html__('Divi Modules', 'dipi-divi-pixel'),
                    'priority' => 20,
                    'tab' => 'modules',
                ],
                'navigation_inject' => [
                    'label' => fn() => esc_html__('Navigation', 'dipi-divi-pixel'),
                    'priority' => 10,
                    'tab' => 'injector',
                ],
                'footer_inject' => [
                    'label' => fn() => esc_html__('Footer', 'dipi-divi-pixel'),
                    'priority' => 20,
                    'tab' => 'injector',
                ],
                'blog_inject' => [
                    'label' => fn() => esc_html__('Blog', 'dipi-divi-pixel'),
                    'priority' => 30,
                    'tab' => 'injector',
                ],
                'error_page_inject' => [
                    'label' => fn() => esc_html__('404 Error Page', 'dipi-divi-pixel'),
                    'priority' => 40,
                    'tab' => 'injector',
                ],
                'settings_general' => [
                    'label' => fn() => esc_html__('General Settings', 'dipi-divi-pixel'),
                    'priority' => 10,
                    'tab' => 'settings',
                ],
                'social_media_general' => [
                    'label' => fn() => esc_html__('General', 'dipi-divi-pixel'),
                    'priority' => 10,
                    'tab' => 'social_media',
                ],
                'social_media_networks' => [
                    'label' => fn() => esc_html__('Networks', 'dipi-divi-pixel'),
                    'priority' => 10,
                    'tab' => 'social_media',
                ],
                'third_party_providers' => [
                    'label' => fn() => esc_html__('Third Party Providers', 'dipi-divi-pixel'),
                    'priority' => 10,
                    'tab' => 'settings',
                ],
                'import_export' => [
                    'label' => fn() => esc_html__('Import / Export', 'dipi-divi-pixel'),
                    'priority' => 10,
                    'tab' => 'import_export',
                ],
            ];
        }
        
        private function create_toggles()
        {
            $toggles = [];

            if (self::is_usable_license()) {
                /**
                 * General Tab
                 */

                //General Section
                $toggles += $this->create_toggle('general', 'general', 'particles');
                $toggles += $this->create_toggle('general', 'general', 'comingsoon');
                $toggles += $this->create_toggle('general', 'general', 'login_page');
                $toggles += $this->create_toggle('general', 'general', 'browser_scrollbar');
                $toggles += $this->create_toggle('general', 'general', 'svg_upload');
                $toggles += $this->create_toggle('general', 'general', 'ttf_upload');
                $toggles += $this->create_toggle('general', 'general', 'back_to_top');
                $toggles += $this->create_toggle('general', 'general', 'hide_projects');
                //$toggles += $this->create_toggle('general', 'general', 'custom_icons');
                $toggles += $this->create_toggle('general', 'general', 'hide_projects');
                $toggles += $this->create_toggle('general', 'general', 'hide_admin_bar');
                $toggles += $this->create_toggle('general', 'general', 'show_clear_divi_cache_in_adminbar');
                $toggles += $this->create_toggle('general', 'general', 'hide_library_shortcodes');
                $toggles += $this->create_toggle('general', 'general', 'hide_edit_in_vb');
                $toggles += $this->create_toggle('general', 'general', 'disable_conditional_module_display');
                $toggles += $this->create_toggle('general', 'general', 'custom_map_marker');
                $toggles += $this->create_toggle('general', 'general', 'custom_preloader');
                $toggles += $this->create_toggle('general', 'general', 'testimonials');
                //Header and Navigation Section
                $toggles += $this->create_toggle('general', 'header_navigation', 'menu_styles');
                $toggles += $this->create_toggle('general', 'header_navigation', 'header_underline');
                $toggles += $this->create_toggle('general', 'header_navigation', 'shrink_header');
                $toggles += $this->create_toggle('general', 'header_navigation', 'fixed_logo');
                $toggles += $this->create_toggle('general', 'header_navigation', 'zoom_logo');
                $toggles += $this->create_toggle('general', 'header_navigation', 'menu_styles');
                //Footer Section
                $toggles += $this->create_toggle('general', 'footer', 'footer_theme_customizer');
                $toggles += $this->create_toggle('general', 'footer', 'footer_customization');
                $toggles += $this->create_toggle('general', 'footer', 'hide_bottom_bar');
                $toggles += $this->create_toggle('general', 'footer', 'fixed_footer');
                $toggles += $this->create_toggle('general', 'footer', 'reveal_footer');

                /**
                 * Blog Tab
                 */
                $toggles += $this->create_toggle('blog', 'blog_general', 'blog_theme_customizer');
                $toggles += $this->create_toggle('blog', 'blog_general', 'custom_archive_page');
                $toggles += $this->create_toggle('blog', 'blog_general', 'blog_meta_icons');
                $toggles += $this->create_toggle('blog', 'blog_general', 'blog_hide_excerpt');
                $toggles += $this->create_toggle('blog', 'blog_general', 'remove_sidebar');
                $toggles += $this->create_toggle('blog', 'blog_general', 'remove_sidebar');
                $toggles += $this->create_toggle('blog', 'blog_general', 'read_more_archive');
                $toggles += $this->create_toggle('blog', 'blog_general', 'read_more_button');
                $toggles += $this->create_toggle('blog', 'blog_general', 'author_box');
                $toggles += $this->create_toggle('blog', 'blog_general', 'blog_nav');
                $toggles += $this->create_toggle('blog', 'blog_general', 'related_articles');
                $toggles += $this->create_toggle('blog', 'blog_general', 'custom_comments');

                /**
                 * Mobile Tab
                 */
                $toggles += $this->create_toggle('mobile', 'mobile_general', 'mobile_theme_customizer');
                $toggles += $this->create_toggle('mobile', 'mobile_general', 'custom_breakpoints');
                $toggles += $this->create_toggle('mobile', 'mobile_general', 'fixed_mobile_header');
                $toggles += $this->create_toggle('mobile', 'mobile_general', 'search_icon_mobile');
                $toggles += $this->create_toggle('mobile', 'mobile_general', 'mobile_logo');
                $toggles += $this->create_toggle('mobile', 'mobile_general', 'center_content');
                $toggles += $this->create_toggle('mobile', 'mobile_menu', 'mobile_menu_style');
                $toggles += $this->create_toggle('mobile', 'mobile_menu', 'hamburger_animation');
                $toggles += $this->create_toggle('mobile', 'mobile_menu', 'collapse_submenu');
                $toggles += $this->create_toggle('mobile', 'mobile_menu', 'mobile_cta_btn');

                /**
                 * Modules Tab
                 */
                $toggles += $this->create_toggle('modules', 'popup_maker', 'md_popup_maker');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'modules_theme_customizer');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_all_modules');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_accordion_image');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_advanced_tabs');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_balloon');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_before_after_slider');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_blog_slider');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_breadcrumbs');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_carousel');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_content_toggle');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_content_slider');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_countdown');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_counter');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_advanced_divider');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_dual_heading');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_expanding_cta');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_faq');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_fancy_text');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_filterable_gallery');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_filterable_grid');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_flip_box');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_floating_multi_images');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_gravity_styler');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_button_grid');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_horizontal_timeline');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_hover_box');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_hover_gallery');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_image_hotspot');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_image_magnifier');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_image_mask');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_image_rotator');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_image_showcase');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_gallery_slider');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_info_circle');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_lottie_icon');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_masonry_gallery');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_panorama');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_parallax_images');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_pricelist');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_pricing_table');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_reading_progress_bar');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_reveal');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_scroll_image');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_star_rating');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_svg_animator');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_table_maker');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_table_of_contents');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_testimonial');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_text_highlighter');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_tile_scroll');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_tilt_image');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_timeline');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_typing_text');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_accordion_slider');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_animated_blurb');
                $toggles += $this->create_toggle('modules', 'custom_modules', 'md_instagram');

                /**
                 * Injector Tab
                 */
                $toggles += $this->create_toggle('injector', 'navigation_inject', 'inject_theme_customizer');
                $toggles += $this->create_toggle('injector', 'navigation_inject', 'nav_injector');
                $toggles += $this->create_toggle('injector', 'footer_inject', 'footer_injector');
                $toggles += $this->create_toggle('injector', 'blog_inject', 'blog_injector');
                $toggles += $this->create_toggle('injector', 'error_page_inject', 'error_page');

            }

            /**
             * Settings Tab
             */
            $toggles += $this->create_toggle('settings', 'settings_general', 'settings_general_license');

            if (self::is_usable_license()) {
                $toggles += $this->create_toggle('settings', 'settings_general', 'settings_general_reset');
                $toggles += $this->create_toggle('settings', 'settings_general', 'beta_programm');
                $toggles += $this->create_toggle('settings', 'third_party_providers', 'settings_google_api');
                $toggles += $this->create_toggle('settings', 'third_party_providers', 'settings_facebook_api');
                // $toggles += $this->create_toggle('settings', 'third_party_providers', 'settings_instagram_api');
                // $toggles += $this->create_toggle('settings', 'third_party_providers', 'settings_instagram_api_basic');
                // $toggles += $this->create_toggle('settings', 'third_party_providers', 'settings_instagram_api_graph');
                /**
                 * Import/Export Tab
                 */
                $toggles += $this->create_toggle('import_export', 'import_export', 'export');
                $toggles += $this->create_toggle('import_export', 'import_export', 'import');
                $toggles += $this->create_toggle('import_export', 'import_export', 'layout_importer');

                /**
                 * Social Media Tab
                 */
                $toggles += $this->create_toggle('social_media', 'social_media_general', 'social_media_theme_customizer');
                $toggles += $this->create_toggle('social_media', 'social_media_general', 'social_media_general');
                $toggles += $this->create_toggle('social_media', 'social_media_networks', 'social_media_facebook');
                $toggles += $this->create_toggle('social_media', 'social_media_networks', 'social_media_instagram');
                $toggles += $this->create_toggle('social_media', 'social_media_networks', 'social_media_twitter');
                $toggles += $this->create_toggle('social_media', 'social_media_networks', 'social_media_youtube');
                $toggles += $this->create_toggle('social_media', 'social_media_networks', 'social_media_pinterest');
                $toggles += $this->create_toggle('social_media', 'social_media_networks', 'social_media_vimeo');
                $toggles += $this->create_toggle('social_media', 'social_media_networks', 'social_media_tumblr');
                $toggles += $this->create_toggle('social_media', 'social_media_networks', 'social_media_linkedin');
                $toggles += $this->create_toggle('social_media', 'social_media_networks', 'social_media_flickr');
                $toggles += $this->create_toggle('social_media', 'social_media_networks', 'social_media_dribbble');
                $toggles += $this->create_toggle('social_media', 'social_media_networks', 'social_media_skype');
                $toggles += $this->create_toggle('social_media', 'social_media_networks', 'social_media_google');
                $toggles += $this->create_toggle('social_media', 'social_media_networks', 'social_media_xing');
                $toggles += $this->create_toggle('social_media', 'social_media_networks', 'social_media_whatsapp');
                $toggles += $this->create_toggle('social_media', 'social_media_networks', 'social_media_snapchat');
                $toggles += $this->create_toggle('social_media', 'social_media_networks', 'social_media_soundcloud');
                $toggles += $this->create_toggle('social_media', 'social_media_networks', 'social_media_tiktok');
                $toggles += $this->create_toggle('social_media', 'social_media_networks', 'social_media_telegram');
                $toggles += $this->create_toggle('social_media', 'social_media_networks', 'social_media_line');
                $toggles += $this->create_toggle('social_media', 'social_media_networks', 'social_media_quora');
                $toggles += $this->create_toggle('social_media', 'social_media_networks', 'social_media_tripadvisor');
                $toggles += $this->create_toggle('social_media', 'social_media_networks', 'social_media_twitch');
                $toggles += $this->create_toggle('social_media', 'social_media_networks', 'social_media_yelp');
                $toggles += $this->create_toggle('social_media', 'social_media_networks', 'social_media_spotify');
            }

            return $toggles;
        }

        /**
         * Use this function to create a toggle
         *
         * @since 1.0
         *
         * @param string $tab This is the tab, the toggle will be added to
         * @param string $section This is the section, the toggle will be added to
         * @param string $toggle This is the id of the toggle that will be added
         */
        private function create_toggle($tab, $section, $toggle)
        {
            return [
                $toggle => [
                    'tab' => $tab,
                    'section' => $section,
                ],
            ];
        }

        private function create_fields()
        {
            $fields = [];
            $fields += $this->create_general_tab_fields();
            $fields += $this->create_settings_tab_fields();
            $fields += $this->create_social_media_tab_fields();
            $fields += $this->create_blog_tab_fields();
            $fields += $this->create_mobile_tab_fields();
            $fields += $this->create_modules_tab_fields();
            $fields += $this->create_injector_tab_fields();
            return $fields;
        }

        private function create_general_tab_fields()
        {
            return [
                // sample fields code to remove //
                // 'test_image' => [
                //     'label' => fn() => esc_html__('Image Option Test', 'dipi-divi-pixel'),
                //     'description' => fn() => esc_html__('Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.', 'dipi-divi-pixel'),
                //     'type' => 'file_upload',
                //     'file_type' => 'image',
                //     'tab' => 'general',
                //     'section' => 'general',
                //     'toggle' => 'test',
                //     'new' => true,
                // ],
                //'test_text' => [
                //    'label' => fn() => esc_html__('Text Option Test', 'dipi-divi-pixel'),
                //    'description' => fn() => sprintf(esc_html__('My template with %1$s. Cool, eh?', 'dipi-divi-pixel'), sprintf('<a href="customize.php?autofocus[section]=dipi_customizer_section_preloader" target="_blank">%1$s</a>', esc_html__('Link', 'dipi-divi-pixel'))),
                //   'type' => 'text',
                //    'tab' => 'general',
                //    'section' => 'general',
                //    'toggle' => 'test',
                //    'default' => 'some default text',
                //],
                // 'test_library_layout' => [
                //     'label' => fn() => esc_html__('Library Layout Option Test', 'dipi-divi-pixel'),
                //     'description' => fn() => esc_html__('Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.', 'dipi-divi-pixel'),
                //     'type' => 'library_layout',
                //     'tab' => 'general',
                //     'section' => 'general',
                //     'toggle' => 'test',
                //     'coming_soon' => true,
                //     'default' => '3897',
                // ],
                // 'test_checkbox' => [
                //     'label' => fn() => esc_html__('Checkbox Option Test', 'dipi-divi-pixel'),
                //     'description' => fn() => esc_html__('Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.', 'dipi-divi-pixel'),
                //     'type' => 'checkbox',
                //     'tab' => 'general',
                //     'section' => 'general',
                //     'toggle' => 'test',
                //     'default' => 'on',
                //     'options' => [
                //         'off' => 'aus',
                //         'on' => 'an',
                //     ],
                // ],
                // 'test_select' => [
                //     'label' => fn() => esc_html__('Select Option Test', 'dipi-divi-pixel'),
                //     'description' => fn() => esc_html__('Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.', 'dipi-divi-pixel'),
                //     'type' => 'select',
                //     'tab' => 'general',
                //     'section' => 'general',
                //     'toggle' => 'test',
                //     'default' => 'b',
                //     'options' => [
                //         'a' => fn() => esc_html__('Select A', 'dipi-divi-pixel'),
                //         'b' => fn() => esc_html__('Select B', 'dipi-divi-pixel'),
                //         'c' => fn() => esc_html__('Select C', 'dipi-divi-pixel'),
                //     ],
                // ],
                // 'test_settings_multiple_buttons' => [
                //     'label' => fn() => esc_html__('Multiple Buttons Option Title', 'dipi-divi-pixel'),
                //     'description' => fn() => esc_html__('Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.', 'dipi-divi-pixel'),
                //     'type' => 'multiple_buttons',
                //     'tab' => 'general',
                //     'section' => 'general',
                //     'toggle' => 'test',
                //     'class' => "some-test",
                //     'default' => 'style4',
                //     'options' => array(
                //         'style1' => [
                //             'image' => plugin_dir_url( __FILE__ ) . 'assets/ham-slider.gif',
                //         ],
                //         'style2' => [
                //             'image' => plugin_dir_url( __FILE__ ) . 'assets/ham-spring.gif',
                //         ],
                //         'style3' => [
                //             'image' => plugin_dir_url( __FILE__ ) . 'assets/ham-collapse.gif',
                //         ],
                //         'style4' => [
                //             'image' => plugin_dir_url( __FILE__ ) . 'assets/ham-vortex.gif',
                //         ],
                //         'style5' => [
                //             'icon' => 'dp-shield',
                //             'title' => fn() => esc_html__('Style 3', 'dipi-divi-pixel'),
                //             'description' => fn() => esc_html__('This is Style 2', 'dipi-divi-pixel'),
                //         ],
                //     ),
                // ],
                // 'test_theme_customizer' => [
                //     'label' => fn() => esc_html__('Theme Customizer Option Test', 'dipi-divi-pixel'),
                //     'description' => fn() => esc_html__('Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.', 'dipi-divi-pixel'),
                //     'type' => 'theme_customizer',
                //     'icon' => 'dp-divi-pixel',
                //     'panel' => 'dipi', //optional, if not set, use 'section' or go to theme customizer in general when neither is set
                //     // 'section' => 'dipi_btt_section_1', //optional, if not set, use 'panel' or go to theme customizer in general when neither is set
                //     'tab' => 'general',
                //     'section' => 'general',
                //     'toggle' => 'test',
                // ],
                //END
                'use_particles' => [
                    'label' => fn() => esc_html__('Enable Particles Background', 'dipi-divi-pixel'),
                    'description' => fn() => sprintf(
                        esc_html__('This options adds a moving particles background effect to any element, you can customize them using Divi Pixel %1$s', 'dipi-divi-pixel'),
                        sprintf(
                            '<a href="customize.php?autofocus[section]=dipi_customizer_section_particles" target="_blank">%1$s</a>', 
                                esc_html__('Theme Customizer', 'dipi-divi-pixel')
                            )
                        ),
                   'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'particles'
                ],
                'use_particles_note' => [
                    'description' => fn() => $this->description_with_info_box(
                        '',
                        esc_html__('To add moving particles as a background, please navigate to the section or module settings and add the following class under Advanced → CSS ID & Classes → CSS ID:', 'dipi-divi-pixel'),
                        'dipi-particles-1 or dipi-particles-2',
                        'dipi-note'
                    ),
                    'label' => '',
                    'type' => 'callback',
                    'callback' => [$this, 'callback_notice_field'],
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'particles',
                    'show_if' => [
                        'use_particles' => "on",
                    ],
                ],
                'use_coming_soon' => [
                    'label' => fn() => esc_html__('Enable Maintenance Mode', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to turn on maintenance mode. When this option is enabled, logged out users will see the page you have selected below.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'comingsoon',
                ],
                'coming_soon_page' => [
                    'label' => fn() => esc_html__('Select Coming Soon Page', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('Custom URL', 'dipi-divi-pixel'),
                    'type' => 'select',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'comingsoon',
                    'options' => $this->get_pages(),
                    'show_if' => [
                        'use_coming_soon' => "on",
                    ],
                ],
                'coming_soon_vip' => [
                    'label' => fn() => esc_html__('VIP Url', 'dipi-divi-pixel'),
                     
                    'description' => fn() => $this->description_with_info_box(
                        esc_html__('Use the generated URL link OR provide your own unique URL slug. For example, “client-login” or “client-portal-login”. Anyone who visits this URL will get access to view the site.', 'dipi-divi-pixel'),
                        esc_html__('IMPORTANT: This should NOT be a real URL on your site. Make sure to click the “Save Changes” button to save the new URL before sharing with anyone.', 'dipi-divi-pixel') 
                         
                    ),
                    'type' => 'callback',
                    'callback' => [$this, 'callback_comingsoon_vip'],
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'comingsoon',
                    'show_if' => [
                        'use_coming_soon' => "on",
                    ],
                ],               
                'login_page' => [
                    'label' => fn() => esc_html__('Custom Login Page', 'dipi-divi-pixel'),
                    'description' => fn() => sprintf(
                        esc_html__('Enable this option to customize login page. You can change logo, style form, login button and more. To customize login page go to Divi Pixel %1$s', 'dipi-divi-pixel'), 
                        sprintf(
                            '<a href="customize.php?autofocus[section]=dipi_customizer_section_login_page&url=%2$s" target="_blank">%1$s</a>', 
                            esc_html__('Theme Customizer', 'dipi-divi-pixel'), 
                            rawurlencode(wp_login_url())
                            )
                        ),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'login_page'
                ],

                'login_page_link' => [
                    'label' => fn() => esc_html__('Custom Logo Url', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('Custom URL', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'login_page',
                    'show_if' => [
                        'login_page' => "on",
                    ],
                ],

                'browser_scrollbar' => [
                    'label' => fn() => esc_html__('Custom Browser Scrollbar', 'dipi-divi-pixel'),
                    //'description' => fn() => esc_html__('Enable this option to customize browser scrollbar', 'dipi-divi-pixel'),
                    'description' => fn() => sprintf(
                        esc_html__('Enable this option to customize browser scrollbar. You can change style and colors in %1$s', 'dipi-divi-pixel'), 
                        sprintf(
                            '<a href="customize.php?autofocus[section]=dipi_customizer_section_browser_scrollbar" target="_blank">%1$s</a>', 
                            esc_html__('Theme Customizer', 'dipi-divi-pixel')
                        )
                    ),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'browser_scrollbar',
                ],

                'svg_upload' => [
                    'label' => fn() => esc_html__('Allow SVG Uploads', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to allow SVG files upload', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'svg_upload',
                ],
                'ttf_upload' => [
                    'label' => fn() => esc_html__('Allow TTF, OTF and WOFF Uploads', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to allow TTF, OTF and WOFF font files upload', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'ttf_upload',
                ],
                'back_to_top' => [
                    'label' => fn() => esc_html__('Customize Back To Top Button', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to customize Back to Top Button.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'back_to_top',
                ],
                'btt_button_style' => [
                    'label' => fn() => esc_html__('Select Button Style', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Select Back To Top button style.', 'dipi-divi-pixel'),
                    'type' => 'select',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'back_to_top',
                    'default' => 'display_icon',
                    'options' => [
                        'display_icon' => fn() => esc_html__('Display Icon (Default)', 'dipi-divi-pixel'),
                        'display_text' => fn() => esc_html__('Display Text', 'dipi-divi-pixel'),
                        'display_text_icon' => fn() => esc_html__('Display Text with Icon', 'dipi-divi-pixel'),
                    ],
                    'show_if' => [
                        'back_to_top' => "on",
                    ],
                ],
                'btt_custom_link' => [
                    'label' => fn() => esc_html__('Custom Back To Top Link', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to define custom back to top button link.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'back_to_top',
                    'show_if' => [
                        'back_to_top' => "on",
                    ],
                ],
                'btt_link' => [
                    'label' => fn() => esc_html__('Custom Back To Top Button URL', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('https://www.example.com', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'back_to_top',
                    'show_if' => [
                        'btt_custom_link' => "on",
                        'back_to_top' => "on",
                    ],
                ],
                'btt_theme_customizer' => [
                    'label' => fn() => esc_html__('Customize Back To Top Button', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Use Divi Pixel Customizer to custimize Back To Top button. Change style, color and icon and make your button unique with ease!', 'dipi-divi-pixel'),
                    'type' => 'theme_customizer',
                    'panel' => 'dipi_btt',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'back_to_top',
                    'customizer_section' => 'back_to_top',
                    'show_if' => [
                        'back_to_top' => "on",
                    ],
                ],
                'hide_projects' => [
                    'label' => fn() => esc_html__('Hide Projects Custom Post Type', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Projects tab in WP Dashboard', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'hide_projects',
                ],
                'rename_projects' => [
                    'label' => fn() => esc_html__('Rename Projects Custom Post Type', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to rename Projects tab in WP Dashboard', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'hide_projects',
                    'show_if' => [
                        'hide_projects' => "off",
                    ],
                ],
                'rename_projects_singular' => [
                    'label' => fn() => esc_html__('Singular Name', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('Project', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'hide_projects',
                    'show_if' => [
                        'rename_projects' => "on",
                        'hide_projects' => "off",
                    ],
                ],
                'rename_projects_plural' => [
                    'label' => fn() => esc_html__('Plural Name', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('Projects', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'hide_projects',
                    'show_if' => [
                        'rename_projects' => "on",
                        'hide_projects' => "off",
                    ],
                ],
                'rename_projects_slug' => [
                    'label' => fn() => esc_html__('Slug', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('projects', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'hide_projects',
                    'show_if' => [
                        'rename_projects' => "on",
                        'hide_projects' => "off",
                    ],
                ],
                'rename_projects_icon' => [
                    'label' => fn() => esc_html__('Dashboard Icon', 'dipi-divi-pixel'),
                    'description' => fn() => sprintf(
                        '%1$s <a href="%2$s" target="blank">%3$s</a> %4$s.',
                        esc_html__('Enter the name of the WordPress Dashicon, you can see the list of available icons on', 'dipi-divi-pixel'),
                        esc_url('https://developer.wordpress.org/resource/dashicons/'),
                        esc_html__('this', 'dipi-divi-pixel'),
                        esc_html__('page', 'dipi-divi-pixel')
                    ),
                    'placeholder' => fn() => esc_html__('dashicons-admin-post', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'hide_projects',
                    'show_if' => [
                        'rename_projects' => "on",
                        'hide_projects' => "off",
                    ],
                ],
                'rename_projects_cat_slug' => [
                    'label' => fn() => esc_html__('Category Slug Name', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('project_category', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'hide_projects',
                    'show_if' => [
                        'rename_projects' => "on",
                        'hide_projects' => "off",
                    ],
                ],
                'rename_projects_tag_slug' => [
                    'label' => fn() => esc_html__('Tag Archive Slug Name', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('project_tag', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'hide_projects',
                    'show_if' => [
                        'rename_projects' => "on",
                        'hide_projects' => "off",
                    ],
                ],
                'rename_projects_notice' => [
                    'label' => fn() => esc_html__('Reseting Permalinks', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Save the options and reload your Dashboard to see the change. In some cases, you might need to re-save your Permalinks structure to get the changes applied to existing projects. To do this, head over to Settings > Permalinks and click the Save Changes button.', 'dipi-divi-pixel'),
                    'type' => 'theme_customizer',
                    'icon' => 'dp-layers',
                    'class' => 'first_customizer_field no_button',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'hide_projects',
                ],

                // Testimonial
                'rename_testimonials' => [
                    'label' => fn() => esc_html__('Rename Testimonials Custom Post Type', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to rename Testimonials tab in WP Dashboard', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'testimonials',
                ],
                'rename_testimonials_singular' => [
                    'label' => fn() => esc_html__('Singular Name', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('Testimonial', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'testimonials',
                    'show_if' => [
                        'rename_testimonials' => "on",
                    ],
                ],
                'rename_testimonials_plural' => [
                    'label' => fn() => esc_html__('Plural Name', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('Testimonials', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'testimonials',
                    'show_if' => [
                        'rename_testimonials' => "on",
                    ],
                ],
                'rename_testimonials_slug' => [
                    'label' => fn() => esc_html__('Slug', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('dipi_testimonial', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'testimonials',
                    'show_if' => [
                        'rename_testimonials' => "on",
                    ],
                ],

                'hide_library_shortcodes' => [
                    'label' => fn() => esc_html__('Hide Divi Library Shortcodes', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Hide the shortcode column in the Divi Library overview.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'hide_library_shortcodes',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                ],

                'disable_conditional_module_display' => [
                    'label' => fn() => esc_html__('Disable Conditional Display', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Disable the Conditional Display module functionality. This option allows you to show or hide sections, rows and modules based on the user login status or role.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'disable_conditional_module_display',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                ],

                'hide_edit_in_vb' => [
                    'label' => fn() => esc_html__('Hide "Edit in Visual Builder" Link', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Hide the "Edit in Visual Builder" link in the Post and Page overview.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'hide_edit_in_vb',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                ],

                'hide_admin_bar' => [
                    'label' => fn() => esc_html__('Hide Admin Bar', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Hide the admin bar while on the front-end and activate it by hovering over the top of the window.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'hide_admin_bar',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                ],
                'show_clear_divi_cache_in_adminbar' => [
                    'label' => fn() => esc_html__('Show Clear Divi Cache', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Show Clear Divi Cache menu in admin bar', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'show_clear_divi_cache_in_adminbar',
                    'default' => 'on',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                ],
                'show_clear_divi_cache_in_adminbar_only_admin' => [
                    'label' => fn() => esc_html__('Only Admin', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Show Clear Divi Cache menu in admin bar only admin', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'show_clear_divi_cache_in_adminbar',
                    'default' => 'on',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                    'show_if' => [
                        'show_clear_divi_cache_in_adminbar' => "on",
                    ],
                ],
                'enable_clear_cache_on_schedule' => [
                    'label' => fn() => esc_html__('Enable Clear Divi Cache on Schedule', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable clear static resources cache on a schedule', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'show_clear_divi_cache_in_adminbar',
                    'default' => 'on',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                ],
                'clear_cache_schedule' => [
                    'label' => fn() => esc_html__('Clear Divi Cache Schedule', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Select the recurrence pattern that triggers the clear static resources cache operation repeatedly.', 'dipi-divi-pixel'),
                    'type' => 'select',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'show_clear_divi_cache_in_adminbar',
                    'computed' => true,
                    'default' => 'daily',
                    'options' => [$this, 'get_schedules'],
                    'show_if' => [
                        'enable_clear_cache_on_schedule' => "on"
                    ],
                ],
                'enable_clear_cache_on_wp_hook' => [
                    'label' => fn() => esc_html__('Clear Divi Cache on WordPress Hook', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to fire clear static resources cache on the selected WordPress hook.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'show_clear_divi_cache_in_adminbar',
                    'default' => 'on',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                ],
                'clear_cache_on_wp_hook' => [
                    'label' => fn() => esc_html__('WordPress hook', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Select WordPress hook that triggers the clear static resources cache operation.', 'dipi-divi-pixel'),
                    'type' => 'select2',
                    'placeholder' => fn() => esc_html__('Select Hooks', 'dipi-divi-pixel'),
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'show_clear_divi_cache_in_adminbar',
                    'default' => ['after_post_save'],
                    'options' => [
                        'after_plugin_update' => fn() => esc_html__('After Plugin Update', 'dipi-divi-pixel'),  
                        'after_theme_update' => fn() => esc_html__('After Theme Update', 'dipi-divi-pixel'),
                        'after_vb_exits' => fn() => esc_html__('After Exiting Visual Builder', 'dipi-divi-pixel'),
                        'after_post_save' => fn() => esc_html__('After Post Save', 'dipi-divi-pixel')
                    ],
                    'show_if' => [
                        'enable_clear_cache_on_wp_hook' => "on"
                    ],
                ],
                'cache_notice' => [
                    'label' => fn() => esc_html__('Clear Divi Cache', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('If you encounter layout issues with Divi Library items on the front end, clearing the Divi cache is essential. Divi Pixel settings provide a convenient solution by allowing you to display “Clear Divi Cache” buttons directly in your WordPress admin bar. You can also automate cache clearing on a set schedule or trigger it after specific events, ensuring your Divi layouts always perform optimally.', 'dipi-divi-pixel'),
                    'type' => 'theme_customizer',
                    'icon' => 'dp-clear-cache',
                    'class' => 'first_customizer_field no_button',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'show_clear_divi_cache_in_adminbar',
                ],
                
                
                'custom_map_marker' => [
                    'label' => fn() => esc_html__('Add Custom Map Marker', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to add custom map marker.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'custom_map_marker',
                ],
                'upload_custom_marker' => [
                    'label' => fn() => esc_html__('Upload Custom Map Marker', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Select image that will be displayed on map.', 'dipi-divi-pixel'),
                    'type' => 'file_upload',
                    'file_type' => 'image',
                    'extension' => 'jpeg,png',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'custom_map_marker',
                    'show_if' => [
                        'custom_map_marker' => "on",
                    ],
                ],
                'custom_map_marker_anchor' => [
                    'label' => fn() => esc_html__('Map Marker Anchor', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('The anchor defines which part of the map marker is placed at the geo location. A default baloon like map marker for example would have its anchor at the bottom center of the image, where the pointy tip of the baloon is.', 'dipi-divi-pixel'),
                    'type' => 'select',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'custom_map_marker',
                    'default' => 'bottom_center',
                    'options' => [
                        'top_left' => fn() => esc_html__('Top Left', 'dipi-divi-pixel'),
                        'top_center' => fn() => esc_html__('Top Center', 'dipi-divi-pixel'),
                        'top_right' => fn() => esc_html__('Top Right', 'dipi-divi-pixel'),
                        'center_left' => fn() => esc_html__('Center Left', 'dipi-divi-pixel'),
                        'center_center' => fn() => esc_html__('Center', 'dipi-divi-pixel'),
                        'center_right' => fn() => esc_html__('Center Right', 'dipi-divi-pixel'),
                        'bottom_left' => fn() => esc_html__('Bottom Left', 'dipi-divi-pixel'),
                        'bottom_center' => fn() => esc_html__('Bottom Center', 'dipi-divi-pixel'),
                        'bottom_right' => fn() => esc_html__('Bottom Right', 'dipi-divi-pixel'),
                    ],
                    'show_if' => [
                        'custom_map_marker' => "on",
                    ],
                ],
                'custom_preloader' => [
                    'label' => fn() => esc_html__('Add Preloader', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to add preloader to your website.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'custom_preloader',
                ],
                'custom_preloader_style' => [
                    'label' => fn() => esc_html__('Select Preloader', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Select preloader you would like to display on your website.', 'dipi-divi-pixel'),
                    'type' => 'preloaders',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'custom_preloader',
                    'class' => "dipi_preloaders",
                    'show_if' => [
                        'custom_preloader' => "on",
                        'custom_preloader_image' => "off",
                    ],
                ],
                'custom_preloader_image' => [
                    'label' => fn() => esc_html__('Upload Custom Preloader', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to upload custom preloader.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'custom_preloader',
                    'show_if' => [
                        'custom_preloader' => "on",
                    ],
                ],
                'upload_preloader' => [
                    'label' => fn() => esc_html__('Upload Custom Preloader', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Upload custom preloader image to be displayed on your website.', 'dipi-divi-pixel'),
                    'type' => 'file_upload',
                    'file_type' => 'image',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'custom_preloader',
                    'show_if' => [
                        'custom_preloader_image' => "on",
                        'custom_preloader' => "on",
                    ],
                ],
                'custom_preloader_homepage' => [
                    'label' => fn() => esc_html__('Display on Homepage Only', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to display preloader on homepage only.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'general',
                    'toggle' => 'custom_preloader',
                    'show_if' => [
                        'custom_preloader' => "on",
                    ],
                ],
                'preloader_customizer' => [
                    'label' => fn() => esc_html__('Customize Preloader in Divi Pixel Customizer', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('To customize your preloader size, colors go to Theme Customizer', 'dipi-divi-pixel'),
                    'type' => 'theme_customizer',
                    'customizer_section' => 'preloader',
                    'tab' => 'general',
                    'icon' => 'dp-preloader',
                    'section' => 'general',
                    'toggle' => 'custom_preloader',
                    'show_if' => [
                        'custom_preloader' => "on",
                    ],
                ],
                'menu_styles' => [
                    'label' => fn() => esc_html__('Customize Header & Navigation Styles', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to customize header & navigation styles.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'header_navigation',
                    'toggle' => 'menu_styles',
                ],
                'enable_menu_hover_styles' => [
                    'label' => fn() => esc_html__('Hover Animation', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to add custom hover animations to the main menu.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'header_navigation',
                    'toggle' => 'menu_styles',
                    'show_if' => [
                        'menu_styles' => "on",
                    ],
                ],
                'menu_hover_styles' => [
                    'label' => fn() => esc_html__('Select Animation Style', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Select hover animation style for your main menu links.', 'dipi-divi-pixel'),
                    'type' => 'menu_styles',
                    'class' => 'dipi_menu_styles',
                    'tab' => 'general',
                    'section' => 'header_navigation',
                    'toggle' => 'menu_styles',
                    'default' => 'three_dots',
                    'show_if' => [
                        'menu_styles' => "on",
                        'enable_menu_hover_styles' => "on",
                    ],
                    'options' => array(
                        'three_dots' => fn() => esc_html__('Three Dots', 'dipi-divi-pixel'),
                        'filled_background' => fn() => esc_html__('Filled Background', 'dipi-divi-pixel'),
                        'slide_up_below' => fn() => esc_html__('Slide Up Below', 'dipi-divi-pixel'),
                        'slide_down_below' => fn() => esc_html__('Slide Down Below', 'dipi-divi-pixel'),
                        'grow_below_left' => fn() => esc_html__('Grow Below Left', 'dipi-divi-pixel'),
                        'grow_below_center' => fn() => esc_html__('Grow Below Center', 'dipi-divi-pixel'),
                        'grow_below_right' => fn() => esc_html__('Grow Below Right', 'dipi-divi-pixel'),
                        'grow_above_and_below_left' => fn() => esc_html__('Grow Both Left', 'dipi-divi-pixel'),
                        'grow_above_and_below_center' => fn() => esc_html__('Grow Both Center', 'dipi-divi-pixel'),
                        'grow_above_and_below_right' => fn() => esc_html__('Grow Both Right', 'dipi-divi-pixel'),
                        'bracketed_out' => fn() => esc_html__('Bracketed Out', 'dipi-divi-pixel'),
                        'bracketed_in' => fn() => esc_html__('Bracketed In', 'dipi-divi-pixel'),

                    ),
                ],
                'custom_dropdown' => [
                    'label' => fn() => esc_html__('Custom Menu Dropdown', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to add custom menu dropdown styles and animation.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'header_navigation',
                    'toggle' => 'menu_styles',
                    'show_if' => [
                        'menu_styles' => "on",
                    ],
                ],

                'menu_button' => [
                    'label' => fn() => esc_html__('Add CTA Button to Main Menu', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to add custom button to main menu.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'header_navigation',
                    'toggle' => 'menu_styles',
                    'show_if' => [
                        'menu_styles' => "on",
                    ],
                ],

                // 'nav_specific_pages' => [
                //     'label' => fn() => esc_html__('Select Pages', 'dipi-divi-pixel'),
                //     'description' => fn() => esc_html__('Select specific pages to show custom navigation', 'dipi-divi-pixel'),
                //     'type' => 'select2',
                //     'tab' => 'injector',
                //     'section' => 'navigation_inject',
                //     'toggle' => 'nav_injector',
                //     'computed' => true,
                //     'options' => [$this, 'get_pages'],
                //     'show_if' => [
                //         'nav_layout_homepage' => "off",
                //         'nav_layout_custom' => "on",
                //     ],
                // ],
                'menu_cta_menu' => [
                    'label' => fn() => esc_html__('Select Menu', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Select specific menu to display CTA', 'dipi-divi-pixel'),
                    'type' => 'select2',
                    'tab' => 'general',
                    'section' => 'header_navigation',
                    'toggle' => 'menu_styles',
                    'computed' => true,
                    'options' => [$this, 'get_menus'],
                    'show_if' => [
                        'menu_styles' => "on",
                        'menu_button' => "on",
                    ],
                ],

                'menu_button_text' => [
                    'label' => fn() => esc_html__('Button Text', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('Button Text', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'general',
                    'default' => 'Click Here',
                    'section' => 'header_navigation',
                    'toggle' => 'menu_styles',
                    'show_if' => [
                        'menu_styles' => "on",
                        'menu_button' => "on",
                    ],
                ],

                'menu_button_url' => [
                    'label' => fn() => esc_html__('Button URL', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('https://www.example.com', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'general',
                    'default' => '#',
                    'section' => 'header_navigation',
                    'toggle' => 'menu_styles',
                    'show_if' => [
                        'menu_styles' => "on",
                        'menu_button' => "on",
                    ],
                ],

                'menu_button_placement' => [
                    'label' => fn() => esc_html__('Apply Menu Button', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Select place where to display menu button.', 'dipi-divi-pixel'),
                    'type' => 'select',
                    'tab' => 'general',
                    'section' => 'header_navigation',
                    'toggle' => 'menu_styles',
                    'default' => 'a',
                    'show_if' => [
                        'menu_styles' => "on",
                        'menu_button' => "on",
                    ],
                    'options' => [
                        'a' => fn() => esc_html__('Last Menu Item', 'dipi-divi-pixel'),
                        'b' => fn() => esc_html__('First Menu Item', 'dipi-divi-pixel'),
                    ],
                ],
                'menu_button_classname' => [
                    'label' => fn() => esc_html__('Button CSS Class', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'general',
                    'default' => '',
                    'section' => 'header_navigation',
                    'toggle' => 'menu_styles',
                    'show_if' => [
                        'menu_styles' => "on",
                        'menu_button' => "on",
                    ],
                ],
                'cta_btn_new_tab' => [
                    'label' => fn() => esc_html__('Open CTA Button in New Tab', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option if you want Menu Button to open in new tab.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'header_navigation',
                    'toggle' => 'menu_styles',
                    'show_if' => [
                        'menu_styles' => "on",
                        'menu_button' => "on",
                    ],
                ],
                'mobile_cta_btn' => [
                    'label' => fn() => esc_html__('Hide CTA Button on Mobiles', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option if you do not want to display Menu Button on mobiles.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'header_navigation',
                    'toggle' => 'menu_styles',
                    'show_if' => [
                        'menu_styles' => "on",
                        'menu_button' => "on",
                    ],
                ],

                'menu_customizer' => [
                    'label' => fn() => esc_html__('Customize Menu in Divi Pixel Customizer', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('To customize your main menu and menu dropdown go to Divi Pixel Customizer', 'dipi-divi-pixel'),
                    'type' => 'theme_customizer',
                    'customizer_panel' => 'header',
                    'tab' => 'general',
                    'icon' => 'dp-header',
                    'section' => 'header_navigation',
                    'toggle' => 'menu_styles',
                    'show_if' => [
                        'menu_styles' => "on",
                    ],
                ],
                'header_underline' => [
                    'label' => fn() => esc_html__('Remove Main Header Shadow', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to remove main header shadow.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'header_navigation',
                    'toggle' => 'header_underline',
                ],
                'shrink_header' => [
                    'label' => fn() => esc_html__('Do Not Shrink Header on Scroll', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to prevent header shrinking on scroll.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'header_navigation',
                    'toggle' => 'shrink_header',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                ],
                'fixed_logo' => [
                    'label' => fn() => esc_html__('Change Logo on Scroll', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to display different logo on fixed navigation.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'header_navigation',
                    'toggle' => 'fixed_logo',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                ],
                'fixed_logo_image' => [
                    'label' => fn() => esc_html__('Upload Fixed Menu Logo', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Select logo image that will be displayed after scroll.', 'dipi-divi-pixel'),
                    'type' => 'file_upload',
                    'file_type' => 'image',
                    'tab' => 'general',
                    'section' => 'header_navigation',
                    'toggle' => 'fixed_logo',
                    'show_if' => [
                        'fixed_logo' => "on",
                    ],
                ],
                'zoom_logo' => [
                    'label' => fn() => esc_html__('Zoom Logo on Hover', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to add zoom hover effect to logo.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'header_navigation',
                    'toggle' => 'zoom_logo',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                ],

                'footer_theme_customizer' => [
                    'label' => fn() => esc_html__('Customize Footer', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('You can customize footer bottom bar, footer menu and social media links in the Divi Pixel Theme Customizer.', 'dipi-divi-pixel'),
                    'type' => 'theme_customizer',
                    'icon' => 'dp-footer',
                    'class' => 'first_customizer_field',
                    'tab' => 'general',
                    'section' => 'footer',
                    'toggle' => 'footer_theme_customizer',
                    'customizer_panel' => 'footer',
                ],

                'footer_layout' => [
                    'label' => fn() => esc_html__('Use Custom Footer Layout', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Display Divi Library item as a footer', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'injector',
                    'section' => 'footer_inject',
                    'toggle' => 'footer_injector',
                ],

                'select_footer_layout' => [
                    'label' => fn() => esc_html__('Select footer layout', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('Divi Library Dropdown', 'dipi-divi-pixel'),
                    'type' => 'library_layout',
                    'tab' => 'injector',
                    'section' => 'footer_inject',
                    'toggle' => 'footer_injector',
                    'show_if' => [
                        'footer_layout' => "on",
                    ],
                ],

                'footer_customization' => [
                    'label' => fn() => esc_html__('Footer Customization', 'dipi-divi-pixel'),
                    'description' => fn() => sprintf(
                        esc_html__('Enable this option to customize footer bottom bar and footer menu in %1$s', 'dipi-divi-pixel'),
                        sprintf(
                            '<a href="customize.php?autofocus%%5Bpanel%%5D=dipi_footer" target="_blank">%1$s</a>',
                            esc_html__('Theme Customizer', 'dipi-divi-pixel')
                        )
                    ),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'footer',
                    'toggle' => 'footer_customization',
                ],

                'hide_bottom_bar' => [
                    'label' => fn() => esc_html__('Hide Footer Bottom Bar', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide bottom footer bar', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'footer',
                    'toggle' => 'hide_bottom_bar',
                ],

                'fixed_footer' => [
                    'label' => fn() => esc_html__('Force Footer to Bottom', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Keep footer fixed at bottom of page, even on pages with little content.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'footer',
                    'toggle' => 'fixed_footer',
                ],
                'reveal_footer' => [
                    'label' => fn() => esc_html__('Reveal Footer Effect', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable Footer Reveal Effect', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'general',
                    'section' => 'footer',
                    'toggle' => 'reveal_footer',
                ],
                'reveal_desktop' => [
                    'label' => fn() => esc_html__('Disable on Desktop', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option if you do not want to display the Footer Reveal effect on desktope devices.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                    'show_if' => [
                        'reveal_footer' => "on",
                    ],
                    'tab' => 'general',
                    'section' => 'footer',
                    'toggle' => 'reveal_footer',
                ],
                'reveal_tablet' => [
                    'label' => fn() => esc_html__('Disable on Tablet', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option if you do not want to display the Footer Reveal effect on tablet devices.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                    'show_if' => [
                        'reveal_footer' => "on",
                    ],
                    'tab' => 'general',
                    'section' => 'footer',
                    'toggle' => 'reveal_footer',
                ],
                'reveal_phone' => [
                    'label' => fn() => esc_html__('Disable on Phone', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option if you do not want to display the Footer Reveal effect on phone devices.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                    'show_if' => [
                        'reveal_footer' => "on",
                    ],
                    'tab' => 'general',
                    'section' => 'footer',
                    'toggle' => 'reveal_footer',
                ],

                'footer_reveal_posts_type' => [
                    'label' => fn() => esc_html__('Disable Posts Type', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Disable Footer Reveal Effect on Posts Type', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'show_if' => [
                        'reveal_footer' => "on",
                    ],
                    'tab' => 'general',
                    'section' => 'footer',
                    'toggle' => 'reveal_footer',
                ],

                'footer_reveal_pages_type' => [
                    'label' => fn() => esc_html__('Disable Pages Type', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Disable Footer Reveal Effect on Pages Type', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'show_if' => [
                        'reveal_footer' => "on",
                    ],
                    'tab' => 'general',
                    'section' => 'footer',
                    'toggle' => 'reveal_footer',
                ],

                'footer_reveal_projects_type' => [
                    'label' => fn() => esc_html__('Disable Projects Type', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Disable Footer Reveal Effect on Projects Type', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'show_if' => [
                        'reveal_footer' => "on",
                    ],
                    'tab' => 'general',
                    'section' => 'footer',
                    'toggle' => 'reveal_footer',
                ],

                'footer_reveal_testimonials_type' => [
                    'label' => fn() => esc_html__('Disable Testimonials Type', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Disable Footer Reveal Effect on Testimonials Type', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'show_if' => [
                        'reveal_footer' => "on",
                    ],
                    'tab' => 'general',
                    'section' => 'footer',
                    'toggle' => 'reveal_footer',
                ],

            ];
        }

        private function description_with_info_box($description, $info_text, $hint = '', $extra_class = '')
        {

            $hint = '' !== $hint ? sprintf('<span>%1$s</span>', $hint) : $hint;
            return sprintf(
                '%1$s
            <div class="dipi_settings_info_box %4$s">
                <span class="tab_icon dp-info"></span>
                <div>
                    <p>%2$s</p>
                    %3$s
                </div>
            </div>',
                $description,
                $info_text,
                $hint,
                $extra_class
            );
        }
        private function create_blog_tab_fields()
        {
            return [
                'blog_theme_customizer' => [
                    'label' => fn() => esc_html__('Customize Blog Archives', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('With Divi Pixel you can customize blog archives & categories pages. You can select blog layout, add Related Artciles, customize comments section and more! To customize your Blog archive page go to Theme Customizer.', 'dipi-divi-pixel'),
                    'type' => 'theme_customizer',
                    'icon' => 'dp-blog',
                    'class' => 'first_customizer_field',
                    'tab' => 'blog',
                    'section' => 'blog_general',
                    'toggle' => 'blog_theme_customizer',
                    'customizer_panel' => 'blog',
                    'target_url' => add_query_arg(['post_type' => 'post'], site_url()),
                ],
                'custom_archive_page' => [
                    'label' => fn() => esc_html__('Custom Archive Page Style ', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to set custom archive page style.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'blog',
                    'section' => 'blog_general',
                    'toggle' => 'custom_archive_page',
                ],
                'custom_archive_styles' => [
                    'label' => fn() => esc_html__('Select Archive Page Layout', 'dipi-divi-pixel'),
                    'new' => true,
                    'description' => fn() => $this->description_with_info_box(
                        esc_html__('Select layout for archive, categories, author, tags and search results pages.', 'dipi-divi-pixel'),
                        esc_html__('To apply the same customizations to Divis Blog module, please navigate to the module settings and add the following class under Advanced → CSS ID & Classes → CSS Class:', 'dipi-divi-pixel'),
                        'dipi-styled-blog'
                    ),
                    'type' => 'multiple_buttons',
                    'tab' => 'blog',
                    'section' => 'blog_general',
                    'toggle' => 'custom_archive_page',
                    //'class' => "some-test",
                    'show_if' => [
                        'custom_archive_page' => "on",
                    ],
                    'options' => array(
                        'style1' => [
                            'image' => 'dipi-blog-layout-01.png',
                        ],
                        'style2' => [
                            'image' => 'dipi-blog-layout-02.png',
                        ],
                        'style3' => [
                            'image' => 'dipi-blog-layout-03.png',
                        ],
                        'style4' => [
                            'image' => 'dipi-blog-layout-04.png',
                        ],
                        'style5' => [
                            'image' => 'dipi-blog-layout-05.png',
                        ],
                        'style6' => [
                            'image' => 'dipi-blog-layout-06.png',
                        ],
                    ),
                ],
                'hide_excerpt_text' => [
                    'label' => fn() => esc_html__('Hide Excerpt Text', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide excerpt text on single post archive/category pages.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'blog',
                    'section' => 'blog_general',
                    'toggle' => 'blog_hide_excerpt',
                ],
                'blog_meta_icons' => [
                    'label' => fn() => esc_html__('Add icons to meta text', 'dipi-divi-pixel'),
                    'description' => fn() => sprintf(
                        esc_html__('Enable this option to add date, author and comments icons to meta text and customize them using Divi Pixel %1$s', 'dipi-divi-pixel'),
                            sprintf(
                                '<a href="customize.php?autofocus[section]=dipi_customizer_section_blog_archives" target="_blank">%1$s</a>', 
                                esc_html__('Theme Customizer', 'dipi-divi-pixel')
                            )
                        ),
                    'type' => 'checkbox',
                    'tab' => 'blog',
                    'section' => 'blog_general',
                    'toggle' => 'blog_meta_icons',
                ],
                'remove_sidebar' => [
                    'label' => fn() => esc_html__('Remove Sidebar', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to remove sidebar from archive pages.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'blog',
                    'section' => 'blog_general',
                    'toggle' => 'remove_sidebar',
                ],
                'remove_sidebar_line' => [
                    'label' => fn() => esc_html__('Remove Sidebar Border', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to remove sidebar border line.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'blog',
                    'section' => 'blog_general',
                    'toggle' => 'remove_sidebar',
                    'show_if' => [
                        'remove_sidebar' => "off",
                    ],
                ],
                'sidebar_customization' => [
                    'label' => fn() => esc_html__('Sidebar Customization', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to use sidebar customization.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'blog',
                    'section' => 'blog_general',
                    'toggle' => 'remove_sidebar',
                    'show_if' => [
                        'remove_sidebar' => "off",
                    ],
                ],
                'add_read_more_archive' => [
                    'label' => fn() => esc_html__('Add Read More Button', 'dipi-divi-pixel'),
                    'description' => fn() => sprintf(
                        esc_html__('Add Read More button to single posts on archive pages. To customize single blog post button go to Divi Pixel %1$s', 'dipi-divi-pixel'), 
                        sprintf(
                            '<a href="customize.php?autofocus[section]=dipi_customizer_section_blog_archives_btn" target="_blank">%1$s</a>', 
                            esc_html__('Theme Customizer', 'dipi-divi-pixel')
                        )
                    ),
                    'type' => 'checkbox',
                    'tab' => 'blog',
                    'section' => 'blog_general',
                    'toggle' => 'read_more_archive',
                ],
                'read_more_button_style' => [
                    'label' => fn() => esc_html__('Button Style', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Select Read More button style.', 'dipi-divi-pixel'),
                    'type' => 'select',
                    'tab' => 'blog',
                    'section' => 'blog_general',
                    'toggle' => 'read_more_archive',
                    'default' => 'text_icon',
                    'options' => [
                        'only_text' => fn() => esc_html__('Display Only Text', 'dipi-divi-pixel'),
                        'text_icon' => fn() => esc_html__('Display Text with Icon', 'dipi-divi-pixel'),
                        'only_icon' => fn() => esc_html__('Display Only Icon', 'dipi-divi-pixel'),
                    ],
                    'show_if' => [
                        'add_read_more_archive' => "on",
                    ],
                ],
                'read_more_button' => [
                    'label' => fn() => esc_html__('Change Read More Button Text', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Replace the default Read More button text with custom text.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'blog',
                    'section' => 'blog_general',
                    'toggle' => 'read_more_archive',
                    'show_if' => [
                        'add_read_more_archive' => "on",
                        'read_more_button_style' => ['text_icon', 'only_text'],
                    ],
                ],
                'read_more_button_text' => [
                    'label' => fn() => esc_html__('Read More Button Text', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('Read More', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'blog',
                    'section' => 'blog_general',
                    'toggle' => 'read_more_archive',
                    'show_if' => [
                        'add_read_more_archive' => "on",
                        'read_more_button' => "on",
                        'read_more_button_style' => ['text_icon', 'only_text'],
                    ],
                ],
                'author_box' => [
                    'label' => fn() => esc_html__('Add Author Box', 'dipi-divi-pixel'),
                    'description' => fn() => sprintf(
                        esc_html__('Enable this option to add author box below posts and customize it using Divi Pixel %1$s', 'dipi-divi-pixel'), 
                        sprintf(
                            '<a href="customize.php?autofocus[section]=dipi_customizer_section_blog_author_box" target="_blank">%1$s</a>', 
                            esc_html__('Theme Customizer', 'dipi-divi-pixel')
                        )
                    ),
                    'type' => 'checkbox',
                    'tab' => 'blog',
                    'section' => 'blog_general',
                    'toggle' => 'author_box',
                ],
                'blog_nav' => [
                    'label' => fn() => esc_html__('Add Blog Navigation', 'dipi-divi-pixel'),
                    'description' => fn() => sprintf(
                        esc_html__('Enable this option to display prev/next links below posts and customize it using Divi Pixel %1$s', 'dipi-divi-pixel'), 
                        sprintf(
                            '<a href="customize.php?autofocus[section]=dipi_customizer_section_blog_navigation" target="_blank">%1$s</a>', 
                            esc_html__('Theme Customizer', 'dipi-divi-pixel')
                        )
                    ),
                    'type' => 'checkbox',
                    'tab' => 'blog',
                    'section' => 'blog_general',
                    'toggle' => 'blog_nav',
                ],
                'blog_nav_prev' => [
                    'label' => fn() => esc_html__('Previous Post Button Text', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Add custom text for Previous Post button link.', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('Previous Article', 'dipi-divi-pixel'),
                    'default' => fn() => esc_html__('Previous Article', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'blog',
                    'section' => 'blog_general',
                    'toggle' => 'blog_nav',
                    'show_if' => [
                        'blog_nav' => "on",
                    ],
                ],
                'blog_nav_next' => [
                    'label' => fn() => esc_html__('Next Post Button Text', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Add custom text for Next Post button link.', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('Next Article', 'dipi-divi-pixel'),
                    'default' => fn() => esc_html__('Next Article', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'blog',
                    'section' => 'blog_general',
                    'toggle' => 'blog_nav',
                    'show_if' => [
                        'blog_nav' => "on",
                    ],
                ],
                'related_articles' => [
                    'label' => fn() => esc_html__('Related Articles', 'dipi-divi-pixel'),
                    'description' => fn() => sprintf(
                        esc_html__('Enable this option to display Related Articles below posts and customize it using Divi Pixel %1$s', 'dipi-divi-pixel'), 
                        sprintf(
                            '<a href="customize.php?autofocus[section]=dipi_customizer_section_blog_related_posts" target="_blank">%1$s</a>', 
                            esc_html__('Theme Customizer', 'dipi-divi-pixel')
                        )
                    ),
                    'type' => 'checkbox',
                    'tab' => 'blog',
                    'section' => 'blog_general',
                    'toggle' => 'related_articles',
                ],
                'related_articles_heading' => [
                    'label' => fn() => esc_html__('Related Articles Heading Text', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Add custom heading to your Related Articles section.', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('Related Articles', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'blog',
                    'section' => 'blog_general',
                    'toggle' => 'related_articles',
                    'show_if' => [
                        'related_articles' => "on",
                    ],
                ],
                'related_articles_limit' => [
                    'label' => fn() => esc_html__('Number of Articles', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Select number of posts displayed in Related Articles section.', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('6', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'blog',
                    'section' => 'blog_general',
                    'toggle' => 'related_articles',
                    'show_if' => [
                        'related_articles' => "on",
                    ],
                ],
                'enable_custom_comments' => [
                    'label' => fn() => esc_html__('Customize Comments Section', 'dipi-divi-pixel'),
                    'description' => fn() => sprintf(
                        esc_html__('Enable this option to customize blog post comments section. To change form appearance go to Divi Pixel %1$s', 'dipi-divi-pixel'), 
                        sprintf(
                            '<a href="customize.php?autofocus[section]=dipi_customizer_section_blog_comments" target="_blank">%1$s</a>', 
                            esc_html__('Theme Customizer', 'dipi-divi-pixel')
                        )
                    ),
                    'type' => 'checkbox',
                    'tab' => 'blog',
                    'section' => 'blog_general',
                    'toggle' => 'custom_comments',
                ],
                'custom_comments_title' => [
                    'label' => fn() => esc_html__('Comments Section Title', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Add custom heading to your websites Comments section.', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('Comments', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'blog',
                    'section' => 'blog_general',
                    'toggle' => 'custom_comments',
                    'show_if' => [
                        'custom_comments' => "on",
                    ],
                ],
            ];
        }

        private function create_mobile_tab_fields()
        {
            return [
                'mobile_theme_customizer' => [
                    'label' => fn() => esc_html__('Customize Mobile Menu with Ease', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Divi Pixel offers tons of Mobile Menu customization. Select hamburger icon animation, change mobile menu colors, fonts and animation using Theme Customizer.', 'dipi-divi-pixel'),
                    'type' => 'theme_customizer',
                    'icon' => 'dp-devices',
                    'class' => 'first_customizer_field',
                    'tab' => 'mobile',
                    'section' => 'mobile_general',
                    'toggle' => 'mobile_theme_customizer',
                    'customizer_panel' => 'mobile',
                ],

                'custom_breakpoints' => [
                    'label' => fn() => esc_html__('Enable Custom Menu Breakpoint', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to set custom breakpoint for mobile menu.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'mobile',
                    'section' => 'mobile_general',
                    'toggle' => 'custom_breakpoints',
                ],

                'breakpoint_mobile' => [
                    'label' => fn() => esc_html__('Display Mobile Menu Below', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Choose below which width the mobile menu should show. On screens which are wider than the defined width, the normal desktop menu will be displayed. The number configured here is inclusive. For example, if you choose 980, the desktop menu will show on screens with a width of at least 981px.', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('980', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'mobile',
                    'section' => 'mobile_general',
                    'toggle' => 'custom_breakpoints',
                    'show_if' => [
                        'custom_breakpoints' => "on",
                    ],
                ],
                'fixed_mobile_header' => [
                    'label' => fn() => esc_html__('Fixed Mobile Header', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to display fixed header on mobiles.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'mobile',
                    'section' => 'mobile_general',
                    'toggle' => 'fixed_mobile_header',
                ],
                'adjust_anchor_links_pos_with_fixed_header' => [
                    'label' => fn() => esc_html__('Adjust Position of Anchor Links', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to adjust the position of anchor links while using Fixed Mobile Header.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'mobile',
                    'section' => 'mobile_general',
                    'toggle' => 'fixed_mobile_header',
                    'new' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                    'show_if' => [
                        'fixed_mobile_header' => "on",
                    ],
                ],
                'search_icon_mobile' => [
                    'label' => fn() => esc_html__('Hide Search Icon on Mobiles', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide search icon on mobiles.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'mobile',
                    'section' => 'mobile_general',
                    'toggle' => 'search_icon_mobile',
                ],
                'mobile_logo' => [
                    'label' => fn() => esc_html__('Change Logo on Mobiles', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to display custom logo on mobile devices.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'mobile',
                    'section' => 'mobile_general',
                    'toggle' => 'mobile_logo',
                ],
                'mobile_logo_url' => [
                    'label' => fn() => esc_html__('Mobile Logo', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Upload the logo you want to use on mobile devices.', 'dipi-divi-pixel'),
                    'type' => 'file_upload',
                    'file_type' => 'image',
                    'tab' => 'mobile',
                    'section' => 'mobile_general',
                    'toggle' => 'mobile_logo',
                    'show_if' => [
                        'mobile_logo' => "on",
                    ],
                ],
                'mobile_menu_style' => [
                    'label' => fn() => esc_html__('Enable Custom Mobile Menu Style', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to display custom mobile menu.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'mobile',
                    'section' => 'mobile_menu',
                    'toggle' => 'mobile_menu_style',
                ],
                'mobile_menu_fullscreen' => [
                    'label' => fn() => esc_html__('Fullscreen Mobile Menu', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to display fullscreen mobile menu.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'mobile',
                    'section' => 'mobile_menu',
                    'toggle' => 'mobile_menu_style',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                    'show_if' => [
                        'mobile_menu_style' => "on",
                        'popup_as_mobile_menu' => "off"
                    ],
                ],
                'popup_as_mobile_menu' => [
                    'label' => fn() => esc_html__('Use Popup as Mobile Menu', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to use popup as mobile menu.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'mobile',
                    'section' => 'mobile_menu',
                    'toggle' => 'mobile_menu_style',
                    'new' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                    'show_if' => [
                        'mobile_menu_style' => "on",
                        'mobile_menu_fullscreen' => "off"
                    ],
                ],
                'allow_multiple_popups' => [
                    'label' => fn() => esc_html__('Allow Multiple Popups', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to allow popups to work properly with translations.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'default' => 'off',
                    'tab' => 'mobile',
                    'section' => 'mobile_menu',
                    'toggle' => 'mobile_menu_style',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                    'show_if' => [
                        'mobile_menu_style' => "on",
                        'mobile_menu_fullscreen' => "off",
                        'popup_as_mobile_menu' => "on"
                    ],
                ],
                'multiple_mobile_menu_popup' => [
                    'label' => fn() => esc_html__('Select Popups', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Select specific popups to open when click on mobile menu.', 'dipi-divi-pixel'),
                    'type' => 'select2',
                    'tab' => 'mobile',
                    'section' => 'mobile_menu',
                    'toggle' => 'mobile_menu_style',
                    'computed' => true,
                    'options' => [$this, 'get_popups'],
                    'show_if' => [
                        'mobile_menu_style' => "on",
                        'popup_as_mobile_menu' => "on",
                        'allow_multiple_popups' => "on"
                    ],
                ],
                'mobile_menu_popup' => [
                    'label' => fn() => esc_html__('Select Popup', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Select specific popup to opne when click on mobile menu.', 'dipi-divi-pixel'),
                    'type' => 'select',
                    'tab' => 'mobile',
                    'section' => 'mobile_menu',
                    'toggle' => 'mobile_menu_style',
                    'computed' => true,
                    'options' => [$this, 'get_popups'],
                    'show_if' => [
                        'mobile_menu_style' => "on",
                        'popup_as_mobile_menu' => "on",
                        'allow_multiple_popups' => "off"
                    ],
                ],
                'popup_select_menu' => [
                    'label' => fn() => esc_html__('Select Menu', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Select specific menu to display popup.', 'dipi-divi-pixel'),
                    'type' => 'select2',
                    'tab' => 'mobile',
                    'section' => 'mobile_menu',
                    'toggle' => 'mobile_menu_style',
                    'computed' => true,
                    'options' => [$this, 'get_menus'],
                    'show_if' => [
                        'mobile_menu_style' => "on",
                        'popup_as_mobile_menu' => "on"
                    ],
                ],
                'hamburger_animation' => [
                    'label' => fn() => esc_html__('Hamburger Customization', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to customize the mobile menu icon.', 'dipi-divi-pixel'),                
                    'type' => 'checkbox',
                    'tab' => 'mobile',
                    'section' => 'mobile_menu',
                    'toggle' => 'hamburger_animation',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                ],
                'hamburger_animation_styles' => [
                    'label' => fn() => esc_html__('Hamburger Animation Style', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Select animation style you would like to display after mobile menu icon is clicked.', 'dipi-divi-pixel'),
                    'type' => 'multiple_buttons',
                    'tab' => 'mobile',
                    'section' => 'mobile_menu',
                    'toggle' => 'hamburger_animation',
                    //'class' => "some-test",
                    'show_if' => [
                        'hamburger_animation' => "on",
                    ],
                    'default' => 'style1',
                    'options' => array(
                        'hamburger--slider' => [
                            'image' => 'ham-slider.gif',
                            'title' => fn() => esc_html__('Slider', 'dipi-divi-pixel'),
                        ],
                        'hamburger--squeeze' => [
                            'image' => 'ham-squeeze.gif',
                            'title' => fn() => esc_html__('Squeeze', 'dipi-divi-pixel'),
                        ],
                        'hamburger--spin' => [
                            'image' => 'ham-spin.gif',
                            'title' => fn() => esc_html__('Spin', 'dipi-divi-pixel'),
                        ],
                        'hamburger--elastic' => [
                            'image' => 'ham-elastic.gif',
                            'title' => fn() => esc_html__('Elastic', 'dipi-divi-pixel'),
                        ],
                        'hamburger--collapse' => [
                            'image' => 'ham-collapse.gif',
                            'title' => fn() => esc_html__('Collapse', 'dipi-divi-pixel'),
                        ],
                        'hamburger--stand' => [
                            'image' => 'ham-stand.gif',
                            'title' => fn() => esc_html__('Stand', 'dipi-divi-pixel'),
                        ],
                        'hamburger--spring' => [
                            'image' => 'ham-spring.gif',
                            'title' => fn() => esc_html__('Spring', 'dipi-divi-pixel'),
                        ],
                        'hamburger--minus' => [
                            'image' => 'ham-minus.gif',
                            'title' => fn() => esc_html__('Minus', 'dipi-divi-pixel'),
                        ],
                        'hamburger--vortex' => [
                            'image' => 'ham-vortex.gif',
                            'title' => fn() => esc_html__('Vortex', 'dipi-divi-pixel'),
                        ],
                    ),
                ],
                'hamburger_animation_customizer' => [
                    'label' => fn() => esc_html__('Customize Mobile Menu Icon', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Use Divi Pixel Customizer to custimize mobile menu icon. Change style, color and icon and make your mobile menu unique with ease!', 'dipi-divi-pixel'),
                    'type' => 'theme_customizer',
                    //'panel' => 'dipi_btt', //optional, if not set, use 'section' or go to theme customizer in general when neither is set
                    // 'section' => 'dipi_btt_section_1', //optional, if not set, use 'panel' or go to theme customizer in general when neither is set
                    'tab' => 'mobile',
                    'section' => 'mobile_menu',
                    'icon' => 'dp-hamburger',
                    'toggle' => 'hamburger_animation',
                    'customizer_section' => 'mobile_menu_effects',
                    'show_if' => [
                        'hamburger_animation' => "on",
                    ],
                ],

                'collapse_submenu' => [
                    'label' => fn() => esc_html__('Collapse Submenu Items on Mobiles', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to collapse submenu items on mobiles.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'mobile',
                    'section' => 'mobile_menu',
                    'toggle' => 'collapse_submenu',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                ],
                'collapse_submenu_prevent_parent_opening' => [
                    'label' => fn() => esc_html__('Remove Parent Menu Link', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to prevent parent menu links from opening.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'mobile',
                    'section' => 'mobile_menu',
                    'toggle' => 'collapse_submenu',
                    'new' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                    'show_if' => [
                        'collapse_submenu' => "on",
                    ],
                ],
            ];
        }

        private function create_modules_tab_fields()
        {
            return [
                //Modules Theme Customizer Toggle
                'divi5_modules_hint' => [
                    'label' => fn() => esc_html__('Divi 5 Support (Beta)', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Divi 5 Support is enabled by default and will automatically activate when the Divi 5 Theme is installed. While Divi Pixel is not yet fully compatible with Divi 5 and some module conversions are still pending, our Divi 5 modules are in beta and may undergo changes before the final release. We recommend using Divi 5 modules only on new or staging websites.', 'dipi-divi-pixel'),
                    'type' => 'theme_customizer',
                    'icon' => 'dp-divi',
                    'class' => 'first_customizer_field no_button',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'modules_theme_customizer',
                ],
                'modules_theme_customizer' => [
                    'label' => fn() => esc_html__('Hide Custom Modules', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Divi Pixel includes 50+ custom modules that load automatically in the Divi Builder. You can hide unused modules here to keep your builder clean and organized.', 'dipi-divi-pixel'),
                    'type' => 'theme_customizer',
                    'icon' => 'dp-switches',
                    'class' => 'first_customizer_field no_button',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'modules_theme_customizer',
                ],
                // Modules Options
                'md_hide_all_modules' => [
                    'label' => fn() => esc_html__('Hide All Modules', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide All Modules.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_all_modules',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ] 
                ],
                // Modules Options
                'md_masonry_gallery' => [
                    'label' => fn() => esc_html__('Hide Masonry Gallery', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Masonry Gallery module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_masonry_gallery',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_testimonial' => [
                    'label' => fn() => esc_html__('Hide Testimonial', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Testimonial module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_testimonial',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_countdown' => [
                    'label' => fn() => esc_html__('Hide Countdown', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Countdown module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_countdown',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_blog_slider' => [
                    'label' => fn() => esc_html__('Hide Blog Slider', 'diro-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Blog Slider module from the Divi Builder.', 'diro-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_blog_slider',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'diro-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'diro-divi-pixel'),
                    ]
                ],
                'md_counter' => [
                    'label' => fn() => esc_html__('Hide Counter', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Counter module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_counter',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_reveal' => [
                    'label' => fn() => esc_html__('Hide Reveal', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Reveal module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_reveal',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_flip_box' => [
                    'label' => fn() => esc_html__('Hide Flip Box', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide FlipBox module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_flip_box',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_button_grid' => [
                    'label' => fn() => esc_html__('Hide Button Grid', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Button Grid module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_button_grid',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_before_after_slider' => [
                    'label' => fn() => esc_html__('Hide Before/After Slider', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Before/After Slider module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_before_after_slider',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_floating_multi_images' => [
                    'label' => fn() => esc_html__('Hide Floating Images', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Floating Images module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_floating_multi_images',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_tilt_image' => [
                    'label' => fn() => esc_html__('Hide Tilt Image', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Tilt Image module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_tilt_image',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_carousel' => [
                    'label' => fn() => esc_html__('Hide Carousel', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Carousel module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_carousel',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_typing_text' => [
                    'label' => fn() => esc_html__('Hide Typing Text Effect', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Typing Text Effect module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_typing_text',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_star_rating' => [
                    'label' => fn() => esc_html__('Hide Star Rating', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Star Rating module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_star_rating',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_breadcrumbs' => [
                    'label' => fn() => esc_html__('Hide Breadcrumbs', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Breadcrumbs module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_breadcrumbs',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_pricelist' => [
                    'label' => fn() => esc_html__('Hide Price List', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Price List module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_pricelist',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_image_hotspot' => [
                    'label' => fn() => esc_html__('Hide Image Hotspot', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Image Hotspot module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_image_hotspot',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_hover_box' => [
                    'label' => fn() => esc_html__('Hide Hover Box', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Hover Box module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_hover_box',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_fancy_text' => [
                    'label' => fn() => esc_html__('Hide Fancy Text', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Fancy Text module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_fancy_text',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_accordion_image' => [
                    'label' => fn() => esc_html__('Hide Accordion Image', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Accordion Image module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_accordion_image',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_scroll_image' => [
                    'label' => fn() => esc_html__('Hide Scroll Image', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Scroll Image module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_scroll_image',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_panorama' => [
                    'label' => fn() => esc_html__('Hide Panorama', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Panorama module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_panorama',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_reading_progress_bar' => [
                    'label' => fn() => esc_html__('Hide Reading Progress Bar', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Reading Progress Bar module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_reading_progress_bar',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_image_magnifier' => [
                    'label' => fn() => esc_html__('Hide Image Magnifier', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Image Magnifier module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_image_magnifier',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_lottie_icon' => [
                    'label' => fn() => esc_html__('Hide Lottie Icon', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Lottie Icon module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_lottie_icon',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_image_showcase' => [
                    'label' => fn() => esc_html__('Hide Image Showcase', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Image Showcase module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_image_showcase',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_image_mask' => [
                    'label' => fn() => esc_html__('Hide Image Mask', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Image Mask module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_image_mask',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_timeline' => [
                    'label' => fn() => esc_html__('Hide Timeline', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Timeline module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_timeline',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_content_toggle' => [
                    'label' => fn() => esc_html__('Hide Content Toggle', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Content Toggle module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_content_toggle',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_faq' => [
                    'label' => fn() => esc_html__('Hide FAQ', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide FAQ module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_faq',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'faq_search_enabled' => [
                    'label' => fn() => esc_html__('Show FAQ Pages', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to publicly show FAQ entries and allow them to appear in the search.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_faq',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_balloon' => [
                    'label' => fn() => esc_html__('Hide Balloon', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Balloon module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_balloon',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_gallery_slider' => [
                    'label' => fn() => esc_html__('Hide Image Slider', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Image Slider module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_gallery_slider',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_dual_heading' => [
                    'label' => fn() => esc_html__('Hide Dual Heading', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Dual Heading module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_dual_heading',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_svg_animator' => [
                    'label' => fn() => esc_html__('Hide SVG Animator', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide SVG Animator module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_svg_animator',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_info_circle' => [
                    'label' => fn() => esc_html__('Hide Info Circle', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Info Circle module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_info_circle',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_advanced_tabs' => [
                    'label' => fn() => esc_html__('Hide Advanced Tabs', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Advanced Tabs module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',                   
                    'section' => 'custom_modules',
                    'toggle' => 'md_advanced_tabs',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_horizontal_timeline' => [
                    'label' => fn() => esc_html__('Hide Horizontal Timeline', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Horizontal Timeline module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_horizontal_timeline',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_expanding_cta' => [
                    'label' => fn() => esc_html__('Hide Expanding CTA', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Expanding CTA module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_expanding_cta',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_text_highlighter' => [
                    'label' => fn() => esc_html__('Hide Text Highlighter', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Text Highlighter module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_text_highlighter',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_popup_maker' => [
                    'label' => fn() => esc_html__('Disable Popup Maker', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide the Popup Maker Custom Post Type and disable all Popup Maker functionality.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'popup_maker',
                    'toggle' => 'md_popup_maker',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_tile_scroll' => [
                    'label' => fn() => esc_html__('Hide Tile Scroll', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Tile Scroll module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_tile_scroll',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_pricing_table' => [
                    'label' => fn() => esc_html__('Hide Pricing Table', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Pricing Table module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_pricing_table',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],   
                'md_filterable_gallery' => [
                    'label' => fn() => esc_html__('Hide Filterable Gallery', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Filterable Gallery module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_filterable_gallery',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_parallax_images' => [
                    'label' => fn() => esc_html__('Hide Parallax Image', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Parallax Image module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_parallax_images',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_filterable_grid' => [
                    'label' => fn() => esc_html__('Hide Filterable Grid', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Filterable Grid module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_filterable_grid',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_hover_gallery' => [
                    'label' => fn() => esc_html__('Hide Hover Gallery', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Hover Gallery module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_hover_gallery',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ], 
                'md_content_slider' => [
                    'label' => fn() => esc_html__('Hide Content Slider', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Content Slider module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_content_slider',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_gravity_styler' => [
                    'label' => fn() => esc_html__('Hide Gravity Forms Styler', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Gravity Forms Styler module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_gravity_styler',
                    'default' => 'on',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_image_rotator' => [
                    'label' => fn() => esc_html__('Hide Image Rotator', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Image Rotator module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_image_rotator',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_table_of_contents' => [
                    'label' => fn() => esc_html__('Hide Table of Contents', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Table of Contents module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_table_of_contents',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_advanced_divider' => [
                    'label' => fn() => esc_html__('Hide Divider', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Divider module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_advanced_divider',
                    'divi_5_ready' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_table_maker' => [
                    'label' => fn() => esc_html__('Hide Table Maker', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Table Maker module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_table_maker',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_accordion_slider' => [
                    'label' => fn() => esc_html__('Hide Accordion Slider', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Accordion Slider module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_accordion_slider',
                    'coming_soon' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_animated_blurb' => [
                    'label' => fn() => esc_html__('Hide Animated Blurb', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Animated Blurb module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_animated_blurb',
                    'coming_soon' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ],
                'md_instagram' => [
                    'label' => fn() => esc_html__('Hide Instagram', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide Instagram module from the Divi Builder.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'modules',
                    'section' => 'custom_modules',
                    'toggle' => 'md_instagram',
                    'coming_soon' => true,
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ]
                ]
            ];
        }

        private function create_injector_tab_fields()
        {
            return [
                'inject_theme_customizer' => [
                    'label' => fn() => esc_html__('Layout Injector', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Divi Pixel allows you to inject Divi Library items in places where by default it is not possible. Add custom layouts to your Divi website and display them globally with ease.', 'dipi-divi-pixel'),
                    'type' => 'theme_customizer',
                    'icon' => 'dp-layers',
                    'class' => 'first_customizer_field no_button',
                    'tab' => 'injector',
                    'section' => 'navigation_inject',
                    'toggle' => 'inject_theme_customizer',
                ],
                'before_nav_layout' => [
                    'label' => fn() => esc_html__('Before Navigation Layout', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to display Divi Library item before main navigation.', 'dipi-divi-pixel'),
                    'type' => 'library_layout',
                    'tab' => 'injector',
                    'section' => 'navigation_inject',
                    'toggle' => 'nav_injector',
                ],
                'after_nav_layout' => [
                    'label' => fn() => esc_html__('After Navigation Layout', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to display Divi Library item after main navigation.', 'dipi-divi-pixel'),
                    'type' => 'library_layout',
                    'tab' => 'injector',
                    'section' => 'navigation_inject',
                    'toggle' => 'nav_injector',
                ],
                'nav_layout_homepage' => [
                    'label' => fn() => esc_html__('Display on Homepage Only', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to display Divi Library item on homepage only.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'injector',
                    'section' => 'navigation_inject',
                    'toggle' => 'nav_injector',
                    'show_if' => [
                        'nav_layout_custom' => "off",
                    ],
                ],
                'nav_layout_custom' => [
                    'label' => fn() => esc_html__('Display on Specific Pages', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to display Divi Library item on specific pages only.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'injector',
                    'section' => 'navigation_inject',
                    'toggle' => 'nav_injector',
                    'show_if' => [
                        'nav_layout_homepage' => "off",
                    ],
                ],

                'nav_specific_pages' => [
                    'label' => fn() => esc_html__('Select Pages', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Select specific pages to show custom navigation', 'dipi-divi-pixel'),
                    'type' => 'select2',
                    'tab' => 'injector',
                    'section' => 'navigation_inject',
                    'toggle' => 'nav_injector',
                    'computed' => true,
                    'options' => [$this, 'get_pages'],
                    'show_if' => [
                        'nav_layout_homepage' => "off",
                        'nav_layout_custom' => "on",
                    ],
                ],

                'before_footer_layout' => [
                    'label' => fn() => esc_html__('Before Footer Layout', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to display Divi Library item before footer.', 'dipi-divi-pixel'),
                    'type' => 'library_layout',
                    'tab' => 'injector',
                    'section' => 'footer_inject',
                    'toggle' => 'footer_injector',
                ],

                'after_footer_layout' => [
                    'label' => fn() => esc_html__('After Footer Layout', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to display Divi Library item after footer.', 'dipi-divi-pixel'),
                    'type' => 'library_layout',
                    'tab' => 'injector',
                    'section' => 'footer_inject',
                    'toggle' => 'footer_injector',
                ],

                'footer_layout_homepage' => [
                    'label' => fn() => esc_html__('Display on Homepage Only', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to display Divi Library item on homepage only.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'injector',
                    'section' => 'footer_inject',
                    'toggle' => 'footer_injector',
                    'show_if' => [
                        'footer_layout_custom' => "off",
                    ],
                ],

                'footer_layout_custom' => [
                    'label' => fn() => esc_html__('Display on Specific Pages', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to display Divi Library item only on specific pages.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'injector',
                    'section' => 'footer_inject',
                    'toggle' => 'footer_injector',
                    'show_if' => [
                        'footer_layout_homepage' => "off",
                    ],
                ],

                'footer_specific_pages' => [
                    'label' => fn() => esc_html__('Select Pages', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Select specific pages to show custom footer', 'dipi-divi-pixel'),
                    'type' => 'select2',
                    'tab' => 'injector',
                    'section' => 'footer_inject',
                    'toggle' => 'footer_injector',
                    //TODO: Compute
                    'computed' => true,
                    'options' => [$this, 'get_pages'],
                    'show_if' => [
                        'footer_layout_homepage' => "off",
                        'footer_layout_custom' => "on",
                    ],
                ],

                'error_page' => [
                    'label' => fn() => esc_html__('Custom 404 Page', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to select custom 404 page from Divi Library.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'injector',
                    'section' => 'error_page_inject',
                    'toggle' => 'error_page',
                ],

                'select_error_page' => [
                    'label' => fn() => esc_html__('Select 404 Page', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Select Divi Library layout for 404 page.', 'dipi-divi-pixel'),
                    'type' => 'library_layout',
                    'tab' => 'injector',
                    'section' => 'error_page_inject',
                    'toggle' => 'error_page',
                    'show_if' => [
                        'error_page' => "on",
                    ],
                ],

                'error_page_header' => [
                    'label' => fn() => esc_html__('Hide Header', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide header on error page.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'injector',
                    'section' => 'error_page_inject',
                    'toggle' => 'error_page',
                    'show_if' => [
                        'error_page' => "on",
                    ],
                ],

                'error_page_footer' => [
                    'label' => fn() => esc_html__('Hide Footer', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to hide footer on error page.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'injector',
                    'section' => 'error_page_inject',
                    'toggle' => 'error_page',
                    'show_if' => [
                        'error_page' => "on",

                    ],
                ],

                'after_nav_post_layout' => [
                    'label' => fn() => esc_html__('Single Post Header Layout - After Nav', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('This layout will be displayed globally after the main header and navigation on single blog post.', 'dipi-divi-pixel'),
                    'type' => 'library_layout',
                    'tab' => 'injector',
                    'section' => 'blog_inject',
                    'toggle' => 'blog_injector',
                ],

                'after_nav_archives' => [
                    'label' => fn() => esc_html__('Archive Page Header Layout - After Nav', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('This layout will be displayed globally after the main header and navigation on archive pages.', 'dipi-divi-pixel'),
                    'type' => 'library_layout',
                    'tab' => 'injector',
                    'section' => 'blog_inject',
                    'toggle' => 'blog_injector',
                ],
                'after_nav_categories' => [
                    'label' => fn() => esc_html__('Categories Header Layout - After Nav', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('This layout will be displayed globally after the main header and navigation on categories pages.', 'dipi-divi-pixel'),
                    'type' => 'library_layout',
                    'tab' => 'injector',
                    'section' => 'blog_inject',
                    'toggle' => 'blog_injector',
                ],
                'after_nav_search' => [
                    'label' => fn() => esc_html__('Search Results Layout - After Nav', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('This layout will be displayed globally after the main header and navigation on search results page.', 'dipi-divi-pixel'),
                    'type' => 'library_layout',
                    'tab' => 'injector',
                    'section' => 'blog_inject',
                    'toggle' => 'blog_injector',
                ],
            ];
        }

        private function create_settings_tab_fields()
        {
            $settings = [];

            //Only add license settings if we are in the EDD version of the plugin
            if (self::$isEDD) {
                $settings = [
                    'license' => [
                        'label' => fn() => esc_html__('License Key', 'dipi-divi-pixel'),
                        'description' => fn() => esc_html__('Enter your license key here.', 'dipi-divi-pixel'),
                        'type' => 'license',
                        'sanitize_callback' => [$this, 'sanitize_license'],
                        'tab' => 'settings',
                        'section' => 'settings_general',
                        'toggle' => 'settings_general_license',
                    ],
                    'enable_beta_program' => [
                        'label' => fn() => esc_html__('Enable Beta Updates', 'dipi-divi-pixel'),
                        'description' => fn() => esc_html__('Enable this option to get access to the latest beta builds. Usually you don\'t want this to be enabled on your production environment but if you encounter issues, our support team might ask you to enable this option.', 'dipi-divi-pixel'),
                        'type' => 'checkbox',
                        'tab' => 'settings',
                        'section' => 'settings_general',
                        'toggle' => 'beta_programm',
                        'options' => [
                            'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                            'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                        ],
                    ],
                ];
            } 

            // We need those license settings, even in non EDD version so we don't cause any unnecessary warnings. They aren't shown in the UI anyways so that should be fine
            $settings['license_status'] = [
                'type' => 'skip',
                'default' => 'invalid',
            ];

            $settings['license_limit'] = [
                'type' => 'skip',
                'default' => 0,
            ];

            $settings['license_site_count'] = [
                'type' => 'skip',
                'default' => 0,
            ];


            // Rest of the settings fields
            return array_merge($settings, [
                'settings_reset_button' => [
                    'label' => fn() => esc_html__('Reset Divi Pixel Settings', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Reset all settings to their default state. Be aware that this canno be undone. We highly advise you to export your settings before resetting them, in case you want to roll back.', 'dipi-divi-pixel'),
                    'type' => 'callback',
                    'callback' => [$this, 'callback_reset_settings'],
                    'tab' => 'settings',
                    'section' => 'settings_general',
                    'toggle' => 'settings_general_reset',
                ],

                'settings_reset_customizer_button' => [
                    'label' => fn() => esc_html__('Reset Divi Pixel Customizer Settings', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Reset all customizer settings to their default state. Be aware that this canno be undone. We highly advise you to export your settings before resetting them, in case you want to roll back.', 'dipi-divi-pixel'),
                    'type' => 'callback',
                    'callback' => [$this, 'callback_reset_customizer_settings'],
                    'tab' => 'settings',
                    'section' => 'settings_general',
                    'toggle' => 'settings_general_reset',
                ],

                'google_place_id' => [
                    'label' => fn() => esc_html__('Google Place ID', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Place IDs uniquely identify a place in the Google Places database and on Google Maps.', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('Enter Google Place ID', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'settings',
                    'section' => 'third_party_providers',
                    'toggle' => 'settings_google_api',
                ],

                'google_api_key' => [
                    'label' => fn() => esc_html__('Google API Key', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('To use the Maps JavaScript API you must have an API key. The API key is a unique identifier that is used to authenticate requests associated with your project for usage and billing purposes.', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('Enter Google API Key', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'settings',
                    'section' => 'third_party_providers',
                    'toggle' => 'settings_google_api',
                ],

                'google_api_lang' => [
                    'label' => fn() => esc_html__('Google API Language', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Select language used to fetch data from Google API.', 'dipi-divi-pixel'),
                    'type' => 'select',
                    'tab' => 'settings',
                    'section' => 'third_party_providers',
                    'toggle' => 'settings_google_api',
                    'default' => 'en',
                    'options' => [
                        'af' => 'Afrikaans',
                        'sq' => 'Albanian',
                        'am' => 'Amharic',
                        'ar' => 'Arabic',
                        'hy' => 'Armenian',
                        'az' => 'Azerbaijani',
                        'eu' => 'Basque',
                        'be' => 'Belarusian',
                        'bn' => 'Bengali',
                        'bs' => 'Bosnian',
                        'bg' => 'Bulgarian',
                        'my' => 'Burmese',
                        'ca' => 'Catalan',
                        'zh' => 'Chinese',
                        'zh-CN' => 'Chinese (Simplified)',
                        'zh-HK' => 'Chinese (Hong Kong)',
                        'zh-TW' => 'Chinese (Traditional)',
                        'hr' => 'Croatian',
                        'cs' => 'Czech',
                        'da' => 'Danish',
                        'nl' => 'Dutch',
                        'en' => 'English',
                        'en-AU' => 'English (Australian)',
                        'en-GB' => 'English (Great Britain)',
                        'et' => 'Estonian',
                        'fa' => 'Farsi',
                        'fi' => 'Finnish',
                        'fil' => 'Filipino',
                        'fr' => 'French',
                        'fr-CA' => 'French (Canada)',
                        'gl' => 'Galician',
                        'ka' => 'Georgian',
                        'de' => 'German',
                        'el' => 'Greek',
                        'gu' => 'Gujarati',
                        'iw' => 'Hebrew',
                        'hi' => 'Hindi',
                        'hu' => 'Hungarian',
                        'is' => 'Icelandic',
                        'id' => 'Indonesian',
                        'it' => 'Italian	',
                        'ja' => 'Japanese',
                        'kn' => 'Kannada',
                        'kk' => 'Kazakh',
                        'km' => 'Khmer',
                        'ko' => 'Korean',
                        'ky' => 'Kyrgyz',
                        'lo' => 'Lao',
                        'lv' => 'Latvian',
                        'lt' => 'Lithuanian',
                        'mk' => 'Macedonian',
                        'ms' => 'Malay',
                        'ml' => 'Malayalam',
                        'mr' => 'Marathi',
                        'mn' => 'Mongolian',
                        'ne' => 'Nepali',
                        'no' => 'Norwegian',
                        'pl' => 'Polish',
                        'pt' => 'Portuguese',
                        'pt-BR' => 'Portuguese (Brazil)',
                        'pt-PT' => 'Portuguese (Portugal)',
                        'pa' => 'Punjabi',
                        'ro' => 'Romanian',
                        'ru' => 'Russian',
                        'sr' => 'Serbian',
                        'si' => 'Sinhalese',
                        'sk' => 'Slovak',
                        'sl' => 'Slovenian',
                        'es' => 'Spanish',
                        'es-419' => 'Spanish (Latin America)',
                        'sw' => 'Swahili',
                        'sv' => 'Swedish',
                        'ta' => 'Tamil',
                        'te' => 'Telugu',
                        'th' => 'Thai',
                        'tr' => 'Turkish',
                        'uk' => 'Ukrainian',
                        'ur' => 'Urdu',
                        'uz' => 'Uzbek',
                        'vi' => 'Vietnamese',
                        'zu' => 'Zulu',
                    ],
                ],

                'facebook_page_id' => [
                    'label' => fn() => esc_html__('Facebook Page ID', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Facebook requires you to input the Page ID when you fetch facebook reviews.', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('Enter Facebook Page ID', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'settings',
                    'section' => 'third_party_providers',
                    'toggle' => 'settings_facebook_api',
                ],



                'facebook_page_access_token' => [
                    'label' => fn() => esc_html__('Facebook Page Access Token', 'dipi-divi-pixel'),
                    'description' => fn() => sprintf(
                        esc_html__('Enter the access token belonging to the Page ID entered above. This access token needs the following permission to the Facebook Graph API, so reviews can be synchronized loaded from your Facebook Page: pages_show_list , pages_read_user_content. To connect your Facebook Page to your Website using our Divi Pixel Facebook App, %1$s', 'dipi-divi-pixel'),
                        sprintf(
                            '<a href="%2$s" target="_blank">%1$s</a>',
                            esc_html__('CLICK HERE', 'dipi-divi-pixel'),
                            add_query_arg(
                                [
                                    "client_id" => "1227291725314435",
                                    "redirect_uri" => "https://www.divi-pixel.com/fb-auth",
                                    "state" => urlencode(wp_json_encode([
                                        "return_url" => home_url("/wp-json/divi-pixel/v1/fb-auth"),  
                                        "secret" => $this->get_facebook_secret()
                                    ])),
                                    "scope" => "pages_show_list pages_read_user_content"
                                ],
                                "https://www.facebook.com/v19.0/dialog/oauth"
                            )
                        )
                    ),
                    'placeholder' => fn() => esc_html__('Enter FB Access Token', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'settings',
                    'section' => 'third_party_providers',
                    'toggle' => 'settings_facebook_api',
                ],

                'instagram_db_version' => [
                    'type' => 'skip',
                    'default' => 0,
                ],

                //Instagram Basic API
                'instagram_accounts' => [
                    'type' => 'skip',
                    'default' => [],
                ],

                'instagram_basic_app_id' => [
                    'label' => fn() => esc_html__('Instagram Basic API App ID', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enter the App ID of your Facebook App which uses the Instagram Basic API.', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('e. g. 919397925252942', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'settings',
                    'section' => 'third_party_providers',
                    'toggle' => 'settings_instagram_api_basic',
                ],

                'instagram_basic_app_secret' => [
                    'label' => fn() => esc_html__('Instagram Basic API App Secret', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enter the App Secret of your Facebook App which uses the Instagram Basic API.', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('e. g. 9fa566f01095b9ca7e9ca63b36523qb5', 'dipi-divi-pixel'),
                    'type' => 'password',
                    'sanitize_callback' => [$this, 'sanitize_insta_app_secret_basic'],
                    'tab' => 'settings',
                    'section' => 'third_party_providers',
                    'toggle' => 'settings_instagram_api_basic',
                ],
                'instagram_connect_basic' => [
                    'label' => fn() => esc_html__('Connect Account', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Click the Connect button to connect an Instagram account using the Basic API. If an accounts access token becomes invalid, you can reconnect it here as well.', 'dipi-divi-pixel'),
                    'type' => 'callback',
                    'callback' => [$this, 'callback_instagram_connect_basic'],
                    'tab' => 'settings',
                    'section' => 'third_party_providers',
                    'toggle' => 'settings_instagram_api_basic',
                ],
                'instagram_connected_accounts_basic' => [
                    'label' => fn() => esc_html__('Connected Accounts', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('These accounts are connected via the Instagram Basic API. The status indicator shows whether or not the access token is still valid. Disconnect accounts by clicking the delete button.', 'dipi-divi-pixel'),
                    'type' => 'callback',
                    'callback' => [$this, 'callback_instagram_connected_accounts_basic'],
                    'tab' => 'settings',
                    'section' => 'third_party_providers',
                    'toggle' => 'settings_instagram_api_basic',
                ],

                //Instagram graph API
                'facebook_accounts' => [
                    'type' => 'skip',
                    'default' => [],
                ],
                'instagram_graph_app_id' => [
                    'label' => fn() => esc_html__('Instagram Graph API App ID', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enter the App ID of your Facebook App which uses the Instagram Graph API.', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('e. g. 919397925252942', 'dipi-divi-pixel'),
                    'type' => 'text',
                    'tab' => 'settings',
                    'section' => 'third_party_providers',
                    'toggle' => 'settings_instagram_api_graph',
                ],
                'instagram_graph_app_secret' => [
                    'label' => fn() => esc_html__('Instagram Graph API App Secret', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enter the App Secret of your Facebook App which uses the Instagram Graph API.', 'dipi-divi-pixel'),
                    'placeholder' => fn() => esc_html__('e. g. 9fa566f01095b9ca7e9ca63b36523qb5', 'dipi-divi-pixel'),
                    'type' => 'password',
                    'sanitize_callback' => [$this, 'sanitize_insta_app_secret_graph'],
                    'tab' => 'settings',
                    'section' => 'third_party_providers',
                    'toggle' => 'settings_instagram_api_graph',
                ],
                'instagram_connect_graph' => [
                    'label' => fn() => esc_html__('Connect Account', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Click the Connect button to connect an Facebook account and it\'s connected Instagram accounts using the Instagram Graph API. If an accounts access token becomes invalid, you can reconnect it here as well.', 'dipi-divi-pixel'),
                    'type' => 'callback',
                    'callback' => [$this, 'callback_instagram_connect_graph'],
                    'tab' => 'settings',
                    'section' => 'third_party_providers',
                    'toggle' => 'settings_instagram_api_graph',
                ],
                'instagram_connected_accounts_graph' => [
                    'label' => fn() => esc_html__('Connected Accounts', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('These accounts are connected via the Facebook and the Instagram Graph API. The status indicator shows whether or not the access token is still valid. Disconnect accounts by clicking the delete button.', 'dipi-divi-pixel'),
                    'type' => 'callback',
                    'callback' => [$this, 'callback_instagram_connected_accounts_graph'],
                    'tab' => 'settings',
                    'section' => 'third_party_providers',
                    'toggle' => 'settings_instagram_api_graph',
                ],

                'export_settings' => [
                    'label' => fn() => esc_html__('Export Settings Panel', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to export Divi Pixel plugin settings.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'import_export',
                    'section' => 'import_export',
                    'toggle' => 'export',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                ],
                'export_customizer' => [
                    'label' => fn() => esc_html__('Export Theme Customizer', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to export Divi Pixel Customizer settings.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'tab' => 'import_export',
                    'section' => 'import_export',
                    'toggle' => 'export',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                ],
                'export_button' => [
                    'label' => fn() => esc_html__('Export', 'dipi-divi-pixel'),
                    'type' => 'callback',
                    'description' => fn() => esc_html__('After choosing which options you would like to export, click the button to generate and dowload the export file.', 'dipi-divi-pixel'),
                    'callback' => [$this, 'callback_export'],
                    'tab' => 'import_export',
                    'section' => 'import_export',
                    'toggle' => 'export',
                ],
                'import_button' => [
                    'label' => fn() => esc_html__('Import', 'dipi-divi-pixel'),
                    'type' => 'callback',
                    'description' => fn() => esc_html__('Choose the file your want to import and click the import button.', 'dipi-divi-pixel'),
                    'callback' => [$this, 'callback_import'],
                    'tab' => 'import_export',
                    'section' => 'import_export',
                    'toggle' => 'import',
                ],

                'layout_importer' => [
                    'label' => fn() => esc_html__('Layout Importer', 'dipi-divi-pixel'),
                    'type' => 'callback',
                    'description' => fn() => esc_html__('Install Divi Pixle Layout Importer', 'dipi-divi-pixel'),
                    'callback' => [$this, 'callback_layout_import'],
                    'tab' => 'import_export',
                    'section' => 'import_export',
                    'toggle' => 'layout_importer',
                ],
            ]);
        }

        private function create_social_media_tab_fields()
        {
            $fields = [

                //Social Media Theme Customizer Toggle
                'social_icons_theme_customizer' => [
                    'label' => fn() => esc_html__('Customize Social Media Icons', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('With Divi Pixel you can select where and what social icons will be displayed on your website. You can also style your icons just like you want with a few clicks! To customize your social media icons go to Theme Customizer.', 'dipi-divi-pixel'),
                    'type' => 'theme_customizer',
                    'icon' => 'dp-share',
                    'class' => 'first_customizer_field',
                    'tab' => 'social_media',
                    'section' => 'social_media_general',
                    'toggle' => 'social_media_theme_customizer',
                ],

                'use_dipi_social_icons' => [
                    'label' => fn() => esc_html__('Enable Divi Pixel Social Icons', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to use the Divi Pixel social icons. Make sure to disable the Divi social icons under Divi → Theme Options → General.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                    'tab' => 'social_media',
                    'section' => 'social_media_general',
                    'toggle' => 'social_media_general',
                ],

                //Social Media General Toggle
                'social_links_new_tab' => [
                    'label' => fn() => esc_html__('Open Social Links in New Tab', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to open social links in new window.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                    'tab' => 'social_media',
                    'section' => 'social_media_general',
                    'toggle' => 'social_media_general',
                    'show_if' => [
                        'use_dipi_social_icons' => 'on',
                    ],
                ],
                'social_icons_individual_location' => [
                    'label' => fn() => esc_html__('Individual Icon Locations', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to individually choose the display location of each icon.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                    'tab' => 'social_media',
                    'section' => 'social_media_general',
                    'toggle' => 'social_media_general',
                    'show_if' => [
                        'use_dipi_social_icons' => 'on',
                    ],
                ],
                'social_icons_menu' => [
                    'label' => fn() => esc_html__('Show in Header', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to show social media icons in header primary or secondary menu.', 'dipi-divi-pixel'),
                    'type' => 'select',
                    'tab' => 'social_media',
                    'section' => 'social_media_general',
                    'toggle' => 'social_media_general',
                    'default' => 'none',
                    'options' => [
                        'none' => fn() => esc_html__('Don\'t show in Header', 'dipi-divi-pixel'),
                        'primary' => fn() => esc_html__('Primary Menu', 'dipi-divi-pixel'),
                        'secondary' => fn() => esc_html__('Secondary Menu', 'dipi-divi-pixel'),
                    ],
                    'show_if' => [
                        'use_dipi_social_icons' => 'on',
                        'social_icons_individual_location' => 'off',
                    ],
                ],
                'social_icons_mobile_menu' => [
                    'label' => fn() => esc_html__('Show in Mobile Menu', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to show the icons in the mobile menu.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                    'tab' => 'social_media',
                    'section' => 'social_media_general',
                    'toggle' => 'social_media_general',
                    'show_if' => [
                        'use_dipi_social_icons' => 'on',
                        'social_icons_individual_location' => 'off',
                    ],
                ],
                
                'social_icons_footer' => [
                    'label' => fn() => esc_html__('Show in Footer', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to show the icons in the footer.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                    'tab' => 'social_media',
                    'section' => 'social_media_general',
                    'toggle' => 'social_media_general',
                    'show_if' => [
                        'use_dipi_social_icons' => 'on',
                        'social_icons_individual_location' => 'off',
                    ],
                ],
                'enable_social_icons_multiple_menus' => [
                    'label' => fn() => esc_html__('Show in Multiple Menus', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to allow the icons in multiple menus.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                    'tab' => 'social_media',
                    'section' => 'social_media_general',
                    'toggle' => 'social_media_general',
                    'show_if' => [
                        'use_dipi_social_icons' => 'on'
                    ]
                ],
                'social_icons_multiple_menus' => [
                    'label' => fn() => esc_html__('Select Menu', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Select specific menu to display social icons', 'dipi-divi-pixel'),
                    'type' => 'select2',
                    'tab' => 'social_media',
                    'section' => 'social_media_general',
                    'toggle' => 'social_media_general',
                    'computed' => true,
                    'options' => [$this, 'get_menus'],
                    'show_if' => [
                        'use_dipi_social_icons' => "on",
                        'enable_social_icons_multiple_menus' => "on"
                    ],
                ]
            ];

            $fields += $this->create_social_network_fields('facebook', fn() => esc_html__('Facebook', 'dipi-divi-pixel'));
            $fields += $this->create_social_network_fields('instagram', fn() => esc_html__('Instagram', 'dipi-divi-pixel'));
            $fields += $this->create_social_network_fields('twitter', fn() => esc_html__('Twitter', 'dipi-divi-pixel'));
            $fields += $this->create_social_network_fields('youtube', fn() => esc_html__('YouTube', 'dipi-divi-pixel'));
            $fields += $this->create_social_network_fields('pinterest', fn() => esc_html__('Pinterest', 'dipi-divi-pixel'));
            $fields += $this->create_social_network_fields('vimeo', fn() => esc_html__('Vimeo', 'dipi-divi-pixel'));
            $fields += $this->create_social_network_fields('tumblr', fn() => esc_html__('Tumblr', 'dipi-divi-pixel'));
            $fields += $this->create_social_network_fields('linkedin', fn() => esc_html__('LinkedIn', 'dipi-divi-pixel'));
            $fields += $this->create_social_network_fields('flickr', fn() => esc_html__('Flickr', 'dipi-divi-pixel'));
            $fields += $this->create_social_network_fields('dribbble', fn() => esc_html__('Dribbble', 'dipi-divi-pixel'));
            $fields += $this->create_social_network_fields('skype', fn() => esc_html__('Skype', 'dipi-divi-pixel'));
            $fields += $this->create_social_network_fields('google', fn() => esc_html__('Google', 'dipi-divi-pixel'));
            $fields += $this->create_social_network_fields('xing', fn() => esc_html__('Xing', 'dipi-divi-pixel'));
            $fields += $this->create_social_network_fields('whatsapp', fn() => esc_html__('WhatsApp', 'dipi-divi-pixel'));
            $fields += $this->create_social_network_fields('snapchat', fn() => esc_html__('Snapchat', 'dipi-divi-pixel'));
            $fields += $this->create_social_network_fields('soundcloud', fn() => esc_html__('Soundcloud', 'dipi-divi-pixel'));
            $fields += $this->create_social_network_fields('tiktok', fn() => esc_html__('TikTok', 'dipi-divi-pixel'));
            $fields += $this->create_social_network_fields('telegram', fn() => esc_html__('Telegram', 'dipi-divi-pixel'));
            $fields += $this->create_social_network_fields('line', fn() => esc_html__('Line', 'dipi-divi-pixel'));
            $fields += $this->create_social_network_fields('quora', fn() => esc_html__('Quora', 'dipi-divi-pixel'));
            $fields += $this->create_social_network_fields('tripadvisor', fn() => esc_html__('Tripadvisor', 'dipi-divi-pixel'));
            $fields += $this->create_social_network_fields('twitch', fn() => esc_html__('Twitch', 'dipi-divi-pixel'));
            $fields += $this->create_social_network_fields('yelp', fn() => esc_html__('Yelp', 'dipi-divi-pixel'));
            $fields += $this->create_social_network_fields('spotify', fn() => esc_html__('Spotify', 'dipi-divi-pixel'));
            return $fields;
        }

        private function create_social_network_fields($id, $label)
        {   
            return [
                "social_media_{$id}" => [
                    'label' => fn() => $label(),
                    'placeholder' => fn() => sprintf('%1$s %2$s', $label(), esc_html__('URL', 'dipi-divi-pixel')),
                    'type' => 'text',
                    'tab' => 'social_media',
                    'class' => 'center_title',
                    'section' => "social_media_networks",
                    'toggle' => "social_media_{$id}",
                ],
                "social_media_{$id}_menu" => [
                    'label' => fn() => esc_html__('Show in Header', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to show social media icons in header primary or secondary menu.', 'dipi-divi-pixel'),
                    'type' => 'select',
                    'tab' => 'social_media',
                    'section' => "social_media_networks",
                    'toggle' => "social_media_{$id}",
                    'default' => 'none',
                    'options' => [
                        'none' => fn() => esc_html__('Don\'t show in Header', 'dipi-divi-pixel'),
                        'primary' => fn() => esc_html__('Primary Menu', 'dipi-divi-pixel'),
                        'secondary' => fn() => esc_html__('Secondary Menu', 'dipi-divi-pixel'),
                    ],
                    'show_if' => [
                        'use_dipi_social_icons' => 'on',
                        'social_icons_individual_location' => 'on',
                    ],
                ],
                "social_media_{$id}_footer" => [
                    'label' => fn() => esc_html__('Show in Footer', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to show the icon in the footer.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                    'tab' => 'social_media',
                    'section' => "social_media_networks",
                    'toggle' => "social_media_{$id}",
                    'show_if' => [
                        'use_dipi_social_icons' => 'on',
                        'social_icons_individual_location' => 'on',
                    ],
                ],
                "social_media_{$id}_mobile_menu" => [
                    'label' => fn() => esc_html__('Show in Mobile Menu', 'dipi-divi-pixel'),
                    'description' => fn() => esc_html__('Enable this option to show the icon in the mobile menu.', 'dipi-divi-pixel'),
                    'type' => 'checkbox',
                    'options' => [
                        'off' => fn() => esc_html__('No', 'dipi-divi-pixel'),
                        'on' => fn() => esc_html__('Yes', 'dipi-divi-pixel'),
                    ],
                    'tab' => 'social_media',
                    'section' => "social_media_networks",
                    'toggle' => "social_media_{$id}",
                    'show_if' => [
                        'use_dipi_social_icons' => 'on',
                        'social_icons_individual_location' => 'on',
                    ],
                ],
            ];
        }

        public function callback_reset_settings($field_id, $field)
        {
            include plugin_dir_path(__FILE__) . 'partials/callback-reset-settings.php';
        }

        public function callback_reset_customizer_settings($field_id, $field)
        {
            include plugin_dir_path(__FILE__) . 'partials/callback-reset-customizer-settings.php';
        }

        public function callback_export($field_id, $field)
        {
            include plugin_dir_path(__FILE__) . 'partials/callback-export-settings.php';
        }

        public function callback_import($field_id, $field)
        {
            include plugin_dir_path(__FILE__) . 'partials/callback-import-settings.php';
        }

        public function callback_instagram_connect_basic($field_id, $field)
        {
            include plugin_dir_path(__FILE__) . 'partials/callback-instagram-connect-basic.php';
        }

        public function callback_instagram_connected_accounts_basic($field_id, $field)
        {
            include plugin_dir_path(__FILE__) . 'partials/callback-instagram-connected-accounts-basic.php';
        }

        public function callback_instagram_connect_graph($field_id, $field)
        {
            include plugin_dir_path(__FILE__) . 'partials/callback-instagram-connect-graph.php';
        }

        public function callback_instagram_connected_accounts_graph($field_id, $field)
        {
            include plugin_dir_path(__FILE__) . 'partials/callback-instagram-connected-accounts-graph.php';
        }

        public function callback_notice_field($field_id, $field)
        {
            include plugin_dir_path(__FILE__) . 'partials/callback-notice.php';
        }
        public function callback_comingsoon_vip($field_id, $field)
        {
            $id = DIPI_Settings::settings_prefix() . $field_id;
            $value = DIPI_Settings::get_option($field_id);
            include plugin_dir_path(__FILE__) . 'partials/callback-coming-soon-vip-url.php';
        }
        public function callback_layout_import($field_id, $field)
        {
            include plugin_dir_path(__FILE__) . 'partials/callback-layout-importer.php';
        }

        public function sanitize_license($license)
        {
            if ($license === constant("DIPI_PASSWORD_MASK")) {
                return self::get_option('license');
            } else {
                return $license;
            }
        }

        public function sanitize_insta_app_secret_basic($secret)
        {
            if ($secret === constant("DIPI_PASSWORD_MASK")) {
                return self::get_option('instagram_basic_app_secret');
            } else {
                return $secret;
            }
        }

        public function sanitize_insta_app_secret_graph($secret)
        {
            if ($secret === constant("DIPI_PASSWORD_MASK")) {
                return self::get_option('instagram_graph_app_secret');
            } else {
                return $secret;
            }
        }

        public static function sanitize_checkbox($input)
        {
            if (isset($input) && 'on' === $input) {
                return 'on';
            } else {
                return 'off';
            }
        }

        public function get_pages()
        {
            if (!isset($this->pages) || is_null($this->pages)) {
                $this->pages = $this->get_posts_by_type('page');
            }
            return $this->pages;
        }
        
        public function get_popups()
        {
            if (!isset($this->popups) || is_null($this->popups)) {
                $this->popups = $this->get_posts_by_type('dipi_popup_maker');
            }
            return $this->popups;
        }

        protected function get_posts_by_type($post_type) {
            global $wpdb;

            $list = [0 => fn() => esc_html__('-- Select Item --', 'dipi-divi-pixel')];

            //phpcs:disable
            $query = $wpdb->prepare(
                "SELECT ID, post_title FROM $wpdb->posts WHERE post_type = %s and post_status = 'publish' ORDER BY post_title ASC",
                $post_type
            );

            $results = $wpdb->get_results($query);
            //phpcs:enable
            
            foreach ($results as $post) {
                $list[$post->ID] = !empty($post->post_title) 
                    ? $post->post_title 
                    : fn() => esc_html__('(no title)', 'dipi-divi-pixel');
            }

            return $list;
        }


        public function get_menus()
        {

            if (is_null($this->menus) || empty($this->menus)) {
                foreach (wp_get_nav_menus() as $menu) {
                    $this->menus["{$menu->term_id}"] = $menu->name;
                }
            }

            return $this->menus;
        }

        public function get_schedules() {
            if (is_null($this->schedules) || empty($this->schedules)) {
                foreach (wp_get_schedules() as $key => $schedule) {
                    $this->schedules["{$key}"] = $schedule['display'];
                }
            }
            return $this->schedules;
        }

        public function get_facebook_secret() {
            $secret =  get_option('dipi_facebook_page_token_secret');
            if(!$secret){
                $secret = wp_generate_uuid4();
                add_option('dipi_facebook_page_token_secret', wp_generate_uuid4());
            }
            return $secret;
        }
    }
}
