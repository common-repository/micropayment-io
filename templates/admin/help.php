<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$templates = []; // todo define templates as well

$shortcodes = [
    BillingFox_Post_ShortCode::SHORT_CODE => [
        'description' => __('Restrict access to blocks', BILLING_FOX_TRANSLATE),
        'options' => [],
    ],
    BillingFox_Profile_ShortCode::SHORT_CODE_TRANSACTIONS => [
        'description' => __('List Transactions of logged in User', BILLING_FOX_TRANSLATE),
        'options' => [],
    ],
    BillingFox_Profile_ShortCode::SHORT_CODE_PROFILE => [
        'description' => __('Show Credits and E-Mail', BILLING_FOX_TRANSLATE),
        'options' => [
            'only' => __('either "email" or "credits"', BILLING_FOX_TRANSLATE),
        ],
    ],
];

echo '<div class="billingfox-help-shortcode">';
echo '<h2>'.__('Shortcodes', BILLING_FOX_TRANSLATE).'</h2>';
echo '<table>';
foreach ($shortcodes as $shortcode => $info) {
    echo '<tr>';
    echo '<td>['.$shortcode.']</td>';
    echo '<td>';

    echo '<b>'.$info['description'].'</b><br />';

    if (!empty($info['options'])) {
        echo '<ul class="list">';
        foreach ($info['options'] as $key => $value) {
            echo '<li>'.$key.' = '.$value.'</li>';
        }
        echo '</ul>';
    }

    echo '</td>';
    echo '</tr>';
}
echo '</table>';
echo '</div>';
