<?php
namespace DIPI\Modules\PricingTableItem;

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
 
    
    $children_ids = $block->parsed_block['innerBlocks'] ? array_map(
			function( $inner_block ) {
				return $inner_block['id'];
			},
			$block->parsed_block['innerBlocks']
		) : [];

    $itemType = $attrs['module']['advanced']['itemType']['desktop']['value'] ?? 'Text';
    $ribbonType = $attrs['ribbon']['decoration']['ribbonType']['desktop']['value'] ?? 'Text';
    $parent = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );

    $item = '';

    if($itemType == 'Text'){
      $item = $elements->render([
        'attrName'      => 'content'
      ]);
    }

    if($itemType == 'Image'){
      $image_src = $attrs['image']['innerContent']['desktop']['value']['src'] ?? '';
      $image_alt = $attrs['image']['innerContent']['desktop']['value']['alt'] ?? '';
      $showLightbox = $attrs['image']['innerContent']['desktop']['value']['useLightbox'] ?? 'off';
      
      $gallery_ids = $attrs['gallery']['desktop']['value'] ?? ''; 
      $gallery_ids =  !empty($gallery_ids)? explode(',', $gallery_ids):[];
     
      $image = sprintf(
        '<img src="%1$s" alt="%2$s" />',
        esc_url($image_src),
        esc_attr($image_alt)
      );

      if("on" === $showLightbox){
        $item = sprintf(
          '<div class="dipi-pt-image dipi-has-gallery-item">
            <div class="et_pb_image_wrap dipi-gallery-item" href="%2$s">%1$s</div>
            %3$s
          </div>',
          $image,
          esc_url($image_src),
          static::popup_gallery($gallery_ids)
        );
      } else {
        $item = sprintf('<div class="dipi-pt-image">
                    <span class="et_pb_image_wrap">%1$s</span>
                </div>',
                $image 
        );
      }
    }

    if($itemType == 'Price'){
      $item = sprintf('
        <div class="dipi-pt-price-container">
          %1$s%2$s%3$s
        </div>
      ', 
      $item = $elements->render([
        'attrName'=> 'pricePrefix'
      ]),
      $item = $elements->render([
        'attrName'=> 'price'
      ]),
      $item = $elements->render([
        'attrName'=> 'priceSuffix'
      ]));
    }

    if($itemType == 'Button'){
      $item = 
      '<div class="dipi-pt-btn-wrap">'.
      $elements->render([
        'attrName'      => 'button'
      ]). '</div>';
    }

    if($itemType == 'Ribbon' && $ribbonType == 'text'){
      $item = $elements->render([
        'attrName'      => 'ribbonText'
      ]);
    }

    if($itemType == 'Ribbon' && $ribbonType == 'image'){
      $image_src = $attrs['ribbonImage']['innerContent']['desktop']['value']['src'] ?? '';
      $image_alt = $attrs['ribbonImage']['innerContent']['desktop']['value']['alt'] ?? '';
      $item = sprintf(
        '<img src="%1$s" class="dipi-pt-ribbon-image" alt="%1$s" />',
        esc_url($image_src),
        esc_attr($image_alt)
      );
    }

    if($itemType == 'Feature') { 
      $icon_placement = $attrs['featureIcon']['decoration']['iconPlacement']['desktop']['value'] ?? 'top';
      $icon_value = Utils::process_font_icon( $attrs['featureIcon']['innerContent']['desktop']['value'] ?? [] );
      $icon = ! empty( $icon_value ) ? HTMLUtility::render([
          'tag'        => 'span',
          'attributes' => [
            'class' => 'et-pb-icon dipi-pt-feature-icon',
          ],
          'children'   => $icon_value,
      ]) : '';
      $featureText = $elements->render([
        'attrName' => 'featureText',
        "attrSubName" => 'text',
				"tagName" => $attrs['featureText']['innerContent']['desktop']['value']['tag'],
      ]);
      $item = '<div class="dipi-pt-feature">';
      $item .= $icon_placement === 'top' || $icon_placement === 'left' ? $icon . $featureText : $featureText . $icon;
      $item .= '</div>';
    }

    if($itemType == 'Icon') { 
      $icon_value = Utils::process_font_icon( $attrs['icon']['innerContent']['desktop']['value'] ?? [] );
      $item = sprintf('<div class="dipi-pt-icon">
        <div class="et_pb_image_wrap">
          <span class="et-pb-icon">%1$s</span>
        </div>
		  </div>
      ', $icon_value);
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
        'moduleclass'     => '',
        'name'                => $block->block_type->name,
        'moduleCategory'      => $block->block_type->category,
        'classnamesFunction'  => [ PricingTableItem::class, 'module_classnames' ],
        'stylesComponent'     => [ PricingTableItem::class, 'module_styles' ],
        'scriptDataComponent' => [ PricingTableItem::class, 'module_script_data' ],
        'parentAttrs'         => $parent->attrs ?? [],
        'parentId'            => $parent->id ?? '',
        'parentName'          => $parent->blockName ?? '',
        'children'            => ElementComponents::component(
					[
						'attrs'         => $attrs['module']['decoration'] ?? [],
						'id'            => $block->parsed_block['id'],

						// FE only.
						'orderIndex'    => $block->parsed_block['orderIndex'],
						'storeInstance' => $block->parsed_block['storeInstance'],
					]
				) . $item,
				'childrenIds'         => $children_ids
      ]
    );
  }
}