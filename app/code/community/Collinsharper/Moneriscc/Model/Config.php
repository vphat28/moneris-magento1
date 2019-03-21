<?php

class Collinsharper_Oasispayments_Model_Config extends Mage_Payment_Model_Config
{
      /**
     * Retrieve array of account types (checking savings)
     *
     * @return array
     */
    public function getAccountTypes()
    {
        $_types = Mage::getConfig()->getNode('global/payment/account/types')->asArray();

        $types = array();
        foreach ($_types as $data) {
            $types[$data['code']] = $data['name'];
        }
        return $types;
    }

    
}
