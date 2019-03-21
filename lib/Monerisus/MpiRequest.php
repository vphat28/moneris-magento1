<?php

################## MpiRequest ###########################################################

class Monerisus_MpiRequest{

	var $txnTypes =array(

		'txn' =>array('xid', 'amount', 'pan', 'expdate','MD',
			'merchantUrl','accept','userAgent','currency','recurFreq',
			'recurEnd','install'),
		'acs' => array('PaRes','MD')
	);
	var $txnArray;

	function Monerisus_MpiRequest($txn) {

		if(is_array($txn)) {
			$this->txnArray = $txn;
		} else {
			$temp[0]=$txn;
			$this->txnArray=$temp;
		}
	}

	function toXML() {

		$tmpTxnArray=$this->txnArray;
		$txnArrayLen=count($tmpTxnArray); //total number of transactions

		for($x=0;$x < $txnArrayLen;$x++) {
			$txnObj=$tmpTxnArray[$x];
			$txn=$txnObj->getTransaction();

			$txnType=array_shift($txn);
			$tmpTxnTypes=$this->txnTypes;
			$txnTypeArray=$tmpTxnTypes[$txnType];
			$txnTypeArrayLen=count($txnTypeArray); //length of a specific txn type

			$txnXMLString="";
			for($i=0;$i < $txnTypeArrayLen ;$i++) {

				$_data = '';
				if(isset($txnTypeArray[$i]) && isset($txn[$txnTypeArray[$i]])) {
					$_data = $txn[$txnTypeArray[$i]];
				}

				$txnXMLString  .="<$txnTypeArray[$i]>"   //begin tag
					. $_data // data
					. "</$txnTypeArray[$i]>"; //end tag

			}

			$txnXMLString = "<$txnType>$txnXMLString";

			$txnXMLString .= "</$txnType>";

			$xmlString = $txnXMLString;

		}

		return $xmlString;

	}//end toXML

}//end class
