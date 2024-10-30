<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/** @var BillingFox_WooCommerce_Loader $wc_loader */
$wc_loader = BillingFox_Plugin::getInstance()->get(BillingFox_WooCommerce_Loader::class);

?>

<div class="content">
    <form method="post">
        <?php wp_nonce_field( BillingFox_Admin_Setup::PAGE ); ?>

        <div class="container">
            <?php if ($wc_loader->exists()) { ?>

                <label for="category">Category Slug</label>
                <input type="text" id="category" class="text-input" name="category" value="<?php esc_attr_e(get_option(BillingFox_Admin_Setting::SETTING_WOOCOMMERCE_CATEGORY_SLUG)); ?>">

                <p>
                    <?php _e('Will be used to recharge Credits if supplied ', BILLING_FOX_TRANSLATE); ?>
                </p>

                <?php if (!empty($error)) { ?>
                    <div class="error">
                        <p><?php echo $error; ?></p>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="error">
                    <p><?php _e('WooCommerce not installed', BILLING_FOX_TRANSLATE); ?></p>
                </div>
            <?php } ?>
        </div>

        <p class="actions step">
            <input type="submit" class="button-primary button" value="<?php _e('Next', BILLING_FOX_TRANSLATE); ?>" name="save_step">
        </p>
    </form>
</div>
