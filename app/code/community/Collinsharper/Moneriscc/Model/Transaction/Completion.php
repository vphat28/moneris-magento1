<?php

class Collinsharper_Moneriscc_Model_Transaction_Completion extends Collinsharper_Moneriscc_Model_Transaction
{
    protected $_requestType = 'completion';

    public function buildTransactionArray()
    {
        $payment = $this->getPayment();

        if (!$payment) {
            return array();
        }

        if(Mage::helper('moneriscc')->isUsApi()) {
            $this->_requestType = 'us_completion';
        }


        return array(
            'type'          => $this->_requestType,
            'order_id'      => $payment->getLastTransId(),
            'comp_amount'   => $this->getAmount(),

            'crypt_type'    => $this->getCryptType(),
            'txn_number'    => $payment->getCcTransId()
        );
    }
}
