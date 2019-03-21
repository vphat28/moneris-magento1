<?php
################## MpiTransaction ###########################################################

class Monerisus_MpiTransaction{

	var $txn;

	function Monerisus_MpiTransaction($txn)
	{
		$this->txn = $txn;
	}

	function getTransaction()
	{
		return $this->txn;
	}

}//end class