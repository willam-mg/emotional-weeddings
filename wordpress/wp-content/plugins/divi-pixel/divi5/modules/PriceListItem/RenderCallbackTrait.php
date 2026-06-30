<?php
namespace DIPI\Modules\PriceListItem;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use ET\Builder\Packages\Module\Module;

trait RenderCallbackTrait {

  public static function dipi_price_list_gallery($attachment_ids) {

    $items = [];
    foreach ($attachment_ids as $attachment_id) {
      $attachment = wp_get_attachment_image_src($attachment_id, "full");
      if(!$attachment){
          continue;
      }
      $image = $attachment[0];
      $image_title = get_the_title($attachment_id);

      $items[] = sprintf(
          '<div class="dipi-pricelist-gallery-item" href="%1$s"%2$s%3$s>
            </div>',
          $image,
          " data-title='$image_title'" ,
          " data-caption='" . htmlspecialchars(wp_get_attachment_caption($attachment_id)) . "'"
      );
    }
    return implode("", $items);
  }
  public static function render_callback( $attrs, $content, $block, $elements ) {


    $image_src = $attrs['image']['innerContent']['desktop']['value']['src'] ?? '';
		$image_alt = $attrs['image']['innerContent']['desktop']['value']['alt'] ?? '';

    $gallery_ids = $attrs['gallery']['desktop']['value'] ?? ''; 
    $gallery_ids =  !empty($gallery_ids)? explode(',', $gallery_ids):[];

    
    
    $children_ids = $block->parsed_block['innerBlocks'] ? array_map(
			function( $inner_block ) {
				return $inner_block['id'];
			},
			$block->parsed_block['innerBlocks']
		) : [];
    $content = $elements->render(
			[
				'attrName'      => 'content',
				'hoverSelector' => '{{parentSelector}}',
			]
		);
    $title = $elements->render([
      'attrName' => 'title',
      'attributes' => [
        'class' => 'dipi_price_list_title'
      ]
    ]);
    
    $image = '';  
    if(count($gallery_ids) > 0) {
      $gallery_items = static::dipi_price_list_gallery($gallery_ids);
      $image = sprintf(
                    '<div class="dipi_price_list_image_wrapper dipi_price_list_gallery_wrapper" href="%1$s">
                        <div class="dipi-pricelist-gallery-item" href="%1$s">
                            %2$s
                        </div>
                        <div class="dipi_price_list_gallery">
                            %3$s
                        </div>
                    </div>',
                    $image_src,
                    $elements->render([
                        'attrName'      => 'image',
                        'attributes' => [
                          'class' => 'dipi_price_list_img'
                        ]
                    ]) ,
                    $gallery_items
                );
    } else {
       $image = 
      $image_src? sprintf('<div class="dipi_price_list_image_wrapper dipi_price_list_empty_gallery" href="%1$s">' ,
      $image_src
      ) . 
      $elements->render([
          'attrName'      => 'image',
          'attributes' => [
            'class' => 'dipi_price_list_img'
          ]
      ]) . '</div>': '';  

      
    }
    


    $price = sprintf('
      <div class="dipi_price_list_price">%1$s%2$s%3$s</div>
      ', 
      $elements->render(['attrName' => 'pricePrefix']),
      $elements->render(['attrName' => 'price']),
      $elements->render(['attrName' => 'priceSuffix'])
    );
    
    $separtor = '<div class="dipi_price_list_separator"></div>';

    $header = HTMLUtility::render([
        'tag'               => 'div',
        'attributes'        => [
          'class' => 'dipi_price_list_header',
        ],
        'childrenSanitizer' => 'et_core_esc_previously',
        'children' => $title . $separtor . $price
      ]);
    $body = sprintf('<div class="dipi_price_list_text_wrapper">%1$s %2$s</div>', $header, $content);
    $price_list_item = HTMLUtility::render([
      'tag'               => 'div',
      'attributes'        => [
        'class' => 'dipi_price_list_item_wrapper',
      ],
      'childrenSanitizer' => 'et_core_esc_previously',
      'children' => $image . $body
    ]);

    
    $parent = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );
    $parent_attrs = $parent->attrs ?? [];
    
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
        'classnamesFunction'  => [ PriceListItem::class, 'module_classnames' ],
        'moduleCategory'      => $block->block_type->category,
        'stylesComponent'     => [ PriceListItem::class, 'module_styles' ],
        'scriptDataComponent' => [ PriceListItem::class, 'module_script_data' ],
        'parentAttrs'         => $parent_attrs,
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
				) . $price_list_item,
				'childrenIds'         => $children_ids,
      ]
    );
  }
}