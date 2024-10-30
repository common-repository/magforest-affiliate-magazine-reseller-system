<?php
defined('ABSPATH') or die('Hnng~');

function magforest_do_template() {
	// copy a post from Magforest
		$nonce = esc_attr( $_REQUEST['_wpnonce'] );
		if(!wp_verify_nonce($nonce,'magforest_template_post'))
			wp_die( __('Security error') );
		
		$id = intval($_GET['id']); // a magforest id
		$product = magforest_get_product($id); // magforest product
		if(!$product) return; // invalid product?
		
		$insert = magforest_create_template_post($product);
		
		if($insert) {
			wp_redirect( admin_url('post.php').'?action=edit&post='.$insert );
			exit;
		}

		wp_redirect( add_query_arg('templated',$insert,remove_query_arg('action')) );
}

function magforest_browse_page() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	if( isset($_GET['copied']) ) {
		// done copying
		if(intval($_GET['copied']) == 0) {
			magforest_generate_error("Failed to create post",true);
		} else {
			magforest_generate_success("Post created. ", true);
		}
		
	} else if( isset($_GET['templated']) ) {
		// done copying
		if(intval($_GET['templated']) == 0) {
			magforest_generate_error("Failed to create post",true);
		} else {
			magforest_generate_success("Post created. ", true);
		}
		
	} 
	// global $wp_rewrite; $wp_rewrite->flush_rules();
	 if( isset($_GET['imported']) ) {
		magforest_generate_success("Posts imported. ", true);
	} 
	 ?>
		<h1>Search Magforest Magazines</h1>
	
		<hr>
		<div class="wrap">
			<p>Version <?= magforest_version() ?></p>
		</div>
		<div class="wrap">
		<form method="get">
			<input type="hidden" value="magforest" name="page" />
			<input type="search" placeholder="Search" name="mag_search" style="width:600px;" value="<?php echo esc_attr($_GET['mag_search']);?>" />
			<input type="submit" value="Search" />
			<?php
				if(isset($_GET['mag_search'])) {
			?>
				<a href="<?php echo esc_attr(remove_query_arg("mag_search")); ?>">Reset search</a>
			<?php
				}
			?>
		</form><br />
		<form method="get" id="mag-list">
			<table class="form-table">
		        
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
			</table>
			        <input type="submit" name="go" value="Import checked" />
			<input type="hidden" name="page" value="magforest" />
			<input type="hidden" name="_wpnonce_quick" value="<?php echo esc_attr( wp_create_nonce( 'magforest_create_bulk' ) ); ?>" />
			<?php
				if(isset($_GET['mag_search'])) {
			?>
				<input type="hidden" name="mag_search" value="<?php echo esc_attr($_GET['mag_search']);?>" />
			<?php
				}
			?>
			<input type="hidden" name="was_paged" value="<?php echo esc_attr( isset($_GET['paged']) ? $_GET['paged'] : 1 ); ?>" />
			<input type="hidden" name="action" value="bulk" />
		<?php
			$reqs = new MagazineTable();
			$reqs->prepare_items();
			$reqs->display();
		?>
			        
		        

		</form>
		</div>
	<?php
	
	
}
