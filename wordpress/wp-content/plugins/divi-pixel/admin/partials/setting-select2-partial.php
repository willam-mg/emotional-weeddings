<?php

if(!isset($field["options"])){
    return;
}

if(isset($field["computed"]) && $field["computed"] === true ){
    $options = call_user_func($field["options"]);
} else {
    $options = $field["options"];
}

if(is_null($options)){
    $options = [];
}

?>

<?php 
    $this->render_ribbon($field); 
    $placeholder = isset($field["placeholder"]) && is_callable($field["placeholder"]) ? $field["placeholder"]() : "Select Pages";
?>
<div class="dipi_row">
    <div class="dipi_settings_option_description col-md-6">
        <div class="dipi_option_label">
            <?php echo esc_html($field["label"]()); ?>
        </div>
        <?php if (isset($field["description"]) && is_callable($field["description"])) : ?>
        <div class="dipi_option_description">
        <?php echo wp_kses_post($field["description"]()); ?>
        </div>        
        <?php endif; ?>
    </div>
    <div class="dipi_settings_option_field dipi_settings_option_field_select col-md-6">
        <div class="dipi_select2">
            <select multiple="multiple" name='<?php echo esc_attr($id); ?>[]' id='<?php echo esc_attr($id); ?>'>
        <?php
            foreach($options as $option_id => $option_title) : 
                $selected = is_array($value) && in_array($option_id, $value ) ? ' selected="selected" ' : '';
        ?>
                <option value='<?php echo esc_attr($option_id); ?>' <?php echo esc_attr($selected); ?>>
                    <?php echo is_string($option_title) ? esc_html($option_title) : esc_html($option_title()); ?>
                </option>

            <?php endforeach; ?>
            </select>
        </div>  
    </div> 
</div>

<script type="text/javascript">
jQuery(function ($) {
    $(document).ready(function () {
        $('#<?php echo esc_attr($id); ?>').select2({
            placeholder: `-- <?php echo et_core_esc_previously($placeholder) ?> --`,
            tags: true
        });
    });
});
</script>