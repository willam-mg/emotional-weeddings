<?php
namespace DIPI\Modules\TiltImage;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Module;

trait RenderCallbackTrait {
  public static function render_callback( $attrs, $content, $block, $elements ) {
    
    
    $image_src = $attrs['image']['innerContent']['desktop']['value']['src'] ?? '';
		$image_alt = $attrs['image']['innerContent']['desktop']['value']['alt'] ?? '';
    $content_img_src = $attrs['overlayImage']['innerContent']['desktop']['value']['src'] ?? '';
    $content_img_alt = $attrs['overlayImage']['innerContent']['desktop']['value']['src'] ?? '';
    $use_icon = $attrs['overlay']['innerContent']['desktop']['value']['useIcon'] ?? false;
    $use_title = $attrs['overlay']['innerContent']['desktop']['value']['tiltOverlayTitle'] ?? false;
     

    $use_desc = $attrs['overlay']['innerContent']['desktop']['value']['tiltOverlayDesc'] ?? false;
    $use_btn = $attrs['overlay']['innerContent']['desktop']['value']['tiltOverlayBtn'] ?? false;
     
    $extraIconClass = implode(' ', [
            isset($attrs['overlayIcon']['advanced']['circleIcon']['desktop']['value']) && $attrs['overlayIcon']['advanced']['circleIcon']['desktop']['value'] === 'on' ? 'dipi-tilt-overlay-icon-circle':'',
            isset($attrs['overlayIcon']['advanced']['showCircleBorder']['desktop']['value']) && $attrs['overlayIcon']['advanced']['showCircleBorder']['desktop']['value'] === 'on' ? 'dipi-tilt-overlay-icon-border':''
        ]);
    
    $icon_value = Utils::process_font_icon( $attrs['overlayIcon']['innerContent']['desktop']['value'] ?? [] );
    $icon = $use_icon === 'on' && ! empty( $icon_value ) ? HTMLUtility::render([
				'tag'        => 'span',
				'attributes' => [
					'class' => 'et-pb-icon dipi-tilt-overlay-icon dipi-tilt-overlay-image-icon-wrap '. $extraIconClass,
				],
				'children'   => $icon_value,
		]) : '';

    $content_image = 'off' === $use_icon ? 
    '<div class="dipi-tilt-overlay-image-icon-wrap">'.
      $elements->render([
          'attrName' => 'overlayImage',
      ]) . '</div>': '';
    
    // Title.
    $header = 'on' === $use_title ? $elements->render(
      [
        'attrName' => 'title',
      ]
    ): '';

    // Content.
    $content = 'on' === $use_desc ? $elements->render([
        'attrName' => 'overlayContent',
    ]):'';

 
    
    $button = $use_btn === 'on'? $elements->render([
        'attrName' => 'button',
    ]): '';

    $button = $use_btn === 'on'? HTMLUtility::render([
      'tag'               => 'div',
      'attributes'        => [
        'class' => 'dipi-tilt-overlay-btn',
      ],
      'childrenSanitizer' => 'et_core_esc_previously',
      'children' => $button
    ]) : '';

    
    $image = !empty($image_src)? HTMLUtility::render(
      [
        'tag'        => 'img',
        'attributes' => [
          'src'   => $image_src,
          'alt'   => $image_alt,
          'class' => '',
        ],
      ]
    ): '';
    $dipi_tilt_image_overlay = HTMLUtility::render([
      'tag'               => 'div',
      'attributes'        => [
        'class' => 'dipi-tilt-image-overlay'
       
      ],
      'childrenSanitizer' => 'et_core_esc_previously',
    ]);

    $content_overlay_wrapper = HTMLUtility::render([
      'tag'               => 'div',
      'attributes'        => [
        'class' => 'dipi-tilt-overlay-wrap',
      ],
      'childrenSanitizer' => 'et_core_esc_previously',
      'children' => $icon . $content_image . $header . $content . $button
      
    ]);
    $content_overlay = HTMLUtility::render([
      'tag'               => 'div',
      'attributes'        => [
        'class' => 'dipi-tilt-overlay',
      ],
      'childrenSanitizer' => 'et_core_esc_previously',
      'children' => $content_overlay_wrapper
    ]);

    $dipi_tilt_wrapper = HTMLUtility::render([
      'tag'               => 'div',
      'attributes'        => [
        'class' => 'dipi-tilt-image-wrap',
      ],
      'childrenSanitizer' => 'et_core_esc_previously',
      'children' => $dipi_tilt_image_overlay . $image . $content_overlay,
    ]);
    
    $tiltGlare = $attrs['tiltBox']['advanced']['tiltGlare']['desktop']['value'] ?? 1;
    $tiltMax = $attrs['tiltBox']['advanced']['tiltMax']['desktop']['value'] ?? 15;
    $tiltPerspective = $attrs['tiltBox']['advanced']['tiltPerspective']['desktop']['value'] ?? 1000;
    $tiltReverse = $attrs['tiltBox']['advanced']['tiltReverse']['desktop']['value'] ?? 'on';
    $tiltScale = $attrs['tiltBox']['advanced']['tiltScale']['desktop']['value'] ?? 1;
    $tiltSpeed = $attrs['tiltBox']['advanced']['tiltSpeed']['desktop']['value'] ?? 600;
    $useTiltGlare = $attrs['tiltBox']['advanced']['useTiltGlare']['desktop']['value'] ?? 'off';

    $dipi_tilt = HTMLUtility::render([
      'tag'               => 'div',
      'attributes'        => [
        'class' => 'dipi-tilt-image',
        'data-tilt' => true,
        'data-tilt-max' => $tiltMax,
        'data-tilt-speed' => $tiltSpeed,
        'data-tilt-perspective' => $tiltPerspective,
        'data-tilt-scale' => $tiltScale,
        'data-tilt-reverse' => 'on' === $tiltReverse ? true : false,
        'data-tilt-glare' => 'on' === $useTiltGlare ? true : false,
        'data-tilt-max-glare' => $tiltGlare
      ],
      'childrenSanitizer' => 'et_core_esc_previously',
      'children' => $dipi_tilt_wrapper  
    ]);
 

    $parent = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );
 
    return Module::render(
      [
        // FE only.
        'orderIndex'          => $block->parsed_block['orderIndex'],
        'storeInstance'       => $block->parsed_block['storeInstance'],

        // VB equivalent.
        'attrs'               => $attrs,
        'elements'            => $elements,
        'id'                  => $block->parsed_block['id'],
        'moduleClassName'     => '',
        'name'                => $block->block_type->name,
        'classnamesFunction'  => [ TiltImage::class, 'module_classnames' ],
        'moduleCategory'      => $block->block_type->category,
        'stylesComponent'     => [ TiltImage::class, 'module_styles' ],
        'scriptDataComponent' => [ TiltImage::class, 'module_script_data' ],
        'parentAttrs'         => $parent->attrs ?? [],
        'parentId'            => $parent->id ?? '',
        'parentName'          => $parent->blockName ?? '',
        'children'            => 
                  // $elements->style_components(['attrName' => 'module']).
                  // $elements->style_components(['attrName' => 'content']).
                  // $elements->style_components(['attrName' => 'image']).
                  $dipi_tilt 
      ]
    );
  }
}