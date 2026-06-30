<?php
/**
 * ContentSliderChild::render_callback()
 *
 * @package DIPI\Modules\ContentSliderChild
 * @since ??
 */

namespace DIPI\Modules\ContentSliderChild;

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

	static function sanitize_content($content)
	{
		return preg_replace('/^<\/p>(.*)<p>/s', '$1', $content);
	}

	static function process_content($content)
	{
		$content = static::sanitize_content($content);
		$content = str_replace(["&#91;", "&#93;"], ["[", "]"], $content);
		$content = do_shortcode($content);
		$content = str_replace(
			["<p><div", "</div></p>", "</div> <!-- .et_pb_section --></p>"],
			["<div", "</div>", "</div>"],
			$content
		);
		return $content;
	}
	
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

		// global $dipi_cs_selectors, $dipi_active_id, $dipi_active_order_num;
		$label = static::getPropValue($attrs, 'title');
        $label = "<span class='content-slider-label'>{$label}</span>";
        $desc_text = static::getPropValue($attrs, 'desc');
        $selector = static::getPropValue($attrs, 'selector');
        $active_item = static::getPropValue($attrs, 'active_item');
        $activate_tab_selector = static::getPropValue($attrs, 'activate_tab_selector');
        $scroll_tab_offset_desktop = isset($attrs["scroll_tab_offset"]["innerContent"]["desktop"]["value"]) ? $attrs["scroll_tab_offset"]["innerContent"]["desktop"]["value"] : "";
		$scroll_tab_offset_tablet = isset($attrs["scroll_tab_offset"]["innerContent"]["tablet"]["value"]) ? $attrs["scroll_tab_offset"]["innerContent"]["tablet"]["value"] : $scroll_tab_offset_desktop;
		$scroll_tab_offset_phone = isset($attrs["scroll_tab_offset"]["innerContent"]["phone"]["value"]) ? $attrs["scroll_tab_offset"]["innerContent"]["phone"]["value"] : $scroll_tab_offset_tablet;
        $show_description = static::getPropValue($attrs, 'show_description');
        $module_classes = [];
		if ($active_item === 'on') {
		    $module_classes[] = "active";
		}
        // $module_id = count($dipi_cs_selectors);
        // if ($dipi_active_id < 0 && $active_item === 'on') {
        //     $dipi_active_id = $module_id;
        //     $dipi_active_order_num = $order_number;
        //     $module_classes[] = "active";
        // }
        // $dipi_cs_selectors[] = str_replace(";", "", $selector);
        $description_html = "";
        if ($show_description === 'on') {
            $description_html = sprintf('<div class="content-slider-desc">%1$s</div>',
                static::process_content($desc_text)
            );
        }
        
        $parent       = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );
		$parent_attrs = $parent->attrs ?? [];
		$parent_innerBlocks = $parent->innerBlocks ?? [];

		$module_id = 0;
		for ($i=0; $i < count($parent_innerBlocks); $i++) { 
			if($block->parsed_block['id'] === $parent_innerBlocks[$i]['id'])
			{
				$module_id = $i;
				break;
			}
		}

		$render_html = sprintf(
            '<div class="content-slider-item %2$s" data-id="%3$s" data-order-num="%4$s" data-activate-selector="%6$s" 
                data-tab-scroll-off="%7$s"
                data-tab-scroll-off-tablet="%8$s"
                data-tab-scroll-off-phone="%9$s"
                >
                <div class="content-slider-gradations-wrapper">
                    <span class="content-slider-gradations"></span>
                </div>
                %1$s
                %5$s
            </div>',
            $label,
            implode(" ", $module_classes),
            $module_id,
            $order_number,
            $description_html, #5
            $activate_tab_selector,
            $scroll_tab_offset_desktop,
            $scroll_tab_offset_tablet,
            $scroll_tab_offset_phone
		);

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
				'classnamesFunction'  => [ ContentSliderChild::class, 'module_classnames' ],
				'stylesComponent'     => [ ContentSliderChild::class, 'module_styles' ],
				'scriptDataComponent' => [ ContentSliderChild::class, 'module_script_data' ],
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
