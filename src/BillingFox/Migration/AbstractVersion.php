<?php

abstract class BillingFox_Migration_AbstractVersion
{
    public function isFinished()
    {
        return true;
    }

    protected function finish($version)
    {
        update_option(BillingFox_Helper_Upgrade::VERSION_KEY, $version);
    }

    public abstract function up();
}