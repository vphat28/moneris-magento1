<?php


class Collinsharper_Moneriscc_Block_Info_Moneris extends Mage_Payment_Block_Info_Cc
{
    /**
     * Init default template for block
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('moneriscc/info/moneris.phtml');
    }

    /**
     * If payment has a CAVV, then there has been a liability shift.
     *
     * @return string 'Yes' or 'No'
     */
    public function getHasLiabilityShifted()
    {
        $cavv = Mage::helper('moneriscc')->getPaymentAdditionalInfo($this->getInfo()->getOrder()->getPayment(), 'cavv');
        return ($cavv ? 'Yes' : 'No');
    }
}
