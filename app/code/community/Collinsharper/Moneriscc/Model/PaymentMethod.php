<?php

class Collinsharper_Moneriscc_Model_PaymentMethod extends Mage_Payment_Model_Method_Cc
{


    /**
     * unique internal payment method identifier
     *
     * @var string [a-z0-9_]
     */
    protected $_code = 'moneriscc';
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;
    protected $_canSaveCc 				= false;
    protected $_payment                 = false;


    protected $_formBlockType = 'moneriscc/form_moneris';
    protected $_infoBlockType = 'moneriscc/info_moneris';

    protected $_vbvCcTypes = array(
        'VI',
        'MC'
    );

    public function __construct()
    {
        parent::__construct();
        $this->setConfigData('useccv', Mage::helper("moneriscc")->getModuleConfig('payment/moneriscc/cvv'));
        if (Mage::helper('moneriscc')->getTest()) {
            if (!defined('MONERIS_TEST')) {
                define('MONERIS_TEST',1);
            }
        }
    }

    protected function log($x, $lineNumber = null)
    {
        if ($x instanceof Varien_Object) {
            $x = $x->getData();
        }

        $content = __CLASS__ . ($lineNumber ? ":{$lineNumber}" : '') . " " .  print_r($x, true);
        Mage::helper('moneriscc')->log($content);

        return $this;
    }

    public function getIsVbvEnabled()
    {
        return Mage::helper('moneriscc')->getIsVbvEnabled();
    }

    public function getIsVbvRequired()
    {
        return Mage::helper('moneriscc')->getIsVbvRequired();
    }

    public function getIsVbvCompatible(Varien_Object $payment)
    {
        $ccType = $payment->getCcType();
        return in_array($ccType, $this->_vbvCcTypes);
    }

    public function isPurchase()
    {
        $action = Mage::helper('moneriscc')->getPaymentAction();
        return $action == Mage_Paygate_Model_Authorizenet::ACTION_AUTHORIZE_CAPTURE;
    }

    public function canRefund()
    {
        return $this->_canRefund;
    }

    public function canVoid(Varien_Object $payment)
    {
        return $this->_canVoid;
    }

    public function canCapturePartial()
    {
        return $this->_canCapturePartial;
    }

    public function canAuthorize()
    {
        return $this->_canAuthorize;
    }

    public function canCapture()
    {
        return $this->_canCapture;
    }

    public function canUseForCurrency($currency)
    {
        // TODO why are we doing this?
        if (Mage::getDesign()->getArea() == 'adminhtml') {
            return true;
        }

        $altCurrencyEnabled = Mage::helper('moneriscc')->getModuleConfig('payment/moneriscc/alternate_password_enabled');
        $altCurrency = 'NO_ALT_CUARRENCY';

        if ($altCurrencyEnabled) {
            $altCurrency = Mage::helper('moneriscc')->getModuleConfig('payment/moneriscc/alternate_password_currency');
        }

        if ($currency == Mage::app()->getStore()->getBaseCurrencyCode() ||
            $currency == $altCurrency) {
            return true;
        }
        return false;
    }

    public function getFormattedExpiry($payment)
    {
        return substr(sprintf('%04d',  $payment->getCcExpYear()), -2) .
        sprintf('%02d',  $payment->getCcExpMonth());
    }

    protected function _getFormattedAmount($amount)
    {
        return number_format($this->getAlternateCurrencyAdjustedAmount(Mage::app()->getLocale()->getNumber($amount)), 2, '.', '');
    }


    /**
     * Gets the crypt type for $payment via Moneris MPI call.
     * Checks if VBV is enabled in the config first.
     * Returns false if the payment needs to be VBV/3DS authenticated.
     *
     * @param Varien_Object $payment, float $amount
     * @return string $cryptType if txn should proceed immediately, else false
     */
    public function fetchCryptType(Varien_Object $payment, $amount)
    {

        if (!$this->getIsVbvEnabled() || !$this->getIsVbvCompatible($payment)) {
            return '7';
        }

        $cryptType = Mage::getModel('moneriscc/mpi_txn')
            ->setPayment($payment)
            ->setAmount($amount)
            ->fetchCryptType();

        switch ($cryptType) {
            case '7':
                // crypt 7 -> no liability shift
                if ($this->getIsVbvRequired()) {
                    $this->_markOrderForCancellation();
                    return false;
                }
                break;
            case '6':
                // crypt 6 for mastercard -> no liability shift
                if ($payment->getCcType() == 'MC' && $this->getIsVbvRequired()) {
                    $this->_markOrderForCancellation();
                    return false;
                }
                break;
            case '5':
                // that's it for now; proceed to PaRes
                return false;
                break;
            default:
                // unexpected; abort
                $this->_markOrderForCancellation();
                return false;
        }

        return $cryptType;
    }

    /**
     * Sets a flag in the session so the observer can cancel the order.
     *
     * @param Varien_Object $payment
     * @return this
     */
    protected function _markOrderForCancellation()
    {
        $this->log('vbv was not successful and is required; marking order for cancellation via observer');
        Mage::helper('moneriscc')->getCheckoutSession()->setMonerisccCancelOrder(true);
        return $this;
    }

    public function getAlternateCurrencyAdjustedAmount($amount, $payment = false)
    {
        if(!$payment) {
            $payment = $this->_payment;
        }

        if(Mage::helper('moneriscc')->isAlternateCurrency()) {
            $difference = $payment->getOrder()->getGrandTotal() / $payment->getOrder()->getBaseGrandTotal();
            return round($amount * $difference, 2);
        }
        return $amount;
    }

    /**
     * Attempts to start a CAVV preauth.
     * Falls back to preauth if the card is not compatible.
     *
     * @see self::getOrderPlaceRedirectUrl()
     * @param Varien_Object $payment, float $amount
     * @return $this
     * @throws Exception
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        $this->_payment = $payment;


        Mage::helper('moneriscc')->getCheckoutSession()->setMonerisccOrderId(false);

        // Reset the settings
        Mage::helper('moneriscc')->getCheckoutSession()->setMonerisccCancelOrder(false);

        $amount = $this->_getFormattedAmount($amount);

        Mage::helper('moneriscc')->log(__METHOD__ . " and amount real amount " . $amount);
        Mage::helper('moneriscc')->log(__METHOD__ . " and amount alt curr " . $payment->getOrder()->getBaseGrandTotal());
        Mage::helper('moneriscc')->log(__METHOD__ . " and amount alt curr " . $payment->getOrder()->getGrandTotal());
        if ($amount < 0) {
            Mage::helper('moneriscc')->handleError(Mage::helper('moneriscc')->__("Invalid amount to authorize: [{$amount}]"),true);
        }

        $cryptType = $this->fetchCryptType($payment, $amount);

        // if no crypt type is returned, then VBV will proceed
        Mage::helper('moneriscc')->log(__FILE__." ".__LINE__." Authorize cryptType: ".$cryptType);
        if (!$cryptType) {
            // Reset the CVD here
            Mage::helper('moneriscc')->log(__FILE__." ".__LINE__." Reset CVD result");
            Mage::helper('moneriscc')->getCheckoutSession()->setMonerisCavvCvdResult(false);

            $payment->setIsTransactionPending(true);
            return $this;
        }

        $this->log('proceeding with non-vbv transaction');
        Mage::helper('moneriscc')->log(__FILE__." ".__LINE__." Proceed Transaction Preauth");

        $transaction = Mage::getModel('moneriscc/transaction_preauth')
            ->setPayment($payment)
            ->setAmount($amount)
            ->setCryptType($cryptType);

        $result = $transaction->post();

        if ($result->getError()) {
            $error = Mage::helper('moneriscc')->__('Error in authorizing payment: ' . $result->getResponseText());
            Mage::helper('moneriscc')->handleError($error, true);
        }

        if (!$result->getSuccess()) {
            Mage::helper('moneriscc')->handleError(Mage::helper('moneriscc')->__('Error in authorizing payment: '
                . $result->getResponseText()), true);
        } else {
             $payment->setIsTransactionClosed(0);
             $payment->save();
        }


        return $this;
    }


    /**
     * Posts a cavv_preauth transaction.
     * Throws an exception on failure.
     * @see Collinsharper_Moneriscc_PaymentController::returnAction
     *
     * @throws Collinsharper_Moneriscc_Exception
     * @param Varien_Object $payment, string $md, string $cavv
     */
    protected function _cavvAuthorize(Varien_Object $payment, $md, $cavv)
    {
        $this->_payment = $payment;

        $mdArray = array();
        parse_str($md, $mdArray);

        $payment->setCcNumber($mdArray['pan']);

        $transaction = Mage::getModel('moneriscc/transaction_cavv_preauth')
            ->setPayment($payment)
            ->setAmount($mdArray['amount'])
            ->setCavv($cavv);

        $result = $transaction->post();

        if ($result->getError()) {
            // log the raw data
            Mage::logException(new Exception("Error on CAVV authorization: {$result->getResponseText()}\n" . print_r($result->getRawData(), true)));

            //$error = Mage::helper('moneriscc')->__('Error on CAVV authorization: ' . $result->getResponseText());
            $error = Mage::helper('moneriscc')->__('There was an error authorizing your payment. Please use a different card or try a different payment method.');
            Mage::helper('moneriscc')->handleError($error, true);
            Mage::getSingleton('core/session')->setMonerCavvError(true);

        }

        if (!$result->getSuccess()) {
            mage::log('Result 1: ' . print_r($result,1));
            Mage::helper('moneriscc')->handleError(Mage::helper('moneriscc')->__('The Transaction has been declined by your bank. Please use a different card or try a different payment method.'), true);
        }

        $this->_cavvSuccess($payment);

        return $this;
    }


    /**
     * Posts a cavv_purchase transaction.
     * Throws an exception on failure.
     * @see Collinsharper_Moneriscc_PaymentController::returnAction
     *
     * @throws Collinsharper_Moneriscc_Exception
     * @param Varien_Object $payment, string $md, string $cavv
     */
    protected function _cavvPurchase(Varien_Object $payment, $md, $cavv)
    {
        $this->_payment = $payment;

        $mdArray = array();
        parse_str($md, $mdArray);

        $payment->setCcNumber($mdArray['pan']);

        $transaction = Mage::getModel('moneriscc/transaction_cavv_purchase')
            ->setPayment($payment)
            ->setAmount($mdArray['amount'])
            ->setCavv($cavv);

        $result = $transaction->post();

        if ($result->getError()) {
            // log the raw data
            Mage::logException(new Exception("Error on CAVV purchase: {$result->getResponseText()}\n" . print_r($result->getRawData(), true)));

            //$error = Mage::helper('moneriscc')->__('Error on CAVV purchase: ' . $result->getResponseText());
            $error = Mage::helper('moneriscc')->__('There was an error processing your card. Please use a different card or try a different payment method.');
            Mage::helper('moneriscc')->handleError($error, true);
        }

        if (!$result->getSuccess()) {
            Mage::helper('moneriscc')->log(__METHOD__ . __LINE__ . 'Result 2: ' . print_r($result,1));
            Mage::helper('moneriscc')->handleError(Mage::helper('moneriscc')->__('The Transaction has been declined by your bank. Please use a different card or try a different payment method.'), true);
        }

        $this->_cavvSuccess($payment);

        // register the capture success
        $payment->setIsTransactionPending(false)
            ->registerCaptureNotification($mdArray['amount']);
        try {
            $payment->getOrder()->save();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }


    /**
     * Adds a VBV/3DS success message to $payment's order.
     *
     * @param Varien_Object $payment
     * @return this
     */
    protected function _cavvSuccess(Varien_Object $payment)
    {
        $order = $payment->getOrder();
        $order->setState (
            Mage_Sales_Model_Order::STATE_PROCESSING, Mage_Sales_Model_Order::STATE_PROCESSING,
            Mage::helper('moneriscc')->__('VBV / 3DS authentication completed successfully'),
            false
        );
        try {
            $order->save();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }


    /**
     * Completes payment with the data passed back from the MPI.
     * Throws an exception on failure.
     * @see Collinsharper_Moneriscc_PaymentController::returnAction
     *
     * @param Varien_Object $payment, $paRes, $md, $order
     * @throws Collinsharper_Moneriscc_Exception
     */
    public function cavvContinue(Varien_Object $payment, $paRes, $md, $order)
    {
        $mpiResponse = Mage::getModel('moneriscc/mpi_acs')
            ->setPaRes($paRes)
            ->setMd($md)
            ->post();

        Mage::helper('moneriscc')->log(__FILE__." ".__LINE__." cavvContinue ".print_r($mpiResponse, true));
        $mpiSuccess = $mpiResponse->getMpiSuccess();
        $mpiMessage = $mpiResponse->getMpiMessage();
        $cavv = $mpiResponse->getMpiCavv();

        $mdArray = array();
        parse_str($md, $mdArray);

        if ($mpiSuccess == 'true') {
            $this->log('mpi success');

            if ($this->isPurchase()) {
                $this->_cavvPurchase($payment, $md, $cavv);
            } else {
                $this->_cavvAuthorize($payment, $md, $cavv);
            }

            $order->sendNewOrderEmail();

            return $this;
        }

        if ($mpiMessage == 'N') {
            $this->log('mpi failure');
            Mage::helper('moneriscc')->handleError(Mage::helper('moneriscc')->__('We were unable to verify your VBV / 3DS credentials. Please try again or use a different payment method.'), true);

        }

        // send regular auth unless vbv is required
        $this->log('no mpi');
        if ($this->getIsVbvRequired()) {
            Mage::helper('moneriscc')->handleError(Mage::helper('moneriscc')->__('Only VBV / 3DS enrolled cards are accepted. Please try another card or a different payment method.'), true);
        }

        if ($this->isPurchase()) {
            $this->capture($payment, $mdArray['amount']);
        } else {
            $this->authorize($payment, $mdArray['amount']);
        }

        return $this;
    }


    /**
     * Sends a completion transaction if $payment has previous transaction fields.
     * Else, sends a CAVV purchase if $payment has a CAVV in its additional_info.
     * Else, sends a purchase.
     *
     * @see self::getOrderPlaceRedirectUrl()
     * @param Varien_Object $payment, float $amount
     * @return $this
     * @throws Exception
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $this->_payment = $payment;
        Mage::helper('moneriscc')->getCheckoutSession()->setMonerisccOrderId(false);
        // Reset the settings
        Mage::helper('moneriscc')->getCheckoutSession()->setMonerisccCancelOrder(false);

        $amount = $this->_getFormattedAmount($amount);

        $this->log("CcTransId: ". $payment->getCcTransId() . " LastTransId: " . $payment->getLastTransId() . " Amount: " . $amount);

        if ($amount < 0) {
            $error = Mage::helper('moneriscc')->__('Invalid amount for capture.');
            Mage::helper('moneriscc')->handleError($error, true);
        }

        // if authorize mode and previous transaction data is present, then complete
        if (!$this->isPurchase() && $payment->getCcTransId() && $payment->getLastTransId()) {
            $transaction = Mage::getModel('moneriscc/transaction_completion');
        } else {

            // if purchase, begin VBV process just like authorize()
            $cryptType = $this->fetchCryptType($payment, $amount);
            // if no crypt type is returned, then VBV will proceed;
            if (!$cryptType) {
                // Reset the CVD result here
                Mage::helper('moneriscc')->getCheckoutSession()->setMonerisCavvCvdResult(false);

                $payment->setIsTransactionPending(true);
                return $this;
            }
            // otherwise, use regular purchase
            $transaction = Mage::getModel('moneriscc/transaction_purchase')
                ->setCryptType($cryptType);
        }

        $transaction->setPayment($payment)
            ->setAmount($amount);

        $cavv = Mage::helper('moneriscc')->getPaymentAdditionalInfo($payment, 'cavv');
        if ($cavv) {
            $transaction->setCavv($cavv);
        }

        $result = $transaction->post();

        if ($result->getError()) {
            //$error = Mage::helper('moneriscc')->__('Error in capturing payment: ' . $result->getResponseText());
            $error = Mage::helper('moneriscc')->__('The Transaction was not approved by your bank. Please use a different card or try a different payment method.');
            Mage::helper('moneriscc')->handleError($error, true);

        }

        if (!$result->getSuccess()) {
            mage::log('Result 3: ' . print_r($result,1));
            $error = Mage::helper('moneriscc')->__('The Transaction has been declined by your bank. Please use a different card or try a different payment method.');
            Mage::helper('moneriscc')->handleError($error, true);
        }

        return $this;
    }


    /**
     * Sends a refund transaction.
     *
     * @param Varien_Object $payment, float $amount
     * @return $this
     * @throws Exception
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $this->_payment = $payment;
        $amount = $this->_getFormattedAmount($amount);

        if ($amount <= 0) {
            $error = Mage::helper('moneriscc')->__('Error in refunding payment: amount (%s) cannot be 0 or less.', $amount);
            Mage::helper('moneriscc')->handleError($error, true);
        }

        $transaction = Mage::getModel('moneriscc/transaction_refund')
            ->setPayment($payment)
            ->setAmount($amount);

        $result = $transaction->post();

        if (!$result->getSuccess()) {
            Mage::helper('moneriscc')->handleError(Mage::helper('moneriscc')->__('Error in refunding payment: '
                . $result->getResponseText()), true);
        }

        return $this;
    }


    /**
     * Voids a transaction.
     *
     * @param Varien_Object $payment
     * @return $this
     * @throws Exception
     */
    public function void(Varien_Object $payment)
    {
        if (!$payment->getVoidTransactionId()) {
            $payment->setStatus(self::STATUS_ERROR);
            $error = Mage::helper('moneriscc')->__('Invalid transaction id');
            Mage::throwException($error);
        }

        $result = $this->_void($payment);

        if (!$result->getSuccess()) {
            $payment->setStatus(self::STATUS_ERROR);
            Mage::helper('moneriscc')->handleError(Mage::helper('moneriscc')->__('Error in voiding payment: '
                . $result->getResponseText()), true);
        }

        return $this;
    }


    /**
     * Posts a transaction of type void.
     *
     * @param Varien_Object $payment
     * @return Varien_Object $result
     */
    protected function _void(Varien_Object $payment)
    {
        $transaction = Mage::getModel('moneriscc/transaction_void')
            ->setPayment($payment)
            ->setAmount(0);
        $result = $transaction->post();

        return $result;
    }


    /**
     * The user is redirected to the URL returned here on order placement
     * (see Mage_Checkout_Model_Type_Onepage).
     *
     * @return string url
     */
    public function getOrderPlaceRedirectUrl()
    {
        $session = Mage::helper('moneriscc')->getCheckoutSession();
        if ($session->getMonerisccOrderId()) {
            $this->log('redirecting!');
            return Mage::getUrl('moneriscc/payment/redirect', array('_secure' => true));
        }

        if ($session->getMonerisccCancelOrder()) {
            $this->log('order canceled; redirecting to repopulated cart');

//             Mage::helper('moneriscc')->repopulateCart();
            $session->setMonerisccCancelOrder(false);
            Mage::helper('moneriscc')->handleError(Mage::helper('moneriscc')->__('Your card could not be VBV/3DS authenticated. Please use a VBV/3DS enrolled card.'));

            $url = Mage::helper("moneriscc")->getPaymentFailedRedirectUrl();
            return $url;
        }
        

        $this->log(__METHOD__ . 'not redirecting');
        return '';
    }

    protected function getUniqueOrderId($increment_id)
    {
        // max length of 50
        $tail = sprintf("%'920d", rand(999,999999999-strlen($increment_id)));
        return $increment_id.'-'.$tail;
    }
}
