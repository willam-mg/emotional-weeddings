<?php
namespace DIPI\Modules\Counter;

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
          'attrName' => 'counter',
        ]),
        $elements->style([
          'attrName' => 'prefix',
        ]),
        $elements->style([
          'attrName' => 'number',
        ]),
        $elements->style([
          'attrName' => 'suffix',
        ]),
        CommonStyle::style([
          'selector' => $order_class . ' .dipi_counter_number .dipi_counter_number_prefix, '.$order_class.' .dipi_counter_number .dipi_counter_number_suffix',
          'attr' => $attrs['counter']['advanced']['text_direction'] ?? [],
          'property' => "display"
        ]),
        CommonStyle::style([
          'selector' => $order_class . ' .half_circle .background-circle',
          'attr' => $attrs['counter']['decoration']['circle_track_color'] ?? [],
          'property' => "stroke"
        ]),
        CommonStyle::style([
          'selector' => $order_class . ' .dipi_counter_number_wrapper.half_circle',
          'attr' => $attrs['counter']['advanced']['circle_line_cap'] ?? [],
          'property' => "stroke"
        ]),
        CommonStyle::style([
          'selector' => $order_class . ' .dipi_counter_number_wrapper.half_circle',
          'attr' => $attrs['counter']['advanced']['circle_size'] ?? [],
          'property' => "width"
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