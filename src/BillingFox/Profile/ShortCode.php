<?php

class BillingFox_Profile_ShortCode extends BillingFox_ContainerAware implements BillingFox_RegistrationInterface
{
    const SHORT_CODE_PROFILE = 'micropay_profile';
    const SHORT_CODE_TRANSACTIONS = 'micropay_transactions';

    public function register()
    {
        add_shortcode(self::SHORT_CODE_PROFILE, [$this, 'showProfile']);
        add_shortcode(self::SHORT_CODE_TRANSACTIONS, [$this, 'showTransactions']);
    }

    public function showProfile($args, $content)
    {
        if (!$this->canShow()) {
            return $this->getTemplating()->getTemplate('profile/error.php', [
                'shortcode' => self::SHORT_CODE_PROFILE,
            ]);
        }

        try {
            $profile = $this->getApi()->getIdentity(
                $this->getNormalizer()->normalizeUser(wp_get_current_user())
            );
        } catch (BillingFox_Api_Exception $e) {
            return $this->getTemplating()->getTemplate('profile/error.php', [
                'shortcode' => self::SHORT_CODE_PROFILE,
            ]);
        }

        $args = shortcode_atts([
            'only' => null,
        ], $args);

        return $this->getTemplating()->getTemplate('profile/show.php', [
            'profile' => $profile,
            'only' => $args['only'],
        ]);
    }

    public function showTransactions()
    {
        if (!$this->canShow()) {
            return $this->getTemplating()->getTemplate('profile/error.php', [
                'shortcode' => self::SHORT_CODE_TRANSACTIONS,
            ]);
        }

        try {
            $list = $this->getApi()->listSpend(
                $this->getNormalizer()->normalizeUser(wp_get_current_user())
            );
        } catch (BillingFox_Api_Exception $e) {
            return $this->getTemplating()->getTemplate('profile/error.php', [
                'shortcode' => self::SHORT_CODE_TRANSACTIONS,
            ]);
        }

        return $this->getTemplating()->getTemplate('profile/list.php', [
            'list' => $list,
        ]);
    }

    /**
     * @return bool
     */
    protected function canShow()
    {
        return is_user_logged_in()
            && $this->getNormalizer()->normalizeUser(wp_get_current_user(), false);
    }
}
