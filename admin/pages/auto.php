<?php

defined('ABSPATH') or die('Hnng~');

function magforest_do_mktask() {
		$nonce = esc_attr( $_REQUEST['_wpnonce'] );
		if(!wp_verify_nonce($nonce,'magforest_create_task'))
			wp_die( __('Security error') );
			
		$interval = ($_GET['scan_interval']);
		$target_category = esc_sql($_GET['cat']);
		$is_draft = ($_GET['import_state'] === 'draft');
		$create_tags = isset($_GET['import_create_tags']);
		$create_cats = isset($_GET['import_create_categories']);
		$append_tags = esc_sql($_GET['import_append_tags']);
		$tag = esc_sql($_GET['import_tag']);
		$publisher = esc_sql($_GET['import_publisher']);
		$category = esc_sql($_GET['import_category']);
		
		magforest_queue_task($interval, $target_category, $is_draft, $create_tags, $create_cats, $append_tags, $tag, $publisher, $category);
		wp_redirect( add_query_arg('added','true',remove_query_arg('action')) );
}
 
function magforest_do_rmtask() {
	$nonce = esc_attr( $_REQUEST['_wpnonce'] );
	if(!wp_verify_nonce($nonce,'magforest_kill_task'))
		wp_die( __('Security error') );
		
	$hash = $_GET['iha'];
	magforest_dequeue_task($hash);
		
	wp_redirect( add_query_arg('killed','true',remove_query_arg('action')) );
}

function magforest_automation_page() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	 if( isset($_GET['added']) ) {
		magforest_generate_success("Sync task added. ", true);
	} 
	 if( isset($_GET['killed']) ) {
		magforest_generate_success("Sync task removed. ", true);
	} 
	 ?>
		<h1>Magforest Affiliate Plugin</h1>
		<h2>Auto Post Sync</h2>
		<div class="wrap">
			<p>Version <?= magforest_version() ?></p>
		</div>
		<hr>
		<h2>Schedule a New Task</h2>
		<div class="wrap">
		<form method="get">
			<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'magforest_create_task' ) ); ?>" />
			<input type="hidden" name="action" value="mktask" />
			<input type="hidden" name="page" value="magforest_schedule" />
		    <table class="form-table"> 
		    	 <tr valign="top">
		        <th scope="row">Interval:</th>
		        <td>
			        <select name="scan_interval">
			        	<?php
			        		$intervals = wp_get_schedules();
			        		foreach($intervals as $key => $value) {
				        		if($value['interval'] >= 86400) {
					        		echo '<option value="'.$key.'">'.$value['display'].'</option>';
				        		}
			        		}
			        	?>
			        </select>
		        </td>
		        </tr>
		        <tr valign="top">
		        <th scope="row">Category:</th>
		        <td>
			        <select name="import_category">
			        	<option value="">Any</option>
			        	<?php
			        		$cats = magforest_get_categories();
			        		foreach((array)$cats as $nus) {
				        		echo '<option value="'.$nus->term_id.'">'.$nus->name.'</option>';
			        		}
			        	?>
			        </select>
		        </td>
		        </tr>
		        
		        <tr valign="top">
		        <th scope="row">Publisher:</th>
		        <td>
			        <select name="import_publisher" style="width:500px;  max-width:500px;">
			        	<option value="">Any</option>
			        	<?php
			        		$cats = magforest_get_publishers();
			        		foreach((array)$cats as $nus) {
				        		echo '<option value="'.$nus->ID.'">'.$nus->display_name.'</option>';
			        		}
			        	?>
			        </select>
		        </td>
		        </tr>
		        
		        <tr valign="top">
		        <th scope="row">Tag:</th>
		        <td>
			        <select name="import_tag" style="width:500px; max-width:500px;">
			        	<option value="">Any</option>
			        	<?php
			        		$cats = magforest_get_tags();
			        		foreach((array)$cats as $nus) {
				        		echo '<option value="'.$nus->term_id.'">'.$nus->name.'</option>';
			        		}
			        	?>
			        </select>
		        </td>
		        </tr>
		        
		        <tr valign="top">
		        <th scope="row">Copy tags:</th>
		        <td>
			        <input type="checkbox" name="import_create_tags" checked="true" />
		        </td>
		        </tr>
		        
		         <tr valign="top">
		        <th scope="row">Add tags (comma separated):</th>
		        <td>
			        <input type="text" name="import_append_tags" placeholder="(e.g. lifestyle,magazine,download...)"/>
		        </td>
		        </tr>
		        
		         <tr valign="top">
		        <th scope="row">Place into category:</th>
		        <td>
			        <?php wp_dropdown_categories('hide_empty=0'); ?>
		        </td>
		        </tr>
		        
		        <tr valign="top">
		        <th scope="row">Copy categories:</th>
		        <td>
			        <input type="checkbox" name="import_create_categories" checked="true" />
		        </td>
		        </tr>
		        
		        
		        <tr valign="top">
		        <th scope="row">Post state:</th>
		        <td>
			        <select name="import_state">
			        	<option value="publish">Published</option>
			        	<option value="draft">Draft</option>
			        </select>
		        </td>
		        </tr>
		        
		        <tr valign="top">
		        	<th scope="row" colspan="2">Note: <em> Existing posts will not be duplicated. To edit the post template, use the <a href="admin.php?page=magforest_setup">settings panel</a>.</em></th>
		        	
		        </tr>
		    </table>
		    <input type="submit" value="Schedule Task" />
		</form>
		</div>
		<hr>
		<h2>Currently Scheduled Tasks</h2>
		<?php
			$reqs = new MFCronTable();
			$reqs->prepare_items();
			$reqs->display();
		?>
		<hr>
		<h3>Troubleshooting</h3>
		<div class="wrap">
			<p>The auto sync tasks won't run? There might be a few reasons why:</p>
			<ul>
				<li><em>Did you do an initial import from the Bulk Import tab?</em> If not, there might be too many magazines to import in the background, so it would either take an extremely long time, or not import them all. We advise you to use the <strong>Bulk Import</strong> tab prior to setting up a scheduled sync rule.</li>
				<li><em>Do you use CloudFlare or another caching system?</em> If so, add a rule to bypass caching for <tt>/wp-cron.php</tt>, since the cache interferes with the scheduled task mechanism of Wordpress. Advanced users might want to replace WP-Cron with Linux Crontab, as described in many articles on the web (i.e. <a href="https://easyengine.io/tutorials/wordpress/wp-cron-crontab/">this&nbsp;one</a>).</li>
				<li><em>Does your website have visitors?</em> The Wordpress scheduling mechanism relies on visitors and editors triggering a special script every time they load a page. If your website doesn't have visitors, the scheduled tasks will not run. </li>
				<li><em>Maybe you already have all the magazines?</em> It might seem to be not adding anything just because you already have all the necessary magazines imported :-)</li>
				
			</ul>
		</div>
<?php

}