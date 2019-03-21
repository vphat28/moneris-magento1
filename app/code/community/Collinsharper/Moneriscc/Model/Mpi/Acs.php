<?php

class Collinsharper_Moneriscc_Model_Mpi_Acs extends Collinsharper_Moneriscc_Model_Mpi
{
    protected $_requestType = 'acs';

    public function buildTransactionArray()
    {
        $txnArray = array(
            'type'  => $this->_requestType,
            'PaRes' => $this->getPaRes(),
            'MD'    => $this->getMd()
        );
        return $txnArray;
    }
}
