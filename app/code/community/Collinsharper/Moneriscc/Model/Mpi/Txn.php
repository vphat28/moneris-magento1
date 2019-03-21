<?php

class Collinsharper_Moneriscc_Model_Mpi_Txn extends Collinsharper_Moneriscc_Model_Mpi
{
    protected $_requestType = 'txn';

    /**
     * Checks if the card is enrolled in VBV/MCSC. If so,
     * sets authentication to happen. Returns the crypt type to be used
     * for the transaction.
     * If a form is returned, it is set in to the session.
     *
     * @param Varien_Object $payment, float $amount
     * @return string $cryptType
     */
    public function fetchCryptType()
    {
        Mage::helper('moneriscc')->log(__METHOD__ . __LINE__ . " in crypt test");
        $payment = $this->getPayment();
        $amount = $this->getAmount();
        $order = $payment->getOrder();

        // store the quote id in the session so the cart can be recovered if vbv goes awry
        Mage::helper('moneriscc')->getCheckoutSession()->setMonerisccQuoteId($order->getQuoteId());

        $mpiResponse = $this->post();
        $cryptType = $this->_interpretMpiResponse($mpiResponse, $payment);
        Mage::helper('moneriscc')->log(__METHOD__ . __LINE__ . " we have crypt test " . $cryptType);
        Mage::helper('moneriscc')->getCheckoutSession()->setCryptType($cryptType);

        return $cryptType;
    }

    protected function _interpretMpiResponse($mpiResponse, $payment)
    {
        $mpiMessage = $mpiResponse->getMpiMessage();

        // check response message
         Mage::helper('moneriscc')->log(__METHOD__ . __LINE__ . ' we have mpi response ' . $mpiMessage .  ' and orderid ' . $payment->getOrder()->getId());
         Mage::helper('moneriscc')->log(__METHOD__ . __LINE__ . ' class  ' .  get_class($mpiResponse));
         Mage::helper('moneriscc')->log(__METHOD__ . __LINE__ . ' data class  ' .  print_r($mpiResponse, 1));

        switch ($mpiMessage) {
            case 'N':
                // card/issuer is not enrolled; proceed with transaction as usual?
                // Visa: merchant NOT liable for chargebacks
                // Mastercard: merchant IS liable for chargebacks
                $cryptType = '6';
                break;
            case 'U':
                // card type does not participate
                // merchant IS liable for chargebacks
                $cryptType = '7';
                break;
            case 'Y':
                // card is enrolled; the included form should be displayed for user authentication
                $form = $mpiResponse->getMpiInLineForm();
                Mage::helper('moneriscc')->getCheckoutSession()->setMonerisccMpiForm($form);
                Mage::helper('moneriscc')->getCheckoutSession()->setMonerisccOrderId($payment->getOrder()->getId());

                // crypt type will depend on the PaRes, but use 5 to signal enrollment
                $cryptType = '5';

                // abuse the additional_information field by making it hold the cryptType for capture ...
                $payment->setAdditionalInformation(array(
                    'crypt' => $cryptType
                ));

                break;
            case 'null':
                Mage::logException(new Exception('Moneris endpoint is not responding.'));
                $cryptType = '7';
                break;
            default:
                Mage::logException(new Exception('Unexpected MPI message: ' . $mpiMessage));
                $cryptType = '7';
        }

        return $cryptType;
    }

    public function buildTransactionArray()
    {
        $payment = $this->getPayment();
        $amount = $this->getAmount();

        // must be exactly 20 alphanums
        $xid = sprintf("%'920d", rand());

        $expiry = Mage::getModel('moneriscc/paymentMethod')->getFormattedExpiry($payment);
        $merchantUrl = Mage::getUrl('moneriscc/payment/return', array('_secure' => true));

        $txnArray = array(
            'type'          => $this->_requestType,
            'xid'           => $xid,
            'amount'        => $amount,
            'pan'           => $payment->getCcNumber(),
            'expdate'       => $expiry,
            'MD'            => htmlspecialchars(http_build_query(array(     // must be encoded to pass through XML
                                    'xid'       => $xid,          // MD is merchant data that can be passed along
                                    'pan'       => $payment->getCcNumber(),
                                    'expiry'    => $expiry,
                                    'amount'    => $amount
                               ))),
            'merchantUrl'   => $merchantUrl,
            'accept'        => getenv('HTTP_ACCEPT'),
            'userAgent'     => getenv('HTTP_USER_AGENT')
        );

        return $txnArray;
    }
}
