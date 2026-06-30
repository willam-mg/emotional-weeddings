<?php
/**
 * TableOfContent::render_callback()
 *
 * @package DIPI\Modules\TableOfContent
 * @since ??
 */

namespace DIPI\Modules\TableOfContent;

if (!defined('ABSPATH')) {
	die('Direct access forbidden.');
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Packages\Module\Module;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use DIPI\Traits\BaseRenderTrait;

trait RenderCallbackTrait
{
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
	public static function render_callback($attrs, $content, $block, $elements)
	{
		$table_title = static::getPropValue($attrs, 'table_title');
		$heading_tags = static::getPropValue($attrs, 'heading_tags');
		$ALL_TAGS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
		if (!is_array($heading_tags)) {
			$heading_tags = [];
		}

		$heading_tags = array_map('strtolower', array_map('trim', $heading_tags));

		$heading_tags_onoff = array_map(function ($tag) use ($heading_tags) {
			return in_array($tag, $heading_tags, true) ? 'on' : 'off';
		}, $ALL_TAGS);

		$heading_tags = implode('|', $heading_tags_onoff);
		$exclude_selector = static::getPropValue($attrs, 'exclude_selector');
		$container_exclude_selector = static::getPropValue($attrs, 'container_exclude_selector');
		$generate_for_whole_page = static::getPropValue($attrs, 'generate_for_whole_page');
		$section_selector = static::getPropValue($attrs, 'section_selector');

		$collapsible_table_d = isset($attrs['collapsible_table']['innerContent']['desktop']['value'])
			? $attrs['collapsible_table']['innerContent']['desktop']['value'] : 'off';
		$collapsible_table_t = isset($attrs['collapsible_table']['innerContent']['tablet']['value'])
			&& !empty($attrs['collapsible_table']['innerContent']['tablet']['value'])
			? $attrs['collapsible_table']['innerContent']['tablet']['value'] : $collapsible_table_d;
		$collapsible_table_p = isset($attrs['collapsible_table']['innerContent']['phone']['value'])
			&& !empty($attrs['collapsible_table']['innerContent']['phone']['value'])
			? $attrs['collapsible_table']['innerContent']['phone']['value'] : $collapsible_table_t;
		$collapsible_table = $collapsible_table_d == "on" || $collapsible_table_t == "on" || $collapsible_table_p == "on";


		$simplify_title_id = static::getPropValue($attrs, 'simplify_title_id') ?: 'off';
		$show_list_numbers = static::getPropValue($attrs, 'show_list_numbers') ?: 'off';
		$show_zero_prefix = static::getPropValue($attrs, 'show_zero_prefix') ?: 'off';
		// Use !== '' / !== null (not empty()) so tablet/phone value 0 is valid; empty('0') is true in PHP.
		$scroll_inner = isset($attrs['scroll_offset']['innerContent']) && is_array($attrs['scroll_offset']['innerContent'])
			? $attrs['scroll_offset']['innerContent'] : [];
		$d_off = isset($scroll_inner['desktop']['value']) ? $scroll_inner['desktop']['value'] : null;
		$scroll_offset = ($d_off !== null && $d_off !== '') ? $d_off : '100';
		$t_off = isset($scroll_inner['tablet']['value']) ? $scroll_inner['tablet']['value'] : null;
		$scroll_offset_tablet = ($t_off !== null && $t_off !== '') ? $t_off : $scroll_offset;
		$p_off = isset($scroll_inner['phone']['value']) ? $scroll_inner['phone']['value'] : null;
		$scroll_offset_phone = ($p_off !== null && $p_off !== '') ? $p_off : $scroll_offset_tablet;


		$icon_code = static::getPropValue($attrs, 'item_bullet') ?: '';
		$icon = Utils::process_font_icon($icon_code ?? []);
		$order_number = $block->parsed_block['orderIndex'];
		$order_class = "dipi_table_of_content_" . $order_number;

		$data = [
			'headingTags' => $heading_tags,
			'generateForWholePage' => $generate_for_whole_page,
			'exclude_selector' => $exclude_selector,
			'container_exclude_selector' => $container_exclude_selector,
			'sectionSelector' => $section_selector,
			'simplify_title_id' => $simplify_title_id,
			'show_list_numbers' => $show_list_numbers,
			'show_zero_prefix' => $show_zero_prefix,
			'scroll_offset' => $scroll_offset . '|' . $scroll_offset_tablet . '|' . $scroll_offset_phone,
			'order_class' => $order_class,
		];

		if ($collapsible_table) {
			$data['collapsible'] = 'on';

			$data['collapsible_table'] = $collapsible_table_d . '|' . $collapsible_table_t . '|' . $collapsible_table_p;

			$closed_default_d = isset($attrs['closed_default']['innerContent']['desktop']['value'])
				? $attrs['closed_default']['innerContent']['desktop']['value'] : 'off';
			$closed_default_t = isset($attrs['closed_default']['innerContent']['tablet']['value'])
				&& !empty($attrs['closed_default']['innerContent']['tablet']['value'])
				? $attrs['closed_default']['innerContent']['tablet']['value'] : $closed_default_d;
			$closed_default_p = isset($attrs['closed_default']['innerContent']['phone']['value'])
				&& !empty($attrs['closed_default']['innerContent']['phone']['value'])
				? $attrs['closed_default']['innerContent']['phone']['value'] : $closed_default_t;

			$data['closed_default'] = $closed_default_d . '|' . $closed_default_t . '|' . $closed_default_p;

			$close_on_click_d = isset($attrs['close_on_click']['innerContent']['desktop']['value'])
				? $attrs['close_on_click']['innerContent']['desktop']['value'] : 'off';
			$close_on_click_t = isset($attrs['close_on_click']['innerContent']['tablet']['value'])
				&& !empty($attrs['close_on_click']['innerContent']['tablet']['value'])
				? $attrs['close_on_click']['innerContent']['tablet']['value'] : $close_on_click_d;
			$close_on_click_p = isset($attrs['close_on_click']['innerContent']['phone']['value'])
				&& !empty($attrs['close_on_click']['innerContent']['phone']['value'])
				? $attrs['close_on_click']['innerContent']['phone']['value'] : $close_on_click_t;

			$data['close_on_click'] = $close_on_click_d . '|' . $close_on_click_t . '|' . $close_on_click_p;

		} else {
			$data['collapsible'] = 'off';
		}

		if (isset($icon) && $icon !== '') {
			$data['icon'] = $icon;
		}

		$data_attr = 'data-dipi-toc=' . base64_encode(json_encode($data));
		$title = '';
		$header_icon_position = static::getPropValue($attrs, 'header_icon_position');
		$icon_class = $header_icon_position === 'right'
			? 'dipi-toc_header-icon dip-content_header-icon-right'
			: 'dipi-toc_header-icon';

		$header_icon = '';

		if (static::getPropValue($attrs, 'use_header_icon') === 'on') {
			$header_icon_code = static::getPropValue($attrs, 'header_icon');
			$header_icon_code = Utils::process_font_icon($header_icon_code ?? []);
			$header_icon_closed = static::getPropValue($attrs, 'header_icon_closed');
			$header_icon_closed = Utils::process_font_icon($header_icon_closed ?? []);

			$header_icon = sprintf('
                <span class="%1$s">
                    <span class="dipi-toc-header-icon-open">%2$s</span>
                    <span class="dipi-toc-header-icon-closed">%3$s</span>
                </span>',
				esc_attr($icon_class),
				$header_icon_code,
				$header_icon_closed
			);
		}

		$title_header_level = static::getPropValue($attrs, 'tabel_title_level');
		$title = sprintf(
			'<div class="dipi-toc__title">
                <%1$s>
                    %3$s
                    <span class="dipi-toc_header-content">%2$s</span>
                </%1$s>
            </div>',
			et_pb_process_header_level($title_header_level, 'h2'),
			et_core_esc_previously($table_title),
			$header_icon
		);

		$render_html = sprintf('
                <div class="dipi-toc open" %2$s>
                    %1$s
                    <div class="dipi-toc__collapse">
                        <div class="dipi-toc__list"></div>
                    </div>
                </div>',
			$title,
			$data_attr
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
				'classnamesFunction' => [TableOfContent::class, 'module_classnames'],
				'stylesComponent' => [TableOfContent::class, 'module_styles'],
				'scriptDataComponent' => [TableOfContent::class, 'module_script_data'],
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
