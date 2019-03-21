<?php


class Collinsharper_Moneriscc_PaymentController extends Mage_Core_Controller_Front_Action
{

	protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

	protected function _getCsession()
    {
        return Mage::getSingleton('customer/session');
    }

	public function indexAction()
    {
        $this->_redirect('/');
		return;
    }

 	protected function help()
	{
		return Mage::helper('moneriscc');
	}

    protected function _addOrderMessage($order, $message, $status=null)
    {
        $originalStatus = $status;
        if (!$status) {
            $status = $order->getStatus();
        }

        $order->setState(
            $status, $status,
            $this->help()->__($message),
            false
        );


        if($originalStatus == Mage_Sales_Model_Order::STATE_CANCELED) {

            foreach ($order->getItemsCollection() as $item) {
                $productId  = $item->getProductId();
                $qty = (int)$item->getQtyOrdered();

                $children = $item->getChildrenItems();
                $qty = $item->getQtyOrdered() - max($item->getQtyShipped(), $item->getQtyInvoiced()) - $item->getQtyCanceled();

                $productId = $item->getProductId();
                if ($item->getId() && $productId && empty($children) && $qty) {
                    Mage::getSingleton('cataloginventory/stock')->backItemQty($productId, $qty);
                }

            }

            $canceledState = Mage_Sales_Model_Order::STATE_CANCELED;
            $order->setState(
                $canceledState, $canceledState,
                Mage::helper('moneriscc')->__('Order canceled due to failed VBV / 3DS authentication.'),
                false
            );

            $order->save();


            Mage::helper('moneriscc')->log(__METHOD__ . __LINE__ . " CANCEL " . $order->getIncrementId() );


            try {
                // this actually wont work.
                $order->delete();
            } catch (Exception $e) {
                // Mage::logException($e);
            }

        } else {


            $order->setState(
                "pending", $status,
                $message,
                false
            );

            $order->save();


            Mage::helper('moneriscc')->log(__METHOD__ . __LINE__ . " IncrementId " . $order->getIncrementId() );


            try {
                // this actually wont work.
                $order->delete();
            } catch (Exception $e) {
               // Mage::logException($e);
            }

        }

        return $this;
    }

    protected function _abort($message = null)
    {
        if (!$message) {
            $message =  Mage::helper('moneriscc')->__('There was a system problem processing your payment. Please try again or contact customer service.');
        }
//This is also called in PaymentMethod.php
//        Mage::helper('moneriscc')->handleError($message);

        $url = Mage::helper("moneriscc")->getPaymentFailedRedirectUrl(false);

        $this->_redirect($url);

        return $this;
    }

    /**
     * The browser is sent here when an order is placed.
     *
     * @see Collinsharper_Moneriscc_Model_PaymentMethod::getOrderPlaceRedirectUrl()
     */
    public function redirectAction()
    {
        $orderId = $this->_getSession()->getMonerisccOrderId();

        if (!$orderId) {
            $url = Mage::helper("moneriscc")->getPaymentFailedRedirectUrl(false);

            $this->_redirect($url);
            return $this;
        }

        $order = Mage::getModel('sales/order')->load($orderId);
        Mage::helper('moneriscc')->log(__METHOD__ . __LINE__ );
        $this->_addOrderMessage($order, 'Pending VBV / 3DS callback', Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW);

        $block = $this->getLayout()->createBlock('moneriscc/redirect')
            ->setTemplate('moneriscc/redirect.phtml');
        $this->getResponse()->setBody($block->toHtml());
        return $this;
    }

    /**
     * Serves the form for the iframe that is set up in redirectAction.
     * TODO: remove, assuming iframe is indeed unnecessary
     *
     * @see self::redirectAction()
     */
    public function formAction()
    {
        $form = $this->_getSession()->getMonerisccMpiForm();
        $this->getResponse()->setBody($form);
        return $this;
    }

    /**
     * The browser returns here after hitting up Moneris for authentication.
     */
    public function returnAction()
    {
        $orderId = $this->_getSession()->getMonerisccOrderId();

        if (!$orderId) {
            $url = Mage::helper("moneriscc")->getPaymentFailedRedirectUrl(false);

            $this->_redirect($url);
            return $this;
        }

        $order = Mage::getModel('sales/order')->load($orderId);

        $paRes = $this->getRequest()->getParam('PaRes');
        $md = $this->getRequest()->getParam('MD');
        if ($this->help()->getModuleConfig('force_decline') || !$paRes || !$md) {
            $this->_addOrderMessage($order, 'Failed VBV / 3DS callback parameters.', Mage_Sales_Model_Order::STATE_CANCELED);
            $this->_abort( Mage::helper('moneriscc')->__("The was an error communicating with your bank. Please try again in a few minutes."));
            return $this;
        }

        try {
            $payment = $order->getPayment();

            // restore CVD from the session
            $checkoutSession = $this->_getSession();
            $payment->setCcCid($checkoutSession->getMonerisCavvCvdResult());
            $checkoutSession->setMonerisCavvCvdResult(false);

            Mage::getModel('moneriscc/paymentMethod')->cavvContinue($payment, $paRes, $md, $order);
        } catch (Collinsharper_Moneriscc_Exception $me) {
            // exception for the purposes of returning an error message
            $this->_addOrderMessage($order, $me->getMessage(), Mage_Sales_Model_Order::STATE_CANCELED);
            $this->_abort($me->getMessage());
            return $this;
        } catch (Exception $e) {
            Mage::logException($e);
            mage::log('Moneris Exception for Order ID ' . $order->getId() . ': ' . $e->getMessage());
            $this->_addOrderMessage($order, 'VBV / 3DS was unable to complete successfully.', Mage_Sales_Model_Order::STATE_CANCELED);
            $this->_abort($e->getMessage());
            return $this;
        }

        // success!
        $this->_redirect('checkout/onepage/success', array('_secure' => true));

        return $this;
   }
}
