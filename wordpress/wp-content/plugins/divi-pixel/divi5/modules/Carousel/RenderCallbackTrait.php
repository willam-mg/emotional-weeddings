<?php
/**
 * Carousel::render_callback()
 *
 * @package DIPI\Modules\Carousel
 * @since ??
 */

namespace DIPI\Modules\Carousel;

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
use DIPI\Modules\Base\Swiper\SwiperRenderTrait;

trait RenderCallbackTrait {
	use BaseRenderTrait;
	use SwiperRenderTrait;

	private static $props = [];

	public static function get_reversed_content($content)
    {
        $carousel_content = $content;
        $length = strlen($carousel_content);
        $div_open_count = 0;
        $div_close_count = 0;
        $childs = array();
        $index = 0;
        $start_index = -1;
        $end_index = 0;
        while($index < $length) {
            if($index >= 3 
                && $carousel_content[$index - 3] == '<' 
                && $carousel_content[$index - 2] == 'd' 
                && $carousel_content[$index - 1] == 'i' 
                && $carousel_content[$index] == 'v') {
                $div_open_count++;
                if($start_index < 0)
                    $start_index = $index - 3;
            }
            else if($index >= 5 
                && $carousel_content[$index - 5] == '<' 
                && $carousel_content[$index - 4] == '/' 
                && $carousel_content[$index - 3] == 'd' 
                && $carousel_content[$index - 2] == 'i' 
                && $carousel_content[$index - 1] == 'v' 
                && $carousel_content[$index] == '>') {
                $div_close_count++;
                $end_index = $index;
            }
            if($div_open_count == $div_close_count && $div_open_count > 0 && $start_index >= 0 && $end_index > $start_index) {
                $childs[] = substr($carousel_content, $start_index, $end_index - $start_index + 1);
                $div_open_count = $div_close_count = 0;
                $start_index = -1;
            }
            $index ++;
        }
        return implode('', array_reverse($childs));
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
		$reverse_order = static::getPropValue($attrs, 'reverse_order') ?? "off";

		$children_ids = $block->parsed_block['innerBlocks'] ? array_map(
			function( $inner_block ) {
				return $inner_block['id'];
			},
			$block->parsed_block['innerBlocks']
		) : [];

		if($reverse_order === 'on') {
			$content = static::get_reversed_content($content);
		}

		$show_lightbox = $attrs['show_lightbox']['innerContent']['desktop']['value'] ?? "on";
        $show_lightbox_tablet = $attrs['show_lightbox']['innerContent']['tablet']['value'] ?? $show_lightbox;
        $show_lightbox_phone = $attrs['show_lightbox']['innerContent']['phone']['value'] ?? $show_lightbox_tablet;

		$show_lightboxclasses = ($show_lightbox === 'on') ? 'show_lightbox' : 'hide_lightbox';
        if (!empty($show_lightbox_tablet)) {
            $show_lightboxclasses .= ($show_lightbox_tablet === 'on') ? ' show_lightbox_tablet' : ' hide_lightbox_tablet';
        }
        if (!empty($show_lightbox_phone)) {
            $show_lightboxclasses .= ($show_lightbox_phone === 'on') ? ' show_lightbox_phone' : ' hide_lightbox_phone';
        }

		$autoplay_settigns = [];
        $autoplay_settigns['loop_wide'] = $attrs['loop']['innerContent']['desktop']['value'] ?? 'off';
        $autoplay_settigns['loop_mid'] = $attrs['loop']['innerContent']['tablet']['value'] ?? $autoplay_settigns['loop_wide'];
        $autoplay_settigns['loop_small'] = $attrs['loop']['innerContent']['phone']['value'] ?? $autoplay_settigns['loop_mid'];
        $autoplay_settigns['autoplay_wide'] = $attrs['autoplay']['innerContent']['desktop']['value'] ?? 'off';
        $autoplay_settigns['autoplay_mid'] = $attrs['autoplay']['innerContent']['tablet']['value'] ?? $autoplay_settigns['autoplay_wide'];
        $autoplay_settigns['autoplay_small'] = $attrs['autoplay']['innerContent']['phone']['value'] ?? $autoplay_settigns['autoplay_mid'];
        $autoplay_settigns['continues_wide'] = $attrs['continues']['innerContent']['desktop']['value'] ?? 'off';
        $autoplay_settigns['continues_mid'] = $attrs['continues']['innerContent']['tablet']['value'] ?? $autoplay_settigns['continues_wide'];
        $autoplay_settigns['continues_small'] = $attrs['continues']['innerContent']['phone']['value'] ?? $autoplay_settigns['continues_mid'];
        $autoplay_settigns['autoplay_reverse_wide'] = $attrs['autoplay_reverse']['innerContent']['desktop']['value'] ?? 'off';
        $autoplay_settigns['autoplay_reverse_mid'] = $attrs['autoplay_reverse']['innerContent']['tablet']['value'] ?? $autoplay_settigns['autoplay_reverse_wide'];
        $autoplay_settigns['autoplay_reverse_small'] = $attrs['autoplay_reverse']['innerContent']['phone']['value'] ?? $autoplay_settigns['autoplay_reverse_mid'];
        $autoplay_settigns['pause_on_hover_wide'] = $attrs['pause_on_hover']['innerContent']['desktop']['value'] ?? 'on';
        $autoplay_settigns['pause_on_hover_mid'] = $attrs['pause_on_hover']['innerContent']['tablet']['value'] ?? $autoplay_settigns['pause_on_hover_wide'];
        $autoplay_settigns['pause_on_hover_small'] = $attrs['pause_on_hover']['innerContent']['phone']['value'] ?? $autoplay_settigns['pause_on_hover_mid'];
        $autoplay_settigns['autoplay_speed_wide'] = $attrs['autoplay_speed']['innerContent']['desktop']['value'] ?? '5000';
        $autoplay_settigns['autoplay_speed_mid'] = $attrs['autoplay_speed']['innerContent']['tablet']['value'] ?? $autoplay_settigns['autoplay_speed_wide'];
        $autoplay_settigns['autoplay_speed_small'] = $attrs['autoplay_speed']['innerContent']['phone']['value'] ?? $autoplay_settigns['autoplay_speed_mid'];

		$allow_touch_move = $attrs['allow_touch_move']['innerContent']['desktop']['value'] ?? "off";
		$allow_touch_move_t = $attrs['allow_touch_move']['innerContent']['tablet']['value'] ?? $allow_touch_move;
		$allow_touch_move_p = $attrs['allow_touch_move']['innerContent']['phone']['value'] ?? $allow_touch_move_t;

		$additional_option = 'data-autoplay_settigns=' . json_encode($autoplay_settigns);
		$additional_option .= ' data-allow_touch_move=' . $allow_touch_move;
		$additional_option .= ' data-allow_touch_move_t=' . $allow_touch_move_t;
		$additional_option .= ' data-allow_touch_move_p=' . $allow_touch_move_p;

        $render_html = static::render_swiper(
            $attrs, 
            $content, 
            $order_number, 
            'dipi-carousel-main '. $show_lightboxclasses, 
            'dipi-carousel-wrapper', 
            'dipi_carousel_child',
			$additional_option,
            ['speed']
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
				'classnamesFunction'  => [ Carousel::class, 'module_classnames' ],
				'stylesComponent'     => [ Carousel::class, 'module_styles' ],
				'scriptDataComponent' => [ Carousel::class, 'module_script_data' ],
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
