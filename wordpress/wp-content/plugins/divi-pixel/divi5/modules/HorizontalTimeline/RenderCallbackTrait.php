<?php
/**
 * HorizontalTimeline::render_callback()
 *
 * @package DIPI\Modules\HorizontalTimeline
 * @since ??
 */

namespace DIPI\Modules\HorizontalTimeline;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use DIPI\Modules\Base\Swiper\SwiperRenderTrait;

trait RenderCallbackTrait {
	use SwiperRenderTrait;

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
		wp_enqueue_script('dipi_horizontal_timeline_public_d5');
		wp_enqueue_style('dipi_swiper_d5');
		wp_enqueue_style('dipi_horizontal_timeline_base_d5');
		wp_enqueue_style('magnific-popup');

		$order_number = $block->parsed_block['orderIndex'];
		
		$speed                          = $attrs['speed']['innerContent']['desktop']['value'] ?? '500';
		$loop                           = $attrs['loop']['innerContent']['desktop']['value'] ?? 'off';
		$centered                       = $attrs['centered']['innerContent']['desktop']['value'] ?? 'off';
		$autoplay                       = $attrs['autoplay']['innerContent']['desktop']['value'] ?? 'off';
		$autoplay_speed                 = $attrs['autoplay_speed']['innerContent']['desktop']['value'] ?? '5000';
		$pause_on_hover                 = $attrs['pause_on_hover']['innerContent']['desktop']['value'] ?? 'on';
		$timeline_active_horizontal_pos = $attrs['timeline_active_horizontal_pos']['innerContent']['desktop']['value'] ?? '50%';

		$navigation          = $attrs['navigation']['innerContent']['desktop']['value'] ?? 'off';
		$navigation_tablet   = $attrs['navigation']['innerContent']['tablet']['value'] ?? $navigation;
		$navigation_phone    = $attrs['navigation']['innerContent']['phone']['value'] ?? $navigation_tablet;
		$navigation_on_hover = $attrs['navigation_on_hover']['innerContent']['desktop']['value'] ?? 'off';

		$pagination      = $attrs['pagination']['innerContent']['desktop']['value'] ?? 'off';
		$dynamic_bullets = $attrs['dynamic_bullets']['innerContent']['desktop']['value'] ?? 'on';

		$show_lightbox        = $attrs['show_lightbox']['innerContent']['desktop']['value'] ?? 'on';
		$show_lightbox_tablet = $attrs['show_lightbox']['innerContent']['tablet']['value'] ?? $show_lightbox;
		$show_lightbox_phone  = $attrs['show_lightbox']['innerContent']['phone']['value'] ?? $show_lightbox_tablet;

		$show_lightboxclasses = ($show_lightbox === 'on') ? 'show_lightbox' : 'hide_lightbox';
		if (!empty($show_lightbox_tablet)) {
			$show_lightboxclasses .= ($show_lightbox_tablet === 'on') ? ' show_lightbox_tablet' : ' hide_lightbox_tablet';
		}
		if (!empty($show_lightbox_phone)) {
			$show_lightboxclasses .= ($show_lightbox_phone === 'on') ? ' show_lightbox_phone' : ' hide_lightbox_phone';
		}

		$columns        = $attrs['columns']['innerContent']['desktop']['value'] ?? '4';
		$columns_tablet = $attrs['columns']['innerContent']['tablet']['value'] ?? $columns;
		$columns_phone  = $attrs['columns']['innerContent']['phone']['value'] ?? $columns_tablet;
		
		if ($columns === "4" && $columns_tablet === "4" && $columns_phone === "4") {
			$columns_tablet = "2";
			$columns_phone = "1";
		}

		$space_between        = $attrs['space_between']['innerContent']['desktop']['value'] ?? '30';
		$space_between_tablet = $attrs['space_between']['innerContent']['tablet']['value'] ?? $space_between;
		$space_between_phone  = $attrs['space_between']['innerContent']['phone']['value'] ?? $space_between_tablet;

		$start_position  = $attrs['start_position']['innerContent']['desktop']['value'] ?? 'top';
		$use_active_line = $attrs['use_active_line']['innerContent']['desktop']['value'] ?? 'on';
		$show_card_arrow = $attrs['show_card_arrow']['innerContent']['desktop']['value'] ?? 'on';

		$layout        = $attrs['layout']['innerContent']['desktop']['value'] ?? 'mixed';
		$layout_tablet = $attrs['layout']['innerContent']['tablet']['value'] ?? $layout;
		$layout_phone  = $attrs['layout']['innerContent']['phone']['value'] ?? $layout_tablet;

		$card_arrow_align        = $attrs['card_arrow_align']['innerContent']['desktop']['value'] ?? 'center';
		$card_arrow_align_tablet = $attrs['card_arrow_align']['innerContent']['tablet']['value'] ?? $card_arrow_align;
		$card_arrow_align_phone  = $attrs['card_arrow_align']['innerContent']['phone']['value'] ?? $card_arrow_align_tablet;

		$data_next_icon          = $attrs['navigation_next_icon']['innerContent']['desktop']['value'] ?? '';
		$data_prev_icon          = $attrs['navigation_prev_icon']['innerContent']['desktop']['value'] ?? '';
		$navigation_next_icon_yn = $attrs['navigation_next_icon_yn']['innerContent']['desktop']['value'] ?? 'off';
		$navigation_prev_icon_yn = $attrs['navigation_prev_icon_yn']['innerContent']['desktop']['value'] ?? 'off';

		$next_icon_render = 'data-icon="9"';
		if ('on' === $navigation_next_icon_yn && !empty($data_next_icon)) {
			$next_icon_render = sprintf('data-icon="%1$s"', esc_attr(Utils::process_font_icon($data_next_icon)));
		}

		$prev_icon_render = 'data-icon="8"';
		if ('on' === $navigation_prev_icon_yn && !empty($data_prev_icon)) {
			$prev_icon_render = sprintf('data-icon="%1$s"', esc_attr(Utils::process_font_icon($data_prev_icon)));
		}

		$navigation_on_hover_class = ($navigation_on_hover === "on") ? "show_on_hover" : "";

		// Match SwiperRenderTrait / Carousel: responsive show/hide classes (base CSS forces display:flex on arrows).
		$navigation_desktop_vis = 'on' === $navigation ? 'show_on_desktop_flex' : 'hide_on_desktop';
		$navigation_tablet_vis  = 'on' === $navigation_tablet ? 'show_on_tablet_flex' : 'hide_on_tablet';
		$navigation_phone_vis   = 'on' === $navigation_phone ? 'show_on_phone_flex' : 'hide_on_phone';
		$navigation_additional_class = trim( "{$navigation_desktop_vis} {$navigation_tablet_vis} {$navigation_phone_vis}" );
		$navigation_nav_classes      = trim( "{$navigation_additional_class} {$navigation_on_hover_class}" );

		$navigation_html = sprintf(
			'<div class="swiper-button-next swiper-arrow-button dipi-sbn%1$s %4$s" %2$s></div>
			<div class="swiper-button-prev swiper-arrow-button dipi-sbp%1$s %4$s" %3$s></div>',
			$order_number,
			$next_icon_render,
			$prev_icon_render,
			esc_attr( $navigation_nav_classes )
		);

		$pagination_html = sprintf(
			'<div class="swiper-pagination dipi-sp%1$s"></div>',
			$order_number
		);

		$timeline_line_html = '<div class="dipi-htl-line"></div>';
		$timeline_active_line_html = '';
		if ($use_active_line === "on") {
			$timeline_active_line_html = '<div class="dipi-htl-line__active"></div>';
		}

		$module_custom_classes = ' dipi_htl_custom_classes';
		$module_custom_classes .= sprintf(' dipi_htl_layout_%1$s', esc_attr($layout));

		if (!empty($layout_tablet)) {
			$module_custom_classes .= " dipi_htl_layout_{$layout_tablet}_tablet";
		} else {
			$module_custom_classes .= " dipi_htl_layout_bottom_tablet";
		}

		if (!empty($layout_phone)) {
			$module_custom_classes .= " dipi_htl_layout_{$layout_phone}_phone";
		} else {
			$module_custom_classes .= " dipi_htl_layout_bottom_phone";
		}

		if (!empty($start_position)) {
			$module_custom_classes .= " startpos-{$start_position}";
		}

		if ($show_card_arrow === 'on') {
			$module_custom_classes .= " dipi_timeline_show-card-arrow";
		}

		if ( $loop === 'on' ) {
			$module_custom_classes .= ' dipi_htl_loop_on';
		}

		if (!empty($card_arrow_align)) {
			$module_custom_classes .= " dipi_timeline_card_arrow_{$card_arrow_align}";
		}
		if (!empty($card_arrow_align_tablet)) {
			$module_custom_classes .= " dipi_timeline_card_arrow_{$card_arrow_align_tablet}_tablet";
		}
		if (!empty($card_arrow_align_phone)) {
			$module_custom_classes .= " dipi_timeline_card_arrow_{$card_arrow_align_phone}_phone";
		}

		// Full data attributes for frontend script (Swiper v11) - must match visual builder
		$additional_options = sprintf(
			'data-ordernumber="%1$s" data-speed="%2$s" data-loop="%3$s" data-centered="%4$s" data-autoplay="%5$s" data-autoplayspeed="%6$s" data-pauseonhover="%7$s" data-navigation="%8$s" data-navigation_t="%9$s" data-navigation_m="%10$s" data-pagination="%11$s" data-dynamicbullets="%12$s" data-columnsdesktop="%13$s" data-columnstablet="%14$s" data-columnsphone="%15$s" data-spacebetween="%16$s" data-spacebetween_tablet="%17$s" data-spacebetween_phone="%18$s" data-layout="%19$s" data-layout_t="%20$s" data-layout_m="%21$s" data-card_arrow_align="%22$s" data-act_horizontal_pos="%23$s"',
			esc_attr( $order_number ),
			esc_attr( $speed ),
			esc_attr( $loop ),
			esc_attr( $centered ),
			esc_attr( $autoplay ),
			esc_attr( $autoplay_speed ),
			esc_attr( $pause_on_hover ),
			esc_attr( $navigation ),
			esc_attr( $navigation_tablet ),
			esc_attr( $navigation_phone ),
			esc_attr( $pagination ),
			esc_attr( $dynamic_bullets ),
			esc_attr( $columns ),
			esc_attr( $columns_tablet ),
			esc_attr( $columns_phone ),
			esc_attr( $space_between ),
			esc_attr( $space_between_tablet ),
			esc_attr( $space_between_phone ),
			esc_attr( $layout ),
			esc_attr( $layout_tablet ),
			esc_attr( $layout_phone ),
			esc_attr( $card_arrow_align ),
			esc_attr( $timeline_active_horizontal_pos )
		);

		// Build the HTML structure to match Divi 4 exactly (swiper class for Swiper v11 CSS)
		$render_html = sprintf(
			'<div class="dipi-carousel-main %5$s %8$s" %2$s>
				<div class="dipi_htl_container swiper">
					<div class="dipi-htl-items">
						%1$s
					</div>
					%6$s
					%7$s
				</div>
				%3$s
				<div class="dipi_htl_container-horizontal">
					%4$s
				</div>
			</div>',
			$content,                      // %1$s - carousel content (children)
			$additional_options,            // %2$s - data attributes
			$navigation_html,              // %3$s - navigation
			$pagination_html,              // %4$s - pagination
			$show_lightboxclasses,         // %5$s - lightbox classes
			$timeline_line_html,           // %6$s - timeline line
			$timeline_active_line_html,    // %7$s - active timeline line
			$module_custom_classes         // %8$s - module custom classes
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
				'classnamesFunction' => [ HorizontalTimeline::class, 'module_classnames' ],
				'stylesComponent' => [ HorizontalTimeline::class, 'module_styles' ],
				'scriptDataComponent' => [ HorizontalTimeline::class, 'module_script_data' ],
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
				'childrenIds' => $block->parsed_block['innerBlocks'] ? array_map(
					function( $inner_block ) {
						return $inner_block['id'];
					},
					$block->parsed_block['innerBlocks']
				) : [],
			]
		);
	}
}

