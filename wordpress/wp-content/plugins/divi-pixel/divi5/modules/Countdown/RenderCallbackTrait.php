<?php
/**
 * Countdown::render_callback()
 *
 * @package DIPI\Modules\Countdown
 * @since ??
 */

namespace DIPI\Modules\Countdown;

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

	private static function dateNotConfigured()
    {
        return sprintf('<div>%1$s</div>', esc_html__('No Date Configured', 'dipi-divi-pixel'));
    }

    private static function isValidDate($date)
    {
        return preg_match("/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/", trim($date));
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
			"style" => static::getPropValue($attrs, 'style'),
            "finish_countdown" => static::getPropValue($attrs, 'finish_countdown'),
            "label_weeks" => static::getPropValue($attrs, 'label_weeks'),
            "label_days" => static::getPropValue($attrs, 'label_days'),
            "label_hours" => static::getPropValue($attrs, 'label_hours'),
            "label_minutes" => static::getPropValue($attrs, 'label_minutes'),
            "label_seconds" => static::getPropValue($attrs, 'label_seconds'),
        ];

		$render_html = "";
		$date_type = static::getPropValue($attrs, 'date_type');
		$date_time_picker = static::getPropValue($attrs, 'date_time_picker');
		$date_time_offset = static::getPropValue($attrs, 'date_time_offset');

		if ($date_type === 'text') {
            $date_time_text = static::getPropValue($attrs, 'date_time_text');
            if (!static::isValidDate($date_time_text)) {
                $render_html = static::dateNotConfigured();
            } else {
                $config["date"] = trim($date_time_text);
            }
        } else if ($date_type === 'picker') {
            if (!$date_time_picker || '' === $date_time_picker) {
                $render_html = static::dateNotConfigured();
            } else {
                $config["date"] = $date_time_picker;
            }
        } else if ($date_type === 'current_time') {
            $config["date"] = "now";
            $config["offset"] = isset($date_time_offset) && '' !== $date_time_offset ? $date_time_offset : 0;
            if ('on' === static::getPropValue($attrs, 'use_cookie')) {
                $config["use_cookie"] = true;
                $config["cookie_id"] = static::getPropValue($attrs, 'cookie_id');
            }
        } else {
            $render_html = static::dateNotConfigured();
        }

        if ('custom_format' === $config["style"]) {
            $config["custom_format"] = static::getPropValue($attrs, "custom_format");
        }

        if ('script' === $config["finish_countdown"]) {
            $config["script"] = static::getPropValue($attrs, "script");
        }

        if ('html' === $config["finish_countdown"]) {
            $config["html"] = static::getPropValue($attrs, "html");
        }

        if ('forward' === $config["finish_countdown"]) {
            $config["forwarding_url"] = static::getPropValue($attrs, "forwarding_url");
        }

        if (in_array($config["style"], ['flip_clock', 'block_clock'])) {
            $config["show_weeks"] = 'on' === static::getPropValue($attrs, "show_weeks");
            $config["show_days"] = 'on' === static::getPropValue($attrs, "show_days");
            $config["show_hours"] = 'on' === static::getPropValue($attrs, "show_hours");
            $config["show_minutes"] = 'on' === static::getPropValue($attrs, "show_minutes");
            $config["show_seconds"] = 'on' === static::getPropValue($attrs, "show_seconds") || 'on' !== static::getPropValue($attrs, "show_minutes");
            $config["clock_label_position"] = static::getPropValue($attrs, "clock_label_position");
        }

		if($render_html == "") {
			$render_html = sprintf(
				'<div class="clock %1$s" data-config="%2$s"></div>',
				esc_attr($config["style"]),
				esc_attr(htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8'))
			);
		}

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
				'classnamesFunction'  => [ Countdown::class, 'module_classnames' ],
				'stylesComponent'     => [ Countdown::class, 'module_styles' ],
				'scriptDataComponent' => [ Countdown::class, 'module_script_data' ],
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
