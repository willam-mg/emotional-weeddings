<?php
namespace DIPI\Modules\FilterableGallery;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;

trait ModuleClassNamesTrait {
    public static function module_classnames( $args ) {
        $classnames_instance = $args['classnamesInstance'];
        $attrs                             = $args['attrs'];

        // Module.
        $classnames_instance->add(
            ElementClassnames::classnames(
                [
                    'attrs' => array_merge(
                        $attrs['module']['decoration'] ?? [],
                        [
                            'link'     => $attrs['module']['advanced']['link'] ?? [],
                        ]
                    ),
                ]
            )
        );
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

    }
}