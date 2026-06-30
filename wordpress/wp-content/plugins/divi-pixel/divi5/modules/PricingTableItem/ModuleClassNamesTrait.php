<?php
namespace DIPI\Modules\PricingTableItem;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;

trait ModuleClassNamesTrait {
  public static function module_classnames( $args ) {
    $classnames_instance = $args['classnamesInstance'];
    $attrs               = $args['attrs'];
    $itemType = $attrs['module']['advanced']['itemType']['desktop']['value'] ?? 'Text';
    $ribbonType = $attrs['ribbon']['decoration']['ribbonType']['desktop']['value'] ?? 'text';
    $ribbonPlacement = $attrs['ribbon']['decoration']['ribbonPlacement']['desktop']['value'] ?? 'top-left';

    $classnames_instance->add(
      "dipi-pt-item--{$itemType}",
      true
    );

    if ( $itemType === 'Ribbon' ) {
      $classnames_instance->add(
        "dipi-pt-item--ribbon-{$ribbonPlacement}",
        true
      );
    }

    // Text Options.
    $classnames_instance->add(
      TextClassnames::text_options_classnames(
        $attrs['module']['advanced']['text'] ?? [],
        [
          'orientation' => false,
        ]
      ),
      true
    );

    // Module.
    $classnames_instance->add(
      ElementClassnames::classnames(
        [
          'attrs' => array_merge(
            $attrs['module']['decoration'] ?? [],
            [
              'link' => $attrs['module']['advanced']['link'] ?? [],
            ]
          ),
        ]
      )
    );

    $classnames_instance->add("et_pb_module");
  }
}