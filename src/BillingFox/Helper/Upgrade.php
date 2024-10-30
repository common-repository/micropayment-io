<?php

class BillingFox_Helper_Upgrade extends BillingFox_ContainerAware implements BillingFox_RegistrationInterface
{
    const VERSION_KEY = 'billingfox_plugin_version';

    public function register()
    {
        add_action('plugins_loaded', [$this, 'testForUpdates']);
    }

    public function testForUpdates()
    {
        if (!$this->lt(get_option(self::VERSION_KEY), $this->getVersion())) {
            return;
        }

        $class = 'BillingFox_Migration_Version_'.str_replace('.', '_', $this->getVersion());

        if (!class_exists($class)) {
            return;
        }

        /** @var BillingFox_Migration_AbstractVersion $migration */
        $migration = new $class;

        $migration->up();
    }

    /**
     * basically check if 0.8.3 < 0.84 = true
     *
     * @param string $v1
     * @param string $v2
     *
     * @return boolean
     */
    private function lt($v1, $v2)
    {
        $v1 = explode('.', $v1);
        $v2 = explode('.', $v2);

        for ($i = 0; $i < 3; $i++) {
            $a = empty($v1[$i]) ? 0 : (int)$v1[$i];
            $b = empty($v2[$i]) ? 0 : (int)$v2[$i];

            if ($a < $b) {
                return true;
            }

            if ($a > $b) {
                return false;
            }
        }

        return false;
    }
}