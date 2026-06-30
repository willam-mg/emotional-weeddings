<?php
/**
 * StarRating::render_callback()
 *
 * @package DIPI\Modules\StarRating
 * @since ??
 */

namespace DIPI\Modules\StarRating;

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
        
		$thisProps = [];
        foreach ($attrs as $key => $value) {
            $thisProps[$key] = static::getPropValue($attrs, $key);
        }

		$rating_scale = $thisProps['rating_scale'];
        $rating = $thisProps['rating'];
        $display_type = $thisProps['display_type'];
        $show_rating_number = $thisProps['show_rating_number'];

        $display_type_class = '';
        if ($display_type == 'block') {
            $display_type_class = ' display-type-block';
        } else {
            $display_type_class = ' display-type-inline';
        }

        $stars = '';
        $star_rating_class = '';
        $fraction = explode('.', $rating);
        for ($i = 1; $i <= $rating_scale; $i++) {
            if ($i <= $fraction[0]) {
                $star_rating_class = 'dipi-star-full';
            } else if ($i == (int)$fraction[0] + 1 && isset($fraction[1]) && $fraction[1] != '' && $fraction[1] != 0) {
                $star_rating_class = 'dipi-star-full dipi-star-' . $fraction[1];
            } else {
                $star_rating_class = 'dipi-star-empty';
            }
            $stars .= '<span class="' . $star_rating_class . '">☆</span>';
        }
        $title = $thisProps['title'];
        $title_tag = $thisProps['title_tag'];
        $rating_number = '';
        if ($show_rating_number === 'on') {
            $rating_number = '<span class="dipi-star-rating-number">(' . $rating . '/' . $rating_scale . ')</span>';
        }
        $title_html = "";
        if ($title) {
            $title_html = sprintf('<%1$s class="dipi-title">%2$s</%1$s>',
                $title_tag,
                $title
            );
        }
        $render_html = sprintf(
            '<div class="dipi-wrapper%1$s">
                %2$s
                <div class="dipi-star-rating">
                    %3$s
                    %4$s
                </div>
                <p class="dipi-description">%5$s</p>
            </div>',
            $display_type_class,
            $title_html,
            $stars,
            $rating_number,
            $thisProps['description'] #5
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
				'classnamesFunction'  => [ StarRating::class, 'module_classnames' ],
				'stylesComponent'     => [ StarRating::class, 'module_styles' ],
				'scriptDataComponent' => [ StarRating::class, 'module_script_data' ],
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
