<?php

class BillingFox_Helper_Installation extends BillingFox_ContainerAware implements BillingFox_RegistrationInterface
{
    const REDIRECT = '_billingfox_activation_redirect';

    public function __construct(BillingFox_Plugin $container)
    {
        parent::__construct($container);
    }

    public function register()
    {
        register_activation_hook(BILLING_FOX_PLUGIN_FILE, [$this, 'activated']);

        add_action('admin_init', [$this, 'checkRedirect']);
        add_action('admin_notices', [$this, 'showNotes']);
    }

    public function checkRedirect()
    {
        if (!get_transient(self::REDIRECT)) {
            return;
        }

        delete_transient(self::REDIRECT);

        if ((!empty($_GET['page']) && in_array($_GET['page'], [BillingFox_Admin_Setup::PAGE]) ) || is_network_admin() || isset($_GET['activate-multi'])) {
            return;
        }

        wp_safe_redirect(admin_url('index.php?page='.BillingFox_Admin_Setup::PAGE ) );
        exit;
    }

    public function activated()
    {
        $this->markDone(false);

        set_transient(self::REDIRECT, 1, 30);
    }

    public function isDone()
    {
        $done = get_option('billingfox_setup_done');

        return !empty($done);
    }

    public function isTestEndpoint()
    {
        return get_option(BillingFox_Admin_Setting::SETTING_TEST_ENDPOINT);
    }

    public function markDone($status = true)
    {
        update_option('billingfox_setup_done', $status);
    }

    public function showNotes()
    {
        if ($this->isDebug()) {
            $html = <<<HTML
<div class="error">
    <p>
        {{text}} - <a href="{{config_link}}">{{config_text}}</a>
    </p>
</div>
HTML;

            echo $this->getTemplating()->render(
                $html,
                [
                    'text' => __('BillingFox Debug Mode is active', BILLING_FOX_TRANSLATE),
                    'config_link' => admin_url('options-general.php?page=billingfox'),
                    'config_text' => __('change', BILLING_FOX_TRANSLATE),
                ]
            );
        }

        if ($this->isTestEndpoint()) {
            $html = <<<HTML
<div class="error">
    <p>
        {{text}} - <a href="{{config_link}}">{{config_text}}</a>
    </p>
</div>
HTML;

            echo $this->getTemplating()->render(
                $html,
                [
                    'text' => __('BillingFox uses test api endpoint', BILLING_FOX_TRANSLATE),
                    'config_link' => admin_url('options-general.php?page=billingfox'),
                    'config_text' => __('change', BILLING_FOX_TRANSLATE),
                ]
            );
        }

        if ($this->isDone()) {
            return;
        }

        $html = <<<HTML
<div class="updated">
    <p>
        {{text}} - {{goto}} <a href="{{setup_link}}">{{setup_text}}</a> {{or}} <a href="{{config_link}}">{{config_text}}</a>
    </p>
</div>
HTML;

        echo $this->getTemplating()->render(
            $html,
            [
                'text' => __('Micropayment requires an API Key', BILLING_FOX_TRANSLATE),
                'setup_link' => admin_url( 'index.php?page='.BillingFox_Admin_Setup::PAGE ),
                'setup_text' => __('setup', BILLING_FOX_TRANSLATE),
                'config_link' => admin_url('options-general.php?page=billingfox'),
                'config_text' => __('configuration', BILLING_FOX_TRANSLATE),
                'goto' => __('goto', BILLING_FOX_TRANSLATE),
                'or' => __('or', BILLING_FOX_TRANSLATE),
            ]
        );
    }
}