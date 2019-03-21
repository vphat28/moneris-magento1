<?php

class Collinsharper_Moneriscc_Model_Transaction_Preauth extends Collinsharper_Moneriscc_Model_Transaction
{
    protected $_requestType = 'preauth';
    protected $_isVoidable  = true;

    protected $_canUseAvsCvd = true;

    public function buildTransactionArray()
    {
        $payment = $this->getPayment();

        if (!$payment) {
            return array();
        }


        if(Mage::helper('moneriscc')->isUsApi() && !strstr($this->_requestType, "us_")) {
            $this->_requestType = "us_" . $this->_requestType;
        }

        return array(
            'type'          => $this->_requestType,
            'order_id'      => $this->generateUniqueOrderId(),
            'cust_id'       => $this->getCustomerId(),
            'amount'        => $this->getAmount(),

            'pan'           => $payment->getCcNumber(),
            'expdate'       => Mage::getModel('moneriscc/paymentMethod')->getFormattedExpiry($payment),
            'crypt_type'    => $this->getCryptType()
        );
    }

    /**
     * @throws Exception if payment billing data is invalid
     * @param array $txnArray
     * @return Moneris_MpgTransaction
     */
    public function buildMpgTransaction($txnArray)
    {
        $mpgTxn = parent::buildMpgTransaction($txnArray);
        $payment = $this->getPayment();

        $mpgCustInfo = $this->buildMpgCustInfo($payment->getOrder());
        if ($mpgCustInfo) {
            $mpgTxn->setCustInfo($mpgCustInfo);
        }

        $mpgAvsInfo = $this->buildMpgAvsInfo($payment->getOrder()->getBillingAddress());
        if ($mpgAvsInfo) {
            $mpgTxn->setAvsInfo($mpgAvsInfo);
        }

        $mpgCvdInfo = $this->buildMpgCvdInfo($payment);
        if ($mpgCvdInfo) {
            $mpgTxn->setCvdInfo($mpgCvdInfo);
        }

        return $mpgTxn;
    }
}
