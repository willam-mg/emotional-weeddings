<?php
namespace DIPI\Modules\InfoCircleItem;

if (!defined("ABSPATH")) {
    die("Direct access forbidden.");
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Text\TextStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Border\BorderStyle;
use ET\Builder\Packages\Module\Options\Spacing\SpacingStyle;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use DIPI\Traits\BaseRenderTrait;

trait ModuleStylesTrait
{
    use CustomCssTrait;
    use StyleDeclarationTrait;
	use BaseRenderTrait;
    
    private static $props = [];

    public static function getAttr(
        $attrs,
        $attr,
        $default = null,
        $zoom = "",
        $unit = "",
        $wrap_func = ""
    ) {
        $AttrValue = (($attrs ?? [])[$attr] ?? [])["innerContent"] ?? [
            "desktop" => ["value" => $default ?? ""],
        ];
        return $AttrValue;
    }

    public static function module_styles($args)
    {
        $attrs    = $args['attrs'] ?? [];
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];
        $order_class  = $args['orderClass'] ?? '';

        $info_icon_selector = "{$order_class}.dipi_info_circle_item .dipi_info_circle_item_container .dipi_info_circle-small .dipi-info-image-icon-wrap .et-pb-icon";
        $info_icon_hover_selector = "{$order_class}.dipi_info_circle_item .dipi_info_circle_item_container  .dipi_info_circle-small:hover .dipi-info-image-icon-wrap .et-pb-icon, {$order_class}.dipi_info_circle_item .dipi_info_circle_item_container.active  .dipi_info_circle-small:hover .dipi-info-image-icon-wrap .et-pb-icon";
        $info_icon_active_selector = "{$order_class}.dipi_info_circle_item .dipi_info_circle_item_container.active .dipi_info_circle-small .dipi-info-image-icon-wrap .et-pb-icon";
        $info_image_icon_width_selector = "{$order_class}.dipi_info_circle_item .dipi_info_circle_item_container .dipi-info-image-icon-wrap.dipi-image-wrapper";
        $info_image_icon_hover_width_selector = "{$order_class}.dipi_info_circle_item .dipi_info_circle_item_container .dipi-info-image-icon-wrap.dipi-image-wrapper:hover, {$order_class}.dipi_info_circle_item .dipi_info_circle_item_container.active .dipi-info-image-icon-wrap.dipi-image-wrapper:hover";
        $info_image_icon_active_width_selector = "{$order_class}.dipi_info_circle_item .dipi_info_circle_item_container.active .dipi-info-image-icon-wrap.dipi-image-wrapper";
        $content_icon_selector = "{$order_class}.dipi_info_circle_item .dipi_info_circle_item_container .dipi_info_circle_item-content-wrapper .dipi-content-image-icon-wrap .et-pb-icon";
        $content_image_icon_width_selector = "{$order_class}.dipi_info_circle_item .dipi_info_circle_item_container .dipi_info_circle_item-content-wrapper .dipi-content-image-icon-wrap.dipi-image-wrapper";

		$use_info_icon = static::getPropValue($attrs, 'use_info_icon') ?? "off";
		$use_content_icon = static::getPropValue($attrs, 'use_content_icon') ?? "off";

        $parent = BlockParserStore::get_parent( $args['id'], $args['storeInstance'] );
        $parent_innerBlocks = $parent->innerBlocks ?? [];
        
        // Calculate the total number of child items in the parent
        $child_items_count = count($parent_innerBlocks);
        
        // Calculate the index for this item within the parent
        $child_item_index = 0;
        for ($i = 0; $i < count($parent_innerBlocks); $i++) {
            $inner_block = $parent_innerBlocks[$i];
            // Handle both object and array structures
            $inner_block_id = is_object($inner_block) ? ($inner_block->id ?? null) : ($inner_block['id'] ?? null);
            
            if ($args['id'] === $inner_block_id) {
                $child_item_index = $i;
                break;
            }
        }

        $circle_list_size = $parent->attrs['circle_list_size']['innerContent']['desktop']['value'] ?? '400';
        $circle_list_size_tablet = $parent->attrs['circle_list_size']['innerContent']['tablet']['value'] ?? $circle_list_size;
        $circle_list_size_phone = $parent->attrs['circle_list_size']['innerContent']['phone']['value'] ?? $circle_list_size_tablet;

        $icon_area_offset = $parent->attrs['icon_area_offset']['innerContent']['desktop']['value'] ?? '0';
        $icon_area_offset_tablet = $parent->attrs['icon_area_offset']['innerContent']['tablet']['value'] ?? $icon_area_offset;
        $icon_area_offset_phone = $parent->attrs['icon_area_offset']['innerContent']['phone']['value'] ?? $icon_area_offset_tablet;

        $start_angle = $parent->attrs['start_angle']['innerContent']['desktop']['value'] ?? '0';
        $start_angle_tablet = $parent->attrs['start_angle']['innerContent']['tablet']['value'] ?? $start_angle;
        $start_angle_phone = $parent->attrs['start_angle']['innerContent']['phone']['value'] ?? $start_angle_tablet;

        $auto_mode = $parent->attrs['auto_mode']['innerContent']['desktop']['value'] ?? 'off';
        $auto_mode_tablet = $parent->attrs['auto_mode']['innerContent']['tablet']['value'] ?? $auto_mode;
        $auto_mode_phone = $parent->attrs['auto_mode']['innerContent']['phone']['value'] ?? $auto_mode_tablet;

        $auto_rotate_mode = $parent->attrs['auto_rotate_mode']['innerContent']['desktop']['value'] ?? 'loop';
        $auto_rotate_mode_tablet = $parent->attrs['auto_rotate_mode']['innerContent']['tablet']['value'] ?? $auto_rotate_mode;
        $auto_rotate_mode_phone = $parent->attrs['auto_rotate_mode']['innerContent']['phone']['value'] ?? $auto_rotate_mode_tablet;

        $reverse_anim_direction = $parent->attrs['reverse_anim_direction']['innerContent']['desktop']['value'] ?? 'on';
        $reverse_anim_direction_tablet = $parent->attrs['reverse_anim_direction']['innerContent']['tablet']['value'] ?? $reverse_anim_direction;
        $reverse_anim_direction_phone = $parent->attrs['reverse_anim_direction']['innerContent']['phone']['value'] ?? $reverse_anim_direction_tablet;

        $parent_args = [
            "desktop" => [
                "value" => [
                    "circle_list_size" => $circle_list_size,
                    "icon_area_offset" => $icon_area_offset,
                    "start_angle" => $start_angle,
                    "auto_mode" => $auto_mode,
                    "auto_rotate_mode" => $auto_rotate_mode,
                    "reverse_anim_direction" => $reverse_anim_direction,
                ]
            ],
            "tablet" => [
                "value" => [
                    "circle_list_size" => $circle_list_size_tablet,
                    "icon_area_offset" => $icon_area_offset_tablet,
                    "start_angle" => $start_angle_tablet,
                    "auto_mode" => $auto_mode_tablet,
                    "auto_rotate_mode" => $auto_rotate_mode_tablet,
                    "reverse_anim_direction" => $reverse_anim_direction_tablet,
                ]
            ],
            "phone" => [
                "value" => [
                    "circle_list_size" => $circle_list_size_phone,
                    "icon_area_offset" => $icon_area_offset_phone,
                    "start_angle" => $start_angle_phone,
                    "auto_mode" => $auto_mode_phone,
                    "auto_rotate_mode" => $auto_rotate_mode_phone,
                    "reverse_anim_direction" => $reverse_anim_direction_phone,
                ]
            ]
        ];

        Style::add([
            'id'            => $args['id'],
            'name'          => $args['name'],
            'orderIndex'    => $args['orderIndex'],
            'storeInstance' => $args['storeInstance'],
            'styles'        => [
                // Module.
                $elements->style([
                    'attrName'   => 'module',
                    'styleProps' => [
                        'disabledOn' => [
                            'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
                        ],
                    ],
                ]),
                CssStyle::style([
                    'selector'  => $args['orderClass'],
                    'attr'      => $attrs['css'] ?? [],
                    'cssFields' => static::custom_css(),
                ]),
                $elements->style([
                    'attrName'   => 'content_button',
                ]),
                $elements->style([
                    'attrName'   => 'info_image_icon',
                ]),
                $elements->style([
                    'attrName'   => 'info_image_icon_hover',
                ]),
                $elements->style([
                    'attrName'   => 'info_image_icon_active',
                ]),
                $elements->style([
                    'attrName'   => 'content_image',
                ]),
                $elements->style([
                    'attrName'   => 'content_title',
                ]),
                $elements->style([
                    'attrName'   => 'content_desc',
                ]),
                CommonStyle::style([
                    'selector'            => $info_icon_selector,
                    'attr'                => static::getAttr($attrs, 'info_icon_color'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return  "color: $attrValue;";
                    }
                ]),
                CommonStyle::style([
                    'selector'            => $info_icon_selector,
                    'attr'                => static::getAttr($attrs, 'info_image_icon_width'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return  "font-size: $attrValue;";
                    }
                ]),
                CommonStyle::style([
                    'selector'            => $info_image_icon_width_selector,
                    'attr'                => static::getAttr($attrs, 'info_image_icon_width'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return  "width: $attrValue;height: $attrValue;";
                    }
                ]),
                CommonStyle::style([
                    'selector'            => $info_icon_hover_selector,
                    'attr'                => static::getAttr($attrs, 'info_icon_hover_color'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return  "color: $attrValue;";
                    }
                ]),
                CommonStyle::style([
                    'selector'            => $info_icon_hover_selector,
                    'attr'                => static::getAttr($attrs, 'info_image_icon_hover_width'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return  "font-size: $attrValue;";
                    }
                ]),
                CommonStyle::style([
                    'selector'            => $info_image_icon_hover_width_selector,
                    'attr'                => static::getAttr($attrs, 'info_image_icon_hover_width'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return  "width: $attrValue;height: $attrValue;";
                    }
                ]),
                CommonStyle::style([
                    'selector'            => $info_icon_active_selector,
                    'attr'                => static::getAttr($attrs, 'info_icon_active_color'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return  "color: $attrValue;";
                    }
                ]),
                CommonStyle::style([
                    'selector'            => $info_icon_active_selector,
                    'attr'                => static::getAttr($attrs, 'info_image_icon_active_width'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return  "font-size: $attrValue;";
                    }
                ]),
                CommonStyle::style([
                    'selector'            => $info_image_icon_active_width_selector,
                    'attr'                => static::getAttr($attrs, 'info_image_icon_active_width'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return  "width: $attrValue;height: $attrValue;";
                    }
                ]),
                CommonStyle::style([
                    'selector'            => $content_icon_selector,
                    'attr'                => static::getAttr($attrs, 'content_icon_color'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return  "color: $attrValue;";
                    }
                ]),
                CommonStyle::style([
                    'selector'            => $content_icon_selector,
                    'attr'                => static::getAttr($attrs, 'content_image_icon_width'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return  "font-size: $attrValue;";
                    }
                ]),
                CommonStyle::style([
                    'selector'            => $content_image_icon_width_selector,
                    'attr'                => static::getAttr($attrs, 'content_image_icon_width'),
                    'declarationFunction' => function ( array $args ) {
                        $attrValue = $args['attrValue'];
                        return  "width: $attrValue;/*height: $attrValue;*/";
                    }
                ]),
                $use_info_icon === "on" ? CommonStyle::style([
                    'selector'            => "$order_class .dipi-info-image-icon-wrap .dipi-info-icon",
                    'attr'                => static::getAttr($attrs, 'info_icon'),
                    'declarationFunction' => [ static::class, 'icon_font_declaration' ],
                ]) : null,
                $use_content_icon === "on" ? CommonStyle::style([
                    'selector'            => "$order_class .dipi-content-image-icon-wrap .dipi-content-icon",
                    'attr'                => static::getAttr($attrs, 'content_icon'),
                    'declarationFunction' => [ static::class, 'icon_font_declaration' ],
                ]) : null,
                CommonStyle::style([
                    'selector'            => "$order_class .dipi_info_circle-small",
                    'attr'                => $parent_args,
                    'declarationFunction' => function ( array $args ) use ($child_items_count, $child_item_index) { 
                        $attrValue = $args['attrValue'];
                        $circle_list_size = $attrValue["circle_list_size"];
                        $icon_area_offset = $attrValue["icon_area_offset"];
                        $start_angle = $attrValue["start_angle"];
                        $auto_mode = $attrValue["auto_mode"];
                        $auto_rotate_mode = $attrValue["auto_rotate_mode"];
                        $reverse_anim_direction = $attrValue["reverse_anim_direction"];
                        $pai = pi();
                        $delta_angle = $child_items_count ? 2 * $pai / $child_items_count : 0;
                        $start_angle_rad = $start_angle * $pai / 360;
                        
                        $direction = ($auto_mode === 'on' 
                                && $auto_rotate_mode !== 'none' 
                                && $reverse_anim_direction === 'on') ? -1 : 1;
                        $angle = $direction * $child_item_index * $delta_angle - $pai / 2.0 + $start_angle_rad;

                        $r = (float) $circle_list_size / 2 + (float) $icon_area_offset;
                        $x0 = (float) $circle_list_size / 2;
                        $y0 = $r;
                        $x = $r * cos($angle) + $x0;
                        $y = $r * sin($angle) + $y0;

                        return  "left: {$x}px !important;top: {$y}px !important;";
                    }
                ])
            ],
		]);
    }
}
