<?php
namespace DIPI\Modules\HoverGalleryItem;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use ET\Builder\Packages\Module\Module;
use DIPI\Traits\PopupGalleryTrait;

trait RenderCallbackTrait {
  
  use PopupGalleryTrait;

  public static function render_callback( $attrs, $content, $block, $elements ) {
 
    $use_button = $attrs['button']['innerContent']['desktop']['value']['useButton'] ?? 'off';
    
    $children_ids = $block->parsed_block['innerBlocks'] ? array_map(
			function( $inner_block ) {
				return $inner_block['id'];
			},
			$block->parsed_block['innerBlocks']
		) : [];

 
    $content = ElementComponents::component(
      [
        'attrs'         => $attrs['module']['decoration'] ?? [],
        'id'            => $block->parsed_block['id'],

        // FE only.
        'orderIndex'    => $block->parsed_block['orderIndex'],
        'storeInstance' => $block->parsed_block['storeInstance'],
      ]
    ) . $content;

    $imageIcon = $attrs['imageIcon']['innerContent']['desktop']['value'];

    if('on' === $imageIcon['useIcon']) {
      $content .= sprintf(
        '<div class="dipi-content-image-icon-wrap dipi-icon-wrap">
          <span class="et-pb-icon et-pb-font-icon dipi-hover-box-content-icon">%1$s</span>
        </div>',
        isset($imageIcon['icon']) ? Utils::process_font_icon($imageIcon['icon']) : ''
      );
    } else {
      if(!empty($imageIcon['src'])){
        $content .= sprintf(
          '<div class="dipi-content-image-icon-wrap dipi-image-wrap">
            <img src="%1$s" class="dipi-hover-box-content-image" alt="%2$s" />
          </div>',
          $imageIcon['src'],
          $imageIcon['alt']
        );  
      }
  }
    $content .= $elements->render([
      'attrName'      => 'title'
    ]);
    $content .= $elements->render([
      'attrName'      => 'content'
    ]);

    if($use_button === 'on') {
      $button = $elements->render([
        'attrName'      => 'button'
      ]);
      $content .= sprintf(
        '<div class="dipi-hg-button-wrapper">
          %1$s
        </div>',
        $button
      ); 
    }

    return Module::render(
      [
        // FE only.
        'orderIndex'          => $block->parsed_block['orderIndex'],
        'storeInstance'       => $block->parsed_block['storeInstance'],

        // VB equivalent.
        'attrs'               => $attrs,
        'elements'            => $elements,
        'id'                  => $block->parsed_block['id'],
        'htmlAttrs' => [
          'test' => 'test',
          'data-item' => $block->parsed_block['id']
        ],
        'moduleclass'     => '',
        'name'                => $block->block_type->name,
        'moduleCategory'      => $block->block_type->category,
        'classnamesFunction'  => [ HoverGalleryItem::class, 'module_classnames' ],
        'stylesComponent'     => [ HoverGalleryItem::class, 'module_styles' ],
        'scriptDataComponent' => [ HoverGalleryItem::class, 'module_script_data' ],
        'parentAttrs'         => $parent->attrs ?? [],
        'parentId'            => $parent->id ?? '',
        'parentName'          => $parent->blockName ?? '',
        'children'            => $content,
				'childrenIds'         => $children_ids
      ]
    );
  }
}