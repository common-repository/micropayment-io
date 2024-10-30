<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (!is_user_logged_in()) {
    // no user, nothing to recharge XD
    return;
}


$slug = get_option(BillingFox_Admin_Setting::SETTING_WOOCOMMERCE_CATEGORY_SLUG, false);

$plugin = BillingFox_Plugin::getInstance();

/** @var BillingFox_WooCommerce_Loader $wc_loader */
$wc_loader = $plugin->get(BillingFox_WooCommerce_Loader::class);

if ($wc_loader->exists() && $slug && ($link = $wc_loader->getCategoryLink($slug))) {
    $target = 'self';
} else {
    /** @var BillingFox_Api_Wrapper $api */
    $api = $plugin->get(BillingFox_Api_Wrapper::class);

    /** @var BillingFox_Api_Normalizer $normalizer */
    $normalizer = $plugin->get(BillingFox_Api_Normalizer::class);

    $user = $normalizer->normalizeUser(wp_get_current_user());
    try {
        $result = $api->getIdentity($user);
    } catch (BillingFox_Api_Exception $e) {
        echo _e('ERROR: '.$e->getMessage());
        return;
    }

    $link = $result['link'];
    $target = '_blank';
}


?><a class="recharge" href="<?php echo $link; ?>" target="<?php echo $target; ?>"><?php echo _e('Add Coins'); ?></a>