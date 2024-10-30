<?php

defined('ABSPATH') or die('Hnng~');

function magforest_do_bulk() {
	if(!isset($_GET['go']) || $_GET['was_paged'] !== $_GET['paged']) {
			return ;
		}
		if(isset($_REQUEST['_wpnonce_quick'])) {
			$nonce = esc_attr( $_REQUEST['_wpnonce_quick'] );
		} else {
				$nonce = esc_attr( $_REQUEST['_wpnonce'] );
		}
		
		if(!wp_verify_nonce($nonce,'magforest_create_bulk'))
			wp_die( __('Security error') );

			
		/* Fix some shared hosting shit */
		if (get_magic_quotes_gpc()) {
		    $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
		    while (list($key, $val) = each($process)) {
		        foreach ($val as $k => $v) {
		            unset($process[$key][$k]);
		            if (is_array($v)) {
		                $process[$key][stripslashes($k)] = $v;
		                $process[] = &$process[$key][stripslashes($k)];
		            } else {
		                $process[$key][stripslashes($k)] = stripslashes($v);
		            }
		        }
		    }
		    unset($process);
		}
		magforest_importing_page();
		die();
		return;
		
				
}

function magforest_bulk_page() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	 if( isset($_GET['imported']) ) {
		magforest_generate_success("Post sync completed. ", true);
	} 
	 ?>
		<h1>Magforest Affiliate Plugin</h1>
		<h2>Bulk Import</h2>
		<hr>
		<div class="wrap">
			<p>Version <?= magforest_version() ?></p>
		</div>
		<div class="wrap">
		<form method="get">
			<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'magforest_create_bulk' ) ); ?>" />
			<input type="hidden" name="action" value="bulk" />
			<input type="hidden" name="page" value="magforest_bulk" />
			<input type="hidden" name="go" value="go" />
		    <table class="form-table">
		     <!--   <tr valign="top">
		        <th scope="row">Import as:</th>
		        <td>
			        <select name="import_type">
			        	<option value="post">Template Posts</option>
			        	<option value="link">Link-posts</option>
			        </select>
		        </td>
		        </tr> -->
		        
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
		        	<th scope="row" colspan="2">Note: <em>This may take a while to import. Existing posts will not be duplicated. To edit the post template, use the <a href="admin.php?page=magforest_setup">settings panel</a>.</em></th>
		        	
		        </tr>
		    </table>
		    <input type="submit" value="Import" />
		</form>
<?php

}