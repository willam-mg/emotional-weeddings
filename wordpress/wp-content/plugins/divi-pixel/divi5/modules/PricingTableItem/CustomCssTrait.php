<?php
namespace DIPI\Modules\PricingTableItem;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'Direct access forbidden.' );
}

trait CustomCssTrait {
  public static function custom_css() {
    return \WP_Block_Type_Registry::get_instance()->get_registered( 'dipi/pricing-table-item' )->customCssFields;
  }
}