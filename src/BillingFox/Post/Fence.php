<?php

class BillingFox_Post_Fence extends BillingFox_ContainerAware implements BillingFox_RegistrationInterface
{
    const SESSION_ERROR = 'billingfox_error';
    const SESSION_READ = 'billingfox_read';
    const SESSION_REMEMBER = 'billingfox_remember';

    public function register()
    {
        add_action('init', [$this, 'startSession'], 1);
        add_action('the_content', [$this, 'checkForAccess']);
        add_action('wp', [$this, 'grantAccess']);
        add_action('send_headers', [$this, 'sendHeaders']);
    }

    public function sendHeaders()
    {
        header('x-micropayment: yes');
    }

    public function startSession()
    {
        if (!session_id()) {
            session_start();
        }
    }

    public function checkForAccess($content)
    {
        /** @var WP_Post $post */
        global $post;

        if (!$this->canReadPost($post, $content)) {

            $this->remember(
                $this->getPaymentSlug($post),
                get_post_meta($post->ID, BillingFox_Admin_MeteredBilling::FIELD_PRICE, true),
                $post->post_title
            );

            ob_start();
            $this->getTemplating()->renderTemplate('fence/post.php', [
                'price' => get_post_meta($post->ID, BillingFox_Admin_MeteredBilling::FIELD_PRICE, true),
                'slug' => $this->getPaymentSlug($post),
                'description' => $post->post_title,
            ]);
            $output = ob_get_contents();
            ob_end_clean();

            return $output;
        }

        return $content;
    }

    /**
     * @param string $slug
     * @return string|false
     */
    public function getError($slug)
    {
        if (empty($_SESSION[self::SESSION_ERROR][$slug])) {
            return false;
        }

        switch($_SESSION[self::SESSION_ERROR][$slug]) {
            case 'invalid_request':
                return 'Please submit Payment again';
            case 'insufficient_funds':
                return 'You do not have enough Credits';
            case 'api_error':
                return 'We could not process your request, please try again later';

            default:
                return $_SESSION[self::SESSION_ERROR][$slug];
        }
    }

    /**
     * @param $slug
     * @param $cost
     * @param string $description
     */
    public function remember($slug, $cost, $description = '')
    {
        if (empty($_SESSION[self::SESSION_REMEMBER])) {
            $_SESSION[self::SESSION_REMEMBER] = [];
        }

        $_SESSION[self::SESSION_REMEMBER][$slug] = [
            'price' => $cost,
            'description' => $description,
        ];
    }

    /**
     * get slug to group payment requests
     *
     * @param WP_Post $post
     * @return string
     */
    public function getPaymentSlug($post)
    {
        $slug = get_post_meta($post->ID, BillingFox_Admin_MeteredBilling::FIELD_SLUG, true);

        if (!empty($slug)) {
            return $slug;
        }

        return 'post:'.$post->ID;
    }

    /**
     * @param $slug
     *
     * @return boolean
     */
    public function canReadSlug($slug)
    {
        return true == $_SESSION[self::SESSION_READ][$slug];
    }

    /**
     * checked on post if user should purchase content!
     */
    public function grantAccess()
    {
        if (empty($_POST['billingfox']['grant']) || !is_user_logged_in()) {
            return;
        }

        $_SESSION[self::SESSION_ERROR] = [];

        /** @var BillingFox_Api_Wrapper $api */
        $api = $this->get(BillingFox_Api_Wrapper::class);

        /** @var BillingFox_Api_Normalizer $normalizer */
        $normalizer = $this->get(BillingFox_Api_Normalizer::class);

        $slug = $_POST['billingfox']['grant'];
        $user = $normalizer->normalizeUser(wp_get_current_user());

        if (empty($_SESSION[self::SESSION_REMEMBER][$slug])) {
            $this->setError($slug, 'invalid_request');
            return;
        }

        $price = $_SESSION[self::SESSION_REMEMBER][$slug]['price'];
        $description = $_SESSION[self::SESSION_REMEMBER][$slug]['description'];

        if (empty($price)) {
            $this->setError($slug, 'invalid_request');
            return;
        }
           // get_post_meta($post->ID, BillingFox_Admin_MeteredBilling::FIELD_PRICE, true);

        try {
            $api->spend($user, $price, $description);
            if (empty($_SESSION[self::SESSION_READ])) {
                $_SESSION[self::SESSION_READ] = [];
            }

            $_SESSION[self::SESSION_READ][$slug] = true;
        } catch (BillingFox_Api_InsufficientCoins $e) {
            $this->setError($slug, 'insufficient_funds');
        } catch (BillingFox_Api_Exception $e) {
            $this->setError($slug, 'api_error');
        }
    }

    /**
     * @param $slug
     * @param $message
     */
    protected function setError($slug, $message)
    {
        $_SESSION[self::SESSION_ERROR][$slug] = $message;
    }

    /**
     * @param $post
     * @param $content
     * @return bool
     */
    protected function canReadPost($post, $content)
    {
        if (!$post) {
            return true;
        }

        if ($this->canReadSlug($this->getPaymentSlug($post))) {
            return true;
        }

        if (strpos($content, '['.BillingFox_Post_ShortCode::SHORT_CODE) !== false) {
            return true;
        }

        if (!get_post_meta($post->ID, BillingFox_Admin_MeteredBilling::FIELD_PRICE, true)) {
            return true;
        }

        return false;
    }
}
