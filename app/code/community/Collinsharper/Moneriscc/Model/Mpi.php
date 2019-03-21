<?php

/**
 * Parent class for MPI requests
 */
class Collinsharper_Moneriscc_Model_Mpi extends Collinsharper_Moneriscc_Model_Abstract
{
    public function buildTransactionArray()
    {
        return array(
            'This should be overridden in the extending class.'
        );
    }

    public function post($txnArray=null)
    {
        if (!$txnArray) {
            $txnArray = $this->buildTransactionArray();
        }

        $storeId = Mage::helper('moneriscc')->getMonerisStoreId();
        $apiToken = Mage::helper('moneriscc')->getMonerisApiToken();

        if(Mage::helper('moneriscc')->isUsApi()) {
            $mpiTxn = new Monerisus_MpiTransaction($txnArray);
            $mpiRequest = new Monerisus_MpiRequest($mpiTxn);
            $mpiHttpsPost = new Monerisus_MpiHttpsPost($storeId, $apiToken, $mpiRequest);
            Mage::helper('moneriscc')->log(__METHOD__ . "This is a US API CALL");
            $mpiResponse = $mpiHttpsPost->getMpiResponse();
        } else {
            Mage::helper('moneriscc')->log(__METHOD__ . "This is a NOT A US API CALL");
            $mpiTxn = new Moneris_MpiTransaction($txnArray);
            $mpiRequest = new Moneris_MpiRequest($mpiTxn);
            $mpiHttpsPost = new Moneris_MpiHttpsPost($storeId, $apiToken, $mpiRequest);
            //Mage::helper('moneriscc')->log($mpiHttpsPost);
            $mpiResponse = $mpiHttpsPost->getMpiResponse();
        }


        return $mpiResponse;
    }
}
