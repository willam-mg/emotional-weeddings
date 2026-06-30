<?php
/**
 * TypingText::render_callback()
 *
 * @package DIPI\Modules\TypingText
 * @since ??
 */

namespace DIPI\Modules\TypingText;

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
		$typing_text = static::getPropValue($attrs, 'typing_text');
        $typing_text = explode('|', $typing_text);
        $typing_text = wp_json_encode($typing_text);
        $typing_prefix = static::getPropValue($attrs, 'typing_prefix');
        $typing_suffix = static::getPropValue($attrs, 'typing_suffix');
        $textWrapperTag = static::getPropValue($attrs, 'text_wrapper_tag');
        $typing_loop = ('off' !== static::getPropValue($attrs, 'typing_loop')) ? esc_attr('true') : esc_attr('false');
        $typing_delay = static::getPropValue($attrs, 'typing_delay');
        $typing_speed = static::getPropValue($attrs, 'typing_speed');
        $typing_backspeed = static::getPropValue($attrs, 'typing_backspeed');
        $typing_backdelay = static::getPropValue($attrs, 'typing_backdelay');
        $typing_prefix_padding = static::getPropValue($attrs, 'typing_prefix_padding');
        $typing_padding = static::getPropValue($attrs, 'typing_padding');
        $typing_suffix_padding = static::getPropValue($attrs, 'typing_suffix_padding');
        $show_cursor = ('off' !== static::getPropValue($attrs, 'show_cursor')) ? esc_attr('true') : esc_attr('false');

        if ('off' !== static::getPropValue($attrs, 'show_cursor')) {
            $cursor_char = ('' != static::getPropValue($attrs, 'cursor_char')) ? static::getPropValue($attrs, 'cursor_char') : '|';
        } else {
            $cursor_char = '';
        }

        $inViewport = static::getPropValue($attrs, 'anim_start') == 'inViewport' ? 'on':'off';
        $viewport_offset = static::getPropValue($attrs, 'anim_start_viewport_offset');
        $data_options = sprintf(
            'data-dipi-loop="%1$s"
            data-dipi-speed="%2$s"
            data-dipi-backspeed="%3$s"
            data-dipi-backdelay="%4$s"
            data-dipi-show-cursor="%5$s"
            data-dipi-cursor-char="%6$s"
            data-dipi-typing-strings="%7$s"
            data-dipi-typing-strings="%7$s"
            data-dipi-typing-inviewport="%8$s"
            data-dipi-typing-offset="%9$s"
            data-dipi-delay="%10$s"
            ',
            esc_attr($typing_loop),
            esc_attr($typing_speed),
            esc_attr($typing_backspeed),
            esc_attr($typing_backdelay),
            esc_attr($show_cursor), #5
            esc_attr($cursor_char),
            esc_attr(htmlspecialchars($typing_text, ENT_QUOTES, 'UTF-8')),
            esc_attr($inViewport),
            esc_attr($viewport_offset),
            esc_attr($typing_delay) #10
        );

        $render_html = sprintf(
            '<%4$s class="dipi-typing">
                <span class="dipi-typing-text-prefix">%1$s</span>
                <span class="dipi-typing-wrap">
                    <span class="dipi-typing-text" %2$s></span>
                </span>
                <span class="dipi-typing-text-suffix">%3$s</span>
            </%4$s>',
            esc_html($typing_prefix),
            et_core_esc_previously($data_options),
            esc_html($typing_suffix),
            esc_html($textWrapperTag)
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
				'classnamesFunction'  => [ TypingText::class, 'module_classnames' ],
				'stylesComponent'     => [ TypingText::class, 'module_styles' ],
				'scriptDataComponent' => [ TypingText::class, 'module_script_data' ],
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
