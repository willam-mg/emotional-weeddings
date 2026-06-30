<?php 
namespace DIPI\Traits;
if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

trait BaseRenderTrait {

    static function getPropValue($attrs, $prop) {
        $attr = $attrs[$prop] ?? null;
        if(!$attr) return null;
        return is_array($attr) && array_key_exists('innerContent', $attr) ? (isset($attr['innerContent']['desktop']['value']) ? $attr['innerContent']['desktop']['value'] : null) : (isset($attr['desktop'])? $attr['desktop']['value']: null);
    }
    static function get_divi_layouts() {
        global $wpdb;
            
        $layouts = $wpdb->get_results($wpdb->prepare(
            "SELECT ID,post_title FROM $wpdb->posts
            WHERE post_type=%s",
            sanitize_text_field('et_pb_layout')
        ));
        
        $layouts_list = [
            '0' => ['label' => __('Select A Layout', 'dipi-divi-pixel')]
        ];
        
        if ( count($layouts) ) {
            foreach ( $layouts as $layout ){
                $layouts_list[$layout->ID] = ['label' => $layout->post_title];
            }
        }
        
        return $layouts_list;
    }
    static function render_library_layout($layoutId) {
        return \DIPI\Utils\LayoutController::render_divi_layout($layoutId);
    }
}
