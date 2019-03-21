<?php

class Collinsharper_Moneriscc_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isOneStepCheckout()
    {
        return Mage::helper('core')->isModuleEnabled('aw_onestepcheckout') &&
        Mage::helper('aw_onestepcheckout/config')->isEnabled();
    }

    public function getPaymentFailedRedirectUrl($fullUrl = true)
    {
        $url = 'checkout/cart';
        if($this->isOneStepCheckout()) {
            $url = 'onestepcheckout';
        }

        return $fullUrl ? Mage::getUrl($url) : $url;
    }

    public function log($data, $label='')
    {
        if (!$this->getTest() && !$this->getDebug()) {
            return $this;
        }

        if ($data instanceof Varien_Object) {
            $data = $data->getData();
        }

        if ($label) {
            $data = print_r($label, true) . ': ' . print_r($data, true);
        }

        Mage::log(print_r($data, true), null, "ch_moneriscc.log");

        return $this;
    }

    public function isAdmin()
    {
        if(Mage::app()->getStore()->isAdmin())
        {
            return true;
        }

        if(Mage::getDesign()->getArea() == 'adminhtml')
        {
            return true;
        }

        return false;
    }

    public function isUsApi()
    {
        return $this->getModuleConfig('payment/moneriscc/usapi');
    }

    public function getModuleConfig($path) {
        // First check to see if we are in Admin or not
        $value = Mage::getStoreConfig($path);
        $storeId = false;

        if ($this->isAdmin()) {
            // Are we in credit memo?
            $_creditMemo = Mage::registry('current_creditmemo');
            if ($_creditMemo) {
                $storeId = $_creditMemo->getOrder()->getData("store_id");
            } else {
                $session = Mage::getSingleton('adminhtml/session_quote');
                // We will get the store ID from here
                $storeId = $session->getStoreId();
            }

            if(!$storeId) {
                if(Mage::registry('current_order') &&  Mage::registry('current_order')->getStoreId()) {
                    $storeId = Mage::registry('current_order')->getStoreId();
                } else if ( Mage::registry('current_invoice') &&  Mage::registry('current_invoice')->getStoreId()) {
                    $storeId = Mage::registry('current_invoice')->getStoreId();
                }
            }


            if($storeId) {
                $value = Mage::getStoreConfig($path, Mage::getModel('core/store')->load( $storeId ));
            }
        }
        return $value;
    }

    public function getTest()
    {
        return $this->getModuleConfig('payment/moneriscc/test');
    }

    public function getDebug()
    {
        return $this->getModuleConfig('payment/moneriscc/debug');
    }

    public function getSession() {
        return Mage::getSingleton('customer/session');
    }

    public function getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    public function getQuoteCurrency()
    {
        $currencyCode = $this->getCheckoutSession()->getQuote()->getQuoteCurrencyCode();
        if ($this->isAdmin()) {
            // Are we in credit memo?
            $_creditMemo = Mage::registry('current_creditmemo');
            if ($_creditMemo) {
                $currencyCode = $_creditMemo->getOrder()->getOrderCurrencyCode();
            } else {
                $session = Mage::getSingleton('adminhtml/session_quote');
                // We will get the store ID from here
                $currencyCode = $session->getCurrencyCode();
                if(!$currencyCode) {
                    $currencyCode = $session->getOrderCurrencyCode();
                }
            }

            if(!$currencyCode && Mage::registry('current_invoice')) {
                $currencyCode = Mage::registry('current_invoice')->getOrder()->getOrderCurrencyCode();

            }
        } else if(!$currencyCode) {
            $order = Mage::getModel('sales/order');
            $order->load(Mage::getSingleton('checkout/session')->getLastOrderId());
            if($order && $order->getId()) {
                $currencyCode = $order->getOrderCurrencyCode();
            }
        }
        return $currencyCode;
    }
    
    public function repopulateCart()
    {
        $session = $this->getCheckoutSession();
        $quoteId = $session->getMonerisccQuoteId(true);

        if (!$quoteId) {
            return $this;
        }

        $session->setQuoteId($quoteId);
        $session->setLoadInactive()->getQuote()->setIsActive(true)->save();

        $cart = Mage::getModel('sales/quote')->load($quoteId);
        $cart->setIsActive(true)->save(); 
        
        return $this;
    }

    public function getAvsSuccessCodes()
    {
        $codesString = $this->getModuleConfig('payment/moneriscc/avssuccess');
        $codes = explode(',', $codesString);

        foreach ($codes as &$c) {
            $c = trim($c);
        }

        return $codes;
    }

    public function getCvdSuccessCodes()
    {
        $codesString = $this->getModuleConfig('payment/moneriscc/cvdsuccess');
        $codes = explode(',', $codesString);

        foreach ($codes as &$c) {
            $c = trim($c);
        }

        return $codes;
    }

    public function getResponseTextOverride($code, $status=null)
    {
        $responses = $this->getResponses();
        // if a status is given, make sure it matches
        if (isset($responses[$code]) && (!$status || $responses[$code]['status'] == $status)) {
            return $responses[$code]['message'];
        }
        return false;
    }

    public function getResponses()
    {
        $responsesString = $this->getModuleConfig('payment/moneriscc/responses');
        $responses = array();

        if (strlen($responsesString) <= 2) {
            return $responses;
        }

        $_t = explode("\n", trim($responsesString));
        foreach($_t as $x) {
            $_d = explode(":",$x);
            if(isset($_d[0]) && count($_d) == 3) {
                $responses[trim($_d[0])] = array('status' => trim($_d[1]), 'message' => trim($_d[2]));
            }
        }

        return $responses;
    }

    public function getReciept() {
        $session = $this->getSession();
        $order = Mage::getModel('sales/order');
        $order->load(Mage::getSingleton('checkout/session')->getLastOrderId());
            if('moneriscc' != (string)$order->getPayment()->getMethod())
                return false;

        if(Mage::getSingleton('customer/session')->getMonerisccData()) {
                $bits = Mage::getSingleton('customer/session')->getMonerisccData(true);
            $this->saveReceipt($bits);
            return $bits;
            }
}

        public function saveReceipt($_d) {

            $session = $this->getSession();
        $order = Mage::getModel('sales/order');
        $order->load(Mage::getSingleton('checkout/session')->getLastOrderId());


         $order->addStatusToHistory(
                    $order->getStatus(),//continue setting current order status
                    Mage::helper('moneriscc')->__('Moneris CC Payment results, '.$_d)
                );
                $order->save();

    }

    public function getIsVbvEnabled()
    {
        return $this->getModuleConfig('payment/moneriscc/vbv_enabled');
    }

    public function getIsVbvRequired()
    {
        $result = false;

        if ($this->getIsVbvEnabled()) {
            if ($this->getModuleConfig('payment/moneriscc/require_vbv')) {
                 $result = true;
            }
        }

        return $result;
    }

    public function isAlternateCurrency()
    {
        return ($this->getModuleConfig('payment/moneriscc/alternate_password_enabled') &&
            $this->getQuoteCurrency() == $this->getModuleConfig('payment/moneriscc/alternate_password_currency')
        );
    }

    public function getMonerisStoreId()
    {
        $store_id = $this->getModuleConfig('payment/moneriscc/login');
        if($this->isAlternateCurrency()) {
          $store_id =  $this->getModuleConfig('payment/moneriscc/alternate_login');
        }
        return $store_id;
    }

    public function getMonerisApiToken()
    {
        $password = $this->getModuleConfig('payment/moneriscc/password');
        if($this->isAlternateCurrency()) {
          $password = $this->getModuleConfig('payment/moneriscc/alternate_password');
        }
        return $password;
    }

    public function getPaymentAction()
    {
        return $this->getModuleConfig('payment/moneriscc/payment_action');
    }

    public function getOrderForceStatus()
    {
        return $this->getModuleConfig('payment/moneriscc/force_status');
    }

    /**
     * Sets data in to the additional_information field of a payment.
     *
     * @param Varien_Object $payment, string $key, mixed $data
     * @return $payment
     */
    public function setPaymentAdditionalInfo(Varien_Object $payment, $key, $data)
    {
        $info = $payment->getAdditionalInformation();

        if (!is_array($info)) {
            $info = array($info);
        }

        $info[$key] = $data;
        $payment->setAdditionalInformation($info);

        return $payment;
    }

    /**
     * Gets data from the additional_information field of a payment.
     *
     * @param Varien_Object $payment, string $key=null
     * @return mixed
     */
    public function getPaymentAdditionalInfo(Varien_Object $payment, $key=null)
    {
        $info = $payment->getAdditionalInformation();

        if (!is_array($info)) {
            return null;
        }

        if (!$key) {
            return $info;
        }

        if (!isset($info[$key])) {
            return null;
        }

        return $info[$key];
    }

    function getTranspart($type, $k)
    {
        if(!is_array($k) || !isset($k[0]))
        {
            return $k;

        }
        $k = strtoupper(trim($k));
        $cvdfirst = array(
            '0' => 'CVD value is deliberately bypassed or is not provided by the merchant.',
            '1' => 'CVD value is present.',
            '2' => 'CVD value is on the card, but is illegible.',
            '9' => 'Cardholder states that the card has no CVD imprint.'
        );
        $cvdsecond = array(
            'M' => 'Match',
            'N' => 'No Match',
            'P' => 'Not Processed',
            'S' => 'CVD should be on the card, but Merchant has indicated that CVD is not present',
            'U' => 'Issuer is not a CVD participant',
            'Other' => 'Invalid Response Code',
        );
        $avsresponse = array(
            'A' => 'Address matches, ZIP does not.  Acquirer rights not implied.',
            'B' => 'Street addresses match.  Postal code not verified due to incompatible formats.  (Acquirer sent both street address and postal code.)',
            'C' => 'Street addresses not verified due to incompatible formats.  (Acquirer sent both street address and postal code.)',
            'D' => 'Street addresses and postal codes match.',
            'F' => 'Street address and postal code match.  Applies to U.K. only',
            'G' => 'Address information not verified for international transaction. Issuer is not an AVS participant, or AVS data was present in the request but issuer did not return an AVS result, or Visa performs AVS on behalf of the issuer and there was no address record on file for this account.',
            'I' => 'Address information not verified.',
            'K' => 'N/A',
            'L' => 'N/A',
            'M' => 'Street address and postal code match.',
            'N' => 'No match.  Acquirer sent postal/ZIP code only, or street address only, or both postal code and street address.  Also used when acquirer requests AVS but sends no AVS data. Neither address nor postal code matches.',
            'O' => 'N/A',
            'P' => 'Postal code match.  Acquirer sent both postal code and street address but street address not verified due to incompatible formats.',
            'R' => 'Retry: system unavailable or timed out.  Issuer ordinarily performs AVS but was unavailable.  The code R is used by Visa when issuers are unavailable.  Issuers should refrain from using this code. Retry; system unable to process.',
            'S' => 'N/A - AVS currently not supported.',
            'U' => 'Address not verified for domestic transaction.  Issuer is not an AVS participant, or AVS data was present in the request but issuer did not return an AVS result, or Visa performs AVS on behalf of the issuer and there was no address record on file for this account.',
            'W' => 'Not applicable. If present, replaced with Z by Visa. Available for U.S. issuers only.',
            'X' => 'N/A',
            'Y' => 'Street address and postal code match. For U.S. addresses, five-digit postal code and address matches.',
            'Z' => 'Postal/Zip matches; street address does not match or street address not included in request.',
        );

        if($type == 'avs')
        {
            return isset($avsresponse[$k]) ? $avsresponse[$k] :  "No Datas for avs information (".$k.")";
        }

        if($type == 'cvd')
        {
            $ret = "";
            if(isset($k[0]) && isset($cvdfirst[$k[0]]))
            {
                $ret .= $cvdfirst[$k[0]];
            }
            else
            {
                $ret .= "No first part of cvd: ".$k[0];
            }
            if(isset($cvdsecond[$k[1]]))
            {
                $ret .= ' '.$cvdsecond[$k[1]];
            }
            else
            {
                $ret .= " No second part of cvd: ".$k[1];
            }
            return $this->__($ret);
        }
    }

    public function handleError($error, $throw = false)
    {
        if(!Mage::getSingleton('core/session')->getMonerCavvError()) {
            Mage::helper('moneriscc')->log(__METHOD__ . __LINE__ . "in handle error");
            $this->repopulateCart();
            if ($this->isOneStepCheckout()) {
                Mage::getSingleton('core/session')->setMonerisError($error);
            } else {
                Mage::getSingleton('checkout/session')->addError($error);
            }

            if ($throw) {
                throw new Exception($error);
            }
        }
        Mage::getSingleton('core/session')->unsMonerCavvError();

    }

}
