<?php
namespace DIPI\Modules\HoverGalleryItem;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Text\TextStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Spacing\SpacingStyle;
use ET\Builder\Packages\ModuleLibrary\Icon\IconModule;

use DIPI\Traits\StyleDeclarationTrait;

trait ModuleStylesTrait {

  use CustomCssTrait;
  use StyleDeclarationTrait;
 

  public static function module_styles( $args ) {
    
    $attrs    = $args['attrs'] ?? [];
    $elements = $args['elements'];
     
    $settings = $args['settings'] ?? [];
    $order_class = $args['orderClass'] ?? '';
    $useImageIconActiveStyle =  $attrs['imageIconActive']['advanced']['useCustomStyle']['desktop']['value'] ?? 'off';
    $useImageIconStyle =  $attrs['imageIcon']['advanced']['useCustomStyle']['desktop']['value'] ?? 'off';
   
    $styles = [
      $elements->style(
        [
          'attrName'   => 'module',
          
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
        'attrName' => 'item',
      ]),
      $elements->style([
        'attrName' => 'itemActive',
      ]),
      $elements->style([
        'attrName' => 'title',
      ]),
      $elements->style([
        'attrName' => 'titleActive',
      ]),
      $elements->style([
        'attrName' => 'content',
      ]),
      $elements->style([
        'attrName' => 'contentActive',
      ]),
      $elements->style([
        'attrName' => 'button',
      ]),
      $elements->style([
        'attrName' => 'buttonActive',
      ])
       
    ];

    $styles[] = CommonStyle::style([
      'selector' => '.dipi_hover_gallery  '. $order_class . '.dipi_hover_gallery_item .dipi-hover-box-content-icon',
      'attr' => $attrs['imageIcon']['innerContent'] ?? [],
      'declarationFunction' => function ( array $args ) {
        $icon = $args['attrValue']['icon'] ?? '';
        return $icon !== '' ? static::icon_font_declaration($icon) : '';
      }
    ]);

      $styles[] = CommonStyle::style([
        'selector' => $order_class . ' .dipi-hg-title, ' . $order_class . ' .dipi-hg__item__content',
        'attr' => $attrs['item']['decoration']['horizontalAlign'] ?? [],
        'declarationFunction' => function ( array $args ) {
          $attrValue = $args['attrValue'];
          return "text-align: {$attrValue};";
        }
      ]);

      $styles[] = CommonStyle::style([
        'selector' => '.dipi_hover_gallery '. $order_class . '.dipi_hover_gallery_item',
        'attr' => $attrs['item']['decoration']['horizontalAlign'] ?? [],
        'declarationFunction' => function ( array $args ) {
          $attrValue = $args['attrValue'];
          $alignment_map = [
            'center' => 'center',
            'left' => 'flex-start',
            'right' => 'flex-end',
            'top' => 'flex-start',
            'bottom' => 'flex-end'
          ];
          return "align-items: {$alignment_map[$attrValue]};";
        }
      ]);
  
      $styles[] = CommonStyle::style([
        'selector' => '.dipi_hover_gallery '. $order_class . '.dipi_hover_gallery_item',
        'attr' => $attrs['item']['decoration']['verticalAlign'] ?? [],
        'declarationFunction' => function ( array $args ) {
          $attrValue = $args['attrValue'];
          $alignment_map = [
            'center' => 'center',
            'left' => 'flex-start',
            'right' => 'flex-end',
            'top' => 'flex-start',
            'bottom' => 'flex-end'
          ];
          return "justify-content: {$alignment_map[$attrValue]};";
        }
      ]);

      $styles[] = CommonStyle::style([
        'selector' => '.dipi_hover_gallery '. $order_class . '.dipi_hover_gallery_item .dipi-hg-button-wrapper',
        'attr' => $attrs['item']['decoration']['horizontalAlign'] ?? [],
        'declarationFunction' => function ( array $args ) {
          $attrValue = $args['attrValue'];
          return "text-align: {$attrValue};";
        }
      ]);

      $styles[] = CommonStyle::style([
        'selector' =>'.dipi_hover_gallery ' . $order_class . '.dipi_hover_gallery_item .dipi-image-wrap',
        'attr' => $attrs['imageIcon']['decoration']['imageWidth'] ?? [],
        'property' => "max-width"
      ]);
     

      if("on" === $useImageIconStyle) {
        $styles =  array_merge($styles, [
          $elements->style([
            'attrName' => 'imageIcon',
          ]),
          CommonStyle::style([
            'selector' => $order_class . ' .et-pb-icon.dipi-hover-box-content-icon',
            'attr' => $attrs['imageIcon']['decoration']['color'] ?? [],
            'property' => "color"
          ]),
          CommonStyle::style([
            'selector' => $order_class . ' .et-pb-icon.dipi-hover-box-content-icon',
            'attr' => $attrs['imageIcon']['decoration']['backgroundColor'] ?? [],
            'property' => "background-color"
          ]),
          CommonStyle::style([
            'selector' => $order_class . ' .et-pb-icon.dipi-hover-box-content-icon',
            'attr' => $attrs['imageIcon']['decoration']['iconSize'] ?? [],
            'property' => "font-size"
          ])
        ]);
      }
      if('on' === $useImageIconActiveStyle){
        $styles = array_merge($styles, [
          $elements->style([
            'attrName' => 'imageIconActive',
          ]),
          CommonStyle::style([
            'selector' => $order_class . '.active .et-pb-icon.dipi-hover-box-content-icon',
            'attr' => $attrs['imageIconActive']['decoration']['color'] ?? [],
            'property' => "color"
          ]),
          CommonStyle::style([
            'selector' => $order_class .'.active .et-pb-icon.dipi-hover-box-content-icon',
            'attr' => $attrs['imageIconActive']['decoration']['backgroundColor'] ?? [],
            'property' => "background-color"
          ]),
          CommonStyle::style([
            'selector' => $order_class .'.active .et-pb-icon.dipi-hover-box-content-icon',
            'attr' => $attrs['imageIconActive']['decoration']['iconSize'] ?? [],
            'property' => "font-size"
          ])
        ]);
      }

      $styles[] = CommonStyle::style([
        'selector' => '.dipi_hover_gallery ' . $order_class . ' .dipi-hg-button-wrapper',
        'attr' => $attrs['button']['decoration']['button'] ?? [],
        'declarationFunction' => function ( array $args ) {
          $attrValue = $args['attrValue'];
          $buttonAlignment = isset($attrValue['alignment']) ? $attrValue['alignment'] : '';
          return 'text-align: ' . $buttonAlignment . ';';
        }
      ]);
 
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