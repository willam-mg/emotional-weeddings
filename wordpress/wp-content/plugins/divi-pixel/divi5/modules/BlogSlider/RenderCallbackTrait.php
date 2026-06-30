<?php
/**
 * BlogSlider::render_callback()
 *
 * @package DIPI\Modules\BlogSlider
 * @since ??
 */

namespace DIPI\Modules\BlogSlider;

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

    public static function get_no_results_template($heading_tag = 'h1')
    {
        global $et_no_results_heading_tag;
        $et_no_results_heading_tag = $heading_tag;
        ob_start();

        if (et_is_builder_plugin_active()) {
            include ET_BUILDER_PLUGIN_DIR . 'includes/no-results.php';
        } else {
            get_template_part('includes/no-results', 'index');
        }

        return ob_get_clean();
    }

	public static function get_post_types_and_taxonomies() {
        $registered_post_types = et_get_registered_post_type_options( false, false );
        $excluded_post_types = array( 'Media', 'Taxonomies', 'Popup Maker');
        $dipi_taxonomies_object = get_taxonomies(
            array(),
            'objects'
        );
        $dipi_taxonomies_object = array_filter($dipi_taxonomies_object, function($object){
            $exclude_taxonomies = array(
                'dipi_media_category',
                'layout_pack',
                'layout_type',
                'layout_category',
                'layout_tag',
                'scope',
                'module_width',
                'nav_menu',
                'link_category',
                'post_format',
                'wp_template_part_area',
                'wp_theme'
            );
            return !in_array($object->name, $exclude_taxonomies);
        });
        $dipi_taxonomies_options = array_map(function($object) {
            return array($object->name => $object->label);
        }, $dipi_taxonomies_object);
        $dipi_taxonomies_options = array_merge($dipi_taxonomies_options, 
            array('post_category'=> (object)array('post_category'=>'Category')));
        $post_types = array_diff( $registered_post_types, $excluded_post_types);

        return json_encode([
            'post_types_options' => $post_types,
            'taxonomies_options' => $dipi_taxonomies_options
        ]);
    }

	public static function filter_invalid_term_ids($term_ids, $taxonomy)
	{
		$valid_term_ids = array();

		foreach ($term_ids as $term_id) {
			$term_id = intval($term_id);
			$term = term_exists($term_id, $taxonomy);
			if (!empty($term)) {
				$valid_term_ids[] = $term_id;
			}
		}

		return $valid_term_ids;
	}

	protected static function filter_meta_categories($categories, $post_id = 0, $taxonomy = 'category')
	{
		$raw_term_ids = is_array($categories) ? $categories : explode(',', $categories);

		if (in_array('all', $raw_term_ids, true)) {
			// If "All Categories" is selected return an empty array so it works for all terms
			// even ones created after the module was last updated.
			return array();
		}

		$term_ids = array();

		foreach ($raw_term_ids as $value) {
			if ('current' === $value) {
				if ($post_id > 0) {
					$post_terms = wp_get_object_terms($post_id, $taxonomy);

					if (is_wp_error($post_terms)) {
						continue;
					}

					$term_ids = array_merge($term_ids, wp_list_pluck($post_terms, 'term_id'));
				} else {
					$is_category = 'category' === $taxonomy && is_category();
					$is_tag = !$is_category && 'post_tag' === $taxonomy && is_tag();
					$is_tax = !$is_category && !$is_tag && is_tax($taxonomy);

					if ($is_category || $is_tag || $is_tax) {
						$term_ids[] = get_queried_object()->term_id;
					}
				}

				continue;
			}
			$term_ids[] = (int) $value;
		}

		$term_ids = static::filter_invalid_term_ids(array_unique(array_filter($term_ids)), $taxonomy);

		return $term_ids;
	}

	protected static function filter_include_categories($include_categories, $post_id = 0, $taxonomy = 'category')
	{
		$categories = array();

		if (!empty($include_categories)) {
			// wp_doing_ajax() covers VB usage when fetching computed values where we always have a post.
			if (is_singular() || wp_doing_ajax()) {
				$post_id = $post_id > 0 ? $post_id : 0;//static::get_current_post_id_reverse();
				$categories = static::filter_meta_categories($include_categories, $post_id, $taxonomy);
			} else {
				$categories = static::filter_meta_categories($include_categories, 0, $taxonomy);
			}
		}

		return $categories;
	}

	private static function get_attachment_image($attachment_id, $image_size, $fallback_url)
    {
        $attachment = wp_get_attachment_image_src($attachment_id, $image_size);
        if ($attachment) {
            return $attachment[0];
        } else {
            return $fallback_url;
        }
    }

	public static function get_blog_posts($args = [], $conditional_tags = [], $current_page = [])
    {
        $defaults = [
            'posts_number' => '10',
            'select_post_type' => 'post',
            'select_custom_tax' => 'category',
            'blog_slider_post' => '',
            'image_animation' => '',
            'show_thumbnail' => '',
            'card_clickable' => '',
            'image_clickable' => '',
            'show_author' => '',
            'show_date' => '',
            'show_categories' => '',
            'show_comments' => '',
            'show_excerpt' => '',
            'excerpt_length' => '',
            'expert_as_raw_html' => 'off',
            'handle_shortcode_with_rawhtml' => 'show',
            'handle_shortcode_without_rawhtml' => 'show',
            'header_level' => '',
            'show_more' => '',
            'button_use_icon' => '',
            'button_icon' => '',
            'button_use_icon' => '',
            'image_size' => '',
            'image_size_tablet' => '',
            'image_size_phone' => '',
            'image_size_last_edited' => '',
            'excluded_posts' => '',
            'post_status' => 'publish',
            'offset_number' => 0,
            'url_new_window' => 'off'
            // 'exclude_current_post' => 'off',
        ];

        $args = wp_parse_args($args, $defaults);

        $width = (int) apply_filters('et_pb_blog_image_width', 1080);
        $height = (int) apply_filters('et_pb_blog_image_height', 675);
        $image_size_last_edited = et_pb_get_responsive_status($args['image_size_last_edited']);
        $args['image_size'] = ($args['image_size']) ? $args['image_size'] : [$width, $height];
        $args['image_size_tablet'] = ($image_size_last_edited && isset($args['image_size_tablet']) && $args['image_size_tablet'] !== '') ? $args['image_size_tablet'] : $args['image_size'];
        $args['image_size_phone'] = ($image_size_last_edited && isset($args['image_size_phone']) && $args['image_size_phone'] !== '') ? $args['image_size_phone'] : $args['image_size_tablet'];

        $processed_header_level = et_pb_process_header_level($args['header_level'], 'h2');
        $processed_header_level = esc_html($processed_header_level);

        $query_args = [
            'posts_per_page' => intval($args['posts_number']),
            'post_status' => 'publish',
            'post_type' => 'post',
        ];

        $is_single = et_fb_conditional_tag('is_single', $conditional_tags);
        $post_id = isset($current_page['id']) ? (int) $current_page['id'] : 0;

        $select_post_type = $args['select_post_type'];
        $select_custom_tax = $args['select_custom_tax'];
        if(isset($args['blog_slider_post']["include_term_ids_of_$select_custom_tax"]))
            $include_term_ids = $args['blog_slider_post']["include_term_ids_of_$select_custom_tax"];
        else
            $include_term_ids = [];
        if ($select_custom_tax === 'category') {
            $query_args['cat'] = implode(',', static::filter_include_categories($include_term_ids, $post_id, $select_custom_tax));
        } else {
            $include_term_ids = static::filter_include_categories($include_term_ids, $post_id, $select_custom_tax);
            if (!empty($include_term_ids)) {
                $tax_query = [
                    [
                        'taxonomy' => $select_custom_tax,
                        'field'    => 'id',
                        'terms'    => $include_term_ids,
                    ]
                ];
                $query_args['tax_query'] = $tax_query;
            }
        }
        $query_args['post_type'] = $select_post_type;        
        $query_args['post_status'] = $args['post_status'];
        $image_animation_class = 'dipi-' . $args['image_animation'];
        switch ($args['orderby']) {
            case 'date_asc':
                $query_args['orderby'] = 'date';
                $query_args['order'] = 'ASC';
                break;
            case 'title_asc':
                $query_args['orderby'] = 'title';
                $query_args['order'] = 'ASC';
                break;
            case 'title_desc':
                $query_args['orderby'] = 'title';
                $query_args['order'] = 'DESC';
                break;
            case 'rand':
                $query_args['orderby'] = 'rand';
                break;
            default:
                $query_args['orderby'] = 'date';
                $query_args['order'] = 'DESC';
                break;
        }

        $excluded_posts = [];
        if ($args['excluded_posts'] !== '') {
            $excluded_posts = explode(',', $args['excluded_posts']);
        }

        // if($args['exclude_current_post'] === 'on' && et_fb_conditional_tag( 'is_single', $conditional_tags )) {
        if (et_fb_conditional_tag('is_single', $conditional_tags)) {
            $post_id = isset($current_page['id']) ? (int) $current_page['id'] : get_the_ID();
            if ($post_id) {
                $excluded_posts[] = $post_id;
            }
        }

        if (!empty($excluded_posts)) {
            $query_args['post__not_in'] = $excluded_posts;
        }
        if (!empty($excluded_posts)) {
            $query_args['post__not_in'] = $excluded_posts;
        }

        $query_args['offset'] = $args['offset_number'];

        // Get query
        $q = new \WP_Query($query_args);

        ob_start();
        if ($q->have_posts()) {
            while ($q->have_posts()) {
                $q->the_post();
                include dirname(__FILE__) . '/templates/dipi-blog-post.php';
            }
        }

        if (!$posts = ob_get_clean()) {
            $posts = static::get_no_results_template(et_core_esc_previously($processed_header_level));
        }

        return $posts;
    }

	/**
	 * When `columns.innerContent` only has desktop (legacy saves before module.json defined swiper defaults),
	 * Divi's responsive fallback would use desktop for every device. Inject factory tablet/phone values (2 / 1).
	 *
	 * @param array $attrs Block attributes.
	 * @return array
	 */
	private static function ensure_blog_slider_responsive_columns( array $attrs ) {
		if ( ! isset( $attrs['columns']['innerContent'] ) || ! is_array( $attrs['columns']['innerContent'] ) ) {
			return $attrs;
		}
		$inner = $attrs['columns']['innerContent'];
		if ( 1 !== count( $inner ) || ! isset( $inner['desktop']['value'] ) ) {
			return $attrs;
		}
		$attrs['columns']['innerContent'] = array_merge(
			[
				'tablet' => [ 'value' => '2' ],
				'phone'  => [ 'value' => '1' ],
			],
			$inner
		);
		return $attrs;
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
		$attrs = static::ensure_blog_slider_responsive_columns( $attrs );
        $order_number = $block->parsed_block['orderIndex'];
        $config = [
            "posts_number" => static::getPropValue($attrs, 'posts_number'),
            "select_post_type" => static::getPropValue($attrs, 'select_post_type'),
            "select_custom_tax" => static::getPropValue($attrs, 'select_custom_tax'),
            "blog_slider_post" => static::getPropValue($attrs, 'blog_slider_post'),
            "show_thumbnail" => static::getPropValue($attrs, 'show_thumbnail'),
            "card_clickable" => static::getPropValue($attrs, 'card_clickable'),
            "url_new_window" => static::getPropValue($attrs, 'url_new_window'),
            "image_clickable" => static::getPropValue($attrs, 'image_clickable'),
            "show_author" => static::getPropValue($attrs, 'show_author'),
            "show_author_prefix" => static::getPropValue($attrs, 'show_author_prefix'),
            "author_prefix" => empty(static::getPropValue($attrs, 'author_prefix'))? __('By', 'dipi-divi-pixel'): static::getPropValue($attrs, 'author_prefix'),
            "show_date" => static::getPropValue($attrs, 'show_date'),
            "show_categories" => static::getPropValue($attrs, 'show_categories'),
            "show_comments" => static::getPropValue($attrs, 'show_comments'),
            "show_excerpt" => static::getPropValue($attrs, 'show_excerpt'),
            "excerpt_length" => static::getPropValue($attrs, 'excerpt_length'),
            "expert_as_raw_html" => static::getPropValue($attrs, 'expert_as_raw_html'),
            "handle_shortcode_with_rawhtml" => static::getPropValue($attrs, 'handle_shortcode_with_rawhtml'),
            "handle_shortcode_without_rawhtml" => static::getPropValue($attrs, 'handle_shortcode_without_rawhtml'),
            "show_more" => static::getPropValue($attrs, 'show_more'),
            "show_more_text" => static::getPropValue($attrs, 'show_more_text'),
            "header_level" => isset($attrs['header_font']['decoraton']['font']['font']['desktop']['value']['headingLevel']) ? $attrs['header_font']['decoraton']['font']['font']['desktop']['value']['headingLevel'] : 'h2',
            "image_animation" => static::getPropValue($attrs, 'image_animation'),
            "button_use_icon" => static::getPropValue($attrs, 'button_use_icon'),
            "button_icon" => static::getPropValue($attrs, 'button_icon'),
            "image_size_last_edited" => static::getPropValue($attrs, 'image_size_last_edited'),
            "image_size" => static::getPropValue($attrs, 'image_size'),
            "image_size_tablet" => static::getPropValue($attrs, 'image_size_tablet'),
            "image_size_phone" => static::getPropValue($attrs, 'image_size_phone'),
            "excluded_posts" => static::getPropValue($attrs, 'excluded_posts'),
            "offset_number" => static::getPropValue($attrs, 'offset_number'),
            "orderby" => static::getPropValue($attrs, 'orderby'),
        ];

        $blog_content = static::get_blog_posts($config);
        $render_html = static::render_swiper(
            $attrs,
            $blog_content,
            $order_number,
            "dipi-blog-slider-main",
            "dipi-blog-slider-wrapper",
            "dipi-blog-post",
            '',
            ['speed']
        );

        $parent       = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );
		$parent_attrs = $parent->attrs ?? [];

		return Module::render(
			[
				// FE only.
				'orderIndex'          => $block->parsed_block['orderIndex'],
				'storeInstance'       => $block->parsed_block['storeInstance'],

				// VB equivalent (uses normalized columns when legacy saves lacked tablet/phone).
				'attrs'               => $attrs,
				'elements'            => $elements,
				'id'                  => $block->parsed_block['id'],
				'name'                => $block->block_type->name,
				'moduleCategory'      => $block->block_type->category,
				'classnamesFunction'  => [ BlogSlider::class, 'module_classnames' ],
				'stylesComponent'     => [ BlogSlider::class, 'module_styles' ],
				'scriptDataComponent' => [ BlogSlider::class, 'module_script_data' ],
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
