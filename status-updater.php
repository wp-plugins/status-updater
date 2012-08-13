<?php
/*
Plugin Name: Facebook Status Updater
Plugin URI: http://www.gnweekly.tk:81/fb_status_updater.zip
Description: Allows direct sharing to Facebook
Version: 2.0
Author: Joel Dentici
Author URI: http://joeldentici.tk
License: Free
*/
/* Fire our meta box setup function on the post editor screen. */
add_action( 'load-post.php', 'statusupdater_meta_boxes_setup' );
add_action( 'load-post-new.php', 'statusupdater_meta_boxes_setup' );
function statusupdater_meta_boxes_setup() {

	/* Add meta boxes on the 'add_meta_boxes' hook. */
	add_action( 'add_meta_boxes', 'statusupdater_add_post_meta_boxes' );
}
$items = "";
function statusupdater_add_post_meta_boxes() {
global $post, $items;
$postid = $post->ID;
$statusupdater_options_json = get_option('statusupdater_options');
$statusupdater_options = json_decode($statusupdater_options_json, true);
if ($statusupdater_options['setup'] == 'yes'){
$statusupdater_posts_json = get_option('statusupdater_posts', "");
if ($statusupdater_posts_json != "")
	$statusupdater_posts = json_decode($statusupdater_posts_json, true);
else{
	$statusupdater_posts = array();
	add_option('statusupdater_posts', json_encode($statusupdater_posts));
} 
$profile = getPage("https://graph.facebook.com/me?access_token=".$statusupdater_options['fb-token']);
$pages = getPage("https://graph.facebook.com/me/accounts?access_token=".$statusupdater_options['fb-token']);
$profile = json_decode($profile);
$pages = json_decode($pages);
if ($statusupdater_options[$profile->id])
{
    $item = "<tr><td>Post to {$profile->name}</td><td><input type='checkbox' id=\"statusupdater-0\" name='statusupdater-0' value='yes' checked='checked' ></td></tr>";
	if (isset($statusupdater_posts[$profile->id]))
	{
		if (in_array($postid, $statusupdater_posts[$profile->id])){
			//Repeat Text
			$item = "<tr><td>Re-Post to {$profile->name}</td><td><input type='checkbox' id=\"statusupdater-0\" name='statusupdater-0' value='yes'></td></tr>";
		}
	}
	$items .= $item;
}
$i = 1;
foreach ($pages->data as $page)
{
	if ($statusupdater_options[$page->id])
	{
    	$item = "<tr><td>Post to {$page->name}</td><td><input id=\"statusupdater-{$i}\" type='checkbox' name='statusupdater-{$i}' value='yes' checked='checked' ></td></tr>";
		if (isset($statusupdater_posts[$page->id]))
		{
			if (in_array($postid, $statusupdater_posts[$page->id])){
			//Repeat Text
				$item = "<tr><td>Re-Post to {$page->name}</td><td><input type='checkbox' id=\"statusupdater-{$i}\" name='statusupdater-{$i}' value='yes'></td></tr>";
			}
		}
		$items .= $item;
	}
	$i++;
}
	add_meta_box(
		'statusupdater-post-class',			// Unique ID
		esc_html__( 'Status Updater', 'statusupdater' ),		// Title
		'statusupdater_post_class_meta_box',		// Callback function
		'post',					// Admin page (or post type)
		'side',					// Context
		'high'					// Priority
	);
}
}
/* Display the post meta box. */
function statusupdater_post_class_meta_box( $object, $box ) { 
global $items;
?>
	<?php wp_nonce_field( basename( __FILE__ ), 'statusupdater_post_class_nonce' ); ?>

	<p>
		<table border=0 cellspacing=5>
		<?php echo ($items == "") ? "<h4>No Timelines Enabled</h4>" : $items; ?>
		</table>
	</p>
<?php }
function my_excerpt($text, $excerpt)
{
    if ($excerpt) return $excerpt;

    $text = strip_shortcodes( $text );

    $text = apply_filters('the_content', $text);
    $text = str_replace(']]>', ']]&gt;', $text);
    $text = strip_tags($text);
    $excerpt_length = apply_filters('excerpt_length', 55);
    $excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');
    $words = preg_split("/[\n\r\t ]+/", $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY);
    if ( count($words) > $excerpt_length ) {
            array_pop($words);
            $text = implode(' ', $words);
            $text = $text . $excerpt_more;
    } else {
            $text = implode(' ', $words);
    }

    return apply_filters('wp_trim_excerpt', $text, $raw_excerpt);
}
add_action( 'admin_menu', 'statusupdater_menu' );
function statusupdater_menu() {
	add_menu_page( 'Status Updater', 'Status Updater', 'manage_options', 'statusupdater', 'statusupdater_options_page' );
	add_filter( 'plugin_action_links', 'statusupdater_options_links', 10, 2 );
	function statusupdater_options_links( $links, $file ) {
		if ( $file != plugin_basename( __FILE__ )) return $links;
		$settings_link = '<a href="options-general.php?page=statusupdater">Settings</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}
}

add_action( 'admin_init', 'statusupdater_settings' );
function statusupdater_settings() {
	register_setting( 'statusupdater', 'statusupdater_options' );
	if (get_option('statusupdater_do_activation_redirect', false)) {
        delete_option('statusupdater_do_activation_redirect');
        wp_redirect(admin_url('admin.php?page=statusupdater'));
    }
}
add_action( 'publish_post', 'pushPost' );
function pushPost($postid)
{
$statusupdater_options_json = get_option('statusupdater_options');
$statusupdater_options = json_decode($statusupdater_options_json, true);
if ($statusupdater_options['setup'] == 'yes')
{
$statusupdater_posts_json = get_option('statusupdater_posts', "");
if ($statusupdater_posts_json != "")
	$statusupdater_posts = json_decode($statusupdater_posts_json, true);
else{
	$statusupdater_posts = array();
	add_option('statusupdater_posts', json_encode($statusupdater_posts));
}
$post = get_post($postid);
$postSummary = my_excerpt($post->post_content, get_the_excerpt());
   		    $postData = "&link=".get_permalink($postid);
   		    $featuredImage = wp_get_attachment_url( get_post_thumbnail_id($postid) );
   		    $postData .= "&picture=".($featuredImage != "" ? $featuredImage : $statusupdater_options['default_image']);
   		    $postData .= "&name={$post->post_title} - ".get_bloginfo('name');
		    $postData .= "&caption=".network_site_url('/');
			$postData .= "&description=".$postSummary;
   $profile = getPage("https://graph.facebook.com/me?access_token=".$statusupdater_options['fb-token']);
   $pages = getPage("https://graph.facebook.com/me/accounts?access_token=".$statusupdater_options['fb-token']);
    $profile = json_decode($profile);
   $pages = json_decode($pages);
   //Check if profile is enabled in settings
   if ($statusupdater_options[$profile->id] && $_POST['statusupdater-0'] == 'yes')
   {
      if (!isset($statusupdater_posts[$profile->id]))
      	$statusupdater_posts[$profile->id] = array($postid);
      else{
        if (!in_array($postid, $statusupdater_posts[$profile->id]))
      		array_push($statusupdater_posts[$profile->id], $postid);
      }
      $url = "https://graph.facebook.com/me/feed";
      getPage($url, "access_token={$statusupdater_options['fb-token']}".$postData);
   }
   //Loop through pages and share to enabled ones
   $i = 1;
   foreach ($pages->data as $page)
   {
   		if ($statusupdater_options[$page->id] && $_POST["statusupdater-".$i] == 'yes')
   		{
      		if (!isset($statusupdater_posts[$page->id]))
      			$statusupdater_posts[$page->id] = array($postid);
      		else{
       			 if (!in_array($postid, $statusupdater_posts[$page->id]))
      				array_push($statusupdater_posts[$page->id], $postid);
      		}
   		    $url = "https://graph.facebook.com/me/feed";
   		    getPage($url, "access_token={$page->access_token}".$postData);
   		}
   		$i++;
   }
   update_option('statusupdater_posts', json_encode($statusupdater_posts));
}
}
function statusupdater_options_page() {
$statusupdater_options_json = get_option('statusupdater_options');
$statusupdater_options = json_decode($statusupdater_options_json, true);
if (isset($_POST['statusupdater_finished']))
{
	if ($_POST['statusupdater_finished'] == "Finish")
	{
		$statusupdater_options['setup'] = 'yes';
		$statusupdater_options_json = json_encode($statusupdater_options);
		update_option('statusupdater_options', $statusupdater_options_json);
	}
}
if ($statusupdater_options['setup'] == 'yes'){
   $profile = getPage("https://graph.facebook.com/me?access_token=".$statusupdater_options['fb-token']);
   $pages = getPage("https://graph.facebook.com/me/accounts?access_token=".$statusupdater_options['fb-token']);
   $profile = json_decode($profile);
   $pages = json_decode($pages);
   //Update options on pages/profile
   foreach($_POST as $key=>$value)
   {
     if ($value == "yes")
      $statusupdater_options[$key] = true;
     else if ($value == "no")
      $statusupdater_options[$key] = false;
     if ($key == "default_image")
    	$statusupdater_options[$key] = $value;
   }
   update_option('statusupdater_options', json_encode($statusupdater_options));
}
$fbStatusBaseUrl = trailingslashit(get_bloginfo('wpurl')).PLUGINDIR.'/'.dirname(plugin_basename(__FILE__));
if (isset($_POST['fb_app_id']) && isset($_POST['fb_secret']))
{
	if ($_POST['fb_app_id'] != "" && $_POST['fb_secret'] != ""){
		$statusupdater_options['fb_app_id'] = $_POST['fb_app_id'];
		$statusupdater_options['fb_secret'] = $_POST['fb_secret'];
		$statusupdater_options['setup'] = 'step2';
		//Now permanently update
		$statusupdater_options_json = json_encode($statusupdater_options);
		update_option('statusupdater_options', $statusupdater_options_json);
	}
}
if (isset($_GET['removeAccount']))
{
   if ($_GET['removeAccount'] == true)
   {
   	  $statusupdater_options = array('setup' => 'step2', 'fb_app_id' => $statusupdater_options['fb_app_id'], 'fb_secret' => $statusupdater_options['fb_secret']);
   	  update_option('statusupdater_options', json_encode($statusupdater_options));
   	  $statusupdater_posts_json = get_option('statusupdater_posts', "");
	  if ($statusupdater_posts_json != "")
			delete_option('statusupdater_posts');
   }
}
if (isset($_POST['statusupdater_step1']))
{
	if ($_POST['statusupdater_step1'] == "Go Back")
	{
		$statusupdater_options = array('setup' => 'no');
		$statusupdater_options_json = json_encode($statusupdater_options);
		update_option('statusupdater_options', $statusupdater_options_json);
	}
}

?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"></div>
      <h2><a href="admin.php?page=statusupdater">Status Updater</a></h2>
	  <form method="post" action="admin.php?page=statusupdater">
	  <?php 
	  if ($statusupdater_options['setup'] == 'no'){?>
	  	  <div class="postbox">
	  	  		<h3>Setup Step 1</h3>
	  	  		<ol>
	  	  		<li><a target="_blank" href="https://developers.facebook.com/setup">Make a new app on Facebook</a>. You must verify your account with a Phone Number to do this</li>
	  	  		<li>For the URL on the app put: <strong><?php echo network_site_url('/'); ?></strong></li>
	  	  		<li>For the Domain on the app put: <strong><?php echo $_SERVER['SERVER_NAME']; ?></strong></li>
	  	  		<li>Now fill in these details from Facebook:<table border=0 cellspacing=5>
	  	  	    <tr><td>App ID:</td><td><input type="text" name="fb_app_id"></td></tr>
	  	  	    <tr><td>App Secret:</td><td><input type="text" name="fb_secret"></td></tr>
	  	  	    </table></li>
	  	  	    </li>Now click the button below:</li>
	  	  		</ol>
	  	  		<input type="submit" value="Next Step">
	  	  </div>
	  	  <?php }
	  	  else if ($statusupdater_options['setup'] == 'step2')
	  	  {?>
	  	  <div class="postbox">
	  	  		<h3>Setup Step 2</h3>
	  	  		<?php if (isset($_GET['removeAccount']) && $_GET['removeAccount'] == true){?><h3>Your account has been removed from Status Updater. Click <a target="_blank" href="http://www.facebook.com/settings?tab=applications">here</a> to go to Facebook and remove your Application from your profile. Click <a target="_blank" href="https://developers.facebook.com/apps">here</a> to go to Facebook Developers and delete your app.</h3><?php } ?>
	  	  		<span id="startDisplay" style="display: block"><h4>Now you need to log in to Facebook to authorize your app. Make sure to authorize the app to post to every single page that you want to send updates to!</h4>
	  	  		<table border=0 cellspacing=5><tr><td>
	  	  		<a href="javascript:connectWithFacebook();"><img src="<?php echo($fbStatusBaseUrl); ?>/facebook/fb-connect.jpg" alt="Sign in with Facebook"/></a></td><td>Or</td><td><input type="submit" style="height: 24px" name="statusupdater_step1" value="Go Back"></td></tr></table></span>
	  	  		<span id="loadingDisplay" style="display: none"><h4 style="text-align: center">Waiting For Facebook</h4><center><img src="<?php echo($fbStatusBaseUrl); ?>/facebook/loading.gif"></center></span>
	  	  		<span id="readyDisplay" style="display: none"><h4>Facebook is now linked and set up. Click the button below to finish setup.</h4><input type="submit" name="statusupdater_finished" value="Finish"></span>
									<script type="text/javascript">
										function connectWithFacebook() {
										   document.getElementById('startDisplay').style.display = 'none';
										   document.getElementById('loadingDisplay').style.display = 'block';										
										   mywindow = window.open (
												"https://www.facebook.com/dialog/oauth?client_id=<?php echo $statusupdater_options['fb_app_id']; ?>"+
												"&client_secret=<?php echo $statusupdater_options['fb_secret']; ?>"+
												"&redirect_uri="+escape('<?php echo($fbStatusBaseUrl);?>/facebook/callback.php')+
												"&type=user_agent&display=popup&scope=publish_stream,manage_pages,offline_access","facebookconnect","status=0,toolbar=0,location=0,menubar=0,directories=0,resizable=1,scrollbars=0,height=400,width=700"); 
										   setInterval(function(){if(mywindow.closed && document.getElementById('loadingDisplay').style.display == 'block'){document.getElementById('loadingDisplay').style.display = 'none'; document.getElementById('startDisplay').style.display = 'block';}},1000);
										}
			    </script>
	  	  </div>
	  	  <?php }
	  	  else if($statusupdater_options['setup'] == 'yes')
	  	  {
	  	  
//$statusupdater_posts_json = get_option('statusupdater_posts', "");
//$statusupdater_posts = json_decode($statusupdater_posts_json, true);
//var_dump($statusupdater_posts);
	  	  ?>
	  	  <div class="postbox">
	  	  		<h3>Manage Status Updater Settings</h3>
	  	  		<h4>Connected To Facebook Account: <?php echo $profile->name; ?><br><a href="<?php echo($_SERVER["REQUEST_URI"]); ?>&removeAccount=true">Remove Account</a> </h4>
				<table width="100%">
				<tr><th>Timeline</th><th>Enable Sharing</th><th>Disable Sharing</th></tr>
				<tr><td align=center><?php echo $profile->name; ?></td><td align=center><input type="radio" name="<?php echo $profile->id; ?>" value="yes" <?php echo ($statusupdater_options[$profile->id] ? "checked=checked" : ""); ?>></td><td align=center><input type="radio" name="<?php echo $profile->id; ?>" value="no" <?php if (isset($statusupdater_options[$profile->id])){echo ($statusupdater_options[$profile->id] ? "" : "checked=checked");}else{echo "checked=checked";} ?>></td></tr>
				<?php
				foreach($pages->data as $page)
				{?>
				   <tr><td align=center><?php echo $page->name; ?></td><td align=center><input type="radio" name="<?php echo $page->id; ?>" value="yes" <?php echo ($statusupdater_options[$page->id] ? "checked=checked" : ""); ?>></td><td align=center><input type="radio" name="<?php echo $page->id; ?>" value="no" <?php if (isset($statusupdater_options[$page->id])){echo ($statusupdater_options[$page->id] ? "" : "checked=checked");}else{echo "checked=checked";} ?>></td></tr>
				<?php
				}?>
				</table>
				<center>Default Post Image: <input type="text" name="default_image" value="<?php echo $statusupdater_options['default_image']; ?>"></center>
	  	  		<center><input type="submit" value="Update"></center>
	  	  </div>
	  	  <div class="postbox">
	  	  <h3>Instructions</h3>
	  	  <h4>How to get the plugin to share posts</h4>
	  	  <ol>
	  	  <li>Enable the Timelines above that you want to allow posting to. Do not enable other Timelines or any user that is a Contributor or greater will be able to post to them!</li>
	  	  <li>Add the url for the default image you want shared on Facebook. This image will be used when a post has no featured image.</li>
	  	  <li>Add or Edit a new Post</li>
	  	  <li>Find the Post Meta Box called "Status Updater"</li>
	  	  <li>I recommend that you move the box right above the "Publish" box</li>
	  	  <li>Timelines that you haven't shared this post to will be checked. Timelines that you have shared this post to will be unchecked.</li>
	  	  <li>Check the appropriate checkboxes</li>
	  	  <li>Publish the post</li>
	  	  <li>Your post will now be shared to the Timelines you selected</li>
	  	  </ol>
	  	  </div>
	  	  <?php } ?>
	  </form>
	</div>
<?php
}
function do_post_request($url, $data = null, $optional_headers = null)
{
  
  if ($data == null)
  {
     
  $params = array('http' => array(
              'method' => 'GET',
            ));
  }
  else
  {
    
  $params = array('http' => array(
              'method' => 'POST',
              'content' => $data
            ));
  }
  if ($optional_headers !== null) {
    $params['http']['header'] = $optional_headers;
  }
  $ctx = stream_context_create($params);
  $fp = @fopen($url, 'rb', false, $ctx);
  if (!$fp) {
    throw new Exception("Problem with $url, $php_errormsg");
  }
  $response = @stream_get_contents($fp);
  if ($response === false) {
    throw new Exception("Problem reading data from $url, $php_errormsg");
  }
  return $response;
}
function getPage2($url, $postData = null) {
	if ($postData == null)
		return do_post_request($url);
	else
		return do_post_request($url, $postData);
}
function getPage($url, $postData = null) {

	if (!function_exists("curl_init")) {
		return getPage2($url, $postData);
	}
  
	global $fbStatusUpdatePath, $fbStatusCookieFile;

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, false);
	if(!ini_get('safe_mode') && !ini_get("open_basedir")) {
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	}
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  //curl_setopt($ch, CURLOPT_COOKIEJAR, $fbStatusCookieFile);
  //curl_setopt($ch, CURLOPT_COOKIEFILE, $fbStatusCookieFile);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_REFERER, $url);
	curl_setopt($ch, CURLOPT_USERAGENT, "Status Updater app running for ".$_SERVER['HTTP_HOST']);

	if ($postData != null) {
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		curl_setopt($ch, CURLOPT_POST, true);
	}

	$response = curl_exec($ch);
	curl_close($ch);

	unset($ch);

	if ($response !== false && $response !== null) { // && $response != ""
		return $response;
	} else {
		return null;
	}

}

register_activation_hook( __FILE__, 'statusupdater_activate' );
function statusupdater_activate() {
	require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
	$statusupdater_options_default = json_encode(array('setup' => 'no'));
	add_option('statusupdater_options', $statusupdater_options_default);
    add_option('statusupdater_do_activation_redirect', true);
}

?>