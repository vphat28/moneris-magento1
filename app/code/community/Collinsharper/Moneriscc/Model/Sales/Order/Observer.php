<?php

class Collinsharper_Moneriscc_Model_Sales_Order_Observer
{
    public function placeAfter(Varien_Event_Observer $observer)
    {
        Mage::helper('moneriscc')->log(__CLASS__);

        $event = $observer->getEvent();

        if (!$event) {
            return $this;
        }

        $order = $event->getOrder();

        if (!$order) {
            return $this;
        }

        if ($this->_getHasVbvAuthFailed()) {
            $this->_cancelOrder($order);
            return $this;
        }

        $this->_forceStatus($order);

        return $this;
	}

	public function showPaymentDetails()
	{
	//	$myBlock = Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('moneriscc_success');
		$myBlock = new Collinsharper_Moneriscc_Block_Success;
		$myBlock->doSomething();
	}

    protected function _getOrderForceStatus()
    {
        return Mage::helper('moneriscc')->getOrderForceStatus();
    }

    protected function _hasOrderForce()
    {
        return $this->_getOrderForceStatus() != '';
    }

    protected function _getState($status)
    {
        $statuses = Mage::getResourceModel('sales/order_status_collection')
            ->joinStates()
            ->addFieldToFilter('main_table.status',$status);
        ;
        return $statuses->getFirstItem()->getState();
    }

    protected function _getHasVbvAuthFailed()
    {
        $session = Mage::helper('moneriscc')->getCheckoutSession();
        return $session->getMonerisccCancelOrder();
    }

    protected function _cancelOrder(Mage_Sales_Model_Order $order)
    {
        Mage::helper('moneriscc')->log('canceling order');

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


        mage::log(__METHOD__ . __LINE__ . " CANCEL " . $order->getIncrementId() );


        try {
    // this actually wont work.
            $order->delete();
        } catch (Exception $e) {
            Mage::logException($e);
        }


        return $this;
    }

    protected function _forceStatus(Mage_Sales_Model_Order $order)
    {
        if (!$this->_hasOrderForce()) {
            return $this;
        }

        try {
            if ($order->getPayment()->getMethod() != 'moneriscc') {
                return $this;
            }

            $status = $this->_getOrderForceStatus();
            $state = $this->_getState($status);

            Mage::helper('moneriscc')->log(__CLASS__ . __LINE__ . " order state " .  $order->getState());
            Mage::helper('moneriscc')->log(__CLASS__ . __LINE__ . " order status " .  $order->getStatus());
            if ($order->getStatus() != $this->_getOrderForceStatus()) {
                $order->setStatus($status);
                $order->setState($state);
                $order->save();
            }
        } catch (Exception $e) {
            Mage::helper('moneriscc')->log(__CLASS__ . "Failed to force update Order Status" . $e->getMessage());
            Mage::logException($e);
        }

        return $this;
    }
}
