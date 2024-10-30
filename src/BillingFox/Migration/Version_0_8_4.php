<?php

class BillingFox_Migration_Version_0_8_4 extends BillingFox_Migration_AbstractVersion
{
    public function up()
    {
        $ids = get_users(['meta_key' => 'billingfox_id', 'fields' => 'ID']);

        foreach ($ids as $id) {
            $old = get_the_author_meta('billingfox_id', $id);

            update_user_meta($id, 'billingfox_test_id', $old);
            delete_user_meta($id, 'billingfox_id');
        }

        $this->finish('0.8.4');
    }
}