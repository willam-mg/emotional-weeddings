<?php
namespace DIPI\Modules\HorizontalTimelineItem;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

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
        
        $icon_placement = $attrs['icon_placement']['innerContent']['desktop']['value'] ?? 'top';
        $icon_placement_tablet = $attrs['icon_placement']['innerContent']['tablet']['value'] ?? $icon_placement;
        $icon_placement_phone = $attrs['icon_placement']['innerContent']['phone']['value'] ?? $icon_placement_tablet;
        $custom_card_arrow = $attrs['custom_card_arrow']['innerContent']['desktop']['value'] ?? 'off';

        $classnames_instance->add("et_pb_module");
        $classnames_instance->add("dipi_htl_item_custom_classes");
        $classnames_instance->add(sprintf('dipi_htl_item_position_%1$s', esc_attr($icon_placement)));
        
        if (!empty($icon_placement_tablet)) {
            $classnames_instance->add(sprintf('dipi_htl_item_position_%1$s_tablet', esc_attr($icon_placement_tablet)));
        }
        
        if (!empty($icon_placement_phone)) {
            $classnames_instance->add(sprintf('dipi_htl_item_position_%1$s_phone', esc_attr($icon_placement_phone)));
        }

        if ($custom_card_arrow === 'on') {
            $classnames_instance->add('dipi_htl_item_custom-card-arrow');
        }
    }
}

