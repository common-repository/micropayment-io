<?php

class BillingFox_Plugin
{
    private static $instance;

    /**
     * simple singelton
     *
     * @return BillingFox_Plugin
     */
    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new BillingFox_Plugin();
        }

        return self::$instance;
    }

    /**
     * @var boolean
     */
    private $debug;

    /**
     * @var BillingFox_RegistrationInterface[]
     */
    private $container;

    /**
     * @var string
     */
    public $version;

    /**
     * @var string
     */
    public $root;

    private function __construct()
    {
        $this->debug = get_option(BillingFox_Admin_Setting::SETTING_DEBUG, false);
        $api_key = get_option(BillingFox_Admin_Setting::SETTING_API_KEY);
        $test_endpoint = get_option(BillingFox_Admin_Setting::SETTING_TEST_ENDPOINT);

        $this->container = [
            BillingFox_Api_Wrapper::class => new BillingFox_Api_Wrapper($api_key, $this->debug, $test_endpoint),
        ];

        $classes = [
            BillingFox_Helper_Asset::class,
            BillingFox_Helper_Template::class,
            BillingFox_Helper_Installation::class,
            BillingFox_Helper_Upgrade::class,

            BillingFox_Api_Normalizer::class,

            BillingFox_Admin_Setup::class,
            BillingFox_Admin_Setting::class,
            BillingFox_Admin_MeteredBilling::class,
            BillingFox_Admin_UserProfile::class,

            BillingFox_Profile_ShortCode::class,

            BillingFox_Post_Fence::class,
            BillingFox_Post_ShortCode::class,

            BillingFox_WooCommerce_Loader::class,
            BillingFox_WooCommerce_ProductTabs::class,
            BillingFox_WooCommerce_Orders::class,
        ];

        foreach ($classes as $class) {
            if (is_subclass_of($class, BillingFox_ContainerAware::class)) {
                $object = new $class($this);
            } else {
                $object = new $class();
            }

            $this->container[$class] = $object;
        }
    }

    /**
     * initial registration of functions
     */
    public function register()
    {
        foreach ($this->container as $entry) {
            if ($entry instanceof BillingFox_RegistrationInterface) {
                $entry->register();
            }
        }
    }

    /**
     * @return bool
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * @param string $class
     *
     * @return mixed
     */
    public function get($class)
    {
        if (!empty($this->container[$class])) {
            return $this->container[$class];
        }

        return null;
    }

}
