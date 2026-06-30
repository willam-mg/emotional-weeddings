<?php
/**
 * MasonryGallery::render_callback()
 *
 * @package DIPI\Modules\MasonryGallery
 * @since ??
 */

namespace DIPI\Modules\MasonryGallery;

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
    private static $vendor_prefix = 'dipi';

	private static function get_attachment_image($attachment_id, $image_size, $fallback_url)
    {
        $attachment = wp_get_attachment_image_src($attachment_id, $image_size);
        if ($attachment) {
            return $attachment[0];
        } else {
            return $fallback_url;
        }
    }

	public static function render_images($args = array(), $conditional_tags = array(), $current_page = array())
    {
        $defaults = [
            'pagination_type'=>'none',
            'gallery_ids' => '',
            'gallery_orderby' => '',
            'title_in_lightbox' => 'off',
            'caption_in_lightbox' => 'off',
            'icon_in_overlay' => 'on',
            'title_in_overlay' => 'off',
            'caption_in_overlay' => 'off',
            'use_media_link' => 'off',
            'use_overlay' => 'off',
            'hover_icon' => '',
            'image_size_desktop' => 'full',
            'image_size_tablet' => 'full',
            'image_size_phone' => 'full',
            'image_animation' => 'none',
            'image_count' => '-1',
			'images_per_page'=>'10',
            'load_more_text' => 'Load More',
            'prev_btn_text' => 'Prev',
            'next_btn_text' => 'Next',
            'fix_lazy' => 'off'
        ];

        $args = wp_parse_args($args, $defaults);

        $icon_animation = $args['icon_animation'];
        $title_animation = $args['title_animation'];
        $caption_animation = $args['caption_animation'];
        $grid_animation = $args['grid_animation'];
        $use_media_link = $args['use_media_link'];
        $use_overlay = $args['use_overlay'];
        $use_overlay_tablet = $args['use_overlay_tablet'];
        $use_overlay_phone = $args['use_overlay_phone'];

        $show_overlay_classes = ($use_overlay === 'on') ? 'show_overlay' : 'hide_overlay';
        if (!empty($use_overlay_tablet)) {
            $show_overlay_classes .= ($use_overlay_tablet === 'on') ? ' show_overlay_tablet' : ' hide_overlay_tablet';
        }
        if (!empty($use_overlay_phone)) {
            $show_overlay_classes .= ($use_overlay_phone === 'on') ? ' show_overlay_phone' : ' hide_overlay_phone';
        }

        $show_lightbox = $args['show_lightbox'];
        $show_lightbox_tablet = isset($args['show_lightbox_tablet']) ? $args['show_lightbox_tablet'] : $show_lightbox;
        $show_lightbox_phone = isset($args['show_lightbox_phone']) ? $args['show_lightbox_phone'] : $show_lightbox_tablet;
        $show_lightboxclasses = ($show_lightbox === 'on') ? 'show_lightbox' : 'hide_lightbox';
        if (!empty($show_lightbox_tablet)) {
            $show_lightboxclasses .= ($show_lightbox_tablet === 'on') ? ' show_lightbox_tablet' : ' hide_lightbox_tablet';
        }
        if (!empty($show_lightbox_phone)) {
            $show_lightboxclasses .= ($show_lightbox_phone === 'on') ? ' show_lightbox_phone' : ' hide_lightbox_phone';
        }
        $data = ($args['horizontal_order_direction'] === 'on') ? 'data-horizontal="on"' : '';
        $use_media_link_class = $args['use_media_link'] === 'on' ? 'use_media_link' : '';
        $image_count = $args['image_count'];
        $image_count_tablet = isset($args['image_count_tablet']) ? $args['image_count_tablet'] : $image_count;
        $image_count_phone = isset($args['image_count_phone']) ? $args['image_count_phone'] : $image_count_tablet;

        $pagination_type = $args['pagination_type'];
        $images_per_page = $args['images_per_page'];
        $load_more_text = $args['load_more_text'];
        $prev_btn_text = $args['prev_btn_text'];
        $next_btn_text = $args['next_btn_text'];

        $items = [
            '<div class="grid-sizer"></div>',
            '<div class="gutter-sizer"></div>',
        ];

        $attachment_ids = explode(",", $args["gallery_ids"]);

        $post_count = count($attachment_ids);

        // Decode HTML entities
        $images_per_page = html_entity_decode($images_per_page);
        // Extract the numeric value (remove any surrounding characters like quotes)
        $images_per_page = filter_var($images_per_page, FILTER_SANITIZE_NUMBER_INT);
        // Ensure it's a valid integer
        $images_per_page = intval($images_per_page);

        $pages = (int) (($post_count - 1) / $images_per_page) + 1;
        $pagination_html = '';
        $pagination_pages = '';

        if (($pagination_type === 'numbered_pagination') && ((int) $pages > 1)) {
            $prev_pagination_html = "<span class='dipi-pagination-btn' data-page='prev'>$prev_btn_text</span>";
            $next_pagination_html = "<span class='dipi-pagination-btn' data-page='next'>$next_btn_text</span>";
            $pagination_html .= $prev_pagination_html;
            for ($pageIndex = 1; $pageIndex <= $pages; $pageIndex++) {
                $one_pagination_html = sprintf(
                    '<span class="dipi-pagination-btn dipi-pagination-btn-%1$s %2$s" data-page="%1$s">
                        %1$s
                    </span>',
                    $pageIndex,
                    $pageIndex == 1 ? 'active' : ''
                );
                $pagination_html .= $one_pagination_html;
            }
            $pagination_html .= $next_pagination_html;
        }
        if ($pagination_type === 'load_more' && ((int) $pages > 1)) {
            $pagination_html = sprintf(
                '<span class="dipi-loadmore-btn" data-page="1">
                    %1$s
                </span>
                ',
                $load_more_text
            );
        }
        if ($pagination_type === 'infinite_scroll' && ((int) $pages > 1)) {
            $pagination_html = sprintf(
                '<span class="dipi-loadmore-btn watch_end_of_grid" data-page="1">
                    %1$s
                </span>
                ',
                $load_more_text
            );
        }

        if ($pagination_html) {
            $pagination_html = sprintf(
                '
                <div class="dipi-pagination" data-page-count="%2$s">
                    %1$s
                </div>',
                $pagination_html,
                $pages
            );
        }
        $overlay_output = '';

        $overlay_icon_classes[] = 'dipi-mansonry-gallery-icon';
        $overlay_icon_use_circle = $args['overlay_icon_use_circle'];
        $overlay_icon_use_circle_border = $args['overlay_icon_use_circle_border'];
        if ('on' === $overlay_icon_use_circle) {
            $overlay_icon_classes[] = 'dipi-mansonry-gallery-icon-circle';
        }

        if ('on' === $overlay_icon_use_circle && 'on' === $overlay_icon_use_circle_border) {
            $overlay_icon_classes[] = 'dipi-mansonry-gallery-icon-circle-border';
        }

        $data_icon = '' !== $args['hover_icon'] ? sprintf(
            ' data-icon="%1$s"',
            esc_attr(Utils::process_font_icon($args['hover_icon']))
        ) : 'data-no-icon';

        if ($use_media_link === 'on') {
            $media_link_url_meta_field = static::$vendor_prefix === 'ds' ? 'ds_masonry_gallery_media_link_url' : 'media_link_url';
            $media_link_target_meta_field = static::$vendor_prefix === 'ds' ? 'ds_masonry_gallery_media_link_target' : 'media_link_target';
        }

        $att_ids = [];
        $gallery_orderby = explode('_', $args['gallery_orderby']);
        if ($gallery_orderby[0] === 'none') {
            $att_ids = $attachment_ids;
        }
        if ($gallery_orderby[0] !== 'none') {
            $query_args = array(
                'post_type' => 'attachment',
                'post_mime_type' => 'image',
                'post__in' => $attachment_ids,
                'posts_per_page' => '-1'
            );

            $query_args['orderby'] = $gallery_orderby[0];

            if (count($gallery_orderby) > 1) {
                $query_args['order'] = strtoupper($gallery_orderby[1]);
            }

            $attachments_posts = get_posts($query_args);
            if ($attachments_posts) {
                foreach ($attachments_posts as $attachment) {
                    $att_ids[] = $attachment->ID;
                }
            }
        }

        foreach ($att_ids as $img_index => $attachment_id) {
            $attachment = wp_get_attachment_image_src($attachment_id, "full");
            if (!$attachment) {
                continue;
            }

            $image = $attachment[0];

            $image_desktop_url = static::get_attachment_image($attachment_id, $args['image_size_desktop'], $image);
            $image_tablet_url = static::get_attachment_image($attachment_id, $args['image_size_tablet'], $image);
            $image_phone_url = static::get_attachment_image($attachment_id, $args['image_size_phone'], $image);

            $image_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
            $image_title = get_the_title($attachment_id);

            $a_open_tag = '';
            $a_close_tag = '';
            if ($use_media_link === 'on') {
                $media_link_url = get_post_meta($attachment_id, $media_link_url_meta_field, true);
                $media_link_target = get_post_meta($attachment_id, $media_link_target_meta_field, true);

                if (!isset($media_link_target)) {
                    $media_link_target = '0';
                }

                $a_open_tag = sprintf(
                    '<a href="%1$s" target="%2$s">',
                    $media_link_url,
                    $media_link_target === '0' ? '_self' : '_blank'
                );
                $a_close_tag = '</a>';
            }

            $icon_html = '';
            if ('on' === $args["icon_in_overlay"]) {
                $icon_html = sprintf(
                    '<div class="et-pb-icon %1$s %3$s animated %4$s"%2$s></div>',
                    ('' !== $args['hover_icon'] ? ' et_pb_inline_icon' : ''),
                    'on' === $args["icon_in_overlay"] ? $data_icon : '',
                    implode(' ', $overlay_icon_classes),
                    $icon_animation
                );
            }

            $title_html = '';
            
            $header_level = isset($args["header_level"])? $args["header_level"] : 'h4';
            if ('on' === $args["title_in_overlay"] && '' !== $image_title) {
                $title_html = sprintf(
                    '<%3$s class="dipi-mansonry-gallery-title animated %2$s">
                        %1$s
                    </%3$s>',
                    $image_title,
                    $title_animation,
                    esc_attr($header_level)
                );
            }

            $caption = wp_get_attachment_caption($attachment_id);
            $caption_html = '';
            if ('on' === $args["caption_in_overlay"] && '' !== $caption) {
                $caption_html = sprintf(
                    '<div class="dipi-mansonry-gallery-caption animated %2$s">
                        %1$s
                    </div>',
                    $caption,
                    $caption_animation
                );
            }

            $overlay_output = sprintf(
                '<span class="dipi_masonry_gallery_overlay background"></span>
                <span class="dipi_masonry_gallery_overlay background-hover"></span>
                <span class="dipi_masonry_gallery_overlay content" style="transition-duration: 0ms;">
                    %1$s
                    %2$s
                    %3$s
                </span>',
                $icon_html,
                $title_html,
                $caption_html
            );
            $item_class = '';
            if ($pagination_type === 'none') {
                if ((int) $image_count >= 0 && $img_index >= (int) $image_count) {
                    $item_class = 'hidden';
                }
				if ((int) $image_count_tablet >= 0 && $img_index >= (int) $image_count_tablet) {
					$item_class .= ' tablet_hidden';
				} else {
					$item_class .= " tablet_show";
				}
				if ((int) $image_count_phone >= 0 && $img_index >= (int) $image_count_phone) {
					$item_class .= ' phone_hidden';
				} else {
					$item_class .= " phone_show";
				}
            } else {
                $page = (int) ($img_index / $images_per_page) + 1;
                $item_class = 'page-' . $page;
                if ($page !== 1) {
                    $item_class .= ' hidden';
                }
                $data_page = 'data-page=' . $page;
                $pagination_pages = 'data-pages=' . $pages;
            }
            $items[] = sprintf(
                '<div class="grid-item et_pb_gallery_image %13$s">
                       %10$s
                        <div class="img-container dipi-mg-animation dipi-mg-%12$s" href="%1$s"%4$s%5$s>
                            <img src="%1$s"
                                alt="%2$s"
                                loading="%14$s"
                            />
                            %6$s
                        </div>
                    %11$s
                </div>',
                $image,
                $image_alt,
                $image_title,
                'on' === $args["title_in_lightbox"] ? " data-title='" . esc_attr($image_title) . "'" : '',
                'on' === $args["caption_in_lightbox"] ? " data-caption='" . esc_attr(wp_get_attachment_caption($attachment_id)) . "'" : '', #5
                et_core_esc_previously($overlay_output),
                $image_desktop_url,
                $image_tablet_url,
                $image_phone_url,
                $a_open_tag, #10
                $a_close_tag,
                $args['image_animation'],
                $item_class,
                $args["fix_lazy"] === 'on' ? esc_attr("eager") : esc_attr("lazy")
            );
        }
        return sprintf('
            <div
                class="dipi_masonry_gallery_container animated %11$s"
                %9$s
                data-count="%10$s"
                data-anim="%11$s"
            >
                <div class="grid %3$s %4$s %5$s" data-lazy="%2$s" %6$s>
                    %1$s
                </div>
                %7$s
            </div>
            ',
            implode("", $items),
            $args["fix_lazy"] === 'on' ? esc_attr("true") : esc_attr("false"),
            $show_lightboxclasses,
            $show_overlay_classes,
            $use_media_link_class, #5
            $data,
            $pagination_html,
            $pages,
            $pagination_pages,
            $post_count, #10
            $grid_animation
        );

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
        $config = [
            "infinite_scroll_viewport" => static::getPropValue($attrs, 'infinite_scroll_viewport'),
        ];

        $computed_depends_on = [
            "pagination_type",
            "gallery_ids",
            "gallery_orderby",
            "title_in_lightbox",
            "caption_in_lightbox",
            "icon_in_overlay",
            "title_in_overlay",
            "caption_in_overlay",
            "use_media_link",
            "use_overlay",
            "hover_icon",
            "image_size_desktop",
            "image_size_tablet",
            "image_size_phone",
            "image_animation",
            "image_count",
            "images_per_page",
            "prev_btn_text",
            "next_btn_text",
            "fix_lazy",
            "icon_animation",
            "title_animation",
            "caption_animation",
            "grid_animation",
            "show_lightbox",
            "horizontal_order_direction",
            "overlay_icon_use_circle",
            "overlay_icon_use_circle_border",
        ];
        $args = [];
        foreach ($computed_depends_on as $key => $value) {
            $args[$value] = static::getPropValue($attrs, $value);
        }
        $args["load_more_text"] = static::getPropValue($attrs, "load_more");
        $args["header_level"] = isset($attrs['header_font']['decoraton']['font']['font']['desktop']['value']['headingLevel']) ? $attrs['header_font']['decoraton']['font']['font']['desktop']['value']['headingLevel'] : 'h4';
        $args["use_overlay_tablet"] = isset($attrs['use_overlay']['innerContent']['tablet']['value']) ? $attrs['use_overlay']['innerContent']['tablet']['value'] : $args['use_overlay'];
        $args["use_overlay_phone"] = isset($attrs['use_overlay']['innerContent']['phone']['value']) ? $attrs['use_overlay']['innerContent']['phone']['value'] : $args['use_overlay_tablet'];
        
        $render_images_html = static::render_images($args);

		$render_html = sprintf(
            '<div class="dipi_masonry_gallery_wrapper" data-config="%2$s">
                %1$s
            </div>
             ',
            $render_images_html,
            esc_attr(htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8'))
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
				'classnamesFunction'  => [ MasonryGallery::class, 'module_classnames' ],
				'stylesComponent'     => [ MasonryGallery::class, 'module_styles' ],
				'scriptDataComponent' => [ MasonryGallery::class, 'module_script_data' ],
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
