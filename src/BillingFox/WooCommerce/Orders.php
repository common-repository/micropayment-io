<?php

class BillingFox_WooCommerce_Orders extends BillingFox_ContainerAware implements BillingFox_RegistrationInterface
{
    public function register()
    {
        add_action( 'woocommerce_order_status_completed', [$this, 'orderCompleted'], 10, 1);
    }

    public function orderCompleted($order_id)
    {
        /** @var BillingFox_Api_Wrapper $api */
        $api = $this->get(BillingFox_Api_Wrapper::class);

        /** @var BillingFox_Api_Normalizer $normalizer */
        $normalizer = $this->get(BillingFox_Api_Normalizer::class);


        $order = wc_get_order($order_id);

        $coins = 0;

        /** @var WC_Order_Item $item */
        foreach ($order->get_items() as $item) {
            if ($item->is_type('billingfox')) {
                $coins += $item->get_meta('billingfoxs');
            }
        }

        if ($coins <= 0) {
            // no coins to process
            return;
        }

        if (!$order->get_user()) {
            error_log('invalid order for billingfox type');

            throw new RuntimeException();
        }

        $id = $normalizer->normalizeUser($order->get_user());

        try {
            $result = $api->recharge($id, $coins);
            $order->add_order_note($result['message'] );
        } catch (BillingFox_Api_Exception $e) {
            error_log($e->getMessage());
            throw new RuntimeException('could not recharge user');
        }
    }
}