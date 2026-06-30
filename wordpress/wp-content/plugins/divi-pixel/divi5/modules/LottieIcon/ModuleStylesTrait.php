<?php
namespace DIPI\Modules\LottieIcon;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Text\TextStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Spacing\SpacingStyle;

trait ModuleStylesTrait {
  use CustomCssTrait;

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

  public static function module_styles( $args ) {
    $attrs    = $args['attrs'] ?? [];
    $elements = $args['elements'];
    $settings = $args['settings'] ?? [];
    $order_class = $args['orderClass'] ?? '';

    $styles = [
      $elements->style(
        [
          'attrName'   => 'module',
          'styleProps' => [
            'disabledOn' => [
              'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
            ],
          ],
        ]
      ),
      CssStyle::style(
        [
          'selector'  => $args['orderClass'],
          'attr'      => $attrs['css'] ?? [],
          'cssFields' => static::custom_css(),
        ]
      ),
      $elements->style([
        'attrName'   => 'title',
      ]),
      $elements->style([
        'attrName'   => 'content',
      ]),
      $elements->style([
        'attrName'   => 'button',
      ]),
      $elements->style([
        'attrName'   => 'lottie_spacing',
      ]),
      CommonStyle::style([
        'selector'            => "$order_class .dipi-lottie-icon",
        'attr'                => static::getAttr($attrs, 'lottie_width'),
        'declarationFunction' => function ( array $args ) {
            $attrValue = $args['attrValue'];
            return  "width: {$attrValue} !important;";
        }
      ]),
      CommonStyle::style([
        'selector'            => "$order_class .dipi-lottie-wrapper",
        'attr'                => static::getAttr($attrs, 'box_alignment'),
        'declarationFunction' => function ( array $args ) {
            $attrValue = $args['attrValue'];
            $align = $attrValue === "left" ? "flex-start" : ($attrValue === "right" ? "flex-end" : "center");
            return  "text-align: {$attrValue} !important; align-items: {$align}; justify-content: {$align};";
        }
      ])
    ];
    
    Style::add(
      [
        'id'            => $args['id'],
        'name'          => $args['name'],
        'orderIndex'    => $args['orderIndex'],
        'storeInstance' => $args['storeInstance'],
        'styles'        => $styles
      ]
    );
  }
}