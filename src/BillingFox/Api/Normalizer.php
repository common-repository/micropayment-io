<?php

class BillingFox_Api_Normalizer extends BillingFox_ContainerAware
{
    const LIVE_META_KEY = 'billingfox_live_id';
    const TEST_META_KEY = 'billingfox_test_id';

    /**
     * round the coins to a specific value
     *
     * @param float $coins
     *
     * @return float
     */
    public function normalizeCoins($coins)
    {
        return ceil($coins);
    }

    /**
     * get billingfox id for user
     *
     * @param WP_User $user
     * @param bool $create
     *
     * @return string|false
     */
    public function normalizeUser(WP_User $user, $create = true)
    {
        $key = get_option(BillingFox_Admin_Setting::SETTING_TEST_ENDPOINT) ? self::TEST_META_KEY : self::LIVE_META_KEY;

        // check if user has a billingfox id?
        $id = get_the_author_meta( $key, $user->ID );

        if (empty($id)) {
            if (!$create) {
                return false;
            }

            /** @var BillingFox_Api_Wrapper $api */
            $api = $this->get(BillingFox_Api_Wrapper::class);

            do {
                $id = $this->getUniqueId();
            } while ($api->hasIdentity($id));

            $success = $api->setIdentity($id, $user->user_email);

            update_user_meta($user->ID, $key, $id);

            if (!$success) {
                return false;
            }
        }

        return $id;
    }

    /**
     * @param int $length
     *
     * @return string
     */
    private function getUniqueId($length = 13)
    {
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil($length / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
        } else {
            return uniqid();
        }
        return substr(bin2hex($bytes), 0, $length);
    }
}