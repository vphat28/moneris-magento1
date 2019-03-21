<?php


##################### mpgTransaction ################################################

class Moneris_MpgTransaction
{

    var $txn;
    var $cvd;
    var $avs;
    var $custInfo = null;
    var $recur = null;

    function Moneris_MpgTransaction($txn)
    {
        $this->txn=$txn;
    }

    function getCustInfo()
    {
        return $this->custInfo;
    }

    function setCustInfo($custInfo)
    {
        $this->custInfo = $custInfo;
        array_push($this->txn,$custInfo);
    }

    function getRecur()
    {
        return $this->recur;
    }

    function setRecur($recur)
    {
        $this->recur = $recur;
    }

    function getTransaction()
    {
        return $this->txn;
    }

    function getCvdInfo()
    {
        return $this->cvd;
    }

    function setCvdInfo($cvd)
    {
        $this->cvd = $cvd;
    }

    function getAvsInfo()
    {
        return $this->avs;
    }

    function setAvsInfo($avs)
    {
        $this->avs = $avs;
    }

}//end class

