<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$error = BillingFox_Plugin::getInstance()->get(BillingFox_Post_Fence::class)->getError($grant);

?><form method="post">
    <input type="hidden" name="billingfox[grant]" value="<?php echo $grant; ?>">
    <button type="submit"><?php echo sprintf(_n('Pay %d Credit to continue', 'Pay %d Credits to continue', $coins, BILLING_FOX_TRANSLATE), $coins); ?></button>
    <?php if ($error) { ?>
        <p class="error"><?php _e($error, BILLING_FOX_TRANSLATE); ?></p>
    <?php } ?>
    <?php $loader->renderTemplate('link/recharge.php'); ?>
</form>
