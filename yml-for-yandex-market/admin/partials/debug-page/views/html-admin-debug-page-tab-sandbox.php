<?php defined( 'WPINC' ) || exit;

/**
 * Display the Sandbox tab.
 * 
 * @version    5.7.0 (17-08-2026)
 * @package    Y4YM
 * @subpackage Y4YM/admin/partials/debug_page/
 */
?>
<div class="postbox">
	<div class="inside">
		<?php
		try {
			y4ym_run_sandbox();
		} catch (Exception $e) {
			echo 'Exception: ', esc_html( $e->getMessage() ), "\n";
		}
		?>
	</div>
</div>