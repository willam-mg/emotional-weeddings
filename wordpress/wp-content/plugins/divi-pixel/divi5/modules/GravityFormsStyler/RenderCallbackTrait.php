<?php
/**
 * GravityFormsStyler::render_callback()
 *
 * @package DIPI\Modules\GravityFormsStyler
 * @since ??
 */

namespace DIPI\Modules\GravityFormsStyler;

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

	public static function get_gravity_form($args = [], $conditional_tags = [], $current_page = [])
    {
		$args = wp_parse_args($args, [
            'gf_form_id' => 0,
            'use_ajax' => 'false',
            'tabindex' => '',
            'field_values' => '',
            'form_title' => 'gf_title',
            'form_desc' => 'gf_desc'
        ]);
        $form_id = intval($args['gf_form_id']);
        $tabindex_attr = '';
        if (!empty($args['tabindex'])) {
            $tabindex_attr = sprintf('tabindex="%1$s"', $args['tabindex']);
        }
        $field_values_attr = '';
        if (!empty($args['field_values'])) {
            $field_values_attr = sprintf('field_values="%1$s"', $args['field_values']);
        }
        if ($form_id === 0) {
            return sprintf('<div class="alert">
                    No form selected.
                    <br>
                    Please select a form form the dropdown list of \'Choose Gravity Form\'.
                </div>');
        }
        // Prevent Form Hiding Conditional Fields In Visual Editor If Conditional Fields
        if (function_exists('et_builder_is_frontend') && !et_builder_is_frontend()) {
            add_filter('gform_has_conditional_logic', function ($has_conditional_logic, $form) {
                return false;
            }, 10, 2);
        }
        // Form Shortcode
        $gf_shortcode = sprintf('[gravityform id="%1$s" title="%2$s" description="%3$s" ajax="%4$s" %5$s %6$s]',
            $form_id,
            $args['form_title'] === 'gf_title' ? "on" : "false",
            $args['form_desc'] === 'gf_desc' ? "on" : "false",
            ($args['use_ajax'] === "on" ? "true" : "false"),
            $tabindex_attr,
            $field_values_attr
        );
        $form_output = sprintf('<div class="dipi_gf_styler_wrapper clearfix">%1$s</div>',
            do_shortcode($gf_shortcode)
        );
        return  $form_output;
    }

    public static function dipi_gravity_forms_styler($attrs, $order_number)
    {
        $select_arrow_icon = $attrs['select_arrow_icon']['innerContent']['desktop']['value'] ?? [];
        $select_arrow_icon_tablet = $attrs['select_arrow_icon']['innerContent']['tablet']['value'] ?? $select_arrow_icon;
        $select_arrow_icon_phone = $attrs['select_arrow_icon']['innerContent']['phone']['value'] ?? $select_arrow_icon_tablet;
        $select_arrow_icon = Utils::process_font_icon($select_arrow_icon);
        $select_arrow_icon_tablet = Utils::process_font_icon($select_arrow_icon_tablet);
        $select_arrow_icon_phone = Utils::process_font_icon($select_arrow_icon_phone);
        return [
            "order_class"                   => "dipi_gravity_forms_styler_$order_number",
            "use_custom_select_arrow"       => self::getPropValue($attrs, 'use_custom_select_arrow'),
            "select_arrow_use_icon"         => self::getPropValue($attrs, 'select_arrow_use_icon'),
            "select_arrow_icon_last_edited" =>  "",
            "select_arrow_icon"             => $select_arrow_icon,
            "select_arrow_icon_tablet"      => $select_arrow_icon_tablet,
            "select_arrow_icon_phone"       => $select_arrow_icon_phone, 
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
        $order_number = $block->parsed_block['orderIndex'];
		$args = [
            "gf_form_id" => self::getPropValue($attrs, 'gf_form_id') ?? "0",
            "use_ajax" => self::getPropValue($attrs, 'use_ajax') ?? "off",
            "tabindex" => self::getPropValue($attrs, 'tabindex') ?? "",
            "field_values" => self::getPropValue($attrs, 'field_values') ?? "",
            "form_title" => self::getPropValue($attrs, 'form_title') ?? "gf_title",
            "form_desc" => self::getPropValue($attrs, 'form_desc') ?? "gf_desc",
        ];

		$config = htmlspecialchars(json_encode(self::dipi_gravity_forms_styler($attrs, $order_number)));
        $gf_output = self::get_gravity_form($args, array(), array());

        $render_html = sprintf(
            '<div class="dipi_gf_styler_container" data-config="%1$s">
                %2$s
            </div>',
            $config,
            $gf_output
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
				'classnamesFunction'  => [ GravityFormsStyler::class, 'module_classnames' ],
				'stylesComponent'     => [ GravityFormsStyler::class, 'module_styles' ],
				'scriptDataComponent' => [ GravityFormsStyler::class, 'module_script_data' ],
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
