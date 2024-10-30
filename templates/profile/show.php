<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

switch ($only) {
    case 'email':
        echo $profile['email'];
        break;

    case 'billingfoxs':
        echo number_format($profile['balances']['available'], 2);
        break;

    default:
        echo 'E-Mail: '.$profile['email'].'<br />';
        echo 'Credits: '.number_format($profile['balances']['available'], 2).'<br />';
}
