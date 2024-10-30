<?php

class BillingFox_WooCommerce_ProductTabs implements BillingFox_RegistrationInterface
{
    public function register()
    {
        add_action('admin_footer', [$this, 'customJs']);

        add_filter('woocommerce_product_data_tabs', [$this, 'processTabs']);
        add_action('woocommerce_product_options_general_product_data', [$this, 'addOptions']);
        add_action('woocommerce_process_product_meta', [$this, 'saveOptions']);
    }

    public function addOptions()
    {
        echo '<div class="options_group">';

        woocommerce_wp_text_input([
            'id'			=> 'billingfoxs',
            'label'			=> __( 'Amount of BillingFoxs', BILLING_FOX_TRANSLATE ),
            'desc_tip'		=> 'true',
            'description'	=> __( 'Be aware that you will get invoiced for the amount of Credits bought', BILLING_FOX_TRANSLATE ),
            'type' 			=> 'numeric',
            'custom_attributes' => [
                'step' => '0.01',
            ],
        ]);

        echo '</div>';

    }

    public function saveOptions($post_id)
    {
        if(!empty( $_POST['billingfoxs'])) {
            update_post_meta( $post_id, 'billingfoxs', esc_attr($_POST['billingfoxs']) );
        }

    }

    public function processTabs($tabs)
    {
        $tabs['shipping']['class'][] = 'hide_if_billingfox';
        //$tabs['linked_product']['class'][] = 'hide_if_billingfox';
        $tabs['variations']['class'][] = 'hide_if_billingfox';

        return $tabs;
    }

    public function customJs()
    {
        if ( 'product' != get_post_type() ) {
            return;
        }

        echo <<<HTML
<script type='text/javascript'>
jQuery( document ).ready( function() {
    jQuery('.product_data_tabs .general_tab').addClass('show_if_billingfox').show();
    jQuery('#general_product_data .pricing').addClass('show_if_billingfox').show();


    jQuery('.product_data_tabs .linked_product_tab').addClass('show_if_billingfox');
    jQuery('#linked_product_data').addClass('show_if_billingfox');
});
</script>
HTML;
    }
}
