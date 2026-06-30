<?php
namespace DiviPixel;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('DIPI_Popup_On_Mobile_Menu')) {
    class DIPI_Popup_On_Mobile_Menu {
        private static $_instance;
        private static $popup_id;
        private static $use_hamburger;
        private static $allow_multiple_popups;
        private static $multiple_popup_ids;

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
            $popup_as_mobile_menu = DIPI_Settings::get_option('popup_as_mobile_menu');
            self::$allow_multiple_popups = DIPI_Settings::get_option('allow_multiple_popups');
            self::$popup_id = DIPI_Settings::get_option('mobile_menu_popup');
            self::$multiple_popup_ids = DIPI_Settings::get_option('multiple_mobile_menu_popup');
            self::$use_hamburger = DIPI_Settings::get_option('hamburger_animation');
            if($popup_as_mobile_menu) {
                add_filter('wp_nav_menu_items', [$this, 'nav_menu_items'], 11, 2);
                add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
                
            }
        }

        public function enqueue_scripts() {
            wp_enqueue_script('dipi-popup-maker-on-mobile-menu', plugins_url('dist/public/js/popup_on_mobile_menu.min.js', constant('DIPI_PLUGIN_FILE')), array('dipi-popup-maker-popup-effect', 'jquery'), DIPI_PM_VERSION, true);
        }
        
        public function nav_menu_items($items, $args) {
            $menu_id = DIPI_Settings::get_option('popup_select_menu');
            $menu_button_placement = DIPI_Settings::get_option('menu_button_placement');

            $fixed_mobile_header = DIPI_Settings::get_option('fixed_mobile_header');
            $adjust_anchor_links_pos_with_fixed_header = DIPI_Settings::get_option('adjust_anchor_links_pos_with_fixed_header');
            $scroll_offset = "0";

            if($fixed_mobile_header == "on" && $adjust_anchor_links_pos_with_fixed_header == "on")
                $scroll_offset = DIPI_Customizer::get_option('mobile_menu_header_height');

            $add_popup = self::dipi_is_popup_enabled($args, $menu_id);
            if( $add_popup ){
                $multiple_popup_ids = is_array(self::$multiple_popup_ids) ? implode(',', self::$multiple_popup_ids) : (self::$multiple_popup_ids ? self::$multiple_popup_ids : '');
                $popup_item = sprintf('<span class="dipi-popup-on-mobile-menu" data-popupid="%1$s" data-scrolloffset="%2$s" data-allow-multiple-popups="%3$s" data-multiple-popup-ids="%4$s" style="display: none!important;"/>', self::$popup_id, intval($scroll_offset), self::$allow_multiple_popups, $multiple_popup_ids);
                $items = ($menu_button_placement == 'a') ? $items . $popup_item : $popup_item . $items;
            }
            return $items;
        }
        
        function dipi_is_popup_enabled($args,  $menu_id){
            $current_menu_id = null;
            if(gettype($args->menu) == 'integer' ){
                $current_menu_id = $args->menu;
            }elseif(gettype($args->menu) == 'object' ){
                $current_menu_id = $args->menu->term_id;
            }
            if(is_null($current_menu_id)) return false;
            if(is_array($menu_id)){
                $integerIDs = array_map('intval', $menu_id);
                if(in_array(intval($current_menu_id), $integerIDs, true)){
                    return true;
                }
            } else {
                if(intval($current_menu_id) === intval($menu_id))
                return true;
            }
            return false;
        }
    }
}
DIPI_Popup_On_Mobile_Menu::instance();