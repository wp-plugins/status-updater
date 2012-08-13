<?php
if (isset($_GET['token'])){
//Update token
require (getenv("DOCUMENT_ROOT") . '/wp-config.php');
$statusupdater_options_json = get_option('statusupdater_options');
$statusupdater_options = json_decode($statusupdater_options_json, true);
if ($statusupdater_options['setup'] == 'step2') //Only continue if setup is at exact step
{
   //Add the FB token
   $statusupdater_options['fb-token'] = $_GET['token'];
   $statusupdater_options_json = json_encode($statusupdater_options);
   update_option('statusupdater_options', $statusupdater_options_json);
   ?>
   <script type="text/javascript">
   if (window.opener && !window.opener.closed) {
      window.opener.document.getElementById('loadingDisplay').style.display = 'none';
	  window.opener.document.getElementById('readyDisplay').style.display = 'block';
	  self.close();
   }
   </script> <?php
}
else{
	echo "<p>Error: You have already set up Facebook Updater or have not yet reached this step!</p>";
}
}
else{

?>
<script type="text/javascript">
if (window.opener && !window.opener.closed) {
	var url = window.location.href;
	var accessToken = url.substring(url.indexOf("#access_token=") + 14, url.lastIndexOf("&"));
	window.location.href = url.substring(0, url.indexOf("#access_token=")) + "?token=" + unescape(accessToken);
	}
	else
	{
	document.write("<p>Cannot communicate with the setup page. Nothing has been done and I am aborting!</p>");
	}
</script>
<?php }/*
<script type="text/javascript">
if (window.opener && !window.opener.closed) {
	document.write("<p>Your blog is now linked to your Facebook account. Close this window AND save the WP settings page after filling all the fileds.</p>");
} else {
	document.write("<p>There's a problem, this window can't talk with the opener one, there's some data they need to exchange. If you didn't close the main window, try checking your browser security / privacy settings and retry</p>");
}
</script>
*/
?>
	