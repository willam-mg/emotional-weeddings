<?php
namespace DIPI\Modules\ButtonGrid;

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
  public static function getAttr($attrs, $attr, $default = null, $zoom = '', $unit = '', $wrap_func = '') {
    $AttrValue = (($attrs??[])[$attr]??[])['innerContent']??['desktop'=>['value'=>$default??'']];
    return $AttrValue;
  }
  public static function module_styles( $args ) {
    $attrs    = $args['attrs'] ?? [];
    $elements = $args['elements'];
    $settings = $args['settings'] ?? [];
    $orderClass = $args['orderClass'] ?? '';
  
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
        'attrName' => 'button_grid',
      ]),
      CommonStyle::style(
        array(
            'attr' => static::getAttr($attrs, 'flex_direction', 'column'),
            'selector' => "$orderClass .dipi-button-grid-container",
            'property' => 'flex-direction',
        )
      ),
      CommonStyle::style(
        array(
            'attr' => static::getAttr($attrs, 'justify_content', 'center'),
            'selector' => "$orderClass .dipi-button-grid-container",
            'property' => 'justify-content',

        )
      ),
      CommonStyle::style(
        array(
            'attr' => static::getAttr($attrs, 'align_items', 'baseline'),
            'selector' => "$orderClass .dipi-button-grid-container",
            'property' => 'align-items',

        )
      ),
      CommonStyle::style([
        'selector' =>"$orderClass .dipi-button-grid-container",
        'attr' => static::getAttr($attrs, 'flex_wrap', 'on'),
        'declarationFunction' => function ( array $args ) {
              $attrValue = $args['attrValue'];
              return  "flex-wrap:". ($attrValue === 'on'? 'wrap' : 'nowrap').";";
        }
      ]),
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