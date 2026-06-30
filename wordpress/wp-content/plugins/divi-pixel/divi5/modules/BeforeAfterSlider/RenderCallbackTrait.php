<?php
/**
 * BeforeAfterSlider::render_callback()
 *
 * @package DIPI\Modules\BeforeAfterSlider
 * @since ??
 */

namespace DIPI\Modules\BeforeAfterSlider;

if (!defined('ABSPATH')) {
	die('Direct access forbidden.');
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Packages\Module\Module;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;

trait RenderCallbackTrait
{
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
	public static function render_callback($attrs, $content, $block, $elements)
	{
		$options = htmlspecialchars(
			json_encode(
				[
					"before_image" => $attrs["before_image"]['innerContent']['desktop']['value']['src'] ?? '',
					"before_image_alt" => $attrs["before_image_alt"]['innerContent']['desktop']['value'] ?? '',
					"before_label" => $attrs["before_label"]['innerContent']['desktop']['value'] ?? '',
					"after_image" => $attrs["after_image"]['innerContent']['desktop']['value']['src'] ?? '',
					"after_image_alt" => $attrs["after_image_alt"]['innerContent']['desktop']['value'] ?? '',
					"after_label" => $attrs["after_label"]['innerContent']['desktop']['value'] ?? '',
					"offset" => $attrs["offset"]['innerContent']['desktop']['value'] ?? '50',
					"direction" => $attrs["direction"]['innerContent']['desktop']['value'] ?? 'horizontal',
					"move_slider" => $attrs["move_slider"]['innerContent']['desktop']['value'] ?? 'on_click'
				]
			),
			ENT_QUOTES,
			'UTF-8'
		);
		$handle_icon = $attrs['handle_icon']['innerContent']['desktop']['value'];
		$handle_use_circle = $attrs['handle_use_circle']['innerContent']['desktop']['value'];
		$move_slider = $attrs['move_slider']['innerContent']['desktop']['value'];
		$overlay_visibility = $attrs['overlay_visibility']['innerContent']['desktop']['value'];
		$extra_classes[] = '';
		$extra_classes[] = "$handle_icon-handle_icon";
		if ($handle_use_circle === 'off') {
			$extra_classes[] = 'no_circle_handle';
		}
		if ($move_slider === 'with_handle') {
			$extra_classes[] = 'move_with_handle';
		}
		$extra_classes[] = "$overlay_visibility-overlay";
		$render_html = sprintf(
			'<div class="dipi_before_after_slider_container %2$s" data-options="%1$s">
            </div>',
			$options,
			implode(' ', $extra_classes)
		);

		$parent = BlockParserStore::get_parent($block->parsed_block['id'], $block->parsed_block['storeInstance']);
		$parent_attrs = $parent->attrs ?? [];

		return Module::render(
			[
				// FE only.
				'orderIndex' => $block->parsed_block['orderIndex'],
				'storeInstance' => $block->parsed_block['storeInstance'],

				// VB equivalent.
				'attrs' => $attrs,
				'elements' => $elements,
				'id' => $block->parsed_block['id'],
				'name' => $block->block_type->name,
				'moduleCategory' => $block->block_type->category,
				'classnamesFunction' => [BeforeAfterSlider::class, 'module_classnames'],
				'stylesComponent' => [BeforeAfterSlider::class, 'module_styles'],
				'scriptDataComponent' => [BeforeAfterSlider::class, 'module_script_data'],
				'parentAttrs' => $parent_attrs,
				'parentId' => $parent->id ?? '',
				'parentName' => $parent->blockName ?? '',
				'children' => ElementComponents::component(
					[
						'attrs' => $attrs['module']['decoration'] ?? [],
						'id' => $block->parsed_block['id'],

						// FE only.
						'orderIndex' => $block->parsed_block['orderIndex'],
						'storeInstance' => $block->parsed_block['storeInstance'],
					]
				) . $render_html,
			]
		);
	}
}
