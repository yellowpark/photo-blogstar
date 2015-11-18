<?php
/**
 * Plugin Name: Photo Blogstar
 * Plugin URI: http://thedalby.uk/photo-blogstar-wordpress-plugin/
 * Description: This plugin adds instagram photos to the homepage.
 * Version: 1.0.0
 * Author: Chris Dalby
 * Author URI: http://thedalby.uk
 * License: GPL2
 */

add_action('admin_menu', 'my_plugin_menu');

function my_plugin_menu() {

	$parent_slug = "themes.php";
	$page_title = "Photo Blogstar Settings";
	$menu_title = "Photo blogstar";
	$capability = "administrator";
	$menu_slug = "photo-blogstar-settings";
	$function = "my_plugin_settings_page";

	add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
}

function my_plugin_settings_page(){
	//must check that the user has the required capability
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }

    // variables for the field and option names
    $opt_name_client_id = 'mt_client_id';
    $opt_name_user_id = 'mt_user_id';

    $hidden_field_name = 'mt_submit_hidden';
    $data_field_name_client_id = 'mt_client_id';
    $data_field_name_user_id = 'mt_user_id';

    // Read in existing option value from database
    $opt_val_client_id = get_option( $opt_name_client_id );
    $opt_val_user_id = get_option( $opt_name_user_id );

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
        // Read their posted value
        $opt_val_client_id = $_POST[ $data_field_name_client_id ];
        $opt_val_user_id = $_POST[ $data_field_name_user_id ];

        // Save the posted value in the database
        update_option( $opt_name_client_id, $opt_val_client_id );
        update_option( $opt_name_user_id, $opt_val_user_id );

        // Put a "settings saved" message on the screen

?>
<div class="updated"><p><strong><?php _e('settings saved.', 'menu-blogstar' ); ?></strong></p></div>
<?php

    }

    // Now display the settings editing screen

    echo '<div class="wrap">';

    // header

    echo "<h2>" . __( 'Photo blogstar Settings', 'menu-blogstar' ) . "</h2>";
	echo "<p>" . __( 'The Client ID and the user ID are obtained from Instagram.  To get the Client ID, you need to visit <a href="http://instagram.com/developer/">instagram.com/developer/</a> and register your application.', 'menu-blogstar' ) . "</p>";
	echo "<p>" . __( 'The User ID is not the same as your username.  To find out your User ID, go to <a href="http://jelled.com/instagram/lookup-user-id">jelled.com/instagram/lookup-user-id</a> and lookup your username.', 'menu-blogstar' ) . "</p>";
	echo "<p>" . __( 'A User ID lookup function will be added to the Photo Blogstar plugin in a future release.', 'menu-blogstar' ) . "</p>";
    // settings form

    ?>

<form name="form1" method="post" action="">
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

<p><?php _e("Client ID:", 'menu-blogstar' ); ?>
&nbsp;&nbsp;<input type="text" name="<?php echo $data_field_name_client_id; ?>" value="<?php echo $opt_val_client_id; ?>" size="30">
</p>

<p><?php _e("User ID:", 'menu-blogstar' ); ?>
&nbsp;&nbsp;<input type="text" name="<?php echo $data_field_name_user_id; ?>" value="<?php echo $opt_val_user_id; ?>" size="30">
</p>

<hr />

<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
</p>

</form>
</div>

<?php

}

function showInstagram(){

	$clientid = get_option( 'mt_client_id', '0' );
	$userid = get_option( 'mt_user_id', '0' );

	$photoUrl = "https://api.instagram.com/v1/users/" . $userid . "/media/recent/?client_id=" . $clientid . "&count=6";

	try {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $photoUrl);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 90);
		$jsonData = curl_exec($ch);
		curl_close($ch);

		//return $jsonData;
		echo "<div style=\"text-align: center; padding-top: 10px;\">";
		$i = 0;
		foreach(json_decode($jsonData)->data as $item){

			$title = (isset($item->caption))?mb_substr($item->caption->text,0,70,"utf8"):null;

			$src = $item->images->thumbnail->url;
			$link = $item->link;
			echo "<a href=\"" . $link . "\"><img src=\"" . $src . "\ title=\"" . $title . "\" /></a>&nbsp;&nbsp;";
			$i++;
		}

		if ($i > 0){
			echo "<p>" . __( 'Instagram photos powered by <a href="http://thedalby.uk/photo-blogstar">Photo Blogstar</a>.  A Wordpress plugin by <a href="http://thedalby.uk">Chris Dalby</a>', 'menu-blogstar' ) . "</p>";
		}
		echo "</div>";

	} catch(Exception $e) {
		echo "<p>" . __( 'There was an issue with displaying your instagram photos.', 'menu-blogstar' ) . "</p>";
		echo "<p>" . $e->getMessage() . "</p>";
	}

}