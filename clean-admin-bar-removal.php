<?php
/**
 * @package Clean_Admin_Bar_Removal
 * @version 1.0
 */
/*
Plugin Name: Clean Admin Bar Removal 
Plugin URI: http://fabapps.com/2012/01/clean-admin-bar-removal/ 
Description: Clean Admin Bar Removal lets you choose whether your users will see a top toolbar in WordPress 3.2 and higher. This is done by (1) allowing you to change your users' toolbar preference en masse and (2) setting a default "no toolbar" preference if desired. Users can re-enable the admin bar in their individual profiles under "Show Toolbar when viewing site". Settings are found in <a href="tools.php?page=Clean_Admin_Bar_Removal">Tools &rarr; Clean Admin Bar</a>. 
Author: Christian MacAuley 
Version: 1.0
Author URI: http://fabapps.com  
*/

// Add the configuration page to the Tools menu 
add_action('admin_menu', 'clean_admin_bar_submenu');
function clean_admin_bar_submenu() {
	$parent_slug="tools.php";
	$page_title="Clean Admin Bar Removal";
	$menu_title="Clean Admin Bar";
	$capability="activate_plugins";
	$menu_slug="Clean_Admin_Bar_Removal";
	$function="clean_admin_bar_settings_page";
	add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function ); 
}

// Show settings page and make changes on save 
function clean_admin_bar_settings_page() {
	$clean_admin_bar_removal_default_roles = get_option('clean_admin_bar_removal_default_roles'); 
	if (!$clean_admin_bar_removal_default_roles) $clean_admin_bar_removal_default_roles = array(); 
	?>
    <div id="clean_admin_bar_removal" class="wrap">
    <style type="text/css">
	#clean_admin_bar_removal, #clean_admin_bar_removal fieldset { margin-left: 20px; } 
	#clean_admin_bar_removal #icon-tools { margin-left: -20px; } 
	#clean_admin_bar_removal #message { margin-right: 40px; } 
	#clean_admin_bar_removal .explain { font-size: 120%; } 
	</style>
    <div id="icon-tools" class="icon32"><br /></div><h2>Clean Admin Bar Removal</h2>
    <p class='explain'>Choose whether the top toolbar is displayed while viewing the site by changing your site users' preferences.</p>
    <?php 
	if (!empty($_POST['clean_admin_bar_turn_off']) && current_user_can('edit_users')) {
		$users_affected = "";
		foreach ($_POST['clean_admin_bar_turn_off'] as $role) {
			$users = get_users(array("role"=>"$role"));
			foreach ($users as $u) {
				update_user_meta( $u->ID, 'show_admin_bar_front', 'false' );
				$users_affected .= $u->user_login.", ";
			}
		}
		$users_affected = substr($users_affected,0,-2);
		echo "<div id='message' class='updated'><p><strong>Existing user settings saved.</strong> User(s) affected: $users_affected</p></div>";
	}
	if (!empty($_POST['clean_admin_bar_turn_off_by_default']) && current_user_can('edit_users')) {
		$clean_admin_bar_removal_default_roles = $_POST['clean_admin_bar_turn_off_by_default'];
		update_option( "clean_admin_bar_removal_default_roles", $clean_admin_bar_removal_default_roles);
		echo "<div id='message' class='updated'><p><strong>Default user settings saved.</strong></p></div>";
	}
	?>
    <form name='clean_admin_bar_settings' id='clean_admin_bar_settings' method='post' action=''>
    <p>Turn off the admin bar for <strong>existing users</strong> with the following user roles: </p>
    <fieldset>
    <p><label><input type='checkbox' name='clean_admin_bar_turn_off[]' value='administrator' /> Administrator</label></p>
    <p><label><input type='checkbox' name='clean_admin_bar_turn_off[]' value='editor' /> Editor</label></p>
    <p><label><input type='checkbox' name='clean_admin_bar_turn_off[]' value='author' /> Author</label></p>
    <p><label><input type='checkbox' name='clean_admin_bar_turn_off[]' value='contributor' /> Contributor</label></p>
    <p><label><input type='checkbox' name='clean_admin_bar_turn_off[]' value='subscriber' /> Subscriber</label></p>
    </fieldset>
    <p>Disable admin bar by default for <strong>new users</strong> with the following user roles: </p>
    <fieldset>
    <p><label><input type='checkbox' name='clean_admin_bar_turn_off_by_default[]' value='administrator' <?php if (in_array('administrator',$clean_admin_bar_removal_default_roles)) echo ' checked="checked";' ?>/> Administrator</label></p>
    <p><label><input type='checkbox' name='clean_admin_bar_turn_off_by_default[]' value='editor' <?php if (in_array('editor',$clean_admin_bar_removal_default_roles)) echo ' checked="checked";' ?>/> Editor</label></p>
    <p><label><input type='checkbox' name='clean_admin_bar_turn_off_by_default[]' value='author' <?php if (in_array('author',$clean_admin_bar_removal_default_roles)) echo ' checked="checked";' ?>/> Author</label></p>
    <p><label><input type='checkbox' name='clean_admin_bar_turn_off_by_default[]' value='contributor' <?php if (in_array('contributor',$clean_admin_bar_removal_default_roles)) echo ' checked="checked";' ?>/> Contributor</label></p>
    <p><label><input type='checkbox' name='clean_admin_bar_turn_off_by_default[]' value='subscriber'<?php if (in_array('subscriber',$clean_admin_bar_removal_default_roles)) echo ' checked="checked";' ?> /> Subscriber</label></p>
    </fieldset>
    <br />
    <p> <input type="submit" id="submit" value="Save Changes" class="button-primary" /> </p> <br />
    </form>
    <p>Did this plugin help you out today? Please consider sending some cash 
    via <a href="https://www.dwolla.com/u/812-725-2609">Dwolla</a> to say thanks. </p>
    <p><a href="https://www.dwolla.com/u/812-725-2609"><img src="http://xiann.com/images/btn-donate-with-dwolla.png" alt="Donate" /></a></p>
    </div>
    <?php 
}

// Automatic action when new users register 
add_action('user_register','clean_admin_bar_default_register');
function clean_admin_bar_default_register($user_ID) {
	$clean_admin_bar_removal_default_roles = get_option('clean_admin_bar_removal_default_roles'); 
	if (!$clean_admin_bar_removal_default_roles) $clean_admin_bar_removal_default_roles = array(); 
	$role = clean_admin_bar_get_user_role($user_ID); 
	if (in_array($role, $clean_admin_bar_removal_default_roles)) update_user_meta( $user_ID, 'show_admin_bar_front', 'false' );
} 

// Function to get user role string by user ID 
function clean_admin_bar_get_user_role($user_ID) { 
	global $wpdb;
	$metaname = $wpdb->prefix."capabilities"; 
	$capabilities = get_user_meta($user_ID, $metaname, true);
	if ($capabilities['administrator']) return "administrator"; 
	if ($capabilities['editor']) return "editor"; 
	if ($capabilities['author']) return "author"; 
	if ($capabilities['contributor']) return "contributor"; 
	if ($capabilities['subscriber']) return "subscriber"; 
	return false;
}

