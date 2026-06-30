<?php
namespace DIPI\Modules\HoverGallery;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Text\TextStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Spacing\SpacingStyle;


function alignmentToStyleValues($alignment) {
  $alignment_map = array(
    'center' => 'center',
    'left'   => 'flex-start',
    'right'  => 'flex-end',
    'top'    => 'flex-start',
    'bottom' => 'flex-end'
  );
  $alignments = explode('-', $alignment); 
  return array(
    'align-items'      => $alignment_map[$alignments[0]],
    'justify-content'  => $alignment_map[$alignments[1]]
  );
}

function alignmentToStyle($alignment) {
  $alignments = alignmentToStyleValues($alignment); 
  return "align-items: {$alignments['align-items']}; justify-content: {$alignments['justify-content']};";
}
 
 


trait ModuleStylesTrait {

  use CustomCssTrait;

  public static function module_styles( $args ) {
    $attrs    = $args['attrs'] ?? [];
    $elements = $args['elements'];
    $settings = $args['settings'] ?? [];
    $order_class = $args['orderClass'] ?? '';

    
    $useImageIconActiveStyle = $attrs['imageIconActive']['innerContent']['desktop']['value']['useActiveStyle'] ?? 'off';
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
        'attrName' => 'grid',
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
        'attrName' => 'item',
      ]),
      $elements->style([
        'attrName' => 'itemActive',
      ]),
      $elements->style([
        'attrName' => 'imageIcon',
      ]),
      $elements->style([
        'attrName' => 'button',
      ]),
      $elements->style([
        'attrName' => 'buttonActive',
      ])
    ];

    
    if('on' === $useImageIconActiveStyle){
      $styles[] = $elements->style([
        'attrName' => 'imageIconActive',
      ]);
    }

    $styles[] = CommonStyle::style([
      'selector' => $order_class . ' .dipi-hg__images',
      'attr' => $attrs['module']['advanced']['galleryAnimation'] ?? [],
      'declarationFunction' => function ( array $args ) {
        $attrValue = $args['attrValue']['animationSpeed'] ?? '700ms';
        return "--dipi-hg-animation-speed :{$attrValue};";
      }
    ]);

    $styles[] = CommonStyle::style([
      'selector' => $order_class . ' .dipi-hg__items',
      'attr' => $attrs['module']['advanced']['columns'] ?? [],
      'declarationFunction' => function ( array $args ) {
        $attrValue = $args['attrValue'];

   

        return "--dipi-hg-grid-columns :{$attrValue};";
        // return "grid-template-columns :repeat({$attrValue}, 1fr);";
      }
    ]);
   

    $styles[] = CommonStyle::style([
      'selector' => $order_class . ' .dipi-hg__items',
      'attr' => $attrs['module']['advanced']['gridGap'] ?? [],
      'declarationFunction' => function ( array $args ) {
        $attrValue = $args['attrValue'];
        return "grid-row-gap :{$attrValue};grid-column-gap:{$attrValue};";
      }
    ]);
    
    $styles[] = CommonStyle::style([
      'selector' => $order_class . ' .dipi-hg__items',
      'attr' => $attrs['module']['advanced']['gridWidth'] ?? [],
      'property' => 'width'
    ]);
    $styles[] = CommonStyle::style([
      'selector' => $order_class . ' .dipi-hover-gallery',
      'attr' => $attrs['module']['advanced']['gridFullHeight'] ?? [],
      'declarationFunction' => function ( array $args ) {
        $attrValue = $args['attrValue'];
        if($attrValue === 'on'){
          return 'align-items: stretch !important;';
        }
        return '';
      }
    ]);
    
    $styles[] = CommonStyle::style([
      'selector' => $order_class . ' .dipi-hover-gallery',
      'attr' => $attrs['module']['advanced']['gridAlignment'] ?? [],
      'declarationFunction' => function ( array $args ) {
          $attrValue = $args['attrValue'];
          return alignmentToStyle($attrValue);
      }
    ]);
 
    $styles[] = CommonStyle::style([
      'selector' => $order_class . ' .dipi-hg-button-wrapper',
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