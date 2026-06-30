<?php
/**
 * ImageShowcase::render_callback()
 *
 * @package DIPI\Modules\ImageShowcase
 * @since ??
 */

namespace DIPI\Modules\ImageShowcase;

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

	private static function get_mockup_url($mockup_name, $size = ''){
        $size_part = (!empty($size)) ? '-' . $size : ''; 
        $mockups_path = __DIR__ . '/' . sprintf('/mockups/%1$s%2$s.png', $mockup_name ,$size_part);
        if(file_exists($mockups_path)) { // return whatever requested size if exists.
            return plugins_url(sprintf('/mockups/%1$s%2$s.png', $mockup_name ,$size_part), __FILE__);
        }
        // at this point requested mockup size not exist
        if($size === 's'){ // If small size is requested try to get medium ( s -> m)
            return static::get_mockup_url($mockup_name, 'm');
        }
        if($size === 'm') { // If medium size is requested try to get small, if small not exist get Larte (m -> s -> l)
            $mockups_path = __DIR__ . '/' . sprintf('/mockups/%1$s%2$s.png', $mockup_name ,'-s');
            if(file_exists($mockups_path)) {
                return plugins_url(sprintf('/mockups/%1$s%2$s.png', $mockup_name ,'-s'), __FILE__);
            } else {
                return static::get_mockup_url($mockup_name);
            }
        }
        return false;
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
		$children_ids = $block->parsed_block['innerBlocks'] ? array_map(
			function( $inner_block ) {
				return $inner_block['id'];
			},
			$block->parsed_block['innerBlocks']
		) : [];

		$order_number = $block->parsed_block['orderIndex'];

        $parent       = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );
		$parent_attrs = $parent->attrs ?? [];

        $speed = static::getPropValue($attrs, 'speed');
        $loop = static::getPropValue($attrs, 'loop');
        $autoplay = static::getPropValue($attrs, 'autoplay');
        $autoplay_speed = static::getPropValue($attrs, 'autoplay_speed');
        $pause_on_hover = static::getPropValue($attrs, 'pause_on_hover');
        $enable_vertical_scroll = static::getPropValue($attrs, 'enable_vertical_scroll') ? static::getPropValue($attrs, 'enable_vertical_scroll') : 'off';
        $effect = static::getPropValue($attrs, 'effect');
        $rotate = static::getPropValue($attrs, 'rotate');
		$mockup_size = static::getPropValue($attrs, 'mockup_size');

        $options['data-columnsdesktop'] = esc_attr(1);
        $options['data-loop'] = esc_attr($loop);
        $options['data-speed'] = esc_attr($speed);
        $options['data-navigation'] = esc_attr('false');
        $options['data-pagination'] = esc_attr('false');
        $options['data-autoplay'] = esc_attr($autoplay);
        $options['data-autoplayspeed'] = esc_attr($autoplay_speed);
        $options['data-pauseonhover'] = esc_attr($pause_on_hover);
        $options['data-effect'] = esc_attr($effect);
        $options['data-rotate'] = esc_attr($rotate);
        $options['data-enableVerticalScroll'] = esc_attr($enable_vertical_scroll);

        $options = implode(
            " ",
            array_map(
                function ($k, $v) {
                    return "{$k}='{$v}'";
                },
                array_keys($options),
                $options
            )
        );

        $mockup_name = str_replace(' ', '-', strtolower(static::getPropValue($attrs, 'mockup')));        
        $mockup_url = static::get_mockup_url( $mockup_name );
        $mockup_url_m = static::get_mockup_url( $mockup_name, 'm' );
        $mockup_url_s = static::get_mockup_url( $mockup_name, 's' );
        
        if($mockup_size === 'Small'){
            $mockup_url = $mockup_url_m = $mockup_url_s;
        }
        if($mockup_size === 'Medium'){
            $mockup_url = $mockup_url_m;
        }
        
        $mockup_img = sprintf(
			'<picture>
				<source media="(max-width: 768px)" srcset="%4$s">
				<source media="(max-width: 980px)" srcset="%3$s">
				<img class="dipi-mockup" src="%1$s" alt="%2$s">
			</picture>',
			$mockup_url,
			$mockup_name,
			$mockup_url_m,
			$mockup_url_s
		);

        $extra_class = ($enable_vertical_scroll === 'on') ? 'dipi-mockup-vs' : '';

        $render_html = sprintf(
            '<div class="dipi-mockup %5$s" data-mockup="%4$s" data-order-number="%6$s">
                <div class="dipi-mockup-screen %4$s" %2$s>
                    <div class="dipi-image-showcase-wrapper swiper-wrapper">
                        %1$s
                    </div>
                </div>
                %3$s
            </div>',
            $content,
            $options, 
            $mockup_img,
            $mockup_name,
            $extra_class,
            $order_number
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
				'classnamesFunction'  => [ ImageShowcase::class, 'module_classnames' ],
				'stylesComponent'     => [ ImageShowcase::class, 'module_styles' ],
				'scriptDataComponent' => [ ImageShowcase::class, 'module_script_data' ],
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
