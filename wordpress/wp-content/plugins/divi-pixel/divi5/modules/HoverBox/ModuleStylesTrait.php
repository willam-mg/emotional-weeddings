<?php
namespace DIPI\Modules\HoverBox;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Text\TextStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;

trait ModuleStylesTrait {

    use CustomCssTrait;
    use StyleDeclarationTrait;
    public static function getAttrByMode($attrs, $attr, $default = null, $mode = null) {
        return (((($attrs??[])[$attr]??[])['innerContent']??[])[$mode??'desktop']??[])['value']??$default??'';
    }
    public static function getAttr($attrs, $attr, $default = null) {
        return (($attrs??[])[$attr]??[])['innerContent']??['desktop'=>['value'=>$default??'']];
    }
    private static function _dipi_box_height($args ) 
    {
        $attrs        = $args['attrs'] ?? [];
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];
        $order_class    = $args['orderClass'] ?? '';
        Style::add(
            [
                'id'            => $args['id'],
                'name'          => $args['name'],
                'orderIndex'    => $args['orderIndex'],
                'storeInstance' => $args['storeInstance'],
                'styles'                => [
                    CommonStyle::style([
                        'selector' => "$order_class .dipi-hover-box-content, $order_class .dipi-hover-box-hover, $order_class .dipi-hover-box-container",
                        'property' => 'height',
                        'attr' =>static::getAttr($attrs,'box_height',''),
                    ]),
                    CommonStyle::style([
                        'selector' => "$order_class .et_pb_section_video_bg video",
                        'property' => 'height',
                        'attr' =>static::getAttr($attrs,'box_height',''),
                        'important' => true,
                    ]),
                    CommonStyle::style([
                        'selector' => "$order_class .dipi-hover-box-container, $order_class .dipi-hover-box-content, $order_class .dipi-hover-box-hover",
                        'property' => 'min-height',
                        'attr' =>static::getAttr($attrs,'box_height',''),
                    ]),
                ]
            ]
        );
    }
    public static function module_styles( $args ) {
        $attrs = $args['attrs'] ?? [];
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];
        $order_class = $args['orderClass'] ?? '';
        $content_icon_selector = "$order_class .dipi-hover-box-content-icon";
        $content_hover_icon_selector = "$order_class .dipi-hover-box-hover-icon";
        $content_icon_color = static::getAttrByMode($attrs, 'content_icon_color', '');
        $content_circle_icon = static::getAttrByMode($attrs, 'content_circle_icon', '');
        $content_circle_color = static::getAttrByMode($attrs, 'content_circle_color', '');
        $content_circle_border = static::getAttrByMode($attrs, 'content_circle_border', '');
        $content_circle_border_color = static::getAttrByMode($attrs, 'content_circle_border_color', '');
        $content_icon_size = static::getAttrByMode($attrs, 'content_icon_size', '');
        $content_image_width = static::getAttrByMode($attrs, 'content_image_width', '');
        $content_hover_icon_color = static::getAttrByMode($attrs, 'content_hover_icon_color', '');
        $content_hover_circle_icon = static::getAttrByMode($attrs, 'content_hover_circle_icon', '');
        $content_hover_circle_color = static::getAttrByMode($attrs, 'content_hover_circle_color', '');
        $content_hover_circle_border = static::getAttrByMode($attrs, 'content_hover_circle_border', '');
        $content_hover_circle_border_color = static::getAttrByMode($attrs, 'content_hover_circle_border_color', '');
        $content_hover_icon_size = static::getAttrByMode($attrs, 'content_hover_icon_size', '');
        $content_hover_image_width = static::getAttrByMode($attrs, 'content_hover_image_width', '');

        if ('on' !== static::getAttrByMode($attrs, 'use_force_square', '')) {
            static::_dipi_box_height($args );
        }

        Style::add(
            [
                'id'            => $args['id'],
                'name'          => $args['name'],
                'orderIndex'    => $args['orderIndex'],
                'storeInstance' => $args['storeInstance'],
                'styles'                => [
                    // Module.
					$elements->style([
						'attrName'   => 'module',
						'styleProps' => [
                            'disabledOn' => [
                                'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
                            ],
                        ],
					]),
					TextStyle::style([
						'selector' => "{$args['orderClass']} .example_flip_box__content-container",
						'attr'     => $attrs['module']['advanced']['text'] ?? [],
					]),
					CssStyle::style([
                        'selector'  => $args['orderClass'],
                        'attr'      => $attrs['css'] ?? [],
                        'cssFields' => static::custom_css(),
					]),
                    // Image.
                    $elements->style([
                        'attrName' => 'front_image',
                    ]),

                    // Title.
                    $elements->style([
                        'attrName' => 'content_title',
                    ]),

                    // Content.
                    $elements->style([
                        'attrName' => 'body_text',
                    ]),

                    // Image.
                    $elements->style([
                        'attrName' => 'content_image',
                    ]),

                    // Icon.
                    $elements->style([
                        'attrName' => 'content_icon',
                    ]),
                    $elements->style([
                        'attrName' => 'content_hover_icon',
                    ]),
                    CommonStyle::style([
                        'selector'            => $content_icon_selector,
                        'attr'                => $attrs['content_icon']['innerContent'],
                        'declarationFunction' => [ static::class, 'icon_font_declaration' ],
                    ]),
                    CommonStyle::style([
                        'selector'            => $content_hover_icon_selector,
                        'attr'                => $attrs['content_hover_icon']['innerContent'],
                        'declarationFunction' => [ static::class, 'icon_font_declaration' ],
                    ]),

                    // Title.
                    $elements->style(
                        [
                            'attrName' => 'content_hover_title',
                        ]
                    ),

                    // Content.
                    $elements->style([
                        'attrName' => 'content_hover_content',
                    ]),

                    $elements->style([
                        'attrName' => 'content_hover_button',
                    ]),

                    // Hover box content and hover content styles - always apply regardless of force_square
                    $elements->style([
                        'attrName' => 'hover_box_content',
                    ]),
                    $elements->style([
                        'attrName' => 'hover_box_hover_content',
                    ]),

                    CommonStyle::style([
                        'selector' => "$order_class .dipi-hover-box-content .dipi-hover-box-content-icon",
                        'property' => 'color',
                        'attr' => static::getAttr($attrs,'content_icon_color',''),
                        'important' => true,
                    ]),

                    'on' == $content_circle_icon ?
                        CommonStyle::style([
                            'selector' => "$order_class .dipi-hover-box-content .dipi-content-icon-circle",
                            'property' => 'background-color',
                            'attr' => static::getAttr($attrs,'content_circle_color',''),
                            'important' => true,
                        ]) : null,

                    'on' == $content_circle_border ?
                        CommonStyle::style([
                            'selector' => "$order_class .dipi-hover-box-content .dipi-content-icon-border",
                            'property' => 'border-color',
                            'attr' => static::getAttr($attrs,'content_circle_border_color',''),
                            'important' => true,
                        ]) : null,

                    CommonStyle::style([
                        'selector' => "$order_class .dipi-hover-box-content .dipi-hover-box-content-icon",
                        'property' => 'font-size',
                        'attr' => static::getAttr($attrs,'content_icon_size',''),
                        'important' => true,
                    ]),
                    CommonStyle::style([
                        'selector' => "$order_class .dipi-hover-box-hover .dipi-hover-box-hover-icon",
                        'property' => 'color',
                        'attr' => static::getAttr($attrs,'content_hover_icon_color',''),
                        'important' => true,
                    ]),

                    'on' == $content_hover_circle_icon ?
                        CommonStyle::style([
                            'selector' => "$order_class .dipi-hover-box-hover .dipi-hover-icon-circle",
                            'property' => 'background-color',
                            'attr' => static::getAttr($attrs,'content_hover_circle_color',''),
                            'important' => true,
                        ]) : null,

                    'on' == $content_hover_circle_border ? 
                        CommonStyle::style([
                            'selector' => "$order_class .dipi-hover-box-hover .dipi-hover-icon-border",
                            'property' => 'border-color',
                            'attr' => static::getAttr($attrs,'content_hover_circle_border_color',''),
                            'important' => true,
                        ]) : null,

                    CommonStyle::style([
                        'selector' => "$order_class .dipi-hover-box-hover .dipi-hover-box-hover-icon",
                        'property' => 'font-size',
                        'attr' => static::getAttr($attrs,'content_hover_icon_size',''),
                        'important' => true,
                    ]),
                    CommonStyle::style([
                        'selector' => "$order_class .dipi-hover-box-content .dipi-image-wrap",
                        'property' => 'max-width', 
                        'attr' => static::getAttr($attrs,'content_image_width',''),
                        'important' => true,
                    ]),
                    CommonStyle::style([
                        'selector' => "$order_class .dipi-hover-box-hover .dipi-image-wrap",
                        'property' => 'max-width',
                        'attr' => static::getAttr($attrs,'content_hover_image_width',''),
                        'important' => true,
                    ]),
                    CommonStyle::style([
                        'selector' => "$order_class .dipi-hover-box-container .dipi-hover-box-content, $order_class .dipi-hover-box-container .dipi-hover-box-hover",
                        'property' => 'transition-duration',
                        'attr'=> static::getAttr($attrs, 'animation_speed',''),
                        'important' => true,
                    ])
                ]
            ]
        );
    }
}