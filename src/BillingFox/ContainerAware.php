<?php

abstract class BillingFox_ContainerAware
{
    private $container;

    public function __construct(BillingFox_Plugin $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    protected function get($name)
    {
        return $this->container->get($name);
    }

    /**
     * @return bool
     */
    protected function isDebug()
    {
        return $this->container->isDebug();
    }

    /**
     * @return string
     */
    protected function getVersion()
    {
        return $this->container->version;
    }

    /**
     * @return BillingFox_Helper_Template
     */
    protected function getTemplating()
    {
        return $this->get(BillingFox_Helper_Template::class);
    }

    /**
     * @return BillingFox_Api_Wrapper
     */
    protected function getApi()
    {
        return $this->get(BillingFox_Api_Wrapper::class);
    }

    /**
     * @return BillingFox_Api_Normalizer
     */
    protected function getNormalizer()
    {
        return $this->get(BillingFox_Api_Normalizer::class);
    }
}