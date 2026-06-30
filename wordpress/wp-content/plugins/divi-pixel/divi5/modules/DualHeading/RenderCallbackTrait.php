<?php
namespace DIPI\Modules\DualHeading;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use DIPI\Traits\BaseRenderTrait;

trait RenderCallbackTrait
{
    use BaseRenderTrait;

    public static function render_callback($attrs, $content, $block, $elements)
    {
        $parent = BlockParserStore::get_parent($block->parsed_block['id'], $block->parsed_block['storeInstance']);
        $parent_attrs = $parent->attrs ?? [];

        // Get props
        $heading_tag_raw = static::getPropValue($attrs, 'heading_tag') ?? 'h2';
        $allowed_heading_tags = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ];
        $heading_tag = in_array( $heading_tag_raw, $allowed_heading_tags, true ) ? $heading_tag_raw : 'h2';
        $use_reveal_effect = static::getPropValue($attrs, 'use_reveal_effect') ?? 'off';

        $start_tag = '<' . $heading_tag . ' class="dipi-dh-main">';
        $end_tag = '</' . $heading_tag . '>';

        $extra_classes = '';
        if ($use_reveal_effect === 'on') {
            $extra_classes .= 'dipi-dh-waypoint dipi-go-animation';
        }

        // Render elements using elements->render()
        $first_heading = $elements->render([
            'attrName' => 'first_heading',
        ]);

        $second_heading = $elements->render([
            'attrName' => 'second_heading',
        ]);

        $output = sprintf(
            '<div class="dipi-dual-heading %5$s">
                %3$s
                    <span class="dipi-dh-first-heading">
                        <span class="dipi-dh-animation-container">
                            <span class="dipi-dh-bg-container">
                                %1$s
                            </span>
                        </span>
                    </span>
                    <span class="dipi-dh-second-heading">
                        <span class="dipi-dh-animation-container">
                            <span class="dipi-dh-bg-container">
                                %2$s
                            </span>
                        </span>
                    </span>
                %4$s
            </div>',
            $first_heading,
            $second_heading,
            $start_tag,
            $end_tag,
            $extra_classes
        );

        return Module::render(
            [
                // FE only.
                'orderIndex' => $block->parsed_block['orderIndex'],
                'storeInstance' => $block->parsed_block['storeInstance'],

                // VB equivalent.
                'attrs' => $attrs,
                'elements' => $elements,
                'id' => $block->parsed_block['id'],
                'name' => $block->block_type->name,
                'moduleCategory' => $block->block_type->category,
                'classnamesFunction' => [DualHeading::class, 'module_classnames'],
                'stylesComponent' => [DualHeading::class, 'module_styles'],
                'scriptDataComponent' => [DualHeading::class, 'module_script_data'],
                'parentAttrs' => $parent_attrs,
                'parentId' => $parent->id ?? '',
                'parentName' => $parent->blockName ?? '',
                'children' => ElementComponents::component(
                    [
                        'attrs' => $attrs['module']['decoration'] ?? [],
                        'id' => $block->parsed_block['id'],
                        'orderIndex' => $block->parsed_block['orderIndex'],
                        'storeInstance' => $block->parsed_block['storeInstance'],
                    ]
                ) . $output,
            ]
        );
    }
}

