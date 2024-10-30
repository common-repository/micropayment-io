<?php

class BillingFox_Helper_Asset extends BillingFox_ContainerAware implements BillingFox_RegistrationInterface
{
    public function register()
    {
        add_action('wp_enqueue_scripts', [$this, 'addStyles']);
        add_action('admin_head', [$this, 'addAdminStyles']);
    }

    public function addAdminStyles()
    {
        wp_enqueue_style('billingfox', plugins_url(BILLING_FOX_PLUGIN_NAME.'/resources/css/help.css'), [], $this->getVersion());
    }

    public function addStyles()
    {
        wp_enqueue_style('billingfox', plugins_url(BILLING_FOX_PLUGIN_NAME.'/resources/css/billingfox.css'), [], $this->getVersion());
        wp_enqueue_style('billingfox_woocommerce', plugins_url(BILLING_FOX_PLUGIN_NAME.'/resources/css/woocommerce.css'), [], $this->getVersion());
    }
}