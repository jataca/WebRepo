<?php
	
    // +___________________________________________________________________________+
	// | Create Sidebar Menu                                                       |
	// +___________________________________________________________________________+
	
	
	add_action('admin_menu', 'mow_allinone');
	function mow_allinone(){
		add_menu_page('MOW SEO', 'MOW SEO', 'manage_options', 'mow-allinone-addon.php', 'mow_allinone_admin_options' ,plugins_url('/mow-allinone-addon/images/icon.png'));
	}


add_action('wp_head', 'mow_noindex');






if (function_exists('mow_dashboard_function')) {
 
} else {
    

function mow_dashboard_function() {
include_once(ABSPATH . WPINC . '/rss.php');
wp_rss('http://marketingonlineworkshop.com/feed/', 5); 
?>
<p align="right">
<a href="http://marketingonlineworkshop.com/compare-memberships/" class="button-primary" target="_blank">Join Now FREE</a></p>
<?php
} 


function MOW_DASHBOARD() {
	wp_add_dashboard_widget('example_dashboard_widget', 'Marketing Online Workshop Latest Tutorials', 'mow_dashboard_function');	
} 


add_action('wp_dashboard_setup', 'MOW_DASHBOARD' );

}










function mow_noindex() {
global $post;
$thePostID = $post->ID;
global $wpdb;
$post_meta=$wpdb->prefix . "postmeta";
$result5 = mysql_query("SELECT * FROM $post_meta WHERE post_id = '$thePostID' AND meta_key='mow_noindex_page'");
$num_rows5 = mysql_num_rows($result5);

if ($num_rows5 > '0'){	

        	echo "<meta name=\"robots\" content=\"noindex, follow\"/>\n";
 } 
	}
	



	
	function mow_allinone_admin_options() {

		$linkss = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
		
		
		if ($_REQUEST['Update2']){
		update_option('mow_seo_title', $_POST[maxtitle]);
update_option('mow_seo_des', $_POST[maxdes]);
update_option('mow_seo_error', $_POST[errors]);
update_option('mow_seo_page_post', $_POST[pageorpost]);
		}
		




$mow_seo_title=get_option('mow_seo_title');
$errors=get_option('mow_seo_error');
$pagepost=get_option('mow_seo_page_post');
$mow_seo_des=get_option('mow_seo_des');
$homeurl=get_option('siteurl');		
$page_on_front=get_option('page_on_front');

		
if(get_option('mow_seo_installed') != 'yes') {
update_option('mow_seo_title', '60');
update_option('mow_seo_errors', 'all');
update_option('mow_seo_des', '160');
update_option('mow_seo_installed', 'yes');
update_option('mow_seo_page_post', 'both');
$mow_seo_page_post = 'both';
$mow_seo_des ='160';
$mow_seo_errors='all';
$mow_seo_title='60';
}		

	
		
		
?>		
		
		
		
<head>
<link rel="stylesheet" href="<?php  echo $linkss; ?>/style.css" type="text/css" />
</head>
</p>
<div class="container">
<div class="header"><div class="menu"><a target="_blank" href="http://marketingonlineworkshop.com">The Marketing Online Workshop</a></div></div>
		
	
	<!--------------------------------------------ADD pages After Submit--------------------------------------------->
<?php
	


		
		if ($_REQUEST['Update']){
		
		global $wpdb;
   $table_name = $wpdb->prefix . "postmeta";
	$table_name2 = $wpdb->prefix . "posts";
	

		$ppid=$_POST[ppid];
		$keywords=$_POST[keywords];
		$title=$_POST[title];
		$des=$_POST[des];
		
		$permalink = $_POST[permalink];
	$permalink = str_replace(" ", "-", $permalink);
				$permalink=strtolower($permalink);
	
		
mysql_query("UPDATE $table_name2 SET 
post_title = '$_POST[headline]',
post_name = '$permalink'
WHERE ID = '$ppid' ");		
		
		
		
$result1 = mysql_query("SELECT * FROM $table_name WHERE post_id = '$ppid' AND meta_key='_aioseop_title'");
$num_rows1 = mysql_num_rows($result1);
if ($num_rows1 > '0'){	
	
mysql_query("UPDATE $table_name SET 
meta_value = '$title'
WHERE post_id = '$ppid' AND meta_key='_aioseop_title'
");

}else {



mysql_query("INSERT INTO $table_name (post_id,meta_key,meta_value)
				  VALUES
				  ('$ppid]','_aioseop_title','$title' )") or die(mysql_error());

}



$result2 = mysql_query("SELECT * FROM $table_name WHERE post_id = '$ppid' AND meta_key='_aioseop_description'");
$num_rows2 = mysql_num_rows($result2);
if ($num_rows2 > '0'){	

mysql_query("UPDATE $table_name SET 
meta_value = '$des'
WHERE post_id = '$ppid' AND meta_key='_aioseop_description'
");

}else {


mysql_query("INSERT INTO $table_name (post_id,meta_key,meta_value)
				  VALUES
				  ('$ppid]','_aioseop_description','$des' )") or die(mysql_error());


}




$result3 = mysql_query("SELECT * FROM $table_name WHERE post_id = '$ppid' AND meta_key='_aioseop_keywords'");
$num_rows3 = mysql_num_rows($result3);
if ($num_rows3 > '0'){	

mysql_query("UPDATE $table_name SET 
meta_value = '$keywords'
WHERE post_id = '$ppid' AND meta_key='_aioseop_keywords'
");

}else {

mysql_query("INSERT INTO $table_name (post_id,meta_key,meta_value)
				  VALUES
				  ('$ppid]','_aioseop_keywords','$keywords' )") or die(mysql_error());

}





$noindex_pages = $_POST['noindex'];

$post_meta=$wpdb->prefix . "postmeta";
if ($noindex_pages == 'no'){


$result2 = mysql_query("SELECT * FROM $post_meta WHERE post_id = '$ppid' AND meta_key='mow_noindex_page'");
$num_rows2 = mysql_num_rows($result2);

if ($num_rows2 > '0'){	

}else {
mysql_query("INSERT INTO $post_meta (post_id,meta_key,meta_value)
				  VALUES
				  ('$ppid','mow_noindex_page','yes' )") or die(mysql_error());

}
}




if ($noindex_pages == 'yes'){
mysql_query("DELETE FROM $post_meta WHERE post_id='$ppid' AND meta_key='mow_noindex_page' ");
}






global $wp_rewrite;
   	$wp_rewrite->flush_rules();
						?>
	<div class="success">Page/Post Updated Successfully!</div>
<?php
 } ?>
 
 
 
<!--------------------------------------------ADD pages form--------------------------------------------->


<div class="text1">
<?php if ($_REQUEST['Edit'] != "Edit"){


if ($_REQUEST['Update'] != "Update"){

?>
<h2>Select Page/Post</h2>
<div class="icons"></div>

<div class="tut"><a href="http://marketingonlineworkshop.com/seo/mow-seo-plugin-overview/" target="_blank"></a></div>
	<form method="post" name="Update2">
Max Title Length 
<input type="text" name="maxtitle" size="3" value="<?php echo $mow_seo_title; ?>"> Max Description 
Length 
<input type="text" name="maxdes" size="3" value="<?php echo $mow_seo_des; ?>"> Show :
<select name="errors" size="1">

<?php if ($errors == 'issues'){?>
<option value="issues">Only Errors</option>
<option value="all">Show All</option>
<?php } else { ?>
<option value="all">All</option>
<option value="issues">Only Errors</option>
<?php } ?>

</select>




Page/Post :
<select name="pageorpost" size="1">


<?php if ($pagepost == NULL){?>
<option value="both">Both</option>
<option value="pages">Only Pages</option>
<option value="posts">Only Posts</option>
<?php } ?>

<?php if ($pagepost == 'posts'){?>
<option value="posts">Only Posts</option>
<option value="pages">Only Pages</option>
<option value="both">Both</option>
<?php } ?>

<?php if ($pagepost == 'pages'){?>
<option value="pages">Only Pages</option>
<option value="posts">Only Posts</option>
<option value="both">Both</option>
<?php } ?>

<?php if ($pagepost == 'both'){?>
<option value="both">Both</option>
<option value="pages">Only Pages</option>
<option value="posts">Only Posts</option>
<?php } ?>

</select>



<input name="Update2" type="Submit" class="button-primary" id="Update2" value="Update">












<?php
if ($errors == 'all'){
?>


<table border="0" width="100%" cellspacing="0" cellpadding="0" class="widefat">
	<tr>
		<td><h4>Page/Post Name</h4></td>
		<td  align="center"><h4>Title Tag</h4></td>
		<td  align="center"><h4>Description</h4></td>
		<td  align="center"><h4>Keywords</h4></td>
		<td align="center"><h4>Edit</h4></td>	</tr>
<?php
if(!is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php')) {
?>
<div class="success">The All In One SEO Pack Is Currently Not Installed Or Is Inactive. Please Install It Before Using This Plugin</div>
<?php				
			}
if ($pagepost == NULL){
$posts = get_posts("post_type=any&post_status=publish&numberposts=-1");
}

if ($pagepost == 'both'){
$posts = get_posts("post_type=any&post_status=publish&numberposts=-1");
}
if ($pagepost == 'pages'){
$posts = get_posts("post_type=page&post_status=publish&numberposts=-1");
}

if ($pagepost == 'posts'){
$posts = get_posts("post_type=post&post_status=publish&numberposts=-1");
}

foreach($posts as $post) {

$ppid=$post->ID;
$permalink=$post->post_name;
$posttitle=$post->post_title;
$keywords = htmlspecialchars(stripcslashes(get_post_meta($ppid, '_aioseop_keywords', true)));
$title = htmlspecialchars(stripcslashes(get_post_meta($ppid, '_aioseop_title', true)));
$des = htmlspecialchars(stripcslashes(get_post_meta($ppid, '_aioseop_description', true)));

if ($page_on_front == $ppid){		
$aioseop_options = get_option('aioseop_options');
$title=$aioseop_options['aiosp_home_title'];
$des=$aioseop_options['aiosp_home_description'];
$keywords=$aioseop_options['aiosp_home_keywords'];
}





?>
	<tr>
		<td valign="top" width="45%"><b>
		
	
		<?php echo $posttitle; ?>
		
		<?php if ($page_on_front == $ppid){
		?>
		<br />
		<font size="1" color="#FF8040">This Is The Home Page</font></b><font size="1"><font color="#FF8040"></font> 
		<?php
		} else { ?>
		<br />
		<font size="1" color="#0000FF">permalink</font></b><font size="1"><font color="#0000FF">:</font> /<?php echo $permalink; ?>/
		</font>
		<?php } ?>
		
		
		</td>
		
		<td valign="top" width="15%" align="center">
		
		
		

		
		
		
		
		
		
		
		<?php if ($title == NULL){?><img src="<?php  echo $linkss; ?>/images/delete.png"><?php 
		$count=$count+1;
		
		
		}else{ ?>
		<?php
		$num_char_title=strlen(utf8_decode($title));
		if ($num_char_title > $mow_seo_title){
		
		?>
		<img src="<?php  echo $linkss; ?>/images/warning.png">
		<?php 
		$count=$count+1;
		
		}else{
			?>
		<img src="<?php  echo $linkss; ?>/images/check.png">
		<?php } ?>
		
		<?php } ?>
		
		
		
		
		
		
		
		
		</td>
		
		<td valign="top" width="15%" align="center"><?php if ($des == NULL){?><img src="<?php  echo $linkss; ?>/images/delete.png"><?php 
		$count=$count+1;
		
		}else{ ?>
		
		
		<?php
		$num_char_title=strlen(utf8_decode($des));
		if ($num_char_title > $mow_seo_des){
		
		?>
		<img src="<?php  echo $linkss; ?>/images/warning.png">
		<?php 
		
		$count=$count+1;
		
		}else{
			?>
		<img src="<?php  echo $linkss; ?>/images/check.png">
		<?php } ?>
	
	
	<?php } ?></td>
		
		<td valign="top" width="15%" align="center"><?php if ($keywords == NULL){
		
		
		$count=$count+1;
		
		
		?><img src="<?php  echo $linkss; ?>/images/delete.png"><?php 
		
		
		}else{ ?><img src="<?php  echo $linkss; ?>/images/check.png"><?php } ?></td>
		
		
		<td valign="top" width="10%" align="center">
		
		
<?php if ($page_on_front == $ppid){
		?>


<form method="post" name="Select" value="Select" action="<?php echo $homeurl; ?>/wp-admin/options-general.php?page=all-in-one-seo-pack/aioseop.class.php">
<input type="hidden" name="ppid" value="<?php  echo $ppid; ?>">
<input class="button-primary" type="Submit" name="Edit" id="Edit" value="Edit" >






<?php }else{ ?>	
		
		
<form method="post" name="Select" value="Select">
<input type="hidden" name="ppid" value="<?php  echo $ppid; ?>">

<p>

<input class="button-primary" type="Submit" name="Edit" id="Edit" value="Edit" >


<?php } ?>



</form>
</td>
	</tr>













<?php
}
}


if ($errors == 'issues'){
?>







<table border="0" width="100%" cellspacing="0" cellpadding="0" class="widefat">
	<tr>
		<td><h4>Page/Post Name</h4></td>
		<td  align="center"><h4>Title Tag</h4></td>
		<td  align="center"><h4>Description</h4></td>
		<td  align="center"><h4>Keywords</h4></td>
		<td align="center"><h4>Edit</h4></td>	</tr>
<?php
if(!is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php')) {
?>
<div class="success">The All In One SEO Pack Is Currently Not Installed Or Is Inactive. Please Install It Before Using This Plugin</div>
<?php				
			}

if ($pagepost == 'both'){
$posts = get_posts("post_type=any&post_status=publish&numberposts=-1");
}
if ($pagepost == 'pages'){
$posts = get_posts("post_type=page&post_status=publish&numberposts=-1");
}

if ($pagepost == 'posts'){
$posts = get_posts("post_type=post&post_status=publish&numberposts=-1");
}

$count = '0';


foreach($posts as $post) {

$ppid=$post->ID;
$permalink=$post->post_name;
$posttitle=$post->post_title;
$keywords = htmlspecialchars(stripcslashes(get_post_meta($ppid, '_aioseop_keywords', true)));
$title = htmlspecialchars(stripcslashes(get_post_meta($ppid, '_aioseop_title', true)));
$des = htmlspecialchars(stripcslashes(get_post_meta($ppid, '_aioseop_description', true)));
if ($page_on_front == $ppid){		
$aioseop_options = get_option('aioseop_options');
$title=$aioseop_options['aiosp_home_title'];
$des=$aioseop_options['aiosp_home_description'];
$keywords=$aioseop_options['aiosp_home_keywords'];
}


$num_char_title=strlen(utf8_decode($title));
$num_char_des=strlen(utf8_decode($des));
if(empty($title) || empty($des) || empty($keywords) || $num_char_title > $mow_seo_title || $num_char_des > $mow_seo_des) {

?>
	<tr>
		<td valign="top" width="45%"><b><?php echo $posttitle; ?>
		
		<?php if ($page_on_front == $ppid){
		?>
		<br />
		<font size="1" color="#FF8040">This Is The Home Page</font></b><font size="1"><font color="#FF8040"></font> 
		<?php
		} else { ?>
		<br />
		<font size="1" color="#0000FF">permalink</font></b><font size="1"><font color="#0000FF">:</font> /<?php echo $permalink; ?>/
		</font>
		<?php } ?>
		
		
		</td>
		
		<td valign="top" width="15%" align="center"><?php if ($title == NULL){
		$count=$count+1;

		?><img src="<?php  echo $linkss; ?>/images/delete.png"><?php }else{ ?>
		
		<?php
		$num_char_title=strlen(utf8_decode($title));
		if ($num_char_title > $mow_seo_title){
		$count=$count+1;

		?>
		<img src="<?php  echo $linkss; ?>/images/warning.png">
		<?php }else{
			?>
		<img src="<?php  echo $linkss; ?>/images/check.png">
		<?php } ?>
		
		<?php } ?>
		
		
		</td>
		
		<td valign="top" width="15%" align="center"><?php if ($des == NULL){
		$count=$count+1;

		?><img src="<?php  echo $linkss; ?>/images/delete.png"><?php }else{ ?>
		
		
		<?php
		$num_char_title=strlen(utf8_decode($des));
		if ($num_char_title > $mow_seo_des){
		$count=$count+1;

		?>
		<img src="<?php  echo $linkss; ?>/images/warning.png">
		<?php }else{
			?>
		<img src="<?php  echo $linkss; ?>/images/check.png">
		<?php } ?>
	
	
	<?php } ?></td>
		
		<td valign="top" width="15%" align="center"><?php if ($keywords == NULL){
	

		
		?><img src="<?php  echo $linkss; ?>/images/delete.png"><?php 
		
		$count=$count+1;

		}else{ ?><img src="<?php  echo $linkss; ?>/images/check.png"><?php } ?></td>
		
		
		<td valign="top" width="10%" align="center">
	
		
<?php if ($page_on_front == $ppid){
		?>


<form method="post" name="Select" value="Select" action="<?php echo $homeurl; ?>/wp-admin/options-general.php?page=all-in-one-seo-pack/aioseop.class.php">
<input type="hidden" name="ppid" value="<?php  echo $ppid; ?>">

<input class="button-primary" type="Submit" name="Edit" id="Edit" value="Edit" >






<?php }else{ ?>	
		
		
<form method="post" name="Select" value="Select">
<input type="hidden" name="ppid" value="<?php  echo $ppid; ?>">
<p>
<input class="button-primary" type="Submit" name="Edit" id="Edit" value="Edit" >


<?php } ?>


</form>
</td>
	</tr>













<?php
}
}
}
}



























}
?>

</table>


<?php if ($count == '0'){
?>
<center><h3>Congratulations You Have No Errors!</h3> To View All Pages Change The Drop-Down Above to "Show All" and Click Update</center>

<?php
} 

if ($count > '0'){
?>
<center><h3><?php echo $count; ?> Errors Found!</h3></center>


<?php
} 

if ($_REQUEST['Edit'] OR $_REQUEST['Update']){
		
		
$ppid=$_POST[ppid];
		


?>

<form method="post" name="post">

<?php

$keywords = htmlspecialchars(stripcslashes(get_post_meta($ppid, '_aioseop_keywords', true)));
$title = htmlspecialchars(stripcslashes(get_post_meta($ppid, '_aioseop_title', true)));
$des = htmlspecialchars(stripcslashes(get_post_meta($ppid, '_aioseop_description', true)));
$page_data = get_page( $ppid );
$content = apply_filters('the_content', $page_data->post_content);
$pagetitle = $page_data->post_title;
$permalink = $page_data->post_name;

?>
<h2>Edit Page/Post</h2>

<a href="<?php echo $homeurl; ?>/wp-admin/admin.php?page=mow-allinone-addon.php" class="button-primary">< BACK</a> <a href="https://adwords.google.com/select/KeywordToolExternal" target="_blank" class="button-primary">Open Google Keyword External Tool In New Tab</a>  

<a href="<?php echo $homeurl; ?>/wp-admin/post.php?post=<?php echo $ppid; ?>&action=edit" class="button-primary">Open Page/Post In WordPress Editor</a>	<br /><br />
Editing : <?php echo $pagetitle; ?><br />




		<SCRIPT LANGUAGE="JavaScript">
		<!-- Begin
		function countChars(field,cntfield) {
		cntfield.value = field.value.length;
		}
		//  End -->
		</script>

<h3>Title Tag</h3>
<input type="text" name="title" size="80" onKeyDown="countChars(document.post.title,document.post.lengthT)" onKeyUp="countChars(document.post.title,document.post.lengthT)" value="<?php echo $title; ?>" <?php if ($title == NULL){ ?>style="border: 3px double #FF0000"<?php }else{ ?> 



		<?php
		$num_char_title=strlen(utf8_decode($title));
		if ($num_char_title > $mow_seo_title){
		
		?>
	style="border: 3px double #FFFF00"
		<?php }else{
			?>
	style="border: 3px double #00FF00"
		<?php } ?>
		
	
 <?php } ?>>

<input readonly type="text" name="lengthT" size="3" maxlength="3" style="text-align:center;" value="<?php echo strlen(utf8_decode($title));?>" />



<input type="hidden" name="ppid" value="<?php echo $ppid; ?>">
<h3>Description</h3>
<textarea name="des" cols="50" rows="5" onKeyDown="countChars(document.post.des,document.post.length1)"onKeyUp="countChars(document.post.des,document.post.length1)" <?php if ($des == NULL){ ?>style="border: 3px double #FF0000"<?php }else{ ?><?php $num_char_title=strlen(utf8_decode($des)); if ($num_char_title > $mow_seo_des){ ?>	style="border: 3px double #FFFF00" <?php }else{	?> style="border: 3px double #00FF00"<?php } ?> <?php } ?>><?php echo $des; ?></textarea>
<input readonly type="text" name="length1" size="3" maxlength="3" value="<?php echo strlen(utf8_decode($des));?>" />

<h3>Keywords</h3>
<textarea name="keywords" cols="50" rows="5" onKeyDown="countChars(document.post.keywords,document.post.length2)"onKeyUp="countChars(document.post.keywords,document.post.length2)" <?php if ($keywords == NULL){ ?>style="border: 3px double #FF0000"<?php }else{ ?>style="border: 3px double #00FF00"<?php } ?>><?php echo $keywords; ?></textarea>
<input readonly type="text" name="length2" size="3" maxlength="3" value="<?php echo strlen(utf8_decode($keywords));?>" />



<h3>Page Headline</h3>
<input type="text" name="headline" size="80" value="<?php echo $pagetitle; ?>">



<h3>permalink</h3>
<input type="text" name="permalink" size="80" value="<?php echo $permalink; ?>">


<h3>Do You Want This Page Indexed By The Search Engines?</h3>


<?php 

global $wpdb;
$post_meta=$wpdb->prefix . "postmeta";
$result5 = mysql_query("SELECT * FROM $post_meta WHERE post_id = '$ppid' AND meta_key='mow_noindex_page'");
$num_rows5 = mysql_num_rows($result5);

if ($num_rows5 > '0'){	
?>
<select name="noindex" size="1">
<option value="no">no</option>
<option value="yes">Yes</option>

</select>


<?php } else { ?>

<select name="noindex" size="1">
<option value="yes">Yes</option>
<option value="no">no</option>
</select>

<?php } ?>

<br /><br />
	<input name="Update" type="Submit" class="button-primary" id="Update" value="Update">	
</form>

<h3>Google Preview</h3>

<?php
$title2=$title;
$tcount=strlen(utf8_decode($title2));
if ($tcount > '65'){
$title2 = substr($title2, 0, 65); 
$dots="...";
$title2 = "$title2"."$dots";
}?>



<?php
$des2=$des;
$dcount=strlen(utf8_decode($des));
if ($dcount > '155'){
$des2 = substr($des2, 0, 155); 
$dots="...";
$des2 = "$des2"."$dots";
}?>

<div class="googleholder">
<div class="googletitle"><?php echo $title2;?><br></div>
<?php echo $des2; ?><br>
<div class="googlelink"><?php echo $homeurl?>/<?php echo $permalink; ?>/</div> <div class="googlelink2">&nbsp;- Cached - Similar</div>
</div>

<br><br><br><br><br>
<h3>Page/Post Preview</h3>
<h2><?php echo $pagetitle; ?></h2>
<?php echo $content; ?>

<?php
		
		}
		?>


</div></div>

<?php
 }// +___________________________________________________________________________+
// | END                                                                       |
// +___________________________________________________________________________+
 ?>