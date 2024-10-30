<?php
defined('ABSPATH') or die('Hnng~');

function magforest_do_bulk_ajax() {
	
		$nonce = esc_attr( $_REQUEST['_wpnonce'] );
		
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
		ini_set('max_execution_time', 0);
		set_time_limit(0);
		
		$is_draft = ($_POST['import_state'] === 'draft');
		$target_category = esc_sql($_POST['cat']);
		$create_tags = isset($_POST['import_create_tags']);
		$create_cats = isset($_POST['import_create_categories']);
		$append_tags = esc_sql($_POST['import_append_tags']);
		$product = json_decode(stripslashes($_POST['product']));
		
		
			$cat_str = ''.$target_category;
				if($create_cats) {
					// copy categories
					foreach($product->info->category as $src_cat) {
						$category_id = get_cat_ID($src_cat->name);
						if(!$category_id) {
							$category_id = wp_create_category($src_cat->name);
						}
						if(strlen($cat_str) > 0) $cat_str .= ',';
						$cat_str .= $category_id;
					}
				}
			
			$insert = wp_insert_post(array(
				'post_content'=>magforest_template_content($product),
				'post_title'=>$product->info->title,
				'post_status'=>($is_draft ? 'draft':'publish'),
				'comment_status'=>'closed',
				'post_type'=>(/*$import_as_links ? 'magforest_affiliate' : */'post'),
				'meta_input'=>array(
					'magforest_link'=>magforest_link_from_slug($product->info->slug),
					'magforest_id'=>$product->info->id
				),
				'tax_input' => array( 'category' => $cat_str )
			));
			if($insert) {
				$thumb = magforest_set_featured_image($product->info->thumbnail, $insert);
				// if we have a gallery tag, exclude the inserted featured image...
				
				$my_post = array(
				      'ID'           => $insert,
				      'post_content' => str_ireplace("[gallery", "[gallery exclude=\"$thumb\"", magforest_template_content($product))
				  );
				
				// Update the post into the database
				  wp_update_post( $my_post );
				  
			foreach($product->previews as $preview) {
					magforest_attach_file($preview, $insert);
				}
				$tag_str = ''.$append_tags;
				if($create_tags) {
					// copy tags
					foreach($product->info->tags as $src_tag) {
						if(strlen($tag_str) > 0) $tag_str .= ',';
						$tag_str .= $src_tag->slug;
					}
				}
				wp_set_post_tags($insert,$tag_str,true);
				die('OK');
			} else {
				var_dump($_POST['product']);
				 die('FAIL');
			}
}

function magforest_importing_page() {
		// I'd rather have this a custom page than a WP-enabled one, since we'll be ajaxing anyway...
		$target_category = esc_sql($_GET['cat']);
		$is_draft = ($_GET['import_state'] === 'draft');
		$create_tags = isset($_GET['import_create_tags']);
		$create_cats = isset($_GET['import_create_categories']);
		$append_tags = esc_sql($_GET['import_append_tags']);
		$tag = esc_sql($_GET['import_tag']);
		$publisher = esc_sql($_GET['import_publisher']);
		$category = esc_sql($_GET['import_category']);
		$exclude = join(',', magforest_get_have_ids());
		
		if(isset($_GET['import_include'])) $include = join(',', $_GET['import_include']);
		else $include = '';
		
		//var_dump($exclude);
			
		$mags = json_encode(magforest_get_all_products('',$tag,$category,$publisher,$exclude,$include));
		
		//var_dump($mags); die();
		
		if(isset($_REQUEST['_wpnonce_quick'])) {
			$nonce = esc_attr( $_REQUEST['_wpnonce_quick'] );
		} else {
				$nonce = esc_attr( $_REQUEST['_wpnonce'] );
		}
		?>
		<html>
			<head>
				<title>[starting] Magforest Import...</title>
				<script src="<?php echo esc_attr( magforest_get_asset_url('progressbar.min.js') ); ?>"></script>
				<script src="<?php echo esc_attr( includes_url('js/jquery/jquery.js') ); ?>"></script>
				<style>
					body {
						background-color: #eee;
						font-family: sans-serif;
					}
					#container {
						margin: auto;
						border-radius: 3px;
						padding: 5px;
						background: #fff;
						max-width: 700px;
					}
					#progresscontainer {
						 margin: 20px;
						  width: 400px;
						  height: 8px;
						  position: relative;
						  margin-bottom: 50px;
					}
					#techdata {
						margin: 5px;
						text-align: left;
					}
					#progress_table {
						width: 100%;
						border-collapse: collapse;
					}
					th > a {
						text-decoration: none !important;
						color: #c5f2f9;
					}
					tr.current {
						background: #c5f2f9;
					}
					th {
						text-align: right;
						padding-right: 5px;
					}
					#whatsup {
						display: block;
					}
				</style>
			</head>
			<body>
				<div id="container" align="center">
					<img src="<?php echo esc_attr( magforest_get_asset_url('magforestlogo2016.png') ); ?>" alt="Magforest Logo" />
					<h2>Import in progress</h2>
					<em>Please <strong>do not</strong> close this page until post sync is finished!</em>
					<!--<em>The magazines, for which there is already a post, will not be duplicately imported.</em>-->
					<div id="progresscontainer">
					
					</div>
					<span id="whatsup">Initializing...</span>
					<div id="techdata">
						<h4>Import progress:</h4>
						<table id="progress_table">
							
						</table>
					</div>
				</div>
				
				<script type="text/javascript">
				window.bar = new ProgressBar.Line(progresscontainer, {
				  strokeWidth: 2,
				  easing: 'easeInOut',
				  duration: 1400,
				  color: '#3084f0',
				  trailColor: '#ccc',
				  trailWidth: 1,
				  svgStyle: {width: '100%', height: '100%'},
				  text: {
				    style: {
				      // Text color.
				      // Default: same as stroke color (options.color)
				      color: '#3084f0',
				      position: 'absolute',
				      right: '0',
				      top: '30px',
				      padding: 0,
				      margin: 0,
				      transform: null
				    },
				    autoStyleContainer: false
				  },
				  from: {color: '#3084f0'},
				  to: {color: '#3084f0'},
				  step: function(state, bar) {
				   // bar.setText(Math.round(bar.value() * 100) + ' %');
				  }
				});
				
				function tableRow(product,state) {
					var ret = "<tr"+(state == "current" ? " class=\"current\"" : "")+" data-product=\""+product.info.id+"\"><th class=\"title\" scope=\"row\">"+product.info.title+"</th><td class=\"state\">";
					if("current" == state) {
						ret += "processing...";
					} else if ("wait" == state) {
						ret += "queued";
					} else if ("done" == state) {
						ret += "processed";
					}
					ret += "</td>";
					ret += "</tr>";
					return ret;
				}
				
				function buildTableInit() {
					jQuery('#progress_table').html('');
					for(var i  in window.mags) {
						var mag = window.mags[i];
						jQuery('#progress_table').append(tableRow(mag,"wait"));
					}
				}
				
				function setWhatsup(text) {
					jQuery('#whatsup').html(text);
					document.title = "["+text.toLowerCase()+"] Magforest Import";
				}
				
				function setProgress(value) {
					window.bar.animate(value);
				}
				
				window.lastFailed = false;
				
				function setCurrent(mag) {
					jQuery('tr.current > .state').html(window.lastFailed ? 'failed' : 'complete');
					jQuery('tr.current').removeClass('current');
					jQuery('tr[data-product="'+mag.info.id+'"] > .state').html('processing...');
					jQuery('tr[data-product="'+mag.info.id+'"]').addClass('current');
				}
				
				function goProcess() {
					if(!window.mags || window.mags.length == 0) {
						// no mags left
						if(!window.mags) {
							setWhatsup("No posts to import");
						} else if(window.mags.failureReason) {
							setWhatsup(window.mags.failureReason);
						} else {
							setProgress(1);
							var of = (window.totalMags - window.mags.length).toString()+" of "+window.totalMags.toString();
							document.title = "["+of+"] Magforest Import";
							window.bar.setText(of);
							setWhatsup("Done!");
						}
						window.onbeforeunload = function(){};
						setTimeout(function(){
							// do a redirect here...
							window.location.href="<?php echo add_query_arg("imported","yes",remove_query_arg(array('cat','import_state','import_create_tags','import_create_categories','import_append_tags','import_tag','import_publisher','import_category','import_include','_wpnonce_quick','_wpnonce','action','go','_wp_http_referer'))); ?>";
						}, 1000);
					} else {
						
						setProgress( (window.totalMags - window.mags.length)/window.totalMags );
						var of = (window.totalMags - window.mags.length).toString()+" of "+window.totalMags.toString();
						document.title = "["+of+"] Magforest Import";
						window.bar.setText(of);
						window.currentMag = window.mags.shift();
						setCurrent( window.currentMag );
						window.lastFailed = false;
						jQuery('#whatsup').html("Importing "+window.currentMag.info.title+"...");
						
						jQuery.ajax({
							type: "POST",
							url: "admin.php?action=bulk_ajax&_wpnonce=<?php echo $nonce; ?>",
							data: {
								'import_state': "<?php echo ($is_draft ? 'draft':'publish'); ?>",
								'cat': "<?php echo $target_category; ?>",
								<?php if($create_cats) echo "'import_create_categories':'YES',"; ?>
								<?php if($create_tags) echo "'import_create_tags':'YES',"; ?>
								'import_append_tags': "<?php echo $append_tags; ?>",
								'product': JSON.stringify(window.currentMag)
							},
							success: function(response) {
								console.log(response);
								setTimeout(function(){
									goProcess();
								}, 100);
							}, error: function(  jqXHR,  textStatus,  errorThrown ) {
								//alert("Could not add post: "+textStatus);
								window.lastFailed = true;
								setTimeout(function(){
									goProcess();
								}, 100);
							}
						});
					}
					
				}
				
				jQuery(document).ready(function() {
					setWhatsup("Initializing...");
					buildTableInit();
					window.onbeforeunload = function(){
					  return 'Are you sure you want to leave? Import is not finished.';
					};
					goProcess();
				});
				</script>
				
				<script type="text/javascript">
				window.mags = <?php echo $mags;?>;
				window.totalMags = window.mags.length;
				</script>
			</body>
		</html>
		<?php
		
}
