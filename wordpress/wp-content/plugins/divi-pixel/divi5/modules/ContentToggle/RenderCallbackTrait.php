<?php
/**
 * ContentToggle::render_callback()
 *
 * @package DIPI\Modules\ContentToggle
 * @since ??
 */

namespace DIPI\Modules\ContentToggle;

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
    public static function dipi_content_toggle($attrs)
    {
        return [
            "before_image"     => $attrs["before_image"]['innerContent']['desktop']['value']['src'],
            "before_image_alt" => $attrs["before_image_alt"]['innerContent']['desktop']['value'],
            "before_label"     => $attrs["before_label"]['innerContent']['desktop']['value'],
            "after_image"      => $attrs["after_image"]['innerContent']['desktop']['value']['src'],
            "after_image_alt"  => $attrs["after_image_alt"]['innerContent']['desktop']['value'],
            "after_label"      => $attrs["after_label"]['innerContent']['desktop']['value'],
            "offset"           => $attrs["offset"]['innerContent']['desktop']['value'],
            "direction"        => $attrs["direction"]['innerContent']['desktop']['value'],
            "move_slider"   => $attrs["move_slider"]['innerContent']['desktop']['value']
        ];
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
		static::$props = array_map(function($attr) {
			return is_array($attr) && array_key_exists('innerContent', $attr) ? $attr['innerContent']['desktop']['value'] : $attr;
		}, $attrs);
		$first_text           = static::$props['first_text'];
		$second_text           = static::$props['second_text'];
		$first_divi_library           = static::$props['first_divi_library'];
		$first_divi_library_shortcode = static::render_library_layout($first_divi_library);
		$second_divi_library           = static::$props['second_divi_library'];
		$second_divi_library_shortcode = static::render_library_layout($second_divi_library);
		$first_content_animation = static::$props['first_content_animation'];
		$first_content_delay = static::$props['first_content_delay'];
		$first_content_speed = static::$props['first_content_speed'];
		$second_content_animation = static::$props['second_content_animation'];
		$second_content_delay = static::$props['second_content_delay'];
		$second_content_speed = static::$props['second_content_speed'];
		$first_disable_browser_lazyload = static::$props['first_disable_browser_lazyload'] === 'on' ? 'disable_browser_lazyload' : '';
		$second_disable_browser_lazyload = static::$props['second_disable_browser_lazyload'] === 'on' ? 'disable_browser_lazyload' : '';
		$first_disable_wprocket_lazyload = static::$props['first_disable_wprocket_lazyload'] === 'on' ? 'disable_wprocket_lazyload' : '';;
		$second_disable_wprocket_lazyload = static::$props['second_disable_wprocket_lazyload'] === 'on' ? 'disable_wprocket_lazyload' : '';;

		$toggle_alignment = static::$props['toggle_alignment'];
		$toggle_alignment = $toggle_alignment === 'left' ? 'flex-start' :
			($toggle_alignment === 'right' ? 'flex-end' : 'center');
		$first_level = $attrs['first_text']['decoration']['font']['font']['desktop']['value']['headingLevel'] ?? 'h5';
		$second_level = $attrs['second_text']['decoration']['font']['font']['desktop']['value']['headingLevel'] ?? 'h5';




        //FIXME: This is most likely wrong and we should do it the right way but it's what's needed to make the proof of concept work
        $config = [
            'f_t_selector' => $attrs["first_toggle_selector"]['innerContent']['desktop']['value'] ?? '',
            'f_t_offset' => $attrs["first_scroll_toggle_offset"]['innerContent']['desktop']['value'] ?? '',
            'f_t_offset_tablet' => $attrs["first_scroll_toggle_offset"]['innerContent']['tablet']['value'] ?? '',
            'f_t_offset_phone' => $attrs["first_scroll_toggle_offset"]['innerContent']['phone']['value'] ?? '',
            's_t_selector' => $attrs["second_toggle_selector"]['innerContent']['desktop']['value'] ?? '',
            's_t_offset' => $attrs["second_scroll_toggle_offset"]['innerContent']['desktop']['value'] ?? '',
            's_t_offset_tablet' => $attrs["second_scroll_toggle_offset"]['innerContent']['tablet']['value'] ?? '',
            's_t_offset_phone' => $attrs["second_scroll_toggle_offset"]['innerContent']['phone']['value'] ?? '',
        ];


		$render_html = sprintf('
			<div class="dipi_content_toggle dipi-content-toggle-container"
                data-config="%13$s">
				<div class="dipi-content-toggle__button-container">
					<div class="dipi-content-toggle__text dipi-content-toggle__first-text">
						<%11$s>%1$s</%11$s>
					</div>
					<div class="dipi-content-toggle__button">
						<input class="dipi-content-toggle__switch" type="checkbox">
						<div class="dipi-content-toggle__slider"></div>
					</div>
					<div class="dipi-content-toggle__text dipi-content-toggle_second-text">
						<%12$s>%2$s</%12$s>
					</div>
				</div>
				<div class="dipi-content-toggle__content dipi-content-toggle__first-layout animated %5$s %7$s %9$s">
					%3$s
				</div>
				<div class="dipi-content-toggle__content dipi-content-toggle__second-layout animated %6$s %8$s $10$s">
					%4$s
				</div>
			</div>
			',
			$first_text,
			$second_text,
			$first_divi_library_shortcode,
			$second_divi_library_shortcode,
			$first_content_animation, #5
			$second_content_animation,
			$first_disable_browser_lazyload,
			$second_disable_browser_lazyload,
			$first_disable_wprocket_lazyload,
			$second_disable_wprocket_lazyload, #10
			et_pb_process_header_level($first_level, 'h5'),
			et_pb_process_header_level($second_level, 'h5'),
            esc_attr(htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8'))
		);
		
		$parent = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );

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
				'classnamesFunction'  => [ ContentToggle::class, 'module_classnames' ],
				'stylesComponent'     => [ ContentToggle::class, 'module_styles' ],
				'scriptDataComponent' => [ ContentToggle::class, 'module_script_data' ],
				'parentAttrs'         => $parent->attrs ?? [],
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
