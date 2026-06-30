<?php
/**
 * FAQ::render_callback()
 *
 * @package DIPI\Modules\FAQ
 * @since ??
 */

namespace DIPI\Modules\FAQ;

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

	public static function get_output($args = [])
    {
        $defaults = [
            'show_all' => 'off',
            'show_uncategorized' => 'off',
            'faq_categories' => [],
            'excluded_post_ids' => '',
            'included_post_ids' => '',
            'faq_order_by' => 'title',
            'faq_order' => 'ASC',
            'output_json' => 'on',
            'output_html' => 'on',
        ];

        $args = wp_parse_args($args, $defaults);

        // Build the args for WP_Query
        $query_args = [
            'post_type' => 'dipi_faq',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'fields' => 'ids',
        ];

        if ('off' === $args['show_all']) {
            $query_args['tax_query'] = [
                'relation' => 'OR',
            ];

            $query_args['tax_query'][] = [
                'taxonomy' => 'dipi_faq_category',
                'field' => 'term_id',
                'terms' => $args['faq_categories'],
            ];

            if ('off' !== $args['show_uncategorized']) {
                $get_terms = get_terms('dipi_faq_category', ['fields' => 'ids']);
                if(is_array($get_terms) && !empty($get_terms)){
                    $query_args['tax_query'][] = [
                        'taxonomy' => 'dipi_faq_category',
                        'field' => 'term_id',
                        'operator' => 'NOT IN',
                        'terms' => get_terms('dipi_faq_category', ['fields' => 'ids']),
                    ];
                } else {
                    $query_args['tax_query'] = null;
                }
            }
        }

        if ('' !== $args['excluded_post_ids']) {
            $query_args['post__not_in'] = explode(",", $args['excluded_post_ids']);
        }

        $query = new \WP_Query($query_args);
        $all_ids = array_merge($query->posts, explode(",", $args['included_post_ids']));
        $query = new \WP_Query([
            'post_type' => 'dipi_faq',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'post__in' => $all_ids,
            'orderby' => $args['faq_order_by'],
            'order' => $args['faq_order'],
        ]);

        $schema = [
            "@context" => "https://schema.org",
            "@type" => "FAQPage",
            "@id" => get_permalink(),
            "mainEntity" => [],
        ];
        $html = "";

        $entry_number = 0;
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                $post = get_post();
                $id = $post->ID;
                $title = $post->post_title;

                // manually setting divi_off_canvas_rendering apparently fixed the issue that canvases were not rendered at all on pages where FAQ was used
                // $content = $post->post_content;
                $GLOBALS['divi_off_canvas_rendering'] = true;
                $content = apply_filters("the_content", $post->post_content);
                unset($GLOBALS['divi_off_canvas_rendering']);
                
                if (!empty($post->post_excerpt)) {
                    $GLOBALS['divi_off_canvas_rendering'] = true;
                    $text = apply_filters("the_excerpt", $post->post_excerpt); //Not working in D5
                    unset($GLOBALS['divi_off_canvas_rendering']);
                    // $text = $post->post_excerpt;
                } else {
                    $text = $content;
                }

                $schema['mainEntity'][] = [
                    "@type" => "Question",
                    "name" => $title,
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => $text,
                    ],
                ];

                $icons = sprintf('<i class="dipi-faq-icon-open">%1$s</i><i class="dipi-faq-icon-closed">%2$s</i>', 
                    esc_attr(Utils::process_font_icon($args['icon_open'])),
                    esc_attr(Utils::process_font_icon($args['icon_closed']))
                );
                                    
                $html .= sprintf(
                    '<div class="dipi-faq-entry" data-entry-number="%4$s">
                        <div class="dipi-faq-title">%5$s<%6$s>%2$s</%6$s></div>
                        <div class="dipi-faq-content">%3$s</div>
                    </div>',
                    $id,
                    $title,
                    $content,
                    $entry_number,
                    $icons,
                    $args['heading_tag']
                );
                $entry_number++;
            }
            wp_reset_postdata();
        }

        $schema_script = '';
        if('on' === $args['output_json']){
            $schema_script = sprintf('<script type="application/ld+json">%1$s</script>', json_encode($schema));
        }

        $html_code = '';
        if('on' === $args['output_html']){
            $html_code = $html;
        }

        $output = sprintf(
            '%1$s%2$s',
            $schema_script,
            $html_code
        );

        if('' === $output){
            $output = esc_html__('Both, ld+json and HTML are disabled. This module has no output.', 'dipi-faq');
        }

        return $output;
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

        $thisProps = [];
        foreach ($attrs as $key => $value) {
            $thisProps[$key] = isset($attrs[$key]['innerContent']) ? static::getPropValue($attrs, $key) : '';
        }

        $config = [
            "layout" => $thisProps['faq_layout'],
        ];

        if ('on' === $thisProps['accordion_open_first']) {
            $config["open_first"] = true;
        }

        if ('on' === $thisProps['accordion_close_all']) {
            $config["close_all"] = true;
        }

        $accordion_not_closable = '';
        if('accordion' === $thisProps['faq_layout'] && 'on' !== $thisProps['accordion_close_all']){
            $accordion_not_closable = "dipi-faq-accordion-not-closable";
        }

		$render_html = sprintf(
            '<div class="dipi-faq-wrapper dipi-faq-%4$s loading %3$s" style="display: none;" data-config="%2$s">
				%1$s
			</div>',
            static::get_output($thisProps),
            esc_attr(htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8')),
            $accordion_not_closable,
            $thisProps['faq_layout']
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
				'classnamesFunction'  => [ FAQ::class, 'module_classnames' ],
				'stylesComponent'     => [ FAQ::class, 'module_styles' ],
				'scriptDataComponent' => [ FAQ::class, 'module_script_data' ],
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
