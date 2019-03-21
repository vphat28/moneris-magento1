<?php

      
class Collinsharper_Moneriscc_Block_Form_Moneris extends Mage_Payment_Block_Form_Cc
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('moneriscc/form/moneris.phtml');
    }
	
	public function hasVerification() 
	{
		return (bool)Mage::helper("moneriscc")->getModuleConfig('payment/moneriscc/useccv');
	}

}
