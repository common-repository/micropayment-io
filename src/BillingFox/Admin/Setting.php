<?php

class BillingFox_Admin_Setting extends BillingFox_ContainerAware implements BillingFox_RegistrationInterface
{
    const SETTING_API_KEY = 'billingfox_api_key';
    const SETTING_DEBUG = 'billingfox_debug';
    const SETTING_WOOCOMMERCE_CATEGORY_SLUG = 'billingfox_woocommerce_category_slug';
    const SETTING_TEST_ENDPOINT = 'billingfox_test_endpoint';

    public function register()
    {
        add_action('admin_init', [$this, 'init']);
        add_action('admin_menu', [$this, 'registerMenu']);
    }

    /**
     * called on admin_init
     */
    public function init()
    {
        register_setting('billingfox', self::SETTING_WOOCOMMERCE_CATEGORY_SLUG, 'string');
        register_setting('billingfox', self::SETTING_API_KEY, 'string');
        register_setting('billingfox', self::SETTING_DEBUG, 'boolean');
        register_setting('billingfox', self::SETTING_TEST_ENDPOINT, 'boolean');

        add_settings_section(
            'default',
            __('Micropayment Configuration', BILLING_FOX_TRANSLATE),
            [$this, 'apiDescription'],
            'billingfox'
        );

        add_settings_field(
            self::SETTING_API_KEY,
            __('Bearer Token', BILLING_FOX_TRANSLATE),
            [$this, 'renderInput'],
            'billingfox',
            'default',
            [
                'label' => __('Bearer Token', BILLING_FOX_TRANSLATE),
                'key' => self::SETTING_API_KEY,
            ]
        );

        if ($this->get(BillingFox_WooCommerce_Loader::class)->exists()) {
            add_settings_field(
                self::SETTING_WOOCOMMERCE_CATEGORY_SLUG,
                __('WooCommerce Category Slug', BILLING_FOX_TRANSLATE),
                [$this, 'renderInput'],
                'billingfox',
                'default',
                [
                    'label' => __('WooCommerce Category Slug', BILLING_FOX_TRANSLATE),
                    'key' => self::SETTING_WOOCOMMERCE_CATEGORY_SLUG,
                ]
            );
        }

        add_settings_field(
            self::SETTING_DEBUG,
            __('Debug Mode', BILLING_FOX_TRANSLATE),
            [$this, 'renderCheckbox'],
            'billingfox',
            'default',
            [
                'label' => __('Debug Mode', BILLING_FOX_TRANSLATE),
                'key' => self::SETTING_DEBUG,
            ]
        );

        add_settings_field(
            self::SETTING_TEST_ENDPOINT,
            __('Use test api endpoint', BILLING_FOX_TRANSLATE),
            [$this, 'renderCheckbox'],
            'billingfox',
            'default',
            [
                'label' => __('Use test api endpoint', BILLING_FOX_TRANSLATE),
                'key' => self::SETTING_TEST_ENDPOINT,
            ]
        );
    }

    public function apiDescription()
    {
        echo __('Metered Payments for your Blog', BILLING_FOX_TRANSLATE);
    }

    public function renderCheckbox($args)
    {
        echo sprintf(
            '<input name="%s" type="checkbox" id="%s" value="1" %s class="regular-text" />',
            $args['key'],
            $args['key'],
            get_option( $args['key'] ) ? 'checked' : ''
        );
    }
    public function renderInput($args)
    {
        echo sprintf(
            '<input name="%s" type="text" id="%s" value="%s" class="regular-text" />',
            $args['key'],
            $args['key'],
            esc_attr( get_option( $args['key'] ) )
        );
    }

    /**
     * called for admin_menu
     */
    public function registerMenu()
    {
        add_options_page(
            'Micropayment Config',
            'Micropayment',
            'manage_options',
            'billingfox',
            [$this, 'render']
        );
    }

    public function render()
    {
        $this->checkAccess();

        ?>
        <div class="wrap">
            <h1><?php echo esc_html( 'Micropayment' ); ?></h1>
            <?php

            /** @var BillingFox_Helper_Installation $installation */
            $installation = $this->get(BillingFox_Helper_Installation::class);


            $ok = true;

            if ($this->isValidApiKey()) {
                echo sprintf(
                    '<div class="updated"><p>%s</p></div>',
                    __('Valid API Key', BILLING_FOX_TRANSLATE)
                );
            } elseif (get_option('billingfox_api_key')) {
                echo sprintf(
                    '<div class="error"><p>%s</p></div>',
                    __('Invalid API Key! (Ping failed)', BILLING_FOX_TRANSLATE)
                );

                $ok = false;
            }

            /** @var BillingFox_WooCommerce_Loader $wc_loader */
            $wc_loader = $this->get(BillingFox_WooCommerce_Loader::class);

            if (!$wc_loader->exists()) {
                echo sprintf(
                    '<div class="error"><p>%s</p></div>',
                    __('WooCommerce not installed / active', BILLING_FOX_TRANSLATE)
                );
            } else {
                $slug = get_option(self::SETTING_WOOCOMMERCE_CATEGORY_SLUG);
                if ($slug && !$wc_loader->getCategoryLink($slug)) {
                    echo sprintf(
                        '<div class="error"><p>%s</p></div>',
                        __('Invalid WooCommerce Category Slug', BILLING_FOX_TRANSLATE)
                    );

                    $ok = false;
                }
            }

            $installation->markDone($ok);

            ?>

            <form action="options.php" method="post">
                <?php settings_fields('billingfox'); ?>
                <?php do_settings_sections('billingfox'); ?>
                <?php submit_button(); ?>
            </form>

            <small><a href="<?php echo admin_url( 'index.php?page='.BillingFox_Admin_Setup::PAGE ); ?>"><?php _e('Open Setup again', BILLING_FOX_TRANSLATE); ?></a></small>

            <?php $this->getTemplating()->renderTemplate('admin/help.php', []); ?>
        </div>
        <?php
    }

    private function isValidApiKey()
    {
        return $this->get(BillingFox_Api_Wrapper::class)->ping();
    }

    /**
     * check access
     */
    private function checkAccess()
    {
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }
    }
}