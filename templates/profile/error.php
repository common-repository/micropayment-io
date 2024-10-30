<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (is_user_logged_in()) {
    echo sprintf(__('error in shortcode %s', BILLING_FOX_TRANSLATE), $shortcode);
} else {
    echo __('User not logged in', BILLING_FOX_TRANSLATE);
}

