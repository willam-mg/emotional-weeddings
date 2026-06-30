<?php
namespace DIPI\Modules\BlogSlider;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

trait CustomCssTrait {
    public static function custom_css() {
        return \WP_Block_Type_Registry::get_instance()->get_registered( 'dipi/blog-slider' )->customCssFields;
    }
}