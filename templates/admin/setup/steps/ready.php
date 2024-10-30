<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>

<div class="content">
    <div class="container">
        <?php _e('Ready to create your first metered post!', BILLING_FOX_TRANSLATE); ?>
    </div>

    <div class="container">
        <p>
            <img style="max-width: 100%" loop="infinite" src="<?php echo plugins_url('resources/img/setup_ready.gif', BILLING_FOX_PLUGIN_FILE); ?>" alt="How to use it">
        </p>

        <?php $loader->renderTemplate('admin/help.php', []); ?>
    </div>

    <p class="actions step">
        <a href="<?php echo admin_url(); ?>" class="button-primary button">
            <?php _e('Home', BILLING_FOX_TRANSLATE); ?>
        </a>
    </p>
</div>