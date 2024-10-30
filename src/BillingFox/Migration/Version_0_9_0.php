<?php

class BillingFox_Migration_Version_0_9_0 extends BillingFox_Migration_AbstractVersion
{
    public function up()
    {
        $ids = get_users(['meta_key' => 'popcoin_id', 'fields' => 'ID']);

        foreach ($ids as $id) {
            update_user_meta($id, 'billingfox_id', get_the_author_meta('popcoin_id', $id));
            delete_user_meta($id, 'popcoin_id');
        }

        update_option(BillingFox_Admin_Setting::SETTING_TEST_ENDPOINT, get_option('popcoin_test_endpoint'));
        update_option(BillingFox_Admin_Setting::SETTING_WOOCOMMERCE_CATEGORY_SLUG, get_option('popcoin_woocommerce_category_slug'));
        update_option(BillingFox_Admin_Setting::SETTING_API_KEY, get_option('popcoin_api_key'));
        update_option(BillingFox_Admin_Setting::SETTING_DEBUG, get_option('popcoin_debug'));

        $this->finish('0.9.0');
    }
}