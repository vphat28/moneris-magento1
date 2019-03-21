<?php

class Collinsharper_Moneriscc_Model_Transaction_Refund extends Collinsharper_Moneriscc_Model_Transaction
{
    protected $_requestType = 'refund';

    public function buildTransactionArray()
    {
        $payment = $this->getPayment();

        if (!$payment) {
            return array();
        }

        if(Mage::helper('moneriscc')->isUsApi()) {
            $this->_requestType = 'us_refund';
        }

        return array(
            'type'          => $this->_requestType,
            'order_id'      => $payment->getLastTransId(),
            'amount'        => $this->getAmount(),

            'crypt_type'    => $this->getCryptType() ? $this->getCryptType() : 5,
            'txn_number'    => $payment->getCcTransId()
        );
    }
}
