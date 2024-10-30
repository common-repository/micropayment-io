<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>

<div class="content">

    <div class="container">
        <h1><?php _e('Welcome', BILLING_FOX_TRANSLATE); ?></h1>
        <h3><?php _e('And Thanks For installing Micropayment.io.', BILLING_FOX_TRANSLATE); ?></h3>


        <p>
            <?php _e('Micropayment.io is an account credits service built to make microtransactions on the Wordpress platform simple, and seamless. Protect articles, audio & video players, and use virtual credits in your WooCommerce store if you like.', BILLING_FOX_TRANSLATE); ?>
        </p>


        <p><?php _e('What does a BillingFox cost?', BILLING_FOX_TRANSLATE); ?> <strong>1 Credit = $0.01</strong></p>


        <p><?php _e('So let’s get started!', BILLING_FOX_TRANSLATE); ?></p>
    </div>

    <p class="actions step">
        <a href="<?php echo admin_url('index.php?page='.BillingFox_Admin_Setup::PAGE.'&step=api_key'); ?>" class="button-primary button">
            <?php _e('Next', BILLING_FOX_TRANSLATE); ?>
        </a>
    </p>
</div>