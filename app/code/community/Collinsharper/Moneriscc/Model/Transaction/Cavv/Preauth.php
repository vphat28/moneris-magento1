<?php

class Collinsharper_Moneriscc_Model_Transaction_Cavv_Preauth extends Collinsharper_Moneriscc_Model_Transaction_Preauth
{
    protected $_requestType = 'cavv_preauth';

    public function buildTransactionArray()
    {
        $txnArray = parent::buildTransactionArray();

        if (empty($txnArray)) {
            return $txnArray;
        }

        unset($txnArray['crypt_type']);

        $txnArray = array_merge($txnArray, array(
            'cavv'  => $this->getCavv()
        ));

        return $txnArray;
    }

    protected function _updatePayment(Varien_Object $result)
    {
        parent::_updatePayment($result);

        $payment = $this->getPayment();

        if (!$payment) {
            return $this;
        }

        Mage::helper('moneriscc')->setPaymentAdditionalInfo($payment, 'cavv', $this->getCavv());

        return $this;
    }
}
