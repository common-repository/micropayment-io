<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>

<div class="content">
    <form method="post">
        <?php wp_nonce_field( BillingFox_Admin_Setup::PAGE ); ?>

        <div class="container">
            <p>
                <?php _e('<a href="https://billingfox.com" target="_blank">Create your BillingFox account</a>, hookup your Stripe, and come back and input your new API key.', BILLING_FOX_TRANSLATE); ?>
            </p>

            <p>
                <img style="max-width: 80%" src="<?php echo plugins_url('resources/img/setup_access_token.png', BILLING_FOX_PLUGIN_FILE); ?>" alt="Token Input">
            </p>

            <label for="api_key">API Key</label>
            <input type="text" id="api_key" class="text-input" name="api_key" required="" value="<?php esc_attr_e(get_option(BillingFox_Admin_Setting::SETTING_API_KEY)); ?>">

            <?php if (!empty($error)) { ?>
                <div class="error">
                    <p><?php echo $error; ?></p>
                </div>
            <?php } ?>
        </div>

        <p class="actions step">
            <input type="submit" class="button-primary button" value="<?php _e('Next', BILLING_FOX_TRANSLATE); ?>" name="save_step">
        </p>
    </form>
</div>