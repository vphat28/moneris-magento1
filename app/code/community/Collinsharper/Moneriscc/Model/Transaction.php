<?php

/**
 * Usage:
 * $mpgTxn = Mage::getModel('moneriscc/transaction')
 *      ->setPayment($payment)
 *      ->setAmount($amount)
 *      ->setCryptType($crypt)  // if needed
 *      ->post();
 */
class Collinsharper_Moneriscc_Model_Transaction extends Collinsharper_Moneriscc_Model_Abstract
{
    protected $_eventPrefix = 'moneriscc_transaction';
    protected $_eventObject = 'transaction';


    const MAX_CHARS_CUSTOMER_ID = 50;
    const CUSTOMER_ID_DELIM = '-';

    
    /**
     * Set these in the child class
     */
    protected $_requestType     = 'none';
    protected $_isVoidable      = false;
    protected $_isRefundable    = false;

    /**
     * Only [cavv_]?purchases and [cavv_]?preauths can use AVS/CVD
     */
    protected $_canUseAvsCvd = false;

    /**
     * @return string, the request type
     */
    public function getRequestType()
    {
        return $this->_requestType;
    }

    public function getIsVoidable()
    {
        return $this->_isVoidable;
    }

    public function getIsRefundable()
    {
        return $this->_isRefundable;
    }

    public function getPaymentAction()
    {
        return Mage::helper('moneriscc')->getPaymentAction();
    }

    /**
     * Returns a unique customer ID that is human  readable
     * Max length of 50
     * @return string
     */
    public function getCustomerId()
    {
        if(!Mage::helper("moneriscc")->getModuleConfig('payment/moneriscc/use_customer_name')) {
            return $this->getPayment()->getOrder()->getCustomerId();
        }

        $payment = $this->getPayment();
        $billingObj = $payment->getOrder()->getBillingAddress();
        $customerId = self::CUSTOMER_ID_DELIM . $payment->getOrder()->getCustomerId();

        $fullCustomerName = $billingObj->getFirstname() . self::CUSTOMER_ID_DELIM . $billingObj->getLastname();
        // we can only send 50 chars
        $customerIdLength = strlen($customerId);
        $fullCustomerName = substr($fullCustomerName, 0, (self::MAX_CHARS_CUSTOMER_ID - $customerIdLength)) . $customerId;
        return $fullCustomerName;
    }


    /**
     * Returns a unique order ID consisting of the order increment ID and
     * a generated tail.
     * Max length of 50
     *
     * @return string
     */
    public function generateUniqueOrderId($len=20)
    {
        $order = $this->getPayment()->getOrder();

        if (!$order) {
            return '';
        }

        $incrementId = $order->getIncrementId();

        $tail = '';
        for ($i = 0; $i < $len; $i++) {
            $tail .= rand(0, 9);
        }

        return "{$incrementId}-{$tail}";
    }

    /**
     * Builds the appropriate transaction array for an MpgTransaction
     * to be built for this transaction.
     *
     * Override in child class.
     *
     * @return array
     */
    public function buildTransactionArray()
    {
        return array(
            'This should be overridden in the extending class.'
        );
    }

    /**
     * Builds a Moneris_MpgTransaction from the $txnArray.
     *
     * @param array $txnArray
     * @return Moneris_MpgTransaction
     */
    public function buildMpgTransaction($txnArray)
    {
        if(Mage::helper('moneriscc')->isUsApi()) {
            $mpgTxn = new Monerisus_MpgTransaction($txnArray);
        } else {
            $mpgTxn = new Moneris_MpgTransaction($txnArray);
        }
        return $mpgTxn;
    }

    /**
     * Builds a Moneris_MpgRequest from the $mpgTxn
     *
     * @param Moneris_MpgTransaction $mpgTxn
     * @return Moneris_MpgRequest
     */
    public function buildMpgRequest($mpgTxn)
    {
        if(Mage::helper('moneriscc')->isUsApi()) {
            $mpgRequest = new Monerisus_MpgRequest($mpgTxn);
        } else {
            $mpgRequest = new Moneris_MpgRequest($mpgTxn);
        }

        return $mpgRequest;
    }

    /**
     * Builds a Moneris_MpgHttpsPost from the $mpgRequest
     *
     * @param Moneris_MpgTransaction $mpgRequest
     * @return Moneris_MpgHttpsPost
     */
    // we cannot type cast it as there are two types Moneris_MpgRequest and Monerisus_MpgRequest
    public function buildMpgHttpsPost($mpgRequest)
    {
        $storeId = Mage::helper('moneriscc')->getMonerisStoreId();
        $apiToken = Mage::helper('moneriscc')->getMonerisApiToken();
        Mage::helper('moneriscc')->log(__FILE__." ".__LINE__." store $storeId");
        Mage::helper('moneriscc')->log(__FILE__." ".__LINE__." api $apiToken");
        if(Mage::helper('moneriscc')->isUsApi()) {
            $mpgHttpsPost = new Monerisus_MpgHttpsPost($storeId, $apiToken, $mpgRequest);
        } else {
            $mpgHttpsPost = new Moneris_MpgHttpsPost($storeId, $apiToken, $mpgRequest);
        }
        return $mpgHttpsPost;
    }

    /**
     * Gets the Moneris_MpgResponse from the $mpgHttpsPost.
     *
     * @param Moneris_MpgHttpsPost $mpgHttpsPost
     * @return Moneris_MpgResponse
     */

    // TODO we have two classes like this . cant define it Moneris_MpgHttpsPost Monerisus_MpgHttpsPost
    public function getMpgResponse($mpgHttpsPost)
    {
        return $mpgHttpsPost->getMpgResponse();
    }

    /**
     * Returns true if $code is a successful one; else, false.
     *
     * @param mixed $code
     * @return bool
     */
    public function getIsSuccessfulResponseCode($code)
    {
        return (is_numeric($code) && $code >= 0 && $code < 50);
    }

    /**
     * Posts the transaction to Moneris. Uses buildTransactionArray() if none is passed in.
     *
     * @param array $txnArray=null
     * @return Varien_Object $result
     */
    public function post($txnArray=null)
    {
        Mage::dispatchEvent($this->_eventPrefix . '_post_before',
                            $this->_getEventData());

        try {
            if (!$txnArray) {
                $txnArray = $this->buildTransactionArray();
            }

            $result = $this->_post($txnArray);
            $this->setResult($result);
        } catch (Exception $e) {
            Mage::logException($e);
        }

        Mage::dispatchEvent($this->_eventPrefix . '_post_after',
                            $this->_getEventData());

        return $result;
    }

    /**
     * Posts the transaction.
     *
     * @param array $txnArray
     * @return Varien_Object result
     */
    protected function _post($txnArray)
    {
        $mpgTxn = $this->buildMpgTransaction($txnArray);
        $mpgRequest = $this->buildMpgRequest($mpgTxn);
        $mpgHttpsPost = $this->buildMpgHttpsPost($mpgRequest);
        $mpgResponse = $this->getMpgResponse($mpgHttpsPost);

        $result = $this->_getResultFromMpgResponse($mpgResponse);

        Mage::helper('moneriscc')->log('txnArray: ' . print_r($txnArray, true));
        //Mage::helper('moneriscc')->log('mpgTxn: ' . print_r($mpgTxn, true));
        //Mage::helper('moneriscc')->log('mpgResponse: ' . print_r($mpgResponse, true));
        Mage::helper('moneriscc')->log('result obj: ' . print_r($result, true));

        $responseCode = $result->getResponseCode();
        $responses = Mage::helper('moneriscc')->getResponses();

        if ($result->getError()) {
            $result->setStatus(Collinsharper_Moneriscc_Model_PaymentMethod::STATUS_ERROR);
        } else if (!$result->getSuccess()) {
            if (isset($responses[$responseCode])) {
                $message = $responses[$responseCode]['message'];
            } else {
                $message = $result->getMessage();
            }
            $result->setStatus(Collinsharper_Moneriscc_Model_PaymentMethod::STATUS_DECLINED)
                ->setDescription($message)
                ->setResponseText($message);
        }

        // check avs/cvd
        if ($result->getSuccess() && $this->_canUseAvsCvd) {
            if (!$this->_checkAvs($result)) {
                return $result;
            }

            if (!$this->_checkCvd($result)) {
                return $result;
            }
        }

        $this->_updatePayment($result);
        $receipt = $this->_buildReceipt($result);
        Mage::getSingleton('customer/session')->setMoneriscccData($receipt);

        return $result;
    }

    /**
     * Updates the payment object with the $result data.
     *
     * @param Varien_Object $result
     * @return this
     */
    protected function _updatePayment(Varien_Object $result)
    {
        $payment = $this->getPayment();

        if (!$payment) {
            return $this;
        }

        $this->getPayment()
            ->setStatus($result->getStatus())
            ->setCcApproval($result->getAuthCode())
         //   ->setLastTransId($result->getLastTransId())
        //    ->setCcTransId($result->getTxnNumber())
            ->setCcAvsStatus($result->getAvsResultCode())
            ->setCcCidStatus($result->getCvdResultCode());

        // dont change ID on refund.. so we can refund again.
        
        if($this->_requestType != 'refund') {
        $this->getPayment()
            ->setLastTransId($result->getLastTransId())
            ->setCcTransId($result->getTxnNumber())
        ;
        }

        if(!$this->getPayment()->getLastTransId()) {
            $this->getPayment()
                ->setLastTransId($result->getLastTransId());
        }

        if(!$this->getPayment()->getTransactionId()) {
            $this->getPayment()
                ->setTransactionId($result->getLastTransId());
        }

        if(!$this->getPayment()->getCcTransId()) {
            $this->getPayment()
                ->setCcTransId($result->getTxnNumber());
        }

        return $this;
    }

    /**
     * Builds an array holding the data for the transaction receipt.
     *
     * @param Varien_Object $result
     * @return array $receipt
     */
    protected function _buildReceipt(Varien_Object $result)
    {
        // TODO: We may need to detect the store currency here
        $currency = "";
        if ($this->getPayment()->getOrder()) {
            $currency = $this->getPayment()->getOrder()->getOrderCurrency()->getCurrencyCode();
        }
        $receipt = array(
            'trnId'             => $result->getTxnNumber(),
            'trnOrderNumber'    => $result->getReferenceNum(),
            'trnAmount'         => $this->getAmount(),
            'currency'          => $currency,
            'authCode'          => $result->getAuthCode(),
            'messageText'       => $result->getMessage(),
            'trnDate'           => $result->getTransDate() . ' ' . $result->getTransTime()
        );

        return $receipt;
    }

    /**
     * Puts the data from an mpgResponse in to a more useful Varien_Object.
     *
     * @param Moneris_MpgResponse $mpgResponse
     * @return Varien_Object $result
     */
    protected function _getResultFromMpgResponse($mpgResponse)
    {
        $result = new Varien_Object();
        $result->setData(array(
            'success'           => $this->getIsSuccessfulResponseCode($mpgResponse->getResponseCode()),
            'error'             => !is_numeric($mpgResponse->getResponseCode()),
            'response_code'     => $mpgResponse->getResponseCode(),
            'last_trans_id'     => $mpgResponse->getReceiptId(),
            'cvd_result_code'   => $mpgResponse->getCvdResultCode(),
            'avs_result_code'   => ($mpgResponse->getAvsResultCode() != 'null') ? $mpgResponse->getAvsResultCode() : null,
            'message'           => $mpgResponse->getMessage(),
            'description'       => $mpgResponse->getMessage(),
            'txn_number'        => $mpgResponse->getTxnNumber(),
            'reference_num'     => $mpgResponse->getReferenceNum(),
            'auth_code'         => $mpgResponse->getAuthCode(),
            'iso_code'          => $mpgResponse->getISO(),
            'trans_date'        => $mpgResponse->getTransDate(),
            'trans_time'        => $mpgResponse->getTransTime(),
            'raw_data'          => $mpgResponse->getMpgResponseData()
        ));

        return $result;
    }

    /**
     * Sends a void transaction if auth mode.
     * Sends a refund if $result if auth & capture mode.
     *
     * @param Varien_Object $result
     * @return this
     */
    protected function _undoTransaction($result)
    {
        if ($this->getIsVoidable()) {
            $this->_voidTransaction($result);
        } else if ($this->getIsRefundable()) {
            $this->_refundTransaction($result);
        }

        return $this;
    }

    protected function _voidTransaction($result)
    {
        Mage::helper('moneriscc')->log('voiding');

        $voidPayment = Mage::getModel('sales/order_payment')
            ->setLastTransId($result->getLastTransId())
            ->setCcTransId($result->getTxnNumber());
        $transaction = Mage::getModel('moneriscc/transaction_void')
            ->setPayment($voidPayment)
            ->setCryptType('7');

        try {
            $transaction->post();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }

    protected function _refundTransaction($result)
    {
        Mage::helper('moneriscc')->log('refunding');

        $refundPayment = Mage::getModel('sales/order_payment')
            ->setLastTransId($result->getLastTransId())
            ->setCcTransId($result->getTxnNumber());
        $transaction = Mage::getModel('moneriscc/transaction_refund')
            ->setPayment($refundPayment)
            ->setAmount($this->getAmount())
            ->setCryptType('7');

        try {
            $transaction->post();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }

    /**
     * Returns true if AVS is successful or disabled.
     * Else, returns false (AVS is enabled and has failed).
     *
     * @param Varien_Object $result
     * @return bool
     */
    protected function _checkAvs(Varien_Object $result)
    {
        if (!Mage::helper("moneriscc")->getModuleConfig('payment/moneriscc/avszip')) {
            return true;
        }

        Mage::helper('moneriscc')->log('AVS result code: ' . $result->getAvsResultCode());
        $avsResultCode = $result->getAvsResultCode();

        if (!$avsResultCode || strcmp($avsResultCode, 'null') === 0) {
            return true;
        }

        $avsSuccessCodes = Mage::helper('moneriscc')->getAvsSuccessCodes();
        if (in_array($avsResultCode, $avsSuccessCodes)) {
            return true;
        }

        // non-success code
        Mage::helper('moneriscc')->log(Mage::helper("moneriscc")->getModuleConfig('payment/moneriscc/payment_action') . ' Failed AVS');

        $status = 'FAILED';
        $message = Mage::helper('moneriscc')->getResponseTextOverride('AVS', $status);
        if (!$message) {
            $message = 'Transaction Failed AVS Match';
        }

        $this->_undoTransaction($result);

        $result->setStatus($status)
            ->setResponseText($message)
            ->setDescription($message)
            ->setLastTransId(false)
            ->setError(true);

        $this->_updatePayment($result);

        return false;
    }

    /**
     * Returns true if CVD check is successful or disabled.
     * Else, returns false (CVD check is enabled and has failed).
     *
     * @param Varien_Object $result
     * @return bool
     */
    protected function _checkCvd(Varien_Object $result)
    {
        // IF we are requiring liability shift we do not need CVV/AVS validation
        if (!Mage::helper("moneriscc")->getModuleConfig('payment/moneriscc/useccv') || Mage::helper("moneriscc")->getModuleConfig('payment/moneriscc/require_vbv')) {
            return true;
        }

        $cvdResultCode = $result->getCvdResultCode();
        // KL: Regardless, save CVD data
        Mage::helper('moneriscc')->getCheckoutSession()->setMonerisCavvCvdResult($cvdResultCode);

        if (!$cvdResultCode || strcmp($cvdResultCode, 'null') === 0) {
            return true;
        }

        $cvdResponseCodes = array(
            '0' => 'CVD value is deliberately bypassed or is not provided by the merchant.',
            '1' => 'CVD value is present.',
            '2' => 'CVD value is on the card, but is illegible.',
            '9' => 'Cardholder states that the card has no CVD imprint.',

            'M' => 'Match',
            'N' => 'No Match',
            'P' => 'Not Processed',
            'S' => 'CVD should be on the card, but Merchant has indicated that CVD is not present',
            'U' => 'Issuer is not a CVD participant'
        );



        //Canadian API usually return 2 digit cvd code US API appears to be returning 1
        $cvdResultCode = trim(strlen(trim($cvdResultCode)) > 1 ? $cvdResultCode[1] : $cvdResultCode);
        Mage::helper('moneriscc')->log(__METHOD__ . __LINE__ . ' cvd result code: ' . $cvdResultCode);

        $cvdSuccessCodes = Mage::helper('moneriscc')->getCvdSuccessCodes();

        if (in_array($cvdResultCode, $cvdSuccessCodes)) {
            return true;
        }

        // non-success code
        Mage::helper('moneriscc')->log(__METHOD__ . __LINE__ . $this->getPaymentAction() . " Failed CVD "
                . Mage_Paygate_Model_Authorizenet::ACTION_AUTHORIZE_CAPTURE);


        $status = 'FAILED';
        $message = Mage::helper('moneriscc')->getResponseTextOverride('CVD', $status);
        if (!$message) {
            $message = 'Card Verification Number mismatch. '
                . $cvdResponseCodes[$cvdResultCode[0]] . ' : ' . $cvdResponseCodes[$cvdResultCode[1]];
        }

        $this->_undoTransaction($result);

        $result->setStatus($status)
            ->setResponseText($message)
            ->setDescription($message)
            ->setLastTransId(false)
            ->setError(true);

        $this->_updatePayment($result);

        return false;
    }

    /**
     * Builds an MpgCustInfo for the $order.
     *
     * @param Mage_Sales_Model_Order $order
     * @return Moneris_MpgCustInfo
     */
    public function buildMpgCustInfo(Mage_Sales_Model_Order $order)
    {
        $billingObj = $order->getBillingAddress();

        if (!$billingObj) {
            throw new Exception('Invalid billing data.');
        }

        $billing = Mage::getModel('moneriscc/address')
            ->objToArray($billingObj);

        $shippingObj = $order->getShippingAddress();

        $shipping = array();
        if ($shippingObj) {
            $shipping = Mage::getModel('moneriscc/address')
                ->objToArray($shippingObj);
        }

        if(Mage::helper('moneriscc')->isUsApi()) {
            $mpgCustInfo = new Monerisus_MpgCustInfo();
        } else {
            $mpgCustInfo = new Moneris_MpgCustInfo();
        }

        $mpgCustInfo->setShipping($shipping);
        $mpgCustInfo->setBilling($billing);
        $mpgCustInfo->setEmail($order->getCustomerEmail());

        return $mpgCustInfo;
    }

    /**
     * Builds an MpgAvsInfo for the $address
     *
     * @param Varien_Object $address
     * @return Moneris_MpgAvsInfo
     */
    public function buildMpgAvsInfo(Varien_Object $address)
    {
        if (!Mage::helper("moneriscc")->getModuleConfig('payment/moneriscc/avszip')) {
            return null;
        }

        $avs = array(
            'avs_street_number' =>'',
            'avs_street_name' =>'',
            'avs_zipcode' => $address->getPostcode()
        );

        if(Mage::helper('moneriscc')->isUsApi()) {
            $mpgAvsInfo = new Monerisus_MpgAvsInfo($avs);
        } else {
            $mpgAvsInfo = new Moneris_MpgAvsInfo($avs);
        }



        return $mpgAvsInfo;
    }

    /**
     * Builds an MpgCvdInfo for the $payment
     *
     * @param Varien_Object $payment
     * @return Moneris_MpgCvdInfo
     */
    public function buildMpgCvdInfo(Varien_Object $payment)
    {
        if (!Mage::helper("moneriscc")->getModuleConfig('payment/moneriscc/useccv')) {
            return null;
        }

        $cvv = array(
            'cvd_indicator' => '1',
            'cvd_value'     => $payment->getCcCid()
        );
        if(Mage::helper('moneriscc')->isUsApi()) {
            $mpgCvdInfo = new Monerisus_MpgCvdInfo($cvv);
        } else {
            $mpgCvdInfo = new Moneris_MpgCvdInfo($cvv);
        }
        return $mpgCvdInfo;
    }
}
