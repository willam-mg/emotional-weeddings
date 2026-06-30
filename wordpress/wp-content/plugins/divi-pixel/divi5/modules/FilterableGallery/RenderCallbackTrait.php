<?php
/**
 * FilterableGallery::render_callback()
 *
 * @package DIPI\Modules\FilterableGallery
 * @since ??
 */

namespace DIPI\Modules\FilterableGallery;

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

	public static function render_filter_bar($args = array(), $conditional_tags = array(), $current_page = array()) {
        $filter_bar_html = '';
        $defaults = [
            'filter_bar_name_level' => 'div',
            'filter_bar_desc_level' => 'div',
        ];
        $args = wp_parse_args($args, $defaults);
        $show_all_filter = $args['show_all_filter'];
        $all_filter_label = $args['all_filter_label'];
        $include_term_ids = $args['include_term_ids'];
        $show_num_of_elements = $args['show_num_of_elements'];
        $filter_bar_name_level = $args['filter_bar_name_level'];
        $filter_bar_desc_level = $args['filter_bar_desc_level'];

        if (!$include_term_ids) {
            return sprintf('<div class="alert" data-items-count="0">
                    Please Select <strong>\'Included Divi Pixel Category\'</strong> to show in filter bar.
                    <br>
                    If you still didn\'t add terms of <strong>\'Divi Pixel Category\'</strong>,
                    you can add new terms and assign in <a href="/wp-admin/edit-tags.php?taxonomy=dipi_media_category&post_type=attachment" target="_blank"><strong>Media Library</strong></a>.
                </div>');
        }

        $dipi_include_terms = [];
        if($include_term_ids){
            foreach ($include_term_ids as $term_id) {
                $dipi_include_term = get_term( $term_id );
                if ($dipi_include_term) {
                    $dipi_include_terms[] = $dipi_include_term;
                }
            }
        }
        if ($show_all_filter == 'on') {
            $dipi_include_term_all = new \stdClass();
            $dipi_include_term_all->name = $all_filter_label;
            array_unshift($dipi_include_terms, $dipi_include_term_all);
        }
        foreach($dipi_include_terms as $index => $dipi_include_term){
            $media = '';
            $extra_class = '';
            if(!empty($dipi_include_term->name) || !empty($dipi_include_term->description)){
                $name_html = sprintf('<%2$s class="dipi-filter-bar-name">%1$s</%2$s>',
                    !empty($dipi_include_term->name) ? $dipi_include_term->name : '',
                    $filter_bar_name_level
                );
                $desc_html = sprintf('<%2$s class="dipi-filter-bar-item-desc">%1$s</%2$s>',
                    !empty($dipi_include_term->description) ? $dipi_include_term->description : '',
                    $filter_bar_desc_level
                );
            }
            $count_html = '';
            if ( $show_num_of_elements === 'on') {
                $query_images_args = [];
                $orderby = 'date';
                $tax_query = [];
                if ($show_all_filter == 'on' && $index == 0 ) {
                    $tax_query = [
                        [
                            'taxonomy' => 'dipi_media_category',
                            'field'    => 'id',
                            'terms'    => $include_term_ids,
                        ]
                    ];
                } else {
                    $tax_query = [
                        [
                        'taxonomy' => 'dipi_media_category',
                        'field'    => 'slug',
                        'terms'    => $dipi_include_term->slug,
                        ]
                    ];
                }
                $query_images_args = array(
                    'post_type'      => 'attachment',
                    'post_mime_type' => 'image',
                    'post_status'    => 'inherit',
                    'posts_per_page' => - 1,
                    'tax_query'      => $tax_query,
                    'orderby'       => $orderby,
                    'fields' => 'ids',
                    'no_found_rows' => true,
                );
                $query_images = new \WP_Query( $query_images_args );
                $post_count = $query_images->post_count;
                $count_html = sprintf('<div class="dipi-filter-bar-count">%1$s</div>', $query_images->post_count);
            }

            $filter_bar_html .=sprintf('
                <div class="dipi-filter-bar-item dipi-filter-bar-item-%3$s %5$s"
                    data-index="%3$s"
                    data-term="%4$s"
                >
                    <div class="dipi-filter-bar-item-title">
                        %1$s
                        %6$s
                    </div>
                    %2$s
                </div>
                ',
                $name_html, 
                $desc_html,
                $index,
                $dipi_include_term->name,
                $index === 0 ? 'active' : '', #5
                $count_html
            );
        }

        return sprintf(
            '<div class="dipi-filter-bar" data-items-count="%2$s">
                %1$s
            </div>
           ',
            $filter_bar_html,
            count($dipi_include_terms)
        );
    }

	public static function render_gallery($args = array(), $conditional_tags = array(), $current_page = array()) {
        $defaults = [
            'images' => '',
            'pagination_type'=>'none',
            'gallery_orderby' => '',
            'title_in_lightbox' => 'off',
            'caption_in_lightbox' => 'off',
            'icon_in_overlay' => 'off',
            'title_in_overlay' => 'off',
            'caption_in_overlay' => 'off',
            'use_media_link' => 'off',
            'use_overlay' => 'off',
            'use_overlay_tablet' => '',
            'use_overlay_phone' => '',
            'hover_icon' => '',
            'image_size_desktop' => 'full',
            'image_size_tablet' => 'full',
            'image_size_phone' => 'full',
            'image_animation' => 'none',
            'grid_animation'=> 'none',
            'show_lightbox_link_icon' => 'off',
            'image_count' => '-1',
            'images_per_page'=>'10',
            'load_more_text' => 'Load More',
            'prev_btn_text' => 'Prev',
            'next_btn_text' => 'Next',
        ];
        $args = wp_parse_args($args, $defaults);
        if ($args['use_overlay_tablet'] === '' || $args['use_overlay_tablet'] === null) {
            $args['use_overlay_tablet'] = $args['use_overlay'];
        }
        if ($args['use_overlay_phone'] === '' || $args['use_overlay_phone'] === null) {
            $args['use_overlay_phone'] = $args['use_overlay_tablet'];
        }
        $show_all_filter = $args['show_all_filter'];
        $all_filter_label = $args['all_filter_label'];
        $include_term_ids = $args['include_term_ids'];
        $grid_animation = $args['grid_animation'];
        $show_lightbox_link_icon = $args['show_lightbox_link_icon'];
        $use_overlay = $args['use_overlay'];
        $use_overlay_tablet = $args['use_overlay_tablet'];
        $use_overlay_phone = $args['use_overlay_phone'];
        $grid_layout = $args['grid_layout'];
        $use_media_link = $args['use_media_link'];
        $icon_animation = $args['icon_animation'];
        $title_animation = $args['title_animation'];
        $caption_animation = $args['caption_animation'];

        $show_lightbox = $args['show_lightbox'];
        $show_lightbox_tablet = isset($args['show_lightbox_tablet']) ? $args['show_lightbox_tablet'] : $show_lightbox;
        $show_lightbox_phone = isset($args['show_lightbox_phone']) ? $args['show_lightbox_phone'] : $show_lightbox_tablet;
        $gallery_orderby = $args['gallery_orderby'];
        $overlay_icon_use_circle = $args['overlay_icon_use_circle'];
        $overlay_icon_use_circle_border = $args['overlay_icon_use_circle_border'];
        $icon_in_overlay = $args["icon_in_overlay"];
        $hover_icon = $args['hover_icon'];
        $title_in_overlay = $args["title_in_overlay"];
        $caption_in_overlay = $args["caption_in_overlay"];
        $show_img_category = $args["show_img_category"];
        $show_img_title = $args["show_img_title"];
        $show_img_caption = $args["show_img_caption"];
        $title_in_lightbox = $args["title_in_lightbox"];
        $caption_in_lightbox = $args["caption_in_lightbox"];
        $image_animation = $args["image_animation"];
        $fix_lazy = $args["fix_lazy"];
        $pagination_type = $args['pagination_type'];
        $image_count = $args['image_count'];
        $image_count_tablet = isset($args['image_count_tablet']) ? $args['image_count_tablet'] : $image_count;
        $image_count_phone = isset($args['image_count_phone']) ? $args['image_count_phone'] : $image_count_tablet;
        $images_per_page = $args['images_per_page'];
        $load_more_text = $args['load_more_text'];
        $prev_btn_text = $args['prev_btn_text'];
        $next_btn_text = $args['next_btn_text'];
        
        $config = [
            'grid_layout' => $grid_layout,
        ];

        $dipi_include_terms = [];
        if($include_term_ids){
            foreach ($include_term_ids as $term_id) {
                $dipi_include_term = get_term( $term_id );
                if ($dipi_include_term) {
                    $dipi_include_terms[] = $dipi_include_term;
                }
            }
        }
        
        if ($show_all_filter == 'on') {
            $dipi_include_term_all = new \stdClass();
            $dipi_include_term_all->name = $all_filter_label;
            array_unshift($dipi_include_terms, $dipi_include_term_all);
        }

        $show_overlay_classes = ($use_overlay === 'on') ? 'show_overlay' : 'hide_overlay';
        if (!empty($use_overlay_tablet)) {
            $show_overlay_classes .= ($use_overlay_tablet === 'on') ? ' show_overlay_tablet' : ' hide_overlay_tablet';
        }
        if (!empty($use_overlay_phone)) {
            $show_overlay_classes .= ($use_overlay_phone === 'on') ? ' show_overlay_phone' : ' hide_overlay_phone';
        }

        $show_lightboxclasses = "";
        $use_media_link_class = "";
        if ($use_media_link  === "on" ) {
            if ($show_lightbox_link_icon === 'on') {
                $show_lightboxclasses = ($show_lightbox === 'on') ? 'show_lightbox' : 'hide_lightbox';
                if (!empty($show_lightbox_tablet)) {
                    $show_lightboxclasses .= ($show_lightbox_tablet === 'on') ? ' show_lightbox_tablet' : ' hide_lightbox_tablet';
                }
                if (!empty($show_lightbox_phone)) {
                    $show_lightboxclasses .= ($show_lightbox_phone === 'on') ? ' show_lightbox_phone' : ' hide_lightbox_phone';
                }
            } else {
                $show_lightboxclasses = "hide_lightbox";
            }
            $use_media_link_class ='use_media_link';
        } else {
            $show_lightboxclasses = ($show_lightbox === 'on') ? 'show_lightbox' : 'hide_lightbox';
            if (!empty($show_lightbox_tablet)) {
                $show_lightboxclasses .= ($show_lightbox_tablet === 'on') ? ' show_lightbox_tablet' : ' hide_lightbox_tablet';
            }
            if (!empty($show_lightbox_phone)) {
                $show_lightboxclasses .= ($show_lightbox_phone === 'on') ? ' show_lightbox_phone' : ' hide_lightbox_phone';
            }
        }

        $gallery_html = '';
        $pagination_pages = '';
        foreach($dipi_include_terms as $index=>$dipi_include_term){
            $items = [
                '<div class="grid-sizer"></div>',
                '<div class="gutter-sizer"></div>',
            ];
            $query_images_args = [];
            $tax_query = [];
            if ($show_all_filter == 'on' && $index == 0 ) {
                $tax_query = [
                    [
                        'taxonomy' => 'dipi_media_category',
                        'field'    => 'id',
                        'terms'    => $include_term_ids,
                    ]
                ];
            } else {
                $tax_query = [
                    [
                    'taxonomy' => 'dipi_media_category',
                    'field'    => 'slug',
                    'terms'    => $dipi_include_term->slug,
                    ]
                ];
            }
            $query_images_args = array(
                'post_type'      => 'attachment',
                'post_mime_type' => 'image',
                'post_status'    => 'inherit',
                'posts_per_page' => - 1,
                'tax_query'      => $tax_query,
            );

            switch ($gallery_orderby) {
                case 'date_asc':
                    $query_images_args['orderby'] = 'date';
                    $query_images_args['order'] = 'ASC';
                    break;
                case 'title_asc':
                    $query_images_args['orderby'] = 'title';
                    $query_images_args['order'] = 'ASC';
                    break;
                case 'title_desc':
                    $query_images_args['orderby'] = 'title';
                    $query_images_args['order'] = 'DESC';
                    break;
                case 'rand':
                    $query_images_args['orderby'] = 'rand';
                    break;
                case 'menu_asc':
                    $query_images_args['orderby'] = 'menu_order';
                    $query_images_args['order'] = 'ASC';
                    break;
                case 'menu_desc':
                    $query_images_args['orderby'] = 'menu_order';
                    $query_images_args['order'] = 'DESC';
                    break;
                case '':
                default:
                    $query_images_args['orderby'] = 'date';
                    $query_images_args['order'] = 'DESC';
                    break;
            }

            $query_images = new \WP_Query( $query_images_args );
            $post_count = $query_images->post_count;
            $pages = (int)(($post_count  - 1) / $images_per_page) + 1;
            $pagination_html = '';
            $pagination_pages = '';
            if (($pagination_type === 'numbered_pagination') &&  ((int)$pages > 1)) {
                $prev_pagination_html = "<span class='dipi-pagination-btn' data-page='prev'>$prev_btn_text</span>";
                $next_pagination_html = "<span class='dipi-pagination-btn' data-page='next'>$next_btn_text</span>";
                $pagination_html .= $prev_pagination_html;
                for ($pageIndex = 1; $pageIndex <= $pages ; $pageIndex++) {
                    $one_pagination_html = sprintf(
                        '<span class="dipi-pagination-btn dipi-pagination-btn-%1$s %2$s" data-page="%1$s">
                            %1$s
                        </span>',
                        $pageIndex,
                        $pageIndex == 1 ? 'active' : ''
                    );
                    $pagination_html.= $one_pagination_html;
                }
                $pagination_html .= $next_pagination_html;
            }
            if ($pagination_type === 'load_more' && ((int)$pages > 1)) {
                $pagination_html =sprintf(
                    '<span class="dipi-loadmore-btn" data-page="1">
                        %1$s
                    </span>
                    ',
                    $load_more_text
                );
            }
            if ($pagination_type === 'infinite_scroll' && ((int)$pages > 1)) {
                $pagination_html =sprintf(
                    '<span class="dipi-loadmore-btn watch_end_of_grid" data-page="1">
                        %1$s
                    </span>
                    ',
                    $load_more_text
                );
            }
            $attachment_ids = array();
            foreach ( $query_images->posts as $image ) {
                $attachment_ids[] = $image->ID;
            }

            //$attachment_ids = explode(",", $args["images"]);
            if ('rand' === $gallery_orderby) {
                // echo "every day I'm shuffling";
                shuffle($attachment_ids);
            } else {
                // echo "no shuffle today";
            }

            $overlay_output = '';

            $overlay_icon_classes[] = 'dipi-filterable-gallery-icon';
            
            if ('on' === $overlay_icon_use_circle) {
                $overlay_icon_classes[] = 'dipi-filterable-gallery-icon-circle';
            }

            if ('on' === $overlay_icon_use_circle && 'on' === $overlay_icon_use_circle_border) {
                $overlay_icon_classes[] = 'dipi-filterable-gallery-icon-circle-border';
            }

            $data_icon = '' !== $hover_icon ? sprintf(
                ' data-icon="%1$s"',
                esc_attr(Utils::process_font_icon($hover_icon))
            ) : 'data-no-icon';

            if ($use_media_link === 'on') {
                $media_link_url_meta_field = static::$vendor_prefix === 'ds' ? 'ds_filterable_gallery_media_link_url' : 'media_link_url';
                $media_link_target_meta_field = static::$vendor_prefix === 'ds' ? 'ds_filterable_gallery_media_link_target' : 'media_link_target';
            }
            
            foreach ($attachment_ids as $img_index=>$attachment_id) {
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
                $img_a_open_tag = '';
                $img_a_close_tag = '';
                $lightbox_and_link_icon_html = '';

                if ($use_media_link === 'on') {
                    $media_link_url = get_post_meta($attachment_id, $media_link_url_meta_field, true);
                    $media_link_target = get_post_meta($attachment_id, $media_link_target_meta_field, true);
                    $a_open_tag = sprintf('<a href="%1$s" target="%2$s">',
                        esc_url($media_link_url),
                        $media_link_target === '0' ? '_self' : '_blank'
                    );
                    $a_close_tag = '</a>';
                    if (!isset($media_link_target)) {
                        $media_link_target = '0';
                    }

                    if ($use_overlay === 'on'
                        && $show_lightbox_link_icon === 'on'
                    ) {
                        $lightbox_icon_html = sprintf(
                            '<a href="%1$s" class="et-pb-icon et_pb_inline_icon %2$s animated %3$s lightbox-icon" data-icon="&#x55;" data-anim="%3$s" aria-label="%4$s"></a>',
                            esc_url($image),
                            implode(' ', $overlay_icon_classes),
                            $icon_animation,
                            esc_attr__('Open image in lightbox', 'dipi-divi-pixel')
                        );

                        $link_icon_html = sprintf(
                            '<a href="%3$s" target="%4$s">
                                <div
                                    class="et-pb-icon et_pb_inline_icon %1$s animated %2$s link-icon"
                                    data-icon="&#xe02c;"
                                    data-anim="%2$s"
                                >
                                </div>
                            </a>',
                            implode(' ', $overlay_icon_classes),
                            $icon_animation,
                            $media_link_url,
                            $media_link_target === '0' ? '_self' : '_blank'
                        );

                        
                        $lightbox_and_link_icon_html =  sprintf(
                            '<div class="dipi_lightbox_link_icon">
                                %1$s
                                %2$s
                            </div>',
                            $lightbox_icon_html,
                            $link_icon_html
                        );
                    } else {
                        $img_a_open_tag = $a_open_tag;
                        $img_a_close_tag = $a_close_tag;
                    }
                }

                $icon_html = '';
                if ('on' === $icon_in_overlay) {
                    $icon_html = sprintf(
                        '<div class="et-pb-icon %1$s %3$s animated %4$s"%2$s data-anim="%4$s"></div>',
                        ('' !== $hover_icon ? ' et_pb_inline_icon' : ''),
                        'on' === $icon_in_overlay ? $data_icon : '',
                        implode(' ', $overlay_icon_classes),
                        $icon_animation
                    );
                }

                $name_html = '';
                $header_level = $args['header_level'];
                if ('on' === $title_in_overlay && '' !== $image_title) {
                    $name_html = sprintf(
                        '<%3$s
                            class="dipi-filterable-gallery-title animated %2$s"
                            data-anim="%2$s"
                        >
                            %1$s
                        </%3$s>',
                        $image_title,
                        $title_animation,
                        $header_level
                    );
                }

                $caption = wp_get_attachment_caption($attachment_id);
                $caption_html = '';
                if ('on' === $caption_in_overlay && '' !== $caption) {
                    $caption_html = sprintf(
                        '<div
                            class="dipi-filterable-gallery-caption animated %2$s"
                            data-anim="%2$s"
                        >
                            %1$s
                        </div>',
                        $caption,
                        $caption_animation
                    );
                }

                $overlay_output = sprintf(
                    '<span class="dipi_filterable_gallery_overlay background"></span>
                    <span class="dipi_filterable_gallery_overlay background-hover"></span>
                    <span class="dipi_filterable_gallery_overlay content" style="transition-duration: 0ms;">
                        %4$s
                        %1$s
                        %2$s
                        %3$s
                    </span>',
                    $icon_html,
                    $name_html,
                    $caption_html,
                    $lightbox_and_link_icon_html
                );

                $item_class = '';
                $data_page = '';
                $pagination_pages = '';
                if ($pagination_type === 'none') {
                    if ((int)$image_count >=0 && $img_index >= (int)$image_count) {
                        $item_class = 'hidden';
                    }
                    if ((int)$image_count_tablet >= 0 && $img_index >=(int)$image_count_tablet) {
                        $item_class .= ' tablet_hidden';
                    } else {
                        $item_class .=" tablet_show";
                    }
                    if ((int)$image_count_phone >= 0 && $img_index >=(int)$image_count_phone) {
                        $item_class .= ' phone_hidden';
                    } else {
                        $item_class .=" phone_show";
                    }
                } else {
                    $page = (int)($img_index  / $images_per_page) + 1;
                    $item_class = 'page-'.$page;
                    if ( $page  !== 1) {
                        $item_class.=' hidden';
                    }
                    $data_page = 'data-page='.$page;
                    $pagination_pages='data-pages='.$pages;
                }
                //Grid Content
                $grid_item_category_html = '';
                if ('on' === $show_img_category &&  !empty($dipi_include_term->name)) {
                    $item_category_terms = get_the_terms($attachment_id, 'dipi_media_category');

                    $item_category_term_name = array_map(function ($term)
                    {
                        return $term->name;
                    }, $item_category_terms);
                    
                    $grid_item_category_html = sprintf(
                        '<span class="dipi-grid-item-category">
                            %1$s
                        </span>',
                        implode(", ", $item_category_term_name)
                    );
                }                
                $grid_item_title_html = '';
                $grid_item_title_level = $args['grid_item_title_level'];
                if ('on' === $show_img_title && '' !== $image_title) {
                    $grid_item_title_html = sprintf(
                        '<%2$s class="dipi-grid-item-title">
                            %1$s
                        </%2$s>',
                        $image_title,
                        esc_attr($grid_item_title_level)
                    );
                }

                $grid_item_caption_html = '';
                if ('on' === $show_img_caption && '' !== $caption) {
                    $grid_item_caption_html = sprintf(
                        '<div class="dipi-grid-item-caption">
                            %1$s
                        </div>',
                        $caption
                    );
                }

                $grid_content_html = sprintf(
                    '<div class="dipi-grid-item-content">
                        %1$s
                        %2$s
                        %3$s
                    </div>',
                    $grid_item_category_html,
                    $grid_item_title_html,
                    $grid_item_caption_html
                );

                $items[] = sprintf(
                    '<div class="grid-item %14$s" %17$s>
                        %10$s
                            <div class="img-container dipi-fg-animation dipi-fg-%12$s" href="%1$s"%4$s%5$s>
                                <img src="%1$s"
                                    alt="%2$s"
                                    srcset="%9$s 768w, %8$s 980w, %7$s 1024w"
                                    sizes="(max-width: 768px) 768px, (max-width: 980px) 980px, 1024px"
                                    loading="%18$s"
                                />
                                %6$s
                            </div>
                        %11$s
                        %15$s
                            %13$s
                        %16$s
                    </div>',
                    $image,
                    $image_alt,
                    $image_title,
                    'on' === $title_in_lightbox ? " data-title='$image_title'" : '',
                    'on' === $caption_in_lightbox ? " data-caption='" . htmlspecialchars(wp_get_attachment_caption($attachment_id)) . "'" : '', #5
                    et_core_esc_previously($overlay_output),
                    $image_desktop_url,
                    $image_tablet_url,
                    $image_phone_url,
                    $img_a_open_tag, #10
                    $img_a_close_tag,
                    $image_animation,
                    $grid_content_html,
                    $item_class,
                    $a_open_tag, #15
                    $a_close_tag,
                    $data_page,
                    $fix_lazy === 'on' ? esc_attr("eager") : esc_attr("lazy")
                );
            }
            if ($pagination_html) {
                $pagination_html = sprintf('
                    <div class="dipi-pagination" data-page-count="%2$s">
                        %1$s
                    </div>',
                    $pagination_html,
                    $pages
                );
            }
            $gallery_html.= sprintf('
                <div
                    class="
                        dipi-filtered-gallery-item
                        dipi-filtered-gallery-item-%6$s
                        %9$s
                        animated
                        %11$s
                    "
                    data-index="%6$s"
                    data-term="%7$s"
                    data-count="%8$s"
                    data-anim="%11$s"
                    %13$s
                >
                    <div class="grid %3$s %4$s %5$s animated %11$s" data-lazy="%2$s" data-config="%10$s">
                        %1$s
                    </div>
                    %12$s
                </div>',
                implode("", $items),
                $fix_lazy === 'on' ? esc_attr("true") : esc_attr("false"),
                $show_lightboxclasses,
                $show_overlay_classes,
                $use_media_link_class, #5
                $index,
                $dipi_include_term->name,
                $post_count,
                $index === 0 ? 'active' : '',
                esc_attr(htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8')), #10
                $grid_animation,
                $pagination_html,
                $pagination_pages
            );
        }
        return sprintf(
            '<div
                class="dipi-filtered-gallery-container"
                data-items-count="%2$s"
            >
                %1$s
             </div>',
            $gallery_html,
            count($dipi_include_terms)
        );
    }

	public static function get_filterable_gallery_data($args = array(), $conditional_tags = array(), $current_page = array()) {
		$filter_bar_html = static::render_filter_bar($args, $conditional_tags, $current_page);
        $gallery_html = static::render_gallery($args, $conditional_tags, $current_page);
        
        return sprintf(
            '%1$s
            %2$s',
            $filter_bar_html,
            $gallery_html
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

        $use_overlay_desktop = isset($attrs['use_overlay']['innerContent']['desktop']['value']) ? $attrs['use_overlay']['innerContent']['desktop']['value'] : 'off';
        $use_overlay_tablet_val = isset($attrs['use_overlay']['innerContent']['tablet']['value']) ? $attrs['use_overlay']['innerContent']['tablet']['value'] : $use_overlay_desktop;
        $use_overlay_phone_val = isset($attrs['use_overlay']['innerContent']['phone']['value']) ? $attrs['use_overlay']['innerContent']['phone']['value'] : $use_overlay_tablet_val;

        $show_lightbox_desktop = isset($attrs['show_lightbox']['innerContent']['desktop']['value']) ? $attrs['show_lightbox']['innerContent']['desktop']['value'] : 'off';
        $show_lightbox_tablet_val = isset($attrs['show_lightbox']['innerContent']['tablet']['value']) ? $attrs['show_lightbox']['innerContent']['tablet']['value'] : $show_lightbox_desktop;
        $show_lightbox_phone_val = isset($attrs['show_lightbox']['innerContent']['phone']['value']) ? $attrs['show_lightbox']['innerContent']['phone']['value'] : $show_lightbox_tablet_val;

        $args = [
            "include_term_ids" => static::getPropValue($attrs, 'include_term_ids'),
            "pagination_type" => static::getPropValue($attrs, "pagination_type"),
            "image_count" => static::getPropValue($attrs, "image_count"),
            "images_per_page" => static::getPropValue($attrs, "images_per_page"),
            "prev_btn_text" => static::getPropValue($attrs, "prev_btn_text"),
            "next_btn_text" => static::getPropValue($attrs, "next_btn_text"),
            "load_more_text" => static::getPropValue($attrs, "load_more"),
            "show_all_filter" => static::getPropValue($attrs, "show_all_filter"),
            "all_filter_label" => static::getPropValue($attrs, "all_filter_label"),
            "show_num_of_elements" => static::getPropValue($attrs, "show_num_of_elements"),
            "grid_layout" => static::getPropValue($attrs, "grid_layout"),
            "use_media_link" => static::getPropValue($attrs, "use_media_link"),
            "show_lightbox_link_icon" => static::getPropValue($attrs, "show_lightbox_link_icon"),
            "show_lightbox" => $show_lightbox_desktop,
            "show_lightbox_tablet" => $show_lightbox_tablet_val,
            "show_lightbox_phone" => $show_lightbox_phone_val,
            "title_in_lightbox" => static::getPropValue($attrs, "title_in_lightbox"),
            "caption_in_lightbox" => static::getPropValue($attrs, "caption_in_lightbox"),
            "icon_in_overlay" => static::getPropValue($attrs, "icon_in_overlay"),
            "title_in_overlay" => static::getPropValue($attrs, "title_in_overlay"),
            "caption_in_overlay" => static::getPropValue($attrs, "caption_in_overlay"),
            "overlay_icon_use_circle" => static::getPropValue($attrs, "overlay_icon_use_circle"),
            "overlay_icon_use_circle_border" => static::getPropValue($attrs, "overlay_icon_use_circle_border"),
            "gallery_orderby" => static::getPropValue($attrs, "gallery_orderby"),
            "hover_icon" => static::getPropValue($attrs, "hover_icon"),
            "use_overlay" => $use_overlay_desktop,
            "use_overlay_tablet" => $use_overlay_tablet_val,
            "use_overlay_phone" => $use_overlay_phone_val,
            "icon_animation" => static::getPropValue($attrs, "icon_animation"),
            "title_animation" => static::getPropValue($attrs, "title_animation"),
            "caption_animation" => static::getPropValue($attrs, "caption_animation"),
            "image_animation" => static::getPropValue($attrs, "image_animation"),
            "grid_animation" => static::getPropValue($attrs, "grid_animation"),
            "grid_animation_delay" => static::getPropValue($attrs, "grid_animation_delay"),
            "grid_animation_speed" => static::getPropValue($attrs, "grid_animation_speed"),
            "show_img_caption" => static::getPropValue($attrs, "show_img_caption"),
            "show_img_title" => static::getPropValue($attrs, "show_img_title"),
            "show_img_category" => static::getPropValue($attrs, "show_img_category"),
            "fix_lazy" => static::getPropValue($attrs, "fix_lazy"),
            "grid_item_title_level" => isset($attrs['grid_item_title']['decoration']['font']['font']['desktop']['value']['headingLevel']) ? $attrs['grid_item_title']['decoration']['font']['font']['desktop']['value']['headingLevel'] : 'h4',
            "header_level" => isset($attrs['header_font']['decoraton']['font']['font']['desktop']['value']['headingLevel']) ? $attrs['header_font']['decoraton']['font']['font']['desktop']['value']['headingLevel'] : 'h4',
            "filter_bar_name_level" => isset($attrs['filter_bar_name_font']['decoraton']['font']['font']['desktop']['value']['headingLevel']) ? $attrs['filter_bar_name_font']['decoraton']['font']['font']['desktop']['value']['headingLevel'] : 'div',
            "filter_bar_desc_level" => isset($attrs['filter_bar_desc_font']['decoraton']['font']['font']['desktop']['value']['headingLevel']) ? $attrs['filter_bar_desc_font']['decoraton']['font']['font']['desktop']['value']['headingLevel'] : 'div',
        ];

        $filterable_gallery_html = static::get_filterable_gallery_data($args);

        $scroll_to_top = $attrs['scroll_to_top']['innerContent']['desktop']['value'] ?? 'off';
        $scroll_to_top_tablet = $attrs['scroll_to_top']['innerContent']['tablet']['value'] ?? $scroll_to_top;
        $scroll_to_top_phone = $attrs['scroll_to_top']['innerContent']['phone']['value'] ?? $scroll_to_top;

        $scroll_to_top_offset = $attrs['scroll_to_top_offset']['innerContent']['desktop']['value'] ?? 'off';
        $scroll_to_top_offset_tablet = $attrs['scroll_to_top_offset']['innerContent']['tablet']['value'] ?? $scroll_to_top;
        $scroll_to_top_offset_phone = $attrs['scroll_to_top_offset']['innerContent']['phone']['value'] ?? $scroll_to_top;

        $config = [
            "infinite_scroll_viewport" => static::getPropValue($attrs, 'infinite_scroll_viewport'),
            "scroll_to_top" => [
                "desktop" => $scroll_to_top,
                "tablet" => $scroll_to_top_tablet,
                "phone" => $scroll_to_top_phone,
            ],
            "scroll_to_top_offset" => [
                "desktop" => $scroll_to_top_offset,
                "tablet" => $scroll_to_top_offset_tablet,
                "phone" => $scroll_to_top_offset_phone,
            ],
        ];
        $grid_layout = static::getPropValue($attrs, 'grid_layout');
        $sticky_filter_bar = static::getPropValue($attrs, 'sticky_filter_bar');
        $sticky_filter_bar_tablet = isset($attrs['sticky_filter_bar']['innerContent']['tablet']['value']) ? $attrs['sticky_filter_bar']['innerContent']['tablet']['value'] : $sticky_filter_bar;
        $sticky_filter_bar_phone = isset($attrs['sticky_filter_bar']['innerContent']['phone']['value']) ? $attrs['sticky_filter_bar']['innerContent']['phone']['value'] : $sticky_filter_bar_tablet;
        $module_custom_classes = 'dipi_filterable_gallery_wrapper';
        if ($grid_layout === 'grid') {
            $module_custom_classes.=" layout_grid";
        }
        if ($sticky_filter_bar  === "on") {
            $module_custom_classes.=" sticky_filter_bar";
        }
        if ($sticky_filter_bar_tablet  === "on") {
            $module_custom_classes.=" sticky_filter_bar_tablet";
        }
        if ($sticky_filter_bar_phone  === "on") {
            $module_custom_classes.=" sticky_filter_bar_phone";
        }

		$render_html = sprintf(
            '<div class="%2$s" data-config="%3$s">
                %1$s
            </div>
           ',
            $filterable_gallery_html,
            $module_custom_classes,
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
				'classnamesFunction'  => [ FilterableGallery::class, 'module_classnames' ],
				'stylesComponent'     => [ FilterableGallery::class, 'module_styles' ],
				'scriptDataComponent' => [ FilterableGallery::class, 'module_script_data' ],
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
