<?php
namespace DIPI\Modules\PriceList;

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

  /**
   * Default separator decoration attributes (must match module-default-render-attributes.json).
   * Used on front-end when saved attrs do not include separator values.
   *
   * @return array<string, array<string, array<string, string>>>
   */
  private static function default_separator_decoration() {
    return [
      'separatorStyle'  => [ 'desktop' => [ 'value' => 'dotted' ] ],
      'separatorWeight' => [ 'desktop' => [ 'value' => '2px' ] ],
      'separatorColor'  => [ 'desktop' => [ 'value' => '#7ebec5' ] ],
      'separatorSpacing' => [ 'desktop' => [ 'value' => '5px' ] ],
    ];
  }

  public static function module_styles( $args ) {
    $attrs    = $args['attrs'] ?? [];
    $elements = $args['elements'];
    $settings = $args['settings'] ?? [];
    $order_class = $args['orderClass'] ?? '';

    // Ensure separator decoration has defaults so the separator displays on front-end
    // when the user has not changed Separator options (VB merges defaults; FE does not).
    $separator_decoration = isset( $attrs['separator']['decoration'] ) && is_array( $attrs['separator']['decoration'] )
      ? $attrs['separator']['decoration']
      : [];
    $attrs['separator'] = $attrs['separator'] ?? [];
    $attrs['separator']['decoration'] = array_replace_recursive(
      static::default_separator_decoration(),
      $separator_decoration
    );

    // Ensure item.decoration.headerAlignment defaults to "baseline" on front-end (VB uses default-render-attributes).
    $item_decoration = isset( $attrs['item']['decoration'] ) && is_array( $attrs['item']['decoration'] )
      ? $attrs['item']['decoration']
      : [];
    $attrs['item'] = $attrs['item'] ?? [];
    $attrs['item']['decoration'] = array_replace_recursive(
      [ 'headerAlignment' => [ 'desktop' => [ 'value' => 'baseline' ] ] ],
      $item_decoration
    );

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
          'attrName' => 'image',
      ]),
      $elements->style([
          'attrName' => 'title',
      ]),
      $elements->style([
          'attrName' => 'content',
      ]),
      $elements->style([
          'attrName' => 'price',
      ]),
      CommonStyle::style([
          'selector' => $order_class . ' .dipi_price_list_item_wrapper',
          'attr' => $attrs['module']['advanced']['layout'] ?? [],
          'property' => "flex-direction"
      ]),
      CommonStyle::style([
          'selector' => $order_class . ' .dipi_price_list_item_wrapper',
          'attr' => $attrs['module']['advanced']['imageAlignment'] ?? [],
          'property' => "align-items"
      ]),
      CommonStyle::style([
          'selector' => $order_class . ' .dipi_price_list_header',
          'attr' => $attrs['item']['decoration']['headerAlignment'] ?? [],
          'property' => "align-items"
      ]),
      CommonStyle::style([
          'selector' => $order_class . ' .dipi_price_list_item .dipi_price_list_separator',
          'attr' => $attrs['separator']['decoration']['separatorStyle'] ?? [],
          'property' => "border-bottom-style"
      ]),
      CommonStyle::style([
          'selector' => $order_class . ' .dipi_price_list_item .dipi_price_list_separator',
          'attr' => $attrs['separator']['decoration']['separatorWeight'] ?? [],
          'property' => "border-bottom-width"
      ]),
      CommonStyle::style([
          'selector' => $order_class . ' .dipi_price_list_item .dipi_price_list_separator',
          'attr' => $attrs['separator']['decoration']['separatorColor'] ?? [],
          'property' => "border-bottom-color"
      ]),
      CommonStyle::style([
          'selector' => $order_class . ' .dipi_price_list_item .dipi_price_list_separator',
          'attr' => $attrs['separator']['decoration']['separatorSpacing'] ?? [],
          'declarationFunction' => function ( array $args ) {
            $attrValue = $args['attrValue'];
            return "margin-left: {$attrValue};margin-right: {$attrValue};";
         }
      ]),
      CommonStyle::style([
          'selector' => $order_class . ' .dipi_price_list_image_wrapper',
          'attr' => $attrs['image']['decoration']['imageSpacing'] ?? [],
          'property' => "margin-right"
      ]),
      CommonStyle::style([
          'selector' => $order_class . ' .dipi_price_list_image_wrapper',
          'attr' => $attrs['image']['decoration']['imageWidth'] ?? [],
          'property' => "width"
      ]),
      CommonStyle::style([
          'selector' => $order_class . ' .dipi_price_list_image_wrapper',
          'attr' => $attrs['image']['decoration']['imageMinWidth'] ?? [],
          'property' => "min-width"
      ]),
      CommonStyle::style([
          'selector' => $order_class . '.dipi_price_list .dipi_price_list_item:not(:last-child)',
          'attr' => $attrs['item']['decoration']['itemSpacing'] ?? [],
          'property' => "margin-bottom"
      ]),
      SpacingStyle::style([
          'selector' => $order_class . ' .dipi_price_list_item',
          'attr' => $attrs['item']['decoration']['itemPadding'] ?? [],
      ]),
      SpacingStyle::style([
          'selector' => $order_class . ' .dipi_price_list_item .dipi_price_list_text_wrapper',
          'attr' => $attrs['item']['decoration']['textSpacing'] ?? [],
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