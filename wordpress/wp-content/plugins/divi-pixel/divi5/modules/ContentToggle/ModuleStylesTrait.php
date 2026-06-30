<?php
namespace DIPI\Modules\ContentToggle;

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
  public static function getAttrByMode($attrs, $attr, $default = null, $mode = null) {
		return (((($attrs??[])[$attr]??[])['innerContent']??[])[$mode??'desktop']??[])['value']??$default??'';
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
  
  public static function module_styles( $args ) {
    $attrs    = $args['attrs'] ?? [];
    $elements = $args['elements'];
    $settings = $args['settings'] ?? [];
    $order_class  = $args['orderClass'] ?? '';
    $toggle_alignment = static::getAttrByMode($attrs,'toggle_alignment', 'center') ?? 'center';
    
		$toggle_alignment = $toggle_alignment === 'left' ? 'flex-start' :
			($toggle_alignment === 'right' ? 'flex-end' : 'center');
    $first_btn_selector = "$order_class .dipi-content-toggle__button .dipi-content-toggle__slider:before";
    $first_bg_selector = "$order_class .dipi-content-toggle__slider";
    $second_btn_selector = "$order_class input.dipi-content-toggle__switch:checked + .dipi-content-toggle__slider:before";
    $second_bg_selector = "$order_class input.dipi-content-toggle__switch:checked + .dipi-content-toggle__slider";
    $first_content_selector = "$order_class .dipi-content-toggle__first-layout";
    $second_content_selector = "$order_class .dipi-content-toggle__second-layout";
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


      $elements->style(
        [
          'attrName' => 'first_text',
        ]
      ),

      // Title.
      $elements->style(
        [
          'attrName' => 'second_text',
        ]
      ),
      CommonStyle::style([
        'selector'  => "$order_class .dipi-content-toggle__button-container",
        'attr' =>  ['desktop'=>['value'=>'']],
        'declarationFunction' => function ( array $args ) use ($toggle_alignment) {
          return "justify-content: $toggle_alignment;";
        }
      ]),
      CommonStyle::style([
        'selector'  => "$order_class .dipi-content-toggle__button",
        'property' => 'font-size',
        'attr' => static::getAttr($attrs, 'toggle_size','14px')
      ]),
      CommonStyle::style([
        'selector'  => $first_btn_selector,
        'property' => 'background-color',
        'attr' => static::getAttr($attrs, 'first_btn_color','#fff')
      ]),
      CommonStyle::style([
        'selector'  => $first_bg_selector,
        'property' => 'background-color',
        'attr' => static::getAttr($attrs, 'first_bg_color','#d3d3d3')
      ]),
      CommonStyle::style([
        'selector'  => $second_btn_selector,
        'property' => 'background-color',
        'attr' => static::getAttr($attrs, 'second_btn_color','#fff')
      ]),
      CommonStyle::style([
        'selector'  => $second_bg_selector,
        'property' => 'background-color',
        'attr' => static::getAttr($attrs, 'second_bg_color','#ff4200')
      ]),
      CommonStyle::style([
        'selector'  => $first_content_selector,
        'property' => 'animation-duration',
        'attr' => static::getAttr($attrs, 'first_content_speed','600ms'),
        'important' =>true
      ]),
      CommonStyle::style([
        'selector'  => $first_content_selector,
        'property' => 'animation-delay',
        'attr' => static::getAttr($attrs, 'first_content_delay','0ms'),
        'important' =>true
      ]),
      CommonStyle::style([
        'selector'  => $second_content_selector,
        'property' => 'animation-duration',
        'attr' => static::getAttr($attrs, 'second_content_speed','600ms'),
        'important' =>true
      ]),
      CommonStyle::style([
        'selector'  => $second_content_selector,
        'property' => 'animation-delay',
        'attr' => static::getAttr($attrs, 'second_content_delay','100ms'),
        'important' =>true
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