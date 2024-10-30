<?php

class BillingFox_WooCommerce_Loader extends BillingFox_ContainerAware implements BillingFox_RegistrationInterface
{
    public function register()
    {
        add_action('woocommerce_payment_gateways', [$this, 'addPaymentGateways']);
        add_filter('woocommerce_available_payment_gateways', [$this, 'limitPaymentGateways']);
        add_action('product_type_selector', [$this, 'addProductTypes']);
        add_action('woocommerce_init', [$this, 'requireFiles']);
        add_filter('pre_option_woocommerce_enable_guest_checkout', [$this, 'allowGuestCheckout']);

        add_action( 'woocommerce_billingfox_add_to_cart', [$this, 'addToCart'], 30 );

        add_filter('wc_get_template', [$this, 'getTemplate'], 10, 5);
    }

    public function getTemplate($located, $template_name, $args, $template_path, $default_path)
    {
        if (
            'checkout/payment-method.php' != $template_name
            || empty($args['gateway'])
            || get_class($args['gateway']) != BillingFox_WooCommerce_PaymentGateway::class
        ) {
            return $located;
        }

        return realpath(dirname(__FILE__).'/../../../templates/checkout/payment-method.php');
    }

    /**
     * include files that have a different naming convention (ie product types)
     */
    public function requireFiles()
    {
        require_once 'Product.php';
    }

    public function addToCart()
    {
        wc_get_template( 'single-product/add-to-cart/simple.php' );
    }

    /**
     * @param string $value
     * @return string
     */
    public function allowGuestCheckout($value)
    {
        if (WC()->cart) {
            foreach (WC()->cart->get_cart() as $item) {
                if ($item['data'] instanceof WC_Product_BillingFox) {
                    return 'no';
                }
            }
        }

        return $value;
    }

    /**
     * @return bool
     */
    public function exists()
    {
        if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return false;

        return true;
    }

    /**
     * @param string $slug
     * @return string
     */
    public function getCategoryLink($slug)
    {
        $category = get_term_by('slug', $slug, 'product_cat', 'ARRAY_A');

        return get_category_link($category['term_id']);
    }

    /**
     * @param array $gateways
     * @return array
     */
    public function limitPaymentGateways($gateways)
    {
        if (isset($gateways['billingfox']) && $this->disableGateway()) {
            unset($gateways['billingfox']);
        }

        return $gateways;
    }

    /**
     * @param array $types
     * @return array
     */
    public function addProductTypes($types)
    {
        $types['billingfox'] = __( 'BillingFox', BILLING_FOX_TRANSLATE);

        return $types;
    }

    /**
     * @param array $gateways
     * @return array
     */
    public function addPaymentGateways($gateways)
    {
        $gateways[] = 'BillingFox_WooCommerce_PaymentGateway';

        return $gateways;
    }

    private function disableGateway()
    {
        if (!is_user_logged_in()) {
            return true;
        }

        if (!WC()->cart) {
            return false;
        }

        foreach (WC()->cart->get_cart() as $item) {
            if ($item['data'] instanceof WC_Product_BillingFox) {
                return true;
            }
        }

        return false;
    }
}