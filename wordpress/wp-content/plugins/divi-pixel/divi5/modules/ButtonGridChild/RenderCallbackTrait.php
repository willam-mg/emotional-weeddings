<?php
namespace DIPI\Modules\ButtonGridChild;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use ET\Builder\Packages\Module\Module;
use DIPI\Traits\PopupGalleryTrait;

trait RenderCallbackTrait
{

    use PopupGalleryTrait;

    public static function render_callback($attrs, $content, $block, $elements)
    {


        $children_ids = $block->parsed_block['innerBlocks'] ? array_map(
            function ($inner_block) {
                return $inner_block['id'];
            },
            $block->parsed_block['innerBlocks']
        ) : [];
        $attr_button_type = $attrs['button_type']['innerContent']['desktop']['value'];
        if ('button' === $attr_button_type || 'dp_button' === $attr_button_type) {
            $button_output = $elements->render([
                'attrName' => 'button'
            ]);
        } else {
            $text_info = $attrs['text_info']['innerContent']['desktop']['value'];
            $button_output = sprintf('<div class="dipi-text-grid dipi-text-wrap">%1$s</div>', esc_attr($text_info));
        }

        $parent = BlockParserStore::get_parent($block->parsed_block['id'], $block->parsed_block['storeInstance']);

        return Module::render(
            [
                // FE only.
                'orderIndex' => $block->parsed_block['orderIndex'],
                'storeInstance' => $block->parsed_block['storeInstance'],

                // VB equivalent.
                'attrs' => $attrs,
                'elements' => $elements,
                'id' => $block->parsed_block['id'],
                'moduleclass' => '',
                'name' => $block->block_type->name,
                'moduleCategory' => $block->block_type->category,
                'classnamesFunction' => [ButtonGridChild::class, 'module_classnames'],
                'stylesComponent' => [ButtonGridChild::class, 'module_styles'],
                'scriptDataComponent' => [ButtonGridChild::class, 'module_script_data'],
                'parentAttrs' => $parent->attrs ?? [],
                'parentId' => $parent->id ?? '',
                'parentName' => $parent->blockName ?? '',
                'children' => ElementComponents::component(
                    [
                        'attrs' => $attrs['module']['decoration'] ?? [],
                        'id' => $block->parsed_block['id'],

                        // FE only.
                        'orderIndex' => $block->parsed_block['orderIndex'],
                        'storeInstance' => $block->parsed_block['storeInstance'],
                    ]
                ) . $button_output,
                'childrenIds' => $children_ids
            ]
        );
    }
}