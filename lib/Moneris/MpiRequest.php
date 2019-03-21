<?php

################## mpgRequest ###########################################################

class Moneris_MpiRequest
{
    var $txnTypes = array(
        'txn' => array(
            'xid',
            'amount',
            'pan',
            'expdate',
            'MD',
            'merchantUrl',
            'accept',
            'userAgent',
            'currency',
            'recurFreq',
            'recurEnd',
            'install'
        ),
        'acs'=> array(
            'PaRes',
            'MD'
        )
    );

    var $txnArray;

    function Moneris_MpiRequest($txn)
    {
        if (!is_array($txn)) {
            $txn = array($txn);
        }

        $this->txnArray = $txn;
    }

    function toXML()
    {
        $xmlString = '';

        foreach ($this->txnArray as $txnObj) {
            $txn = $txnObj->getTransaction();
            $txnType = $txn['type'];
            $txnFieldsArray = $this->txnTypes[$txnType];

            $txnXmlString = '';
            foreach ($txnFieldsArray as $txnField) {
                if (isset($txn[$txnField])) {
                    $txnXmlString .= "<{$txnField}>{$txn[$txnField]}</$txnField>";
                }
            }
            $xmlString .= "<{$txnType}>{$txnXmlString}</{$txnType}>";
        }


        // original implementation (buggy)

        /*$tmpTxnArray=$this->txnArray;
        $txnArrayLen=count($tmpTxnArray); //total number of transactions

        for ($x=0;$x < $txnArrayLen;$x++)
        {
            $txnObj=$tmpTxnArray[$x];
            $txn=$txnObj->getTransaction();

            $txnType=array_shift($txn);
            $tmpTxnTypes=$this->txnTypes;
            $txnTypeArray=$tmpTxnTypes[$txnType];
            $txnTypeArrayLen=count($txnTypeArray); //length of a specific txn type

            $txnXMLString="";
            for ($i = 0;$i < $txnTypeArrayLen ;$i++)
            {
              $txnXMLString  .="<$txnTypeArray[$i]>"   //begin tag
                                  .$txn[$txnTypeArray[$i]] // data
                                  . "</$txnTypeArray[$i]>"; //end tag

            }

           $txnXMLString = "<$txnType>$txnXMLString";

           $txnXMLString .="</$txnType>";

           $xmlString .=$txnXMLString;

        }*/

        return $xmlString;

    }//end toXML

}//end class
