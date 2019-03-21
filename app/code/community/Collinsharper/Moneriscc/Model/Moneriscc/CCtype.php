<?php
class Collinsharper_Moneriscc_Model_Moneriscc_CCtype extends Mage_Payment_Model_Source_Cctype
{
    public function getAllowedTypes()
    {
        return array('VI', 'MC', 'AE', 'DI', 'JCB');
    }
}
