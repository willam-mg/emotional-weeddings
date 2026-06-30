<?php
namespace DiviPixel;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

if (!class_exists('DIPI_MobileMenu')) {
    class DIPI_MobileMenu
    {
        private static $instance = null;

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

            if (DIPI_Settings::get_option('custom_breakpoints')) {
                $this->initialize_breakpoint_feature();
            }

            if (DIPI_Settings::get_option('collapse_submenu')) {
                $this->initialize_collapse_submenu_feature();
            }

            if (DIPI_Settings::get_option('fixed_mobile_header')) {
                $this->initialize_fixed_header_feature();
            }

            if (DIPI_Settings::get_option('mobile_menu_style') && DIPI_Settings::get_option('mobile_menu_fullscreen')) {
                $this->initialize_fullscreen_feature();
            }

            if (DIPI_Settings::get_option('hamburger_animation')) {
                $this->initialize_hamburger_feature();
            }

            if (DIPI_Settings::get_option('mobile_menu_style')) {
                $this->initialize_mobile_menu_styles();
                $this->initialize_mobile_options();
                $this->initialize_mobile_submenu_styles();
            }
        }

        /**
         * Breakpoint Feature
         */
        private function initialize_breakpoint_feature()
        {
            add_action('wp_head', [$this, 'breakpoint_wp_head']);
            add_action('wp_enqueue_scripts', [$this, 'breakpoint_enqueue_scripts']);
        }

        public function breakpoint_wp_head()
        {
            require __DIR__ . '/styles/StylesBreakpoint.php';
            require __DIR__ . '/styles/DiviCoreStyles.php';
        }

        public function breakpoint_enqueue_scripts()
        {
            wp_enqueue_script('DIPI_Breakpoint', plugins_url('dist/public/js/Breakpoint.min.js', constant('DIPI_PLUGIN_FILE')), ['jquery'], constant('DIPI_VERSION'), true);
        }

        /**
         * Collapse Submenu Feature
         */
        private function initialize_collapse_submenu_feature()
        {
            add_action('wp_head', [$this, 'collapse_submenu_wp_head']);
            add_action('wp_enqueue_scripts', [$this, 'collapse_submenu_enqueue_scripts']);
        }

        public function collapse_submenu_wp_head()
        {
            require __DIR__ . '/styles/StylesCollapseSubmenu.php';
        }

        public function collapse_submenu_enqueue_scripts()
        {
            wp_enqueue_script('DIPI_CollapseSubmenu', plugins_url('/dist/public/js/CollapseSubmenu.min.js', constant('DIPI_PLUGIN_FILE')), ['jquery'], constant('DIPI_VERSION'), true);

            wp_localize_script(
                'DIPI_CollapseSubmenu',
                'dipi_CollapseSubmenu_data',
                [
                    'collapse_submenu_prevent_parent_opening' => DIPI_Settings::get_option('collapse_submenu_prevent_parent_opening'),
                ]
            );
        }

        /**
         * Fixed Mobile Header Feature
         */
        private function initialize_fixed_header_feature()
        {
            add_action('wp_head', [$this, 'fixed_header_wp_head']);
            if(DIPI_Settings::get_option('adjust_anchor_links_pos_with_fixed_header')) {
                add_action('wp_enqueue_scripts', [$this, 'fixed_header_enqueue_scripts']);
            }
        }

        public function fixed_header_wp_head()
        {
            require __DIR__ . '/styles/StylesFixedHeader.php';
        }

        public function fixed_header_enqueue_scripts()
        {
            wp_enqueue_script('DIPI_AdjustScrollOffset', plugins_url('dist/public/js/AdjustScrollOffset.min.js', constant('DIPI_PLUGIN_FILE')), ['jquery'], constant('DIPI_VERSION'), true);
        }

        /**
         * Fullscreen Feature
         */
        private function initialize_fullscreen_feature()
        {
            add_action('body_class', [$this, 'fullscreen_body_class'], 5);
            add_action('wp_head', [$this, 'fullscreen_wp_head']);
            add_action('wp_enqueue_scripts', [$this, 'fullscreen_enqueue_scripts']);
        }

        public function fullscreen_body_class($classes)
        {
            if (DIPI_Settings::get_option('mobile_menu_fullscreen')) {
                $classes[] = 'dipi-mobile-menu-fullscreen';
            }

            return $classes;
        }

        public function fullscreen_wp_head()
        {
            require __DIR__ . '/styles/StylesFullScreen.php';
        }

        public function fullscreen_enqueue_scripts()
        {
            wp_enqueue_script('DIPI_MobileMenuFullscreen', plugins_url('dist/public/js/Fullscreen.min.js', constant('DIPI_PLUGIN_FILE')), ['jquery'], constant('DIPI_VERSION'), true);

            $slide_menu_class = 'dipi-menu-animation-' . str_replace('_', '-', DIPI_Customizer::get_option('mobile_menu_animation'));
            $background_animation_class = 'dipi-menu-background-animation-' . str_replace('_', '-', DIPI_Customizer::get_option('mobile_menu_background_animation'));

            wp_localize_script(
                'DIPI_MobileMenuFullscreen',
                'dipi_data',
                [
                    'slide_menu_class' => $slide_menu_class,
                    'background_animation_class' => $background_animation_class,
                ]
            );
        }

        /**
         * Hamburger Customization Feature
         */
        private function initialize_hamburger_feature()
        {
            add_action('wp_head', [$this, 'hamburger_wp_head']);
        }

        public function hamburger_wp_head()
        {
            require __DIR__ . '/styles/StylesHamburger.php';
        }

        /**
         * Mobile Menu Styles
         */
        private function initialize_mobile_menu_styles()
        {
            add_action('wp_head', [$this, 'mobile_menu_styles_wp_head']);
        }

        public function mobile_menu_styles_wp_head()
        {
            require __DIR__ . '/styles/StylesMobileMenu.php';
        }

        /**
         * Mobile Options
         */
        private function initialize_mobile_options()
        {
            add_action('wp_head', [$this, 'mobile_options_wp_head']);
        }

        public function mobile_options_wp_head()
        {
            require __DIR__ . '/styles/StylesMobileOption.php';
        }

        /**
         * Mobile Submenu Styles
         */
        private function initialize_mobile_submenu_styles()
        {
            add_action('wp_head', [$this, 'mobile_submenu_styles_wp_head']);
        }

        public function mobile_submenu_styles_wp_head()
        {
            require __DIR__ . '/styles/StylesMobileSubmenu.php';
        }
    }

    // Initialize the mobile menu class
    DIPI_MobileMenu::instance();
}