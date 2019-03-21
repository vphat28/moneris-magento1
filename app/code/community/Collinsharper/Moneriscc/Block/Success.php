<?php

class Collinsharper_Moneriscc_Block_Success extends Mage_Core_Block_Text
{
	protected $_called = false;
	
	public function doSomething()
	{
		$this->_called = true;
	}
	
	protected function _toHtml()
    {
		if(!strstr(mage::helper('core/url')->getCurrentUrl(), 'checkout/onepage/success')) {
			return '';
		}

		$data_array  = Mage::getSingleton('customer/session')->getMoneriscccData();
		if(!$data_array || !is_array($data_array)) {
			return '';
		}
		
		$o = vsprintf(Mage::helper('moneriscc')->__('receipt text'), $data_array);
		Mage::getSingleton('customer/session')->setMoneriscccData(false);
		if($o == 'receipt text') {
			$o = '';
		}
		return $o;
	}
}
