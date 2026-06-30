<?php
namespace DIPI\Modules\BeforeAfterSlider;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Text\TextStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Border\BorderStyle;

trait ModuleStylesTrait {

  use CustomCssTrait;
  use StyleDeclarationTrait;
  public static function getAttr($attrs, $attr, $default = null, $zoom = '', $unit = '', $wrap_func = '') {
    $AttrValue = (($attrs??[])[$attr]??[])['innerContent']??['desktop'=>['value'=>$default??'']];
    return $AttrValue;
  }
  public static function getDipiAttr($attrs, $attr, $default = null, $zoom = '', $unit = '', $wrap_func = '') {
    $beforeAttrValue = (($attrs??[])[$attr]??[])['innerContent']??['desktop'=>['value'=>$default??'']];
    $afterAttrValue = $beforeAttrValue;
    $slug_value = $afterAttrValue['desktop']['value']?? $default;
    $slug_value_tablet = $slug_value;
    if (empty($afterAttrValue['tablet'])) {
      $afterAttrValue['tablet'] = $afterAttrValue['desktop'];
    } else {
      $slug_value_tablet = $afterAttrValue['tablet']['value'];
    }
    $slug_value_phone = $slug_value_tablet;
    if (empty($afterAttrValue['phone'])) {
      $afterAttrValue['phone'] = $afterAttrValue['tablet'];
    } else {
      $slug_value_phone = $afterAttrValue['phone']['value'];
    }
    
    if ($zoom === '') {
      $slug_value = $slug_value . $unit;
      $slug_value_tablet = $slug_value_tablet. $unit;
      $slug_value_phone = $slug_value_phone .$unit;
    } else {
      $slug_value = ((float)$slug_value * $zoom) . $unit;
      $slug_value_tablet = ((float)$slug_value_tablet * $zoom) . $unit;
      $slug_value_phone = ((float)$slug_value_phone * $zoom) . $unit;
    }
    if ($wrap_func !== '') {
        $slug_value = "$wrap_func($slug_value)";
        $slug_value_tablet = "$wrap_func($slug_value_tablet)";
        $slug_value_phone = "$wrap_func($slug_value_phone)";
    }
    $afterAttrValue['desktop']['value'] = $slug_value;
    if (isset($beforeAttrValue['tablet'])) {
      $afterAttrValue['tablet']['value'] = $slug_value_tablet;
    }
    if (isset($beforeAttrValue['phone'])) {
      $afterAttrValue['phone']['value'] = $slug_value_phone;
    }
    return $afterAttrValue;
  }
  public static function getDipiAttrNumber($attrs, $attr, $default = null, $delta = 0) {
    $beforeAttrValue = (($attrs??[])[$attr]??[])['innerContent']??['desktop'=>['value'=>$default??'']];
    $afterAttrValue = $beforeAttrValue;
    $afterAttrValue['desktop']['value'] = (float)$beforeAttrValue['desktop']['value'] + (float)$delta;
    if (isset($beforeAttrValue['tablet'])) {
      $afterAttrValu['tablet']['value'] = (float)$beforeAttrValue['tablet']['value'] + (float)$delta;
    }
    if (isset($beforeAttrValue['phone'])) {
      $afterAttrValue['phone']['value'] = (float)$beforeAttrValue['phone']['value'] +(float)$delta;
    }
    return $afterAttrValue;
  }
  private static function _dipi_box_height($args ) 
  {
    $attrs    = $args['attrs'] ?? [];
    $order_class  = $args['orderClass'] ?? '';
    Style::add(
      [
        'id'            => $args['id'],
        'name'          => $args['name'],
        'orderIndex'    => $args['orderIndex'],
        'storeInstance' => $args['storeInstance'],
        'styles'        => [
          CommonStyle::style(
            [
              'selector' => "$order_class .dipi-before-after-slider-content, $order_class .dipi-before-after-slider-hover, $order_class .dipi-before-after-slider-container",
              'property' => 'height',
              'attr' =>static::getDipiAttr($attrs,'box_height',''),
            ]),
          CommonStyle::style(
            [

              'selector' => "$order_class .et_pb_section_video_bg video",
              'property' => 'height',
              'attr' =>static::getDipiAttr($attrs,'box_height',''),
              'important' => true,
            ]),
          CommonStyle::style(
            [

              'selector' => "$order_class .dipi-before-after-slider-container, $order_class .dipi-before-after-slider-content, $order_class .dipi-before-after-slider-hover",
              'property' => 'min-height',
              'attr' =>static::getDipiAttr($attrs,'box_height',''),
            ]),
        ]
      ]
    );
  }
  public static function module_styles( $args ) {
    $attrs    = $args['attrs'] ?? [];
    $elements = $args['elements'];
    $settings = $args['settings'] ?? [];
    $order_class  = $args['orderClass'] ?? '';
    
    $slider_selector = "$order_class .dipi_before_after_slider_handle:before, $order_class  .dipi_before_after_slider_handle:after";
    $before_slider_selector = "$order_class .dipi_before_after_slider_handle:before";
    $after_slider_selector = "$order_class  .dipi_before_after_slider_handle:after";
    $h_slider_selector = "$order_class .dipi_before_after_slider_horizontal .dipi_before_after_slider_handle:before, $order_class .dipi_before_after_slider_horizontal .dipi_before_after_slider_handle:after";
    $h_slider_before_selector = "$order_class .dipi_before_after_slider_horizontal .dipi_before_after_slider_handle:before";
    $h_slider_after_selector = "$order_class  .dipi_before_after_slider_horizontal .dipi_before_after_slider_handle:after";
    $v_slider_selector = "$order_class .dipi_before_after_slider_vertical .dipi_before_after_slider_handle:before, $order_class .dipi_before_after_slider_vertical .dipi_before_after_slider_handle:after";
    $v_slider_before_selector = "$order_class .dipi_before_after_slider_vertical .dipi_before_after_slider_handle:before";
    $v_slider_after_selector = "$order_class  .dipi_before_after_slider_vertical .dipi_before_after_slider_handle:after";
    $handle_selector = "$order_class .dipi_before_after_slider_handle";
    $handle_arrow_selector = "$order_class  .dipi_before_after_slider_left_arrow, 
        $order_class  .dipi_before_after_slider_right_arrow,
        $order_class  .dipi_before_after_slider_down_arrow,
        $order_class  .dipi_before_after_slider_up_arrow
        ";
    $handle_arrow_arrow_selector = "$order_class .arrow-handle_icon .dipi_before_after_slider_left_arrow, 
        $order_class .arrow-handle_icon .dipi_before_after_slider_right_arrow,
        $order_class .arrow-handle_icon .dipi_before_after_slider_down_arrow,
        $order_class .arrow-handle_icon .dipi_before_after_slider_up_arrow
        ";
    $handle_left_arrow_selector = "$order_class .dipi_before_after_slider_handle .dipi_before_after_slider_left_arrow";
    $handle_right_arrow_selector = "$order_class .dipi_before_after_slider_handle .dipi_before_after_slider_right_arrow";
    $handle_up_arrow_selector = "$order_class .dipi_before_after_slider_handle .dipi_before_after_slider_up_arrow";
    $handle_down_arrow_selector = "$order_class .dipi_before_after_slider_handle .dipi_before_after_slider_down_arrow";
    $handle_hover_left_arrow_selector = "$order_class .dipi_before_after_slider_container:not(.move_with_handle):hover .dipi_before_after_slider_left_arrow,
        $order_class .dipi_before_after_slider_handle:hover .dipi_before_after_slider_left_arrow";
    $handle_hover_right_arrow_selector = "$order_class .dipi_before_after_slider_container:not(.move_with_handle):hover .dipi_before_after_slider_right_arrow,
        $order_class .dipi_before_after_slider_handle:hover .dipi_before_after_slider_right_arrow";
    $handle_hover_up_arrow_selector = "$order_class .dipi_before_after_slider_container:not(.move_with_handle):hover .dipi_before_after_slider_up_arrow,
        $order_class .dipi_before_after_slider_handle:hover .dipi_before_after_slider_up_arrow";
    $handle_hover_down_arrow_selector = "$order_class .dipi_before_after_slider_container:not(.move_with_handle):hover .dipi_before_after_slider_down_arrow,
        $order_class .dipi_before_after_slider_handle:hover .dipi_before_after_slider_down_arrow";

    $styles = [
      // Module.
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
      TextStyle::style(
        [
          'selector' => "{$args['orderClass']} .example_flip_box__content-container",
          'attr'     => $attrs['module']['advanced']['text'] ?? [],
        ]
      ),
      CssStyle::style(
        [
          'selector'  => $args['orderClass'],
          'attr'      => $attrs['css'] ?? [],
          'cssFields' => static::custom_css(),
        ]
      ),


      // Title.
      $elements->style(
        [
          'attrName' => 'before_label',
        ]
      ),


      // Title.
      $elements->style(
        [
          'attrName' => 'after_label',
        ]
      ),
      $elements->style(
        [
          'attrName' => 'advanced_slider_line',
        ]
      ),
      $elements->style(
        [
          'attrName' => 'advanced_label'
        ]
        ),
      CommonStyle::style( [
          'selector' => "$order_class .dipi_before_after_slider_overlay .dipi_before_after_slider_before_label_span",
          'property' => 'background', 'attr' => static::getDipiAttr($attrs, 'before_label_bg_color', '')
      ]),

      CommonStyle::style( [
          'selector' => "$order_class .dipi_before_after_slider_overlay .dipi_before_after_slider_after_label_span",
          'property' => 'background', 'attr' => static::getDipiAttr($attrs, 'after_label_bg_color', '')
      ]),
      

      //Slider handle color
      CommonStyle::style( [
          'selector' => "$order_class .dipi_before_after_slider_handle:before, $order_class  .dipi_before_after_slider_handle:after",
          'property' => 'background', 'attr' => static::getAttr($attrs, 'slider_color', '')
      ]),
      // Horizontal
      CommonStyle::style(
          array(
              'attr'                => static::getDipiAttr($attrs, 'slider_width', ''),
              'selector' => $h_slider_selector,
              'property' => 'width',

          )
      ),
      CommonStyle::style([
        'selector'            => $h_slider_selector,
        'property' => 'margin-left',
        'attr'   => static::getDipiAttr($attrs, 'slider_width', '',
            -0.5,
            'px'
      ),
      'important' => true
      ]),
      // Vertical
      CommonStyle::style(
          array(
              'attr'                => static::getDipiAttr($attrs, 'slider_width', ''),
              'selector' => $v_slider_selector,
              'property' => 'height',

          )
          ),
      CommonStyle::style([
        'selector'            =>$v_slider_selector,
        'property' => 'margin-top',
        'attr'                => static::getDipiAttr($attrs, 'slider_width', '',
            -0.5,
            'px'
      ),
      'important' => true
      ]),



      CommonStyle::style( [
          'selector' => "$order_class .dipi_before_after_slider_handle",
          'property' => 'background', 'attr' => static::getDipiAttr($attrs, 'slider_handle_bg_color', '')
      ]),

      //Arrow of handle
      CommonStyle::style( [
          'selector' => "$order_class .dipi_before_after_slider_left_arrow",
          'property' => 'border-right-color', 'attr' => static::getDipiAttr($attrs, 'slider_handle_icon_color', '')
      ]),

      CommonStyle::style( [
          'selector' => "$order_class .dipi_before_after_slider_right_arrow",
          'property' => 'border-left-color', 'attr' => static::getDipiAttr($attrs, 'slider_handle_icon_color', '')
      ]),

      CommonStyle::style( [
          'selector' => "$order_class .dipi_before_after_slider_up_arrow",
          'property' => 'border-bottom-color', 'attr' => static::getDipiAttr($attrs, 'slider_handle_icon_color', '')
      ]),

      CommonStyle::style( [
          'selector' => "$order_class .dipi_before_after_slider_down_arrow",
          'property' => 'border-top-color', 'attr' => static::getDipiAttr($attrs, 'slider_handle_icon_color', '')
      ]),
      CommonStyle::style(
          array(
              'attr'                => static::getDipiAttr($attrs, 'slider_handle_icon_color', ''),
              'selector' => $handle_arrow_arrow_selector,
              'property' => 'border-color',

          )
          ),
    CommonStyle::style([
      'selector' =>$handle_selector,
          'attr'                => static::getDipiAttr($attrs, 'handle_container_bg_blur', '',
            1,
            'px',
            'blur'),
          'declarationFunction' => function ( array $args ) {
              $attrValue = $args['attrValue'];
              return  $attrValue? "backdrop-filter: {$attrValue};" : '';
          }
        ]
        ),
      CommonStyle::style(
          array(
              'attr' => static::getDipiAttr($attrs, 'handle_icon_size', ''),
              'selector' => $handle_arrow_selector,
              'property' => 'border-width',
          )
          ),
      CommonStyle::style(
          array(
              'attr'                => static::getDipiAttr($attrs, 'handle_icon_arrow_width', ''),
              'selector' => $handle_arrow_arrow_selector,
              'property' => 'border-width',

          )
          ),
      
      CommonStyle::style(
          array(
              'attr'                => static::getDipiAttr($attrs, 'handle_icon_size', ''),
              'selector' => $handle_arrow_arrow_selector,
              'property' => 'width',

          )
          ),
      CommonStyle::style(
          array(
              'attr'                => static::getDipiAttr($attrs, 'handle_icon_size', ''),
              'selector' => $handle_arrow_arrow_selector,
              'property' => 'height',

          )
          ),
      
      CommonStyle::style( array(
          'selector' => $handle_left_arrow_selector,
          'attr' => static::getDipiAttrNumber($attrs,'handle_icon_gap', '', -1),
          'declarationFunction' => function ( array $args ) {
            $attrValue = $args['attrValue'];
            return  "left: calc(50% - {$attrValue}px);";
          }
        )),
      CommonStyle::style( array(
          'selector' => $handle_right_arrow_selector,
          'attr' => static::getDipiAttrNumber($attrs,'handle_icon_gap', ''),
          'declarationFunction' => function ( array $args ) {
            $attrValue = $args['attrValue'];
            return  "right: calc(50% - {$attrValue}px);";
          }
      )),
      CommonStyle::style( array(
          'selector' => $handle_up_arrow_selector,
          'attr' => static::getDipiAttrNumber($attrs,'handle_icon_gap', ''),
          'declarationFunction' => function ( array $args ) {
            $attrValue = $args['attrValue'];
            return  "top: calc(50% - {$attrValue}px);";
          }
      )),
      CommonStyle::style( array(
          'selector' => $handle_down_arrow_selector,
          'attr' => static::getDipiAttrNumber($attrs,'handle_icon_gap', ''),
          'declarationFunction' => function ( array $args ) {
            $attrValue = $args['attrValue'];
            return  "bottom: calc(50% - {$attrValue}px);";
          }
        )),
          
      CommonStyle::style( array(
          'selector' => $handle_hover_left_arrow_selector,
          'attr' => static::getDipiAttrNumber($attrs,'handle_hover_icon_gap', '', -1),
          'declarationFunction' => function ( array $args ) {
            $attrValue = $args['attrValue'];
            return  "left: calc(50% - {$attrValue}px);";
          }
      )),
      CommonStyle::style( array(
          'selector' => $handle_hover_right_arrow_selector,
          'attr' => static::getDipiAttrNumber($attrs,'handle_hover_icon_gap', ''),
          'declarationFunction' => function ( array $args ) {
            $attrValue = $args['attrValue'];
            return  "right: calc(50% - {$attrValue}px);";
          }
        )),

      CommonStyle::style( array(
          'selector' => $handle_hover_up_arrow_selector,
          'attr' => static::getDipiAttrNumber($attrs,'handle_hover_icon_gap', ''),
          'declarationFunction' => function ( array $args ) {
            $attrValue = $args['attrValue'];
            return  "top: calc(50% - {$attrValue}px);";
          }
      )),
      CommonStyle::style( array(
          'selector' => $handle_hover_down_arrow_selector,
          'attr' => static::getDipiAttrNumber($attrs,'handle_hover_icon_gap', ''),
          'declarationFunction' => function ( array $args ) {
            $attrValue = $args['attrValue'];
            return  "bottom: calc(50% - {$attrValue}px);";
          }
      )),
      CommonStyle::style(
          array(
              'attr'                => static::getDipiAttr($attrs, 'handle_circle_size', ''),
              'selector' => $handle_selector,
              'property' => 'width',

          )
          ),
      CommonStyle::style(
          array(
              'attr'                => static::getDipiAttr($attrs, 'handle_circle_size', ''),
              'selector' => $handle_selector,
              'property' => 'height',

          )
          ),
      // Horizontal Handle
    CommonStyle::style([
      'selector' =>$h_slider_before_selector,
        'attr'  => static::getDipiAttr($attrs, 'handle_circle_size', '',
          -0.5,
          'px',
          'translateY'
        ),
        'property' =>'transform'
      ]),
    CommonStyle::style([
      'selector' =>$h_slider_after_selector,
      'property' =>'transform',
        'attr' => static::getDipiAttr($attrs, 'handle_circle_size', '',
          0.5,
          'px',
          'translateY'
      )
    ]),

     

      // Vertical Handle
    CommonStyle::style([
      'selector' =>$v_slider_before_selector,
      'property' => 'transform',
      'attr'=> static::getDipiAttr($attrs, 'handle_circle_size','',
          0.5,
          'px',
          'translateX'
        )
      ]),
    CommonStyle::style([
      'selector' =>$v_slider_after_selector,
      'property' => 'transform',
      'attr'  => static::getDipiAttr($attrs, 'handle_circle_size','',
          -0.5,
          'px',
          'translateX'
        )
      ]),
      CommonStyle::style(
        array(
            'attr'                => static::getAttr($attrs, 'handle_circle', ''),
            'selector' => $h_slider_before_selector,
            'declarationFunction' => function ( array $args ) {
              $attrValue = $args['attrValue'];
              return  is_array( $attrValue) ? "margin-bottom:".($attrValue['width'] ?? '0px').";" : "";
            }

        )
        ),
    CommonStyle::style(
        array(
            'attr'                => static::getAttr($attrs, 'handle_circle', ''),
            'selector' => $h_slider_after_selector,
            'declarationFunction' => function ( array $args ) {
              $attrValue = $args['attrValue'];
              return  is_array( $attrValue) ? "margin-top:".($attrValue['width'] ?? '0px').";" : "";
            }

        )
        ),
      CommonStyle::style(
          array(
              'attr'   => static::getAttr($attrs, 'handle_circle', ''),
              'selector' => $v_slider_before_selector,
              'declarationFunction' => function ( array $args ) {
                $attrValue = $args['attrValue'];
                return  is_array( $attrValue) ? "margin-left:".($attrValue['width'] ?? '0px').";" : "";
              }
          )
          ),
      CommonStyle::style( array(
        'selector' => $v_slider_after_selector,
        'attr' => static::getAttr($attrs,'handle_circle', ''),
        'declarationFunction' => function ( array $args ) {
          $attrValue = $args['attrValue'];
          return  is_array( $attrValue) ? "margin-right:calc(".($attrValue['width'] ?? '0px')." + 1px);" : "";
        }
      )),
          
        BorderStyle:: style(
          [
            'attr'  => static::getAttr($attrs, 'handle_circle', ''),
            'selector' => $handle_selector,
          ]
        )
      
    ];
    if("on" === static::getAttr($attrs, "always_show_labels", '')['desktop']['value']) {
      $styles[] = CommonStyle::style( [
          'selector' => "$order_class .dipi_before_after_slider_overlay .dipi_before_after_slider_after_label_span, $order_class .dipi_before_after_slider_overlay .dipi_before_after_slider_before_label_span",
          'attr' => static::getAttr($attrs,'always_show_labels', ''),
          'declarationFunction' => function ( array $args ) {
              return "opacity: 1 !important;";
          }
        ]);
    }
    if("on" ===static::getAttr($attrs, "enable_overlay", '')['desktop']['value']) {            
      $styles[] = CommonStyle::style( [
            'selector' => "$order_class .dipi_before_after_slider_container:not(.hide_on_hover-overlay) .dipi_before_after_slider_overlay:hover,
                $order_class .hide_on_hover-overlay .dipi_before_after_slider_handle:not(:hover) ~ .dipi_before_after_slider_overlay:not(:hover)
                ",
            'property' => 'background', 'attr' => static::getDipiAttr($attrs, 'overlay_color', '')
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