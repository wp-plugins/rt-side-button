<?php
/*
Plugin Name: Royal Side Button
Plugin URI: http://wordpress.org/plugins/royal-side-button/
Description: Display a side button that you can easily link to any page like feedback, twitter, facebook etc & customise as you like.
Version: 1.0.1
Author: Mehdi Akram
Author URI: http://profiles.wordpress.org/royaltechbd
License: GPLv2
*/

// Hook will fire upon activation - we are using it to set default option values
register_activation_hook( __FILE__, 'royal_sidebutton_activate_plugin' );



//Additional links on the plugin page
add_filter( 'plugin_row_meta', 'rsb_register_plugin_links', 10, 2 );
function rsb_register_plugin_links($links, $file) {
	$base = plugin_basename(__FILE__);
	if ($file == $base) {
		$links[] = '<a href="http://royaltechbd.com/" target="_blank">' . __( 'Royal Technologies', 'rsb' ) . '</a>';
		$links[] = '<a href="http://shamokaldarpon.com/" target="_blank">' . __( 'Shamokal Darpon', 'rsb' ) . '</a>';
	}
	return $links;
}







// Add options and populate default values on first load
function royal_sidebutton_activate_plugin() {

	// populate plugin options array
	$royal_sidebutton_plugin_options = array(
		'text_for_tab'     => 'Feedback',
		'font_family'      => '"Righteous", cursive',
		'font_weight_bold' => '1',
		'button_position'  => '0',
		'tab_url'          => 'http://royaltechbd.com/blog/',
		'pixels_from_top'  => '350',
		'text_color'       => '#FFFFFF',
		'tab_color'        => '#3891B3',
		'hover_color'      => '#3EA2C7',
		'target_blank'     => '0'
		);

	// create field in WP_options to store all plugin data in one field
	add_option( 'royal_sidebutton_plugin_options', $royal_sidebutton_plugin_options );

}


// Fire off hooks depending on if the admin settings page is used or the public website
if ( is_admin() ){ // admin actions and filters

	// Hook for adding admin menu
	add_action( 'admin_menu', 'royal_sidebutton_admin_menu' );

	// Hook for registering plugin option settings
	add_action( 'admin_init', 'royal_sidebutton_settings_api_init');

	// Hook to fire farbtastic includes for using built in WordPress color picker functionality
	add_action('admin_enqueue_scripts', 'royal_sidebutton_farbtastic_script');

	// Display the 'Settings' link in the plugin row on the installed plugins list page
	add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'royal_sidebutton_admin_plugin_actions', -10);

} else { // non-admin enqueues, actions, and filters


	// get the current page url
	$royal_sidebutton_current_page_url 			= royal_sidebutton_get_full_url();


	// get the tab url from the plugin option variable array
	$royal_sidebutton_plugin_option_array	= get_option( 'royal_sidebutton_plugin_options' );
	$royal_sidebutton_tab_url				= $royal_sidebutton_plugin_option_array[ 'tab_url' ];


	// compare the page url and the option tab - don't render the tab if the values are the same
	if ( $royal_sidebutton_tab_url != $royal_sidebutton_current_page_url ) {

		// hook to get option values and dynamically render css to support the tab classes
		add_action( 'wp_head', 'royal_sidebutton_custom_css_hook' );

		// hook to get option values and write the div for the Royal Side Button to display
		add_action( 'wp_footer', 'royal_sidebutton_body_tag_html' );
	}
}



// get the complete url for the current page
function royal_sidebutton_get_full_url()
{
	$s 			= empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
	$sp 		= strtolower($_SERVER["SERVER_PROTOCOL"]);
	$protocol 	= substr($sp, 0, strpos($sp, "/")) . $s;
	$port 		= ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
	return $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
}



// Include WordPress color picker functionality
function royal_sidebutton_farbtastic_script($hook) {

	// only enqueue farbtastic on the plugin settings page
	if( $hook != 'settings_page_royal_side_button' ) 
		return;


	// load the style and script for farbtastic
	wp_enqueue_style( 'farbtastic' );
	wp_enqueue_script( 'farbtastic' );

}



// action function to get option values and write the div for the Royal Side Button to display
function royal_sidebutton_body_tag_html() {

	// get plugin option array and store in a variable
	$royal_sidebutton_plugin_option_array	= get_option( 'royal_sidebutton_plugin_options' );

	// fetch individual values from the plugin option variable array
	$royal_sidebutton_text_for_tab			= $royal_sidebutton_plugin_option_array[ 'text_for_tab' ];
	$royal_sidebutton_tab_url				= $royal_sidebutton_plugin_option_array[ 'tab_url' ];
	$royal_sidebutton_target_blank			= $royal_sidebutton_plugin_option_array[ 'target_blank' ];

	// set the page target
	if ($royal_sidebutton_target_blank == '1') {
		$royal_sidebutton_target_blank = ' target="_blank"';
	}

	// Write HTML to render tab
	echo '<a href="' . esc_url( $royal_sidebutton_tab_url ) . '"' . $royal_sidebutton_target_blank . '><div id="royal_sidebutton_tab" class="royal_sidebutton_contents royal_sidebutton_left">' . esc_html( $royal_sidebutton_text_for_tab ) . '</div></a>';
}



// action function to add a new submenu under Settings
function royal_sidebutton_admin_menu() {

	// Add a new submenu under Settings
	add_options_page( 'Royal Side Button Option Settings', 'Royal Side Button', 'manage_options', 'royal_side_button', 'royal_sidebutton_options_page' );
}


// Display and fill the form fields for the plugin admin page
function royal_sidebutton_options_page() {


?>

	<div class="wrap">
	<?php screen_icon( 'plugins' ); ?>
	<h2>Royal Side Button</h2>
	<p>Royal Side Button was created to give you an easy option for adding a link like contact/feedback/facebook page.
	<strong>NOTE: This plugin requires the WP_footer() hook to be fired from your theme.</strong></p>
	<form method="post" action="options.php">


<?php

	settings_fields( 'royal_sidebutton_option_group' );
	do_settings_sections( 'royal_side_button' );

	// get plugin option array and store in a variable
	$royal_sidebutton_plugin_option_array	= get_option( 'royal_sidebutton_plugin_options' );

	// fetch individual values from the plugin option variable array
	$royal_sidebutton_text_for_tab			= $royal_sidebutton_plugin_option_array[ 'text_for_tab' ];
	$royal_sidebutton_font_family			= $royal_sidebutton_plugin_option_array[ 'font_family' ];
	$royal_sidebutton_font_weight_bold		= $royal_sidebutton_plugin_option_array[ 'font_weight_bold' ];
	$royal_sidebutton_button_position		= $royal_sidebutton_plugin_option_array[ 'button_position' ];
	$royal_sidebutton_tab_url				= $royal_sidebutton_plugin_option_array[ 'tab_url' ];
	$royal_sidebutton_pixels_from_top		= $royal_sidebutton_plugin_option_array[ 'pixels_from_top' ];
	$royal_sidebutton_text_color			= $royal_sidebutton_plugin_option_array[ 'text_color' ];
	$royal_sidebutton_tab_color				= $royal_sidebutton_plugin_option_array[ 'tab_color' ];
	$royal_sidebutton_hover_color			= $royal_sidebutton_plugin_option_array[ 'hover_color' ];
	$royal_sidebutton_target_blank			= $royal_sidebutton_plugin_option_array[ 'target_blank' ];

?>



	<script type="text/javascript">

		jQuery(document).ready(function() {
			jQuery('#colorpicker1').hide();
			jQuery('#colorpicker1').farbtastic("#color1");
			jQuery("#color1").click(function(){jQuery('#colorpicker1').slideToggle()});
		});

		jQuery(document).ready(function() {
			jQuery('#colorpicker2').hide();
			jQuery('#colorpicker2').farbtastic("#color2");
			jQuery("#color2").click(function(){jQuery('#colorpicker2').slideToggle()});
		});

		jQuery(document).ready(function() {
			jQuery('#colorpicker3').hide();
			jQuery('#colorpicker3').farbtastic("#color3");
			jQuery("#color3").click(function(){jQuery('#colorpicker3').slideToggle()});
		});

	</script>


	<table class="widefat">

		<tr valign="top">
		<th scope="row" width="230"><label for="royal_sidebutton_text_for_tab">Text for Button</label></th>
		<td width="525"><input maxlength="30" size="25" type="text" name="royal_sidebutton_plugin_options[text_for_tab]" value="<?php echo esc_html( $royal_sidebutton_text_for_tab ); ?>" /></td>
		</tr>


		<tr valign="top">
		<th scope="row"><label for="royal_sidebutton_tab_font">Select Google font</label></th>
		<td>
			<select name="royal_sidebutton_plugin_options[font_family]">	
				<option value='"Righteous", cursive'							<?php selected( $royal_sidebutton_font_family, '"Righteous", cursive' );							?>	>Righteous</option>
				<option value='"Titan One", cursive'							<?php selected( $royal_sidebutton_font_family, '"Titan One", cursive' );							?>	>Titan One</option>
				<option value='"Finger Paint", cursive'							<?php selected( $royal_sidebutton_font_family, '"Finger Paint", cursive' );							?>	>Finger Paint</option>
				<option value='"Londrina Shadow", cursive'						<?php selected( $royal_sidebutton_font_family, '"Londrina Shadow", cursive' );						?>	>Londrina Shadow</option>
				<option value='"Autour One", cursive'							<?php selected( $royal_sidebutton_font_family, '"Autour One", cursive' );							?>	>Autour One</option>
				<option value='"Meie Script", cursive'							<?php selected( $royal_sidebutton_font_family, '"Meie Script", cursive' );							?>	>Meie Script</option>
				<option value='"Sonsie One", cursive'							<?php selected( $royal_sidebutton_font_family, '"Sonsie One", cursive' );							?>	>Sonsie One</option>
				<option value='"Kavoon", cursive'								<?php selected( $royal_sidebutton_font_family, '"Kavoon", cursive' );								?>	>Kavoon</option>
				<option value='"Racing Sans One", cursive'						<?php selected( $royal_sidebutton_font_family, '"Racing Sans One", cursive' );						?>	>Racing Sans One</option>
				<option value='"Gravitas One", cursive'							<?php selected( $royal_sidebutton_font_family, '"Gravitas One", cursive' );							?>	>Gravitas One</option>
				<option value='"Nosifer", cursive'								<?php selected( $royal_sidebutton_font_family, '"Nosifer", cursive' );								?>	>Nosifer</option>
				<option value='"Offside", cursive'								<?php selected( $royal_sidebutton_font_family, '"Offside", cursive' );								?>	>Offside</option>
				<option value='"Audiowide", cursive'							<?php selected( $royal_sidebutton_font_family, '"Audiowide", cursive' );							?>	>Audiowide</option>
				<option value='"Faster One", cursive'							<?php selected( $royal_sidebutton_font_family, '"Faster One", cursive' );							?>	>Faster One</option>
				<option value='"Germania One", cursive'							<?php selected( $royal_sidebutton_font_family, '"Germania One", cursive' );							?>	>Germania One</option>
				<option value='"Emblema One", cursive'							<?php selected( $royal_sidebutton_font_family, '"Emblema One", cursive' );							?>	>Emblema One</option>
				<option value='"Sansita One", cursive'							<?php selected( $royal_sidebutton_font_family, '"Sansita One", cursive' );							?>	>Sansita One</option>
				<option value='"Creepster", cursive'							<?php selected( $royal_sidebutton_font_family, '"Creepster", cursive' );							?>	>Creepster</option>
				<option value='"Delius Unicase", cursive'						<?php selected( $royal_sidebutton_font_family, '"Delius Unicase", cursive' );						?>	>Delius Unicase</option>
				<option value='"Wallpoet", cursive'								<?php selected( $royal_sidebutton_font_family, '"Wallpoet", cursive' );								?>	>Wallpoet</option>
				<option value='"Monoton", cursive'								<?php selected( $royal_sidebutton_font_family, '"Monoton", cursive' );								?>	>Monoton</option>
				<option value='"Kenia", cursive'								<?php selected( $royal_sidebutton_font_family, '"Kenia", cursive' );								?>	>Kenia</option>
				<option value='"Monofett", cursive'								<?php selected( $royal_sidebutton_font_family, '"Monofett", cursive' );								?>	>Monofett</option>
				<option value='"Denk One", sans-serif'							<?php selected( $royal_sidebutton_font_family, '"Denk One", sans-serif' );							?>	>Denk One</option>
				<option value='"Ropa Sans", sans-serif'							<?php selected( $royal_sidebutton_font_family, '"Ropa Sans", sans-serif' );							?>	>Ropa Sans</option>
				<option value='"Paytone One", sans-serif'						<?php selected( $royal_sidebutton_font_family, '"Paytone One", sans-serif' );						?>	>Paytone One</option>
				<option value='"Russo One", sans-serif'							<?php selected( $royal_sidebutton_font_family, '"Russo One", sans-serif' );							?>	>Russo One</option>
				<option value='"Krona One", sans-serif'							<?php selected( $royal_sidebutton_font_family, '"Krona One", sans-serif' );							?>	>Krona One</option>
				<option value='"Rum Raisin", sans-serif'						<?php selected( $royal_sidebutton_font_family, '"Rum Raisin", sans-serif' );						?>	>Rum Raisin</option>
				
			</select>
		</td>
		</tr>


		<tr valign="top">
		<th scope="row"><label for="royal_sidebutton_font_weight_bold">Bold text</label></th>
		<td><input name="royal_sidebutton_plugin_options[font_weight_bold]" type="checkbox" value="1" <?php checked( '1', $royal_sidebutton_font_weight_bold ); ?> /></td>
		</tr>
		
		<tr valign="top">
		<th scope="row"><label for="royal_sidebutton_text_shadow">Button Position (Check for Left, Uncheck for Right)</label></th>
		<td><input name="royal_sidebutton_plugin_options[button_position]" type="checkbox" value="1" <?php checked( '1', $royal_sidebutton_button_position); ?> /></td>
		</tr>


		<tr valign="top">
		<th scope="row"><label for="royal_sidebutton_target_blank">Open link in new window</label></th>
		<td><input name="royal_sidebutton_plugin_options[target_blank]" type="checkbox" value="1" <?php checked( '1', $royal_sidebutton_target_blank ); ?> /></td>
		</tr>


		<tr valign="top">
		<th scope="row"><label for="royal_sidebutton_tab_url">Button URL</label></th>
		<td><input size="45" type="text" name="royal_sidebutton_plugin_options[tab_url]" value="<?php echo esc_url( $royal_sidebutton_tab_url ); ?>" /></td>
		</tr>


		<tr valign="top">
		<th scope="row"><label for="royal_sidebutton_pixels_from_top">Position from top (px)</label></th>
		<td><input maxlength="4" size="4" type="text" name="royal_sidebutton_plugin_options[pixels_from_top]" value="<?php echo sanitize_text_field( $royal_sidebutton_pixels_from_top ); ?>" /></td>
		</tr>

	</table>

<BR>

	<table class="widefat" border="1">

		<tr valign="top">
			<th scope="row" colspan="2" width="33%"><strong>Colors:</strong> Click on each field to display the color picker. Click again to close it.</th>
			<td width="33%" rowspan="4">
				<div id="colorpicker1"></div>
				<div id="colorpicker2"></div>
				<div id="colorpicker3"></div>
			</td>
		</tr>


		<tr valign="top">
			<th scope="row"><label for="royal_sidebutton_text_color">Button Text color</label></th>
			<td width="33%"><input type="text" maxlength="7" size="6" value="<?php echo esc_attr( $royal_sidebutton_text_color ); ?>" name="royal_sidebutton_plugin_options[text_color]" id="color1" /></td>
		</tr>


		<tr valign="top">
			<th scope="row"><label for="royal_sidebutton_tab_color">Button color</label></th>
			<td width="33%"><input type="text" maxlength="7" size="6" value="<?php echo esc_attr( $royal_sidebutton_tab_color ); ?>" name="royal_sidebutton_plugin_options[tab_color]" id="color2" /></td>
		</tr>


		<tr valign="top">
			<th scope="row"><label for="royal_sidebutton_hover_color">Button hover color</label></th>
			<td width="33%"><input type="text" maxlength="7" size="6" value="<?php echo esc_attr( $royal_sidebutton_hover_color ); ?>" name="royal_sidebutton_plugin_options[hover_color]" id="color3" /></td>
		</tr>

		<tr valign="top">
			<td colspan="3">&nbsp;</td>
		</tr>

	</table>

	<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>



<?php
	echo '</form>';
	echo '</div>';
}



// Use Settings API to whitelist options
function royal_sidebutton_settings_api_init() {

	register_setting( 'royal_sidebutton_option_group', 'royal_sidebutton_plugin_options' );

}



// Build array of links for rendering in installed plugins list
function royal_sidebutton_admin_plugin_actions($links) {

	$links[] = '<a href="options-general.php?page=royal_side_button">'.__('Settings').'</a>';
	return $links;

}



// This function runs all the css and dynamic css elements for displaying the Royal Side Button
function royal_sidebutton_custom_css_hook() {

	// get plugin option array and store in a variable
	$royal_sidebutton_plugin_option_array	= get_option( 'royal_sidebutton_plugin_options' );

	// fetch individual values from the plugin option variable array
	$royal_sidebutton_text_for_tab			= $royal_sidebutton_plugin_option_array[ 'text_for_tab' ];
	$royal_sidebutton_font_family			= $royal_sidebutton_plugin_option_array[ 'font_family' ];
	$royal_sidebutton_font_weight_bold		= $royal_sidebutton_plugin_option_array[ 'font_weight_bold' ];
	$royal_sidebutton_text_shadow			= $royal_sidebutton_plugin_option_array[ 'text_shadow' ];
	$royal_sidebutton_button_position		= $royal_sidebutton_plugin_option_array[ 'button_position' ];
	$royal_sidebutton_tab_url				= $royal_sidebutton_plugin_option_array[ 'tab_url' ];
	$royal_sidebutton_pixels_from_top		= $royal_sidebutton_plugin_option_array[ 'pixels_from_top' ];
	$royal_sidebutton_text_color			= $royal_sidebutton_plugin_option_array[ 'text_color' ];
	$royal_sidebutton_tab_color				= $royal_sidebutton_plugin_option_array[ 'tab_color' ];
	$royal_sidebutton_hover_color			= $royal_sidebutton_plugin_option_array[ 'hover_color' ];

?>

<style type='text/css'>
@import url(http://fonts.googleapis.com/css?family=Autour+One|Meie+Script|Armata|Rum+Raisin|Sonsie+One|Kavoon|Denk+One|Gravitas+One|Racing+Sans+One|Nosifer|Ropa+Sans|Offside|Titan+One|Paytone+One|Audiowide|Righteous|Faster+One|Russo+One|Germania+One|Krona+One|Emblema+One|Creepster|Delius+Unicase|Wallpoet|Sansita+One|Monoton|Kenia|Monofett);


/* Begin Royal Side Button Styles*/
#royal_sidebutton_tab {
	font-family:<?php echo $royal_sidebutton_font_family; ?>;
	top:<?php echo $royal_sidebutton_pixels_from_top; ?>px;
	background-color:<?php echo $royal_sidebutton_tab_color; ?>;
	color:<?php echo $royal_sidebutton_text_color; ?>;
	border-style:solid;
	border-width:0px;

}

#royal_sidebutton_tab:hover {
	background-color: <?php echo $royal_sidebutton_hover_color; ?>;
}

.royal_sidebutton_contents {
	position:fixed;
	margin:0;
	padding:6px 13px 8px 13px;
	text-decoration:none;
	text-align:center;
	font-size:15px;
	<?php
	if ( $royal_sidebutton_font_weight_bold =='1' ) :
	  echo 'font-weight:bold;' . "\n";
	else :
	  echo 'font-weight:normal;' . "\n";
	endif;
	?>
	border-style:solid;
	display:block;
	z-index:100000;
}

.royal_sidebutton_left {
	cursor: pointer;
	-webkit-transform-origin:0 0;
	-moz-transform-origin:0 0;
	-o-transform-origin:0 0;
	-ms-transform-origin:0 0;
	-webkit-transform:rotate(270deg);
	-moz-transform:rotate(270deg);
	-ms-transform:rotate(270deg);
	-o-transform:rotate(270deg);
	transform:rotate(270deg);

	<?php
	if ( $royal_sidebutton_button_position =='1' ) :
	  echo 'left:-2px;' . "\n";
	  echo '-moz-border-radius:0px 0px 10px 10px;' . "\n";
	  echo 'border-radius:0px 0px 10px 10px;' . "\n";	  	  
	else :
	  echo 'right:-54px;' . "\n";
	  echo '-moz-border-radius:10px 10px 0px 0px;' . "\n";
	  echo 'border-radius:10px 10px 0px 0px;' . "\n";
	endif;
	?>	
	
	
	<!--[if lte IE 8]>
		/* Internet Explorer 8 and below */
		-ms-transform:rotate(270deg) / !important;
		*filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3);
	<![endif]-->

}
/* End Royal Side Button Styles*/

</style>

<?php
}
