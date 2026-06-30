<?php
namespace DIPI\Modules\PriceList;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use ET\Builder\Packages\Module\Module;



trait RenderCallbackTrait {
  public static function render_callback( $attrs, $content, $block, $elements ) {
    $children_ids = $block->parsed_block['innerBlocks'] ? array_map(
			function( $inner_block ) {
				return $inner_block['id'];
			},
			$block->parsed_block['innerBlocks']
		) : [];
    $parent = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );
    $parent_attrs = $parent->attrs ?? [];


    $showLightbox = $attrs['image']['decoration']['showLightbox']['desktop']['value'] ?? 'on';
    $showLightbox_tablet = $attrs['image']['decoration']['showLightbox']['tablet']['value'] ?? 'on';
    $showLightbox_phone = $attrs['image']['decoration']['showLightbox']['phone']['value'] ?? 'on';

    $classNames = 'dipi_price_list-main';
    $classNames .= $showLightbox === 'on' ? ' show_lightbox' : '';
    $classNames .= $showLightbox_tablet === 'on' ? ' show_lightbox_tablet' : '';
    $classNames .= $showLightbox_phone === 'on' ? ' show_lightbox_phone' : '';

    $content =   HTMLUtility::render([
        'tag'               => 'div',
        'attributes'        => [
          'class' =>  $classNames,
        ],
        'childrenSanitizer' => 'et_core_esc_previously',
        'children' => $content
      ]);
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
        'classnamesFunction'  => [ PriceList::class, 'module_classnames' ],
        'moduleCategory'      => $block->block_type->category,
        'stylesComponent'     => [ PriceList::class, 'module_styles' ],
        'scriptDataComponent' => [ PriceList::class, 'module_script_data' ],
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
				) . $content,
				'childrenIds'         => $children_ids,
      ]
    );
  }
}