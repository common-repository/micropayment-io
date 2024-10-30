<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?><div class="billingfox">
    <div class="block">
        <h2><?php _e('Protected', BILLING_FOX_TRANSLATE); ?></h2>

        <?php $loader->renderTemplate('fence/form.php', ['grant' => $slug, 'coins' => $price]); ?>
    </div>
</div>