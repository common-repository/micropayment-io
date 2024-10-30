<?php

class BillingFox_Admin_Setup extends BillingFox_ContainerAware implements BillingFox_RegistrationInterface
{
    const PAGE = 'micropayment-io-setup';

    public function register()
    {
        if (!empty($_GET['page']) && $_GET['page'] == self::PAGE) {
            add_action( 'admin_menu', [$this, 'menu']);
            add_action( 'admin_init', [$this, 'init']);
        }
    }

    public function menu()
    {
        add_dashboard_page( '', '', 'manage_options', self::PAGE, '' );
    }

    public function init()
    {
        wp_enqueue_style('css-help', plugins_url(BILLING_FOX_PLUGIN_NAME.'/resources/css/help.css'), [], $this->getVersion());
        wp_enqueue_style('css-setup', plugins_url(BILLING_FOX_PLUGIN_NAME.'/resources/css/setup.css'), ['dashicons', 'install'], $this->getVersion());

        $step = empty($_GET['step'])?'intro':$_GET['step'];
        $args = [];

        if (!empty($_POST)) {
            check_admin_referer(self::PAGE);

            switch ($step) {
                case 'api_key':
                    // validate and then update slug

                    update_option(BillingFox_Admin_Setting::SETTING_API_KEY, $_POST['api_key']);
                    $api = new BillingFox_Api_Wrapper($_POST['api_key'], false);

                    if (!$api->ping()) {
                        $args['error'] = __('Invalid API KEY!', BILLING_FOX_TRANSLATE);
                    } else {
                        wp_safe_redirect(admin_url('index.php?page='.self::PAGE.'&step=woo_commerce'));
                        exit;
                    }

                    break;

                case 'woo_commerce':
                    // validate and then update slug
                    update_option(BillingFox_Admin_Setting::SETTING_WOOCOMMERCE_CATEGORY_SLUG, $_POST['category']);

                    /** @var BillingFox_WooCommerce_Loader $wc_loader */
                    $wc_loader = $this->get(BillingFox_WooCommerce_Loader::class);

                    if ($_POST['category'] && !$wc_loader->getCategoryLink($_POST['category'])) {
                        $args['error'] = __('Invalid Category Slug!', BILLING_FOX_TRANSLATE);
                    } else {
                        wp_safe_redirect(admin_url('index.php?page='.self::PAGE.'&step=ready'));
                        exit;
                    }

                    break;
            }
        }

        $steps = [
            'intro' => [
                'file' => 'admin/setup/steps/intro.php',
                'title' => __('Intro', BILLING_FOX_TRANSLATE),
                'active' => 'intro' == $step,
            ],
            'api_key' => [
                'file' => 'admin/setup/steps/api_key.php',
                'title' => __('Api Key', BILLING_FOX_TRANSLATE),
                'active' => 'api_key' == $step,
            ],
            'woo_commerce' => [
                'file' => 'admin/setup/steps/woo_commerce.php',
                'title' => __('WooCommerce', BILLING_FOX_TRANSLATE),
                'active' => 'woo_commerce' == $step,
            ],
            'ready' => [
                'file' => 'admin/setup/steps/ready.php',
                'title' => __('Ready!', BILLING_FOX_TRANSLATE),
                'active' => 'ready' == $step,
            ],
        ];

        $this->getTemplating()->renderTemplate('admin/setup/header.php');
        $this->getTemplating()->renderTemplate('admin/setup/steps.php', ['steps' => $steps]);

        if (empty($steps[$step])) {
            $active = $steps['intro'];
        } else {
            $active = $steps[$step];
        }
        $this->getTemplating()->renderTemplate($active['file'], $args);

        $this->getTemplating()->renderTemplate('admin/setup/footer.php');
        exit;
    }
}