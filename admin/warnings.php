<?php

defined( 'ABSPATH' ) or die( 'Hnnng~' );

function magforest_render_warnings() {
	 if(! magforest_get_affiliate_id() || strlen(magforest_get_affiliate_id()) < 1) {
		magforest_generate_error("Please specify your affiliate name <a href=\"admin.php?page=magforest_setup\">here</a>");
	} 
}
add_action( 'admin_notices', 'magforest_render_warnings' );


function magforest_issue_notice($class, $title, $dismissable) {
	?>
	 <div class="notice notice-<?= $class ?> <?= ($dismissable ? "is-dismissible":"") ?>">
        <p><strong>Magforest&nbsp;Affiliate:</strong> <?php _e( $title, 'magforest' ); ?></p>
    </div>
	<?php
}

function magforest_generate_info($title, $dismissable = false) {
	magforest_issue_notice('info', $title, $dismissable);
}

function magforest_generate_warning($title, $dismissable = false) {
	magforest_issue_notice('warning', $title, $dismissable);
}

function magforest_generate_error($title, $dismissable = false) {
	magforest_issue_notice('error', $title, $dismissable);
}

function magforest_generate_success($title, $dismissable = false) {
	magforest_issue_notice('success', $title, $dismissable);
}

