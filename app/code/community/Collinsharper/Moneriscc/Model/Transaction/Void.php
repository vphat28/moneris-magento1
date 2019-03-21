<?php

class Collinsharper_Moneriscc_Model_Transaction_Void extends Collinsharper_Moneriscc_Model_Transaction
{
    protected $_requestType = 'purchasecorrection';

    public function buildTransactionArray()
    {
        $payment = $this->getPayment();

        if (!$payment) {
            return array();
        }

        $cryptType = $this->getCryptType();
        if (!$cryptType) {
            $cryptType = $payment->getCryptType();
        }

        if(Mage::helper('moneriscc')->isUsApi()) {
            $this->_requestType = 'us_purchasecorrection';
        }


        return array(
            'type'           => $this->_requestType,
            'order_id'       => $payment->getLastTransId(),

            'crypt_type'     => $cryptType,
            'txn_number'     => $payment->getCcTransId()
        );
    }
}
