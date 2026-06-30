<?php
/**
 * FancyText::render_callback()
 *
 * @package DIPI\Modules\FancyText
 * @since ??
 */

namespace DIPI\Modules\FancyText;

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
		$fancy_text_items = [];

        $children_ids = $block->parsed_block['innerBlocks'] ? array_map(
			function( $inner_block ) {
				return $inner_block['id'];
			},
			$block->parsed_block['innerBlocks']
		) : [];

		$text_level = $attrs['all_font']['decoration']['font']['font']['desktop']['value']['headingLevel'] ?? 'h3';
        $prefix = static::getPropValue($attrs, 'prefix');
        $suffix = static::getPropValue($attrs, 'suffix');

		$options['data-in-animation'] = esc_attr(static::getPropValue($attrs, 'in_animation'));
        $options['data-out-animation'] = esc_attr(static::getPropValue($attrs, 'out_animation'));
        $options['data-speed'] = esc_attr(static::getPropValue($attrs, 'speed'));
        $options['data-duration'] = esc_attr(static::getPropValue($attrs, 'duration'));

		$options = implode(
            " ",
            array_map(
                function ($k, $v) {
                    return "{$k}='{$v}'";
                },
                array_keys($options),
                $options
            )
        );

		$fancy_text_items = array_map( function( $child ) {
			$text = $child['attrs']['title']['innerContent']['desktop']['value'];
			$order_number = preg_replace('/[^0-9]/', '', $child['id']);
			return sprintf('
				<div class="dipi_fancy_text_child_%1$s">%2$s</div>',
				esc_attr($order_number),
				$text
			);
		}, $block->parsed_block['innerBlocks']);

		$highlight_animation_start = static::getPropValue($attrs, 'highlight_animation_start');
		$highlight_animation_start_viewport = static::getPropValue($attrs, 'highlight_animation_start_viewport');
        $animation_only_once = static::getPropValue($attrs, 'animation_only_once');
        $config = [
			'animation_start' => $highlight_animation_start,
			'animation_start_viewport' => $highlight_animation_start_viewport,
            'animation_only_once' =>$animation_only_once,
            'item_count' => count($fancy_text_items)
        ];

        $text = sprintf(
            '<div class="fancy-text-wrap" %2$s>%1$s</div>',
            implode('||', $fancy_text_items),
            $options
        );

		$render_html = sprintf('<div class="dipi-fancy-text-container" data-config="%5$s">
                <%1$s class="fancy-text">
                    <div class="fancy-text-prefix">%3$s</div>%2$s<div class="fancy-text-suffix">%4$s</div>
                </%1$s>
            </div>',
            $text_level,
            $text,
            $prefix,
            $suffix,
            esc_attr(htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8')));

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
				'classnamesFunction'  => [ FancyText::class, 'module_classnames' ],
				'stylesComponent'     => [ FancyText::class, 'module_styles' ],
				'scriptDataComponent' => [ FancyText::class, 'module_script_data' ],
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
				'childrenIds'         => $children_ids,
			]
		);
	}
}
