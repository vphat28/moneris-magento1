<?php

class Collinsharper_Moneriscc_Block_Redirect extends Mage_Core_Block_Template
{
    public function getFormHtml()
    {
        $session = Mage::getSingleton('checkout/session');
        return $session->getMonerisccMpiForm();
    }
}
