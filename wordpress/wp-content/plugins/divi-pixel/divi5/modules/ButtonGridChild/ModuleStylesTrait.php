<?php
namespace DIPI\Modules\ButtonGridChild;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Text\TextStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Spacing\SpacingStyle;
use ET\Builder\Packages\ModuleLibrary\Icon\IconModule;

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
  
    $itemType = $attrs['module']['advanced']['itemType']['desktop']['value'] ?? 'Text';
    $ribbonType = $attrs['ribbon']['decoration']['ribbonType']['desktop']['value'] ?? 'Text';
    $buttonIconDisabled = ($attrs['button']['decoration']['button']['desktop']['value']['icon']['enable'] ?? 'off') === 'off';

    $padding = $attrs['button']['decoration']['spacing']['desktop']['value']['padding'];
    $top = empty($padding['top']) ? ".3em" : $padding['top'];
    $bottom = empty($padding['bottom']) ? ".3em" : $padding['bottom'];
    $left = empty($padding['left']) ? "1em" : $padding['left'];
    $right = empty($padding['right']) ? "1em" : $padding['right'];

    $styles = [
      $elements->style(
        [
          'attrName'   => 'module',
          'styleProps' => [
            'disabledOn' => [
              'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
            ],
            'advancedStyles' => [
              [
                'componentName' => 'divi/text',
                'props' => [
                  'selector'=> "{$order_class} .dipi-pt-text, {$order_class} .dipi-pt-price-container",
                  'attr'=> $attrs['module']['advanced']['text'],
                ],
              ]
            ]
          ],
        ]
      ),
      CssStyle::style(
        [
          'selector'  => $order_class,
          'attr'      => $attrs['css'] ?? [],
          'cssFields' => static::custom_css(),
        ]
      ),
      $elements->style([
        'attrName' => 'text_style',
      ]),
      $elements->style([
        'attrName' => 'button',
      ]),
      $buttonIconDisabled ? CommonStyle::style([
        'selector' => "$order_class .dipi-button-wrap.dipi-button-grid:hover, $order_class:hover .dipi-button-wrap.dipi-button-grid",
        'attr' => static::getAttr($attrs, 'button'),
        'declarationFunction' => function ( array $args ) use ($top, $bottom, $left, $right) {
          return "padding-top: $top!important;padding-bottom: $bottom!important;padding-left: $left!important;padding-right: $right!important;";
        }
      ]) : null,
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