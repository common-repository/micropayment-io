<?php
/**
 * Output a single payment method
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/payment-method.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$selectable = true;

/** @var BillingFox_WooCommerce_PaymentGateway $gateway */

$balance = $gateway->getBalance();

if (!is_user_logged_in() || $balance['available'] < $balance['required']) {
    $selectable = false;
    $gateway->chosen = false;
}

?>
<li class="wc_payment_method payment_method_<?php echo $gateway->id; ?>" title="<?php echo wc_float_to_string($balance['available']); ?> <?php _e('Credits available', BILLING_FOX_TRANSLATE); ?>">
    <?php if ($selectable) { ?>
    <input id="payment_method_<?php echo $gateway->id; ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php checked( $gateway->chosen, true ); ?> data-order_button_text="<?php echo esc_attr( $gateway->order_button_text ); ?>" />
    <?php } ?>

    <label for="payment_method_<?php echo $gateway->id; ?>">
        <?php echo $gateway->get_title(); ?> <a href="https://billingfox.com" target="_blank" rel="nofollow"><?php echo $gateway->get_icon(); ?></a><br />
        <em><?php echo wc_float_to_string($balance['available']); ?> <?php _e('Credits available', BILLING_FOX_TRANSLATE); ?></em>
    </label>
    <?php if ( $gateway->has_fields() || $gateway->get_description() ) : ?>
        <div class="payment_box payment_method_<?php echo $gateway->id; ?>" <?php if ( ! $gateway->chosen ) : ?>style="display:none;"<?php endif; ?>>
            <?php $gateway->payment_fields(); ?>
        </div>
    <?php endif; ?>

    <?php if (!$selectable) { ?>
        <div class="payment_box payment_method_<?php echo $gateway->id; ?>">
            <?php if (!is_user_logged_in()) { ?>
            <p><?php echo __('You need to log in to use this payment method', BILLING_FOX_TRANSLATE); ?></p>
            <?php } ?>
            <?php if ($balance['available'] < $balance['required']) { ?>
            <p><?php echo __('Insufficient funds in your wallet', BILLING_FOX_TRANSLATE); ?></p>
            <a href="<?php echo $balance['recharge']; ?>" class="button" target="<?php echo $balance['target']; ?>"><?php echo __('recharge', BILLING_FOX_TRANSLATE); ?></a>
            <?php } ?>
        </div>
    <?php } ?>
</li>
