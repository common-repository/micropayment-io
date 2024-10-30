<?php

class BillingFox_Api_InsufficientCoins extends BillingFox_Api_Exception
{
    private $invoiceLink;

    /**
     * @return mixed
     */
    public function getInvoiceLink()
    {
        return $this->invoiceLink;
    }

    /**
     * @param mixed $invoiceLink
     */
    public function setInvoiceLink($invoiceLink)
    {
        $this->invoiceLink = $invoiceLink;
    }
}