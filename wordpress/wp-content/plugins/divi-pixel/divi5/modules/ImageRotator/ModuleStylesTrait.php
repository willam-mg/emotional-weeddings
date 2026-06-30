<?php
namespace DIPI\Modules\ImageRotator;

if (!defined("ABSPATH")) {
	die("Direct access forbidden.");
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Text\TextStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Border\BorderStyle;
use ET\Builder\Packages\Module\Options\Spacing\SpacingStyle;

trait ModuleStylesTrait
{
	use CustomCssTrait;
	use StyleDeclarationTrait;
	private static $props = [];

	public static function getAttrByMode($attrs, $attr, $default = null, $mode = null) {
		return (((($attrs??[])[$attr]??[])['innerContent']??[])[$mode??'desktop']??[])['value']??$default??'';
	}

	public static function getAttr(
		$attrs,
		$attr,
		$default = null,
		$zoom = "",
		$unit = "",
		$wrap_func = ""
	) {
		$AttrValue = (($attrs ?? [])[$attr] ?? [])["innerContent"] ?? [
			"desktop" => ["value" => $default ?? ""],
		];
		return $AttrValue;
	}

	public static function module_styles($args)
	{
		$attrs	= $args['attrs'] ?? [];
		$elements = $args['elements'];
		$settings = $args['settings'] ?? [];
		$order_class  = $args['orderClass'] ?? '';
		$order_number = preg_replace('/[^0-9]/', '', $order_class);

		$use_preload = static::getAttrByMode($attrs, "use_preload", "on");
		$play_button = $attrs['play_button']['decoration']['button']['desktop']['value'] ?? null;
		$pause_button = $attrs['pause_button']['decoration']['button']['desktop']['value'] ?? null;

		Style::add(
			[
				'id'			=> $args['id'],
				'name'		  => $args['name'],
				'orderIndex'	=> $args['orderIndex'],
				'storeInstance' => $args['storeInstance'],
				'styles'		=> [
					// Module.
					$elements->style([
						'attrName'   => 'module',
						'styleProps' => [
							'disabledOn' => [
								'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
							],
						],
					]),
					CssStyle::style([
						'selector'  => $args['orderClass'],
						'attr'	 	=> $attrs['css'] ?? [],
						'cssFields'	=> static::custom_css(),
					]),
					$elements->style([
						'attrName'   => 'play_button',
					]),
					$elements->style([
						'attrName'   => 'pause_button',
					]),
					CommonStyle::style([
						'selector'				=> "$order_class .dipi-image-rotator-playpause-buttons",
						'attr'					=> static::getAttr($attrs, 'button_h_alignment', ''),
						'declarationFunction' 	=> function ( array $args ) {
							$button_alignment = $args["attrValue"];
							return  "text-align: $button_alignment;";
						}
					]),
					$use_preload === "on" ? CommonStyle::style([
						'selector'			=> "$order_class .dipi-image-rotator-preload",
						'attr'				=> static::getAttr($attrs, 'preload_background_color', 'rgba(255,255,255,0.8)'),
						'declarationFunction' => function ( array $args ) {
							$attrValue = $args["attrValue"];
							return  "background-color: $attrValue;";
						}
					]) : null,
					$use_preload === "on" ? CommonStyle::style([
						'selector'			=> "$order_class .dipi-image-rotator-preload",
						'attr'				=> static::getAttr($attrs, 'preload_height', '200'),
						'declarationFunction' => function ( array $args ) {
							$attrValue = $args["attrValue"];
							return  "min-height: {$attrValue}px;";
						}
					]) : null,
					$use_preload === "on" ? CommonStyle::style([
						'selector'			=> "$order_class .dipi-image-rotator-preload img, $order_class .dipi-image-rotator-preload svg",
						'attr'				=> static::getAttr($attrs, 'preload_size', '50'),
						'declarationFunction' => function ( array $args ) {
							$attrValue = $args["attrValue"];
							return  "width: {$attrValue}px;";
						}
					]) : null,
					$play_button && isset($play_button["icon"]) && isset($play_button["icon"]["placement"]) && $play_button["enable"] === "on" && $play_button["icon"]["placement"] === "left" && $play_button["icon"]["onHover"] === "off" ? CommonStyle::style([
						'selector'			=> "$order_class .dipi-img-rotator-play",
						'attr'				=> static::getAttr($attrs, 'preload_size', '50'),
						'declarationFunction' => function ( array $args ) {
							return  "padding-right: 0.7em!important;padding-left: 2em!important;";
						}
					]) : null,
					$pause_button && isset($pause_button["icon"]) && isset($pause_button["icon"]["placement"]) && $pause_button["enable"] === "on" && $pause_button["icon"]["placement"] === "left" && $pause_button["icon"]["onHover"] === "off" ? CommonStyle::style([
						'selector'			=> "$order_class .dipi-img-rotator-pause",
						'attr'				=> static::getAttr($attrs, 'preload_size', '50'),
						'declarationFunction' => function ( array $args ) {
							return  "padding-right: 0.7em!important;padding-left: 2em!important;";
						}
					]) : null,
				],
			]
		);
	}
}
