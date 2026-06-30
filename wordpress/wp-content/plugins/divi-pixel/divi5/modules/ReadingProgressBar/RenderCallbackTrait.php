<?php
/**
 * ReadingProgressBar::render_callback()
 *
 * @package DIPI\Modules\ReadingProgressBar
 * @since ??
 */

namespace DIPI\Modules\ReadingProgressBar;

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

        $bar_position = static::getPropValue($attrs, 'bar_position');
        $bar_color = static::getPropValue($attrs, 'bar_color');
        $bar_bg_color = static::getPropValue($attrs, 'bar_bg_color');
		$bar_size = static::getPropValue($attrs, 'bar_size');
        $bar_animation = static::getPropValue($attrs, 'bar_animation');
        $bar_striped_color = static::getPropValue($attrs, 'bar_striped_color');
		$exclude_footer = static::getPropValue($attrs, 'exclude_footer');
		$module_classname = "dipi_reading_progress_bar dipi_reading_progress_bar_{$order_number}";

		$striped_classes = '';
		if( 'striped' === $bar_animation ) {
			$striped_classes = 'dipi-progress-striped dipi-striped-color';
		}

		if( 'on' === $exclude_footer ) {
			$module_classname .= ' dipi-reading-progress-exclude-footer';
		}

		$render_html = sprintf(
            '<div class="%4$s dipi-reading-progress-wrap" data-position="%1$s" data-color="%2$s" data-bgcolor="%3$s">
                <div class="dipi-reading-progress dipi-reading-progress-%1$s">
                    <div class="dipi-reading-progress-fill %5$s"></div>
                </div>
            </div>',
            $bar_position,
            $bar_color,
            $bar_bg_color,
            $module_classname,
            $striped_classes
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
				'classnamesFunction'  => [ ReadingProgressBar::class, 'module_classnames' ],
				'stylesComponent'     => [ ReadingProgressBar::class, 'module_styles' ],
				'scriptDataComponent' => [ ReadingProgressBar::class, 'module_script_data' ],
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
