<?php
namespace DIPI\Modules\TiltImage;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Text\TextStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Spacing\SpacingStyle;
use DIPI\Traits\StyleDeclarationTrait;

trait ModuleStylesTrait {

  use CustomCssTrait;
  use StyleDeclarationTrait;

  public static function module_styles( $args ) {
    $attrs    = $args['attrs'] ?? [];
    $elements = $args['elements'];
    $settings = $args['settings'] ?? [];
    $order_class = $args['orderClass'] ?? '';
    $tiltParallax  = $attrs['tiltBox']['advanced']['tiltParallax']['desktop']['value'] ?? 'off'; 
    $parallaxStyle = $tiltParallax === 'on' ?  : ''; 
    $useIcon = $attrs['overlay']['innerContent']['desktop']['value']['useIcon'];

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
    
      $elements->style(
        [
          'attrName'   => 'overlayIcon',
          'styleProps' => [
            // 'isAdvancedTransitionStyle' => ET_BUILDER_5_ADVANCED_TRANSITION_STYLE,
            'advancedStyles' => [
              [
                'componentName' => 'divi/common',
                'props'         => [
                  'selector' => $order_class,
                  'attr'     => $attrs['icon']['advanced']['align'] ?? [],
                  'declarationFunction' => [ IconModule::class, 'icon_alignment_declaration' ],
                ],
              ],
              [
                'componentName' => 'divi/common',
                'props'         => [
                  'attr' => $attrs['icon']['innerContent'] ?? [],
                  'selector'=> $order_class. " .et-pb-icon.dipi-tilt-overlay-icon",
                  'declarationFunction' => [ IconModule::class, 'icon_style_declaration' ],
                ],
              ],
              [
                'componentName' => 'divi/common',
                'selector'=> $order_class. " .et-pb-icon.dipi-tilt-overlay-icon",
                'props'         => [
                  'attr'     => $attrs['icon']['decoration']['color'] ?? [],
                  'property' => 'color',
                ],
              ],
             
            ],
          ],
        ]
      ),
      // Element: Title.
      $elements->style([
          'attrName' => 'title',
      ]),

      // Element: Content.
      $elements->style([
          'attrName' => 'overlayContent',
      ]),
      $elements->style([
          'attrName' => 'button',
      ]),
      $elements->style([
          'attrName' => 'overlayImage',
      ]),
      CommonStyle::style([
          'selector' => $order_class . ' .dipi-tilt-image-wrap .dipi-tilt-image-overlay,'.$order_class . ' .dipi-tilt-image-wrap img.dipi-tilt-main-image,' . $order_class . ' .js-tilt-glare',
          'attr' => $attrs['module']['decoration']['border'] ?? [],
          'declarationFunction' => function ( array $args ) {
            $attrValue = $args['attrValue'];
            $borderWidth = $attrValue['styles']['all']['width'] ?? '0px';
            return "border-top-left-radius: calc({$attrValue['radius']['topLeft']} - {$borderWidth});
              border-top-right-radius: calc({$attrValue['radius']['topRight']} - {$borderWidth});
              border-bottom-right-radius: calc({$attrValue['radius']['bottomRight']} - {$borderWidth});
              border-bottom-left-radius: calc({$attrValue['radius']['bottomLeft']} - {$borderWidth});";
          }
      ]),
      CommonStyle::style([
          'selector' => $order_class . ' .dipi-tilt-image-wrap,'.$order_class . ' .dipi-tilt-image-overlay',
          'attr' => $attrs['imageWrapper']['decoration']['height'] ?? [],
          'property' => 'height'
      ]),
      SpacingStyle::style([
          'selector' => $order_class . ' .et-pb-icon.dipi-tilt-overlay-icon',
          'attr' => $attrs['overlayIcon']['decoration']['spacing'] ?? []
      ]),
      CommonStyle::style([
        'selector' => $order_class . ' .et-pb-icon.dipi-tilt-overlay-icon',
        'attr' => $attrs['overlayIcon']['decoration']['color'] ?? [],
        'property' => 'color'
      ]),
      CommonStyle::style([
        'selector' => $order_class . ' .et-pb-icon.dipi-tilt-overlay-icon',
        'attr' => $attrs['overlayIcon']['decoration']['iconSize'] ?? [],
        'property' => 'font-size'
      ]),
      CommonStyle::style([
        'selector' => $order_class . ' .dipi-tilt-overlay-icon-circle',
        'attr' => $attrs['overlayIcon']['decoration']['circleColor'] ?? [],
        'property' => 'background-color'
      ]),
      CommonStyle::style([
        'selector' => $order_class . ' .et-pb-icon.dipi-tilt-overlay-icon.dipi-tilt-overlay-image-icon-wrap.dipi-tilt-overlay-icon-circle.dipi-tilt-overlay-icon-border',
        'attr' => $attrs['overlayIcon']['decoration']['circleBorderColor'] ?? [],
        'property' => 'border-color'
      ]),
      CommonStyle::style([
        'selector' => $order_class . ' .dipi-tilt-image-overlay',
        'attr' => $attrs['overlay']['decoration']['color'] ?? [],
        'property' => 'background-color'
      ]),
      CommonStyle::style([
        'selector' => $order_class . ' .dipi-tilt-overlay',
        'attr' => $attrs['overlay']['decoration']['alignHorizontal'] ?? [],
        'property' => "text-align"
      ]),
      CommonStyle::style([
        'selector' => $order_class . ' .et-pb-icon.dipi-tilt-overlay-icon',
        'attr' => $attrs['overlayIcon']['innerContent'] ?? [],
        'declarationFunction' => function ( array $args ) {
          $icon = $args['attrValue'] ?? '';
          return $icon !== '' ? static::icon_font_declaration($icon) : '';
        }
      ]) 
    ];

    if( 'off' === $useIcon ) {
      $styles[] = CommonStyle::style([
        'selector' => $order_class . ' .dipi-tilt-overlay-image-icon-wrap',
        'attr' => $attrs['overlayImage']['decoration']['imageWidth'] ?? [],
        'property' => "width"
      ]);
    }

    if($tiltParallax === 'on') {
      $styles[] = CommonStyle::style([
        'selector' => $order_class . '.dipi-tilt-image--has-parallax .dipi-tilt-overlay',
        'attr' => $attrs['tiltBox']['advanced']['tiltParallaxValue'] ?? [],
        'declarationFunction' => function ( array $args ) {
        
          $attrValue = $args['attrValue'];
          $style = $attrValue? "transform: translateZ({$attrValue});" : '';
          return $style;
        }
      ]);
    }
    
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