<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

echo '<ol class="steps">';

foreach ($steps as $slug => $step) {
    echo '<li';
    if ($step['active']) {
        echo ' class="active"';
    }
    echo '><a href="';
    echo admin_url('index.php?page='.BillingFox_Admin_Setup::PAGE.'&step='.$slug);
    echo '">'.$step['title'].'</a></li>';
}

echo '</ol>';

