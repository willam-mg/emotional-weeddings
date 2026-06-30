<?php
/**
 * FloatingMultiImagesChild::render_callback()
 *
 * @package DIPI\Modules\FloatingMultiImagesChild
 * @since ??
 */

namespace DIPI\Modules\FloatingMultiImagesChild;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Packages\Module\Module;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use DIPI\Traits\BaseRenderTrait;

trait RenderCallbackTrait {
	use BaseRenderTrait;

	private static $props = [];
	
	/**
	 * Static module render callback which outputs server side rendered HTML on the Front-End.
	 *
	 * @since ??
	 * @param array          $attrs    Block attributes that were saved by VB.
	 * @param string         $content  Block content.
	 * @param WP_Block       $block    Parsed block object that being rendered.
	 * @param ModuleElements $elements ModuleElements instance.
	 *
	 * @return string HTML rendered of Static module.
	 */
	public static function render_callback( $attrs, $content, $block, $elements ) {
		$order_number = $block->parsed_block['orderIndex'];

        $use_icon = static::getPropValue($attrs, "use_icon");
        $use_img_link = static::getPropValue($attrs, 'use_img_link');
        $img_link = static::getPropValue($attrs, 'img_link');
        $img_link_target = static::getPropValue($attrs, 'img_link_target') === 'on' ? '_blank' : '_self';

	$start_link_wrap = 'on' == $use_img_link ? sprintf(
            '<a href="%1$s" target="%2$s">', 
            esc_url($img_link), 
            $img_link_target
        ) : '';

        $end_link_wrap = 'on' == $use_img_link ? '</a>' : '';
        $image_content = '';
		if($use_icon === 'on'){
			$icon = static::getPropValue($attrs, 'icon');
            $image_content = sprintf(
                '<span class="et-pb-icon dipi-fi-icon">
                    %1$s
                </span>',
                Utils::process_font_icon($icon)
            );
        } else {
			$image = static::getPropValue($attrs, 'image');
            $image_content = sprintf(
                '<div class="dipi-fi-img"><img src="%1$s" alt="%2$s"/></img></div>',
                esc_attr($image["src"] ?? ""),
                esc_attr($image["alt"] ?? "")
            );
        }

		$render_html = sprintf(
            '%2$s
                %1$s
            %3$s',
            $image_content,
            $start_link_wrap,
            $end_link_wrap
        );

        $parent       = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );
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
				'name'                => $block->block_type->name,
				'moduleCategory'      => $block->block_type->category,
				'classnamesFunction'  => [ FloatingMultiImagesChild::class, 'module_classnames' ],
				'stylesComponent'     => [ FloatingMultiImagesChild::class, 'module_styles' ],
				'scriptDataComponent' => [ FloatingMultiImagesChild::class, 'module_script_data' ],
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
				) . $render_html,
			]
		);
	}
}
