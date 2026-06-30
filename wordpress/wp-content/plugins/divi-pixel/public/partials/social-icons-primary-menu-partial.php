<?php
namespace DiviPixel; 

add_filter('wp_nav_menu_items', 'DiviPixel\dipi_primary_menu_social_icons', 10, 2);
if (!function_exists('get_dipi_theme_location_by_menu_id')) {
	function get_dipi_theme_location_by_menu_id($menu_id) {
		// Retrieve all registered menu locations
		$locations = get_nav_menu_locations();
	
		// Loop through the locations to find the corresponding menu ID
		foreach ($locations as $location => $id) {
			if ($id == $menu_id) {
				return $location;  // Return the theme location if the menu ID matches
			}
		}
	
		return false;  // Return false if no matching location is found
	}
}
function dipi_primary_menu_social_icons($items, $args) {
	$use_social_icons_menu = DIPI_Settings::get_option('social_icons_menu');
	$menu_location = '';
	if (is_numeric($args->menu)) { // Theme Builder Menu
		$menu_location = get_dipi_theme_location_by_menu_id($args->menu);
	} else {
		$menu_location = $args->theme_location;
	}
	if(isset($use_social_icons_menu) && $menu_location !== $use_social_icons_menu . '-menu'){
		return $items;
	}
	include plugin_dir_path(__FILE__) . 'social-icons-partial.php';

	$social_links_new_tab = DIPI_Settings::get_option('social_links_new_tab') ? 'target="_blank"' : '';

	$social_icon_hover_effect = DIPI_Customizer::get_option('social_icon_hover_effect');
	$social_icon_box_style_class = DIPI_Customizer::get_option('social_icon_box_style') ? 'dipi-social-icon-box-style ' : '';

	$dipi_hover_effect_class = '';
	if($social_icon_hover_effect == 'zoom') :
		$dipi_hover_effect_class = 'dipi-social-icon-zoom ';
	elseif($social_icon_hover_effect == 'slide_up') :
		$dipi_hover_effect_class = 'dipi-social-icon-slideup ';
	elseif($social_icon_hover_effect == 'rotate') :
		$dipi_hover_effect_class = 'dipi-social-icon-rotate ';
	endif;

	ob_start();

	?>

	<div id="dipi-primary-menu-social-icons-id" class="dipi-social-icons dipi-primary-menu-social-icons">
		<?php foreach($primary_menu_social_icons as $primary_menu_social_icon_value) : ?>
		<div class="dipi-social-icon <?php echo esc_attr($dipi_hover_effect_class); echo esc_attr($social_icon_box_style_class); ?>dipi-social-<?php echo esc_attr($primary_menu_social_icon_value['title']); ?>">
			<a href="<?php echo esc_url($primary_menu_social_icon_value['url']); ?>" <?php echo esc_attr($social_links_new_tab); ?>>
				<span class="dipi-icon">
					<?php include DIPI_DIR . "public/assets/" . $primary_menu_social_icon_value['icon']; ?>
				</span>
			</a>
		</div>
		<?php endforeach; ?>
	</div>

	<?php

	$output = ob_get_clean();

	if( $menu_location == 'primary-menu' ){
		$items .= $output;
	}

	return $items;

}

?>