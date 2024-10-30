<?php

defined('ABSPATH') or die('Hnng~');


function magforest_setup_true_sync_interval_changed() {
	$new = get_option('magforest_true_sync_interval', 'daily');
	magforest_true_sync_dequeue();
	if($new != 'never') {
		magforest_true_sync_enqueue($new);
	}
}
add_action( 'update_option_magforest_true_sync_interval', 'magforest_setup_true_sync_interval_changed'); 

function magforest_do_truesync() {
	$nonce = esc_attr( $_REQUEST['_wpnonce'] );
	if(!wp_verify_nonce($nonce,'magforest_true_sync_manual'))
			wp_die( __('Security error') );
	
	magforest_true_sync_do();
	wp_redirect( add_query_arg('synced','true',remove_query_arg('action')) );
}

function magforest_setup_page() {
		if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	if( isset($_GET['synced']) ) {
		magforest_generate_success("True Sync performed successfully. ", true);
	} 
	// global $wp_rewrite; $wp_rewrite->flush_rules();
	 ?>
		<h1>Magforest Affiliate Settings</h1>
	
		<hr>
		<div class="wrap">
			<p>Version <?= magforest_version() ?></p>
		</div>
		<div class="wrap">
		<form method="post" action="options.php">
			<?php settings_fields( 'magforest_setup' ); ?>
		    <?php do_settings_sections( 'magforest_setup' ); ?>
		    <table class="form-table">
		        <tr valign="top">
		        <th scope="row">Magforest Affiliate Name:</th>
		        <td><input type="text" name="magforest_affiliate_id" value="<?php echo esc_attr( get_option('magforest_affiliate_id') ); ?>" required /></td>
		        </tr>
		        
		        <tr valign="top">
		        	<th scope="row" colspan="2">Note: <em>This is the part that is added after the <tt>?ref=</tt> in your affiliate link. You can find yours in the <a href="https://www.magforest.com/affiliate-account-page" target="_blank">Magforest Affiliate Area.</a></em></th>
		        	
		        </tr>
		        
		        <tr valign="top">
		        <th scope="row">True&nbsp;Sync:</th>
		        <td>
					<select name="magforest_true_sync_interval">
					<?php
							$intervals = wp_get_schedules();
			        		$csched = magforest_true_sync_schedule();
			        ?>
						<option value="never" <?php if(!$csched) echo "checked"; ?>>Never</option>
			        	<?php
			        		
			        		foreach($intervals as $key => $value) {
				        		if($value['interval'] >= 86400) {
					        		echo '<option value="'.$key.'" '.($key == $csched ? 'selected' : '').' >'.$value['display'].'</option>';
				        		}
			        		}
			        	?>
			        </select>
			        <?php
			        	printf( '<a href="?page=%s&action=%s&_wpnonce=%s">Perform Now</a>', esc_attr( $_REQUEST['page'] ), 'truesync', wp_create_nonce( 'magforest_true_sync_manual' ) )
			         ?>
			        <?php if($csched) { 
			        	echo "<br/>";
			        	echo "<em>Next True&nbsp;Sync:</em> ".magforest_get_next_cron_execution(magforest_true_sync_next());
			        }	?>
			        </td>
		        </tr>
		        <tr valign="top">
		        	<th scope="row" colspan="2"><em>True&nbsp;Sync</em> watches the Magforest website for changes, so if any of the magazines is deleted, it will be deleted on your website as well.</th>
		        	
		        </tr>
		        
		        <tr valign="top">
		        	<th scope="row">Post Template:<br /><em>Confused? Please read below.</em></th>
		        	<td>
		        		<textarea name="magforest_template" cols="80" rows="20"><?php echo esc_textarea( get_option('magforest_template') ); ?></textarea>
		        	</td>
		        </tr>
		    </table>
		    
		    <?php submit_button(); ?>
		</form>
		<h2>Customising your post template!</h2>
		<p>You can use the above field to customize your Post Template, <br />either by adding in your own HTML code or using our shortcodes below.</p> 
		<p>All HTML code works - for example: <tt>&lt;br/&gt;&lt;center&gt;{DOWNLOAD_LINK}&lt;/center&gt;&lt;br/&gt;</tt></p>
		<p>Please note: the tags enclosed in <tt>{BRACES}</tt> are case sensitive, please type them all-caps.</p>
		<p>Changing the post template only affects new posts, not existing posts.</p>
		<table>
			<thead>
				<tr>
					<th>Shortcode</th>
					<th>Description</th>
					<th>Scope</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><tt>{DESCRIPTION}</tt></td>
					<td>Inserts the magazine description.</td>
					<td>Only post template</td>
				</tr>
				<tr>
					<td><tt>{DOWNLOAD_LINK}</tt></td>
					<td>Inserts the default download link (including your Magforest affiliate ID).</td>
					<td>Only post template</td>
				</tr>
				<tr>
					<td><tt>{PRINT_LINK}</tt></td>
					<td>Inserts the default print-on-demand link (including your Magforest affiliate ID).</td>
					<td>Only post template</td>
				</tr>
				<tr>
					<td><tt>{PRODUCT_ID}</tt></td>
					<td>Inserts the magazine ID.</td>
					<td>Only post template</td>
				</tr>
				<tr>
					<td>Example:</td>
					<td colspan="2"><tt>[magforest_link id="{PRODUCT_ID}"]Download Now[/magforest_link]</tt></td>
				</tr>
				<tr>
					<td>Another example:</td>
					<td colspan="2"><tt>[magforest_image id="{PRODUCT_ID}"]</tt></td>
				</tr>
				<tr>
					<td><tt>[magforest_link]</tt></td>
					<td>Used to link to Magforest using your affilate link - requires <tt>[/magforest_link]</tt> closing tag at the end.</td>
					<td>Your whole website</td>
				</tr>
				<tr>
					<td><tt>[magforest_print]</tt></td>
					<td>Used to link to Magforest print-on-demand service using your affilate link - requires <tt>[/magforest_print]</tt> closing tag at the end.</td>
					<td>Your whole website</td>
				</tr>
				<tr>
					<td><tt>[magforest_image]</tt></td>
					<td>Used to display the magazines featured image - requires <tt>[/magforest_image]</tt> closing tag at the end.</td>
					<td>Your whole website</td>
				</tr>
				<tr>
					<td><tt>[gallery]</tt></td>
					<td>Used to display the magazines preview images within the magazine post. (Standard WordPress tag)</td>
					<td>Magazine post only</td>
				</tr>
			</tbody>
			<p>For example, you can also use this code to customize your download button. Add your image link to the <tt>img src=""</tt> attribute, such as:</p>
			<pre>[magforest_link id="{PRODUCT_ID}"]&lt;img src="http://www.website.com/custombutton.png" /&gt;[/magforest_link]</pre>
		</table>
<?php
}
