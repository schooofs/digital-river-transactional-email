<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.digitalriver.com/company/
 * @since      1.0.0
 *
 * @package    Digital_River_Transactional_Email
 * @subpackage Digital_River_Transactional_Email/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
    <h1><?php esc_html_e( get_admin_page_title(), 'digital-river-transactional-email' ); ?></h1>
    <form method="post" action="options.php">
        <?php
        settings_fields( $this->plugin_name );
        do_settings_sections( $this->plugin_name );
        submit_button();
        ?>
    </form>
</div>
