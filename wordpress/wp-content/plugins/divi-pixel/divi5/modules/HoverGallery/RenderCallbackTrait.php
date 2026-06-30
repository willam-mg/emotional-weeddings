<?php
namespace DIPI\Modules\HoverGallery;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use ET\Builder\Packages\Module\Module;

trait RenderCallbackTrait {
  public static function render_callback( $attrs, $content, $block, $elements ) {
    $children_ids = $block->parsed_block['innerBlocks'] ? array_map(
			function( $inner_block ) {
				return $inner_block['id'];
			},
			$block->parsed_block['innerBlocks']
		) : [];

    // const galleryAnimation = getAttrByMode(attrs?.module?.advanced?.galleryAnimation);


    $parent = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );
    $parent_attrs = $parent->attrs ?? [];

    $gallery_animation = $attrs['module']['advanced']['galleryAnimation']['desktop']['value'];
    $activate_on_click = $attrs['module']['advanced']['activateOnClick']['desktop']['value'] ?? 'off';
    $autoplay_speed = $attrs['module']['advanced']['autoPlaySpeed']['desktop']['value'] ?? '3000ms';
    $autoplay = $attrs['module']['advanced']['autoplay']['desktop']['value'] ?? 'off';
    
    $images = [];
    $index = 0;

    foreach ($block->parsed_block['innerBlocks'] as $child) {
      $image = $child['attrs']['image']['innerContent']['desktop']['value']['src'];
      $imageHtml = sprintf('<div style="background-image: url(%1$s);"></div>', esc_url($image));
      if($gallery_animation['animationName'] === 'SliceAnim'){
        $imageHtml = sprintf(
          '<div class="dipi-hg-slice-clones">
            <div key="hg-slice-0" class="dipi-hg-slice">
              <img src="%1$s" />
            </div>
            <div key="hg-slice-1" class="dipi-hg-slice">
              <img src="%1$s" />
            </div>
            <div key="hg-slice-2" class="dipi-hg-slice">
              <img src="%1$s" />
            </div>
            <div key="hg-slice-3" class="dipi-hg-slice">
              <img src="%1$s" />
            </div>
            <div key="hg-slice-4" class="dipi-hg-slice">
              <img src="%1$s" />
            </div>
          </div>',
          esc_url($image)
        );
      }

      $gallery_animation['animationName'] = ucfirst($gallery_animation['animationName']);
      
      $images[] = sprintf('
        <div class="dipi-hg-image %4$s dipi-hg-%1$s" data-item="%2$s">
          %3$s
        </div>',
        esc_attr($gallery_animation['animationName']),
        esc_attr($child['id']),
        $imageHtml,
        $index === 0 ? 'active' : ''
      );

      $index++;
    }

    $images = sprintf('<div class="dipi-hg__images">%1$s</div>', implode('', $images));

    $html_attrs = [
      'data-animation-speed' => $gallery_animation['animationSpeed'],
      'data-animation' => $gallery_animation['animationName'],
      'data-animation-delay' => $gallery_animation['animationDelay'],
      'data-test' => 'test'
    ];
   
    $content = ElementComponents::component(
      [
        'attrs'         => $attrs['module']['decoration'] ?? [],
        'id'            => $block->parsed_block['id'],
        'htmlAttrs'     => [
          'data-item' => $block->parsed_block['id']
        ],
        // FE only.
        'orderIndex'    => $block->parsed_block['orderIndex'],
        'storeInstance' => $block->parsed_block['storeInstance'],
      ]
    ) . $content;

    $content = sprintf('<div class="dipi-hg__items">%1$s</div>', $content);

		$activate_on_click = $activate_on_click === 'on' ? 'data-activate-on-click="on"': ''	 ;
    $autoplay = $autoplay === 'on' ? 'data-autoplay="on"': 'data-autoplay="off"';

    $output = sprintf(
			'<div class="dipi-hover-gallery" data-animation="%5$s" data-animation-speed="%3$s" style="--dipi-hg-animation-speed: %3$sms;" %4$s %6$s data-autoplay-speed="%7$s" >
				%1$s
				%2$s
			</div>',
      $images,
      $content,
      floatval($gallery_animation['animationSpeed']) / 1000,
      $activate_on_click,
      $gallery_animation['animationName'],
      $autoplay,
      floatval($autoplay_speed) / 1000
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
        'moduleClassName'     => '',
        'name'                => $block->block_type->name,
        'classnamesFunction'  => [ HoverGallery::class, 'module_classnames' ],
        'moduleCategory'      => $block->block_type->category,
        'stylesComponent'     => [ HoverGallery::class, 'module_styles' ],
        'scriptDataComponent' => [ HoverGallery::class, 'module_script_data' ],
        // 'htmlAttrs' => $html_attrs,
        'parentAttrs'         => $parent_attrs,
        'parentId'            => $parent->id ?? '',
        'parentName'          => $parent->blockName ?? '',
        'children'            => $output,
				'childrenIds'         => $children_ids,
      ]
    );
  }
}