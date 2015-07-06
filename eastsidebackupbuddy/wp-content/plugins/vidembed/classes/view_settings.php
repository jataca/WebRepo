<?php
$this->_parent->load();
$this->admin_scripts();

if ( !empty( $_POST['save'] ) ) {
	$this->savesettings();
}

// color picker javascript
$colorpicker = "
<script type='text/javascript'>
	jQuery(document).ready(function() {
		jQuery('.vejpicker').jPicker( {
			window: {
				expandable: true,
				alphaSupport: false,
				effects: { type: 'fade' }
			}, 
			images : {  clientPath: '" . $this->_pluginURL . "/images/colorpicker/' }
		});
	});
</script>
";
echo $colorpicker;

// save YouTube player
if ( !empty( $_POST[$this->_var . '-ytsave'] ) ) {
	$this->_ytsave();
}
// save Vimeo player
if ( !empty( $_POST[$this->_var . '-vimsave'] ) ) {
	$this->_vimsave();
}
// save Open source player
if ( !empty( $_POST[$this->_var . '-opsave'] ) ) {
	$this->_opsave();
}

?>
<div class="wrap">
	<h2><?php echo $this->_name; ?> Settings</h2>
	<p>The following forms are the custom settings that will be applied to all occurences of the players on the site.</p>
	<br/>
	<h3>YouTube Player</h3>
	<p>Two themes are now available, Dark and Light, default is set to Dark.</p>
	<form method="post" action="<?php echo $this->_selfLink . '-settings'; ?>">
		<table class="form-table">
			<?php $ytplayer = $this->_options['players']['youtube']; ?>
			<!--<tr>
				<th><label for="ytcolor1">Primary border</label></th>
				<td>#<input type="text" maxlength="6" size="6" class="vejpicker" id="ytcolor1" name="<?php echo $this->_var; ?>-ytcolor1" value="<?php echo $ytplayer['color1']; ?>" /></td>
			</tr>
			<tr>
				<th><label for="ytcolor2">Secondary border and controlbar</label></th>
				<td>#<input type="text" maxlength="6" size="6" class="vejpicker" id="ytcolor2" name="<?php echo $this->_var; ?>-ytcolor2" value="<?php echo $ytplayer['color2']; ?>" /></td>
			</tr>
			<tr>
				<th><label for="ytborder">Add border around player</label></th>
				<td><input type="checkbox" name="<?php echo $this->_var; ?>-ytborder" id="ytborder" <?php if ($ytplayer['border'] == '1') { echo "checked "; } ?>value="1" /></td>
			</tr>-->
			<tr>
				<th><label for="yttheme">Use Light Theme</label></th>
				<td><input type="checkbox" name="<?php echo $this->_var; ?>-yttheme" id="yttheme" <?php if ($ytplayer['theme'] == '1') { echo "checked "; } ?>value="1" /></td>
			</tr>
			<tr>
				<th><label for="ytrelate">Hide related videos <?php $this->_parent->tip('Select yes to hide related videos that normally show after your video is finished.'); ?></label></th>
				<td>
					<label><input type="radio" name="<?php echo $this->_var; ?>-relate" value="false" <?php if ($ytplayer['related'] != 'true') { echo " checked "; } ?> /> No</label><br />
					<label><input type="radio" name="<?php echo $this->_var; ?>-relate" value="true" <?php if ($ytplayer['related'] == 'true') { echo " checked "; } ?>/> Yes</label>
				</td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="<?php echo $this->_var; ?>-ytsave" value="Save Settings" class="button-primary" /></p>
		<?php wp_nonce_field( $this->_var . '-nonce' ); ?>
	</form>
	<br/>
	<h3>Vimeo Player</h3>
	<form method="post" action="<?php echo $this->_selfLink . '-settings'; ?>">
		<table class="form-table">
			<?php $vimplayer = $this->_options['players']['vimeo']; ?>
			<tr>
				<th><label for="vimcolor">Color</label></th>
				<td>#<input type="text" maxlength="6" size="6" class="vejpicker" id="vimcolor" name="<?php echo $this->_var; ?>-vimcolor" value="<?php echo $vimplayer['color']; ?>" /></td>
			</tr>
			<tr>
				<th><label for="vimintro">Intro<?php $this->_parent->tip('Check the boxes of the following objects you would like to show in the intro.'); ?></label></th>
				<td>
					<label><input type="checkbox" name="<?php echo $this->_var; ?>-vimportrait" <?php if ($vimplayer['portrait'] == '1') { echo "checked "; } ?>value="1" /> Portrait</label> &nbsp;
					<label><input type="checkbox" name="<?php echo $this->_var; ?>-vimtitle" <?php if ($vimplayer['title'] == '1') { echo "checked "; } ?>value="1" /> Title</label> &nbsp;
					<label><input type="checkbox" name="<?php echo $this->_var; ?>-vimbyline" <?php if ($vimplayer['byline'] == '1') { echo "checked "; } ?>value="1" /> Byline</label>
				</td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="<?php echo $this->_var; ?>-vimsave" value="Save Settings" class="button-primary" /></p>
		<?php wp_nonce_field( $this->_var . '-nonce' ); ?>
	</form>
	<br/>
	<h3>Open Source Player</h3>
	<form method="post" action="<?php echo $this->_selfLink . '-settings'; ?>">
		<table class="form-table">
			<?php $opplayer = $this->_options['players']['op']; ?>
			<tr>
				<th><label for="optheme">Theme color</label></th>
				<td>#<input type="text" maxlength="6" size="6" class="vejpicker" id="optheme" name="<?php echo $this->_var; ?>-optheme" value="<?php echo $opplayer['theme']; ?>" /></td>
			</tr>
			<tr>
				<th><label for="opfont">Controlbar font color</label></th>
				<td>#<input type="text" maxlength="6" size="6" class="vejpicker" id="opfont" name="<?php echo $this->_var; ?>-opfont" value="<?php echo $opplayer['font']; ?>" /></td>
			</tr>
			<tr>
				<th><label for="ytcolor2">Frame color</label></th>
				<td>#<input type="text" maxlength="6" size="6" class="vejpicker" id="opframe" name="<?php echo $this->_var; ?>-opframe" value="<?php echo $opplayer['frame']; ?>" /></td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="<?php echo $this->_var; ?>-opsave" value="Save Settings" class="button-primary" /></p>
		<?php wp_nonce_field( $this->_var . '-nonce' ); ?>
	</form>
</div>
