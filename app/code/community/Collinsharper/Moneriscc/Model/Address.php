<?php

class Collinsharper_Moneriscc_Model_Address extends Collinsharper_Moneriscc_Model_Abstract
{
    /**
     * Creates a Moneris-friendly array out of an address object.
     *
     * @param Varien_Object $addressObj
     * @return array
     */
    public function objToArray(Varien_Object $addressObj)
    {
        $state_canus = $addressObj->getRegionCode();
        if (!$state_canus) {
            $state_canus = '--';
        }

        $address = array(
            'first_name' =>$addressObj->getFirstname(),
            'last_name' => $addressObj->getLastname(),
            'company_name' => $addressObj->getCompany(),
            'address' =>$addressObj->getStreet(1),
            'city' =>$addressObj->getCity(),
            'province' => $state_canus,
            'postal_code' => $addressObj->getPostcode() ,
            'country' => $addressObj->getCountry(),
            'phone_number' =>  $addressObj->getTelephone(),
            'fax' => $addressObj->getFax(),
            'tax1' => 0,
            //    'tax2' => $tax2,
            //  'tax3' => $tax3,
            'shipping_cost' => 0
        );      

        return $address;
    }
}
