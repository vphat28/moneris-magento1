<?php


############# mpgResponse #####################################################


class Monerisus_MpgResponse
{

	var $responseData;

 	var $p; //parser

 	var $currentTag;
 	var $purchaseHash = array();
 	var $refundHash;
 	var $correctionHash = array();
 	var $isBatchTotals;
 	var $term_id;
 	var $receiptHash = array();
 	var $ecrHash = array();
 	var $CardType;
 	var $currentTxnType;
 	var $ecrs = array();
 	var $cards = array();
 	var $cardHash= array();

 	var $ACSUrl;

 	function Monerisus_MpgResponse($xmlString)
 	{

  		$this->p = xml_parser_create();
  		xml_parser_set_option($this->p,XML_OPTION_CASE_FOLDING,0);
  		xml_parser_set_option($this->p,XML_OPTION_TARGET_ENCODING,"UTF-8");
  		xml_set_object($this->p,$this);
  		xml_set_element_handler($this->p,"startHandler","endHandler");
  		xml_set_character_data_handler($this->p,"characterHandler");
  		xml_parse($this->p,$xmlString);
  		xml_parser_free($this->p);


 	}	//end of constructor


 	function getMpgResponseData()
	{
   		return($this->responseData);

 	}

	function getRecurSuccess()
	{
 		return ($this->responseData['RecurSuccess']);
	}

	function getAvsResultCode()
	{
		if(isset($this->responseData['AvsResultCode']))
		{
			return ($this->responseData['AvsResultCode']);
		}
	}

	function getCvdResultCode()
	{
		if(isset($this->responseData['CvdResultCode']))
		{
			return ($this->responseData['CvdResultCode']);
		}
	}

	function getCardType()
	{
 		return ($this->responseData['CardType']);
	}

	function getTransAmount()
	{
 		return ($this->responseData['TransAmount']);
	}

	function getTxnNumber()
	{
 		return ($this->responseData['TransID']);
	}

	function getReceiptId()
	{
 		return ($this->responseData['ReceiptId']);
	}

	function getTransType()
	{
 		return ($this->responseData['TransType']);
	}

	function getReferenceNum()
	{
 		return ($this->responseData['ReferenceNum']);
	}

	function getResponseCode()
	{
 		return ($this->responseData['ResponseCode']);
	} 

	function getISO()
	{
		$_var = 'ISO';
		if(isset($this->responseData[$_var]))
		{
			return ($this->responseData[$_var]);
		} return '';
	}

	function getBankTotals()
	{
		$_var = 'BankTotals';
		if(isset($this->responseData[$_var]))
		{
			return ($this->responseData[$_var]);
		} return '';
	}

	function getMessage()
	{
		$_var = 'Message';
		if(isset($this->responseData[$_var]))
		{
			return ($this->responseData[$_var]);
		}
		return '';
	}

	function getAuthCode()
	{
		$_var = 'AuthCode';
		if(isset($this->responseData[$_var]))
		{
			return ($this->responseData[$_var]);
		}
	}

	function getComplete()
	{
 		$_var = 'Complete';
		if(isset($this->responseData[$_var]))
		{
			return ($this->responseData[$_var]);
		} return '';
	}

	function getTransDate()
	{
 		$_var = 'TransDate';
		if(isset($this->responseData[$_var]))
		{
			return ($this->responseData[$_var]);
		} return '';
	}

	function getTransTime()
	{
 		$_var = 'TransTime';
		if(isset($this->responseData[$_var]))
		{
			return ($this->responseData[$_var]);
		} return '';
	}

	function getTicket()
	{
 		return ($this->responseData['Ticket']);
	}

	function getTimedOut()
	{
 		return ($this->responseData['TimedOut']);
	}

	function getRecurUpdateSuccess(){
		return ($this->responseData['RecurUpdateSuccess']);
	}

	function getNextRecurDate(){
		return ($this->responseData['NextRecurDate']);
	}

	function getRecurEndDate(){
		return ($this->responseData['RecurEndDate']);
}

	function getTerminalStatus($ecr_no)
	{
 		return ($this->ecrHash[$ecr_no]);
	}

	function getPurchaseAmount($ecr_no,$card_type)
	{
 		return ($this->purchaseHash[$ecr_no][$card_type]['Amount']=="" ? 0:$this->purchaseHash[$ecr_no][$card_type]['Amount']);
	}

	function getPurchaseCount($ecr_no,$card_type)
	{
 		return ($this->purchaseHash[$ecr_no][$card_type]['Count']=="" ? 0:$this->purchaseHash[$ecr_no][$card_type]['Count']);
	}

	function getRefundAmount($ecr_no,$card_type)
	{
 		return ($this->refundHash[$ecr_no][$card_type]['Amount']=="" ? 0:$this->refundHash[$ecr_no][$card_type]['Amount']);
	}

	function getRefundCount($ecr_no,$card_type)
	{
 		return ($this->refundHash[$ecr_no][$card_type]['Count']=="" ? 0:$this->refundHash[$ecr_no][$card_type]['Count']);
	}

	function getCorrectionAmount($ecr_no,$card_type)
	{
 		return ($this->correctionHash[$ecr_no][$card_type]['Amount']=="" ? 0:$this->correctionHash[$ecr_no][$card_type]['Amount']);
	}

	function getCorrectionCount($ecr_no,$card_type)
	{
 		return ($this->correctionHash[$ecr_no][$card_type]['Count']=="" ? 0:$this->correctionHash[$ecr_no][$card_type]['Count']);
	}

	function getTerminalIDs()
	{
 		return ($this->ecrs);
	}

	function getCreditCardsAll()
	{
 		return (array_keys($this->cards));
	}

	function getCreditCards($ecr)
	{
 		return ($this->cardHash[$ecr]);
	}



	function characterHandler($parser,$data)
	{

 		if($this->isBatchTotals)
 		{
   			switch($this->currentTag)
    			{
     			case "term_id"    :
			{
                       		$this->term_id=$data;
                          	array_push($this->ecrs,$this->term_id);
                          	$this->cardHash[$data]=array();
                          	break;
                        }

     			case "closed"     :
			{
                        	$ecrHash=$this->ecrHash;
                          	$ecrHash[$this->term_id]=$data;
                          	$this->ecrHash = $ecrHash;
                          	break;
                        }

     			case "CardType"   :
			{
                          	$this->CardType=$data;
                          	$this->cards[$data]=$data;
                          	array_push($this->cardHash[$this->term_id],$data) ;
                          	break;
                         }

     			case "Amount"     :
			{
                          	if($this->currentTxnType == "Purchase")
                           	{
                             		$this->purchaseHash[$this->term_id][$this->CardType]['Amount']=$data;
                            	}
                           	elseif( $this->currentTxnType == "Refund")
                            	{
                              		$this->refundHash[$this->term_id][$this->CardType]['Amount']=$data;
                            	}
                           	elseif( $this->currentTxnType == "Correction")
                            	{
                              		$this->correctionHash[$this->term_id][$this->CardType]['Amount']=$data;
                            	}
                           	break;
                         }

    			case "Count"     :
			{
                        	if($this->currentTxnType == "Purchase")
                            	{
                             		$this->purchaseHash[$this->term_id][$this->CardType]['Count']=$data;
                            	}
                           	elseif( $this->currentTxnType == "Refund")
                            	{
                              		$this->refundHash[$this->term_id][$this->CardType]['Count']=$data;

                            	}
                           	else if( $this->currentTxnType == "Correction")
                            	{
                              		$this->correctionHash[$this->term_id][$this->CardType]['Count']=$data;
                            	}
                          	break;
                        }
	    		}

 		}
 		else
 		{
			if(isset($this->responseData[$this->currentTag]))
			{
				$this->responseData[$this->currentTag] .=$data;
			}
			else
			{
				$this->responseData[$this->currentTag] =$data;
			}
 		}

	}//end characterHandler



	function startHandler($parser,$name,$attrs)
	{

		$this->currentTag=$name;

  		if($this->currentTag == "BankTotals")
  	 	{
  	  		$this->isBatchTotals=1;
  	 	}
  		elseif($this->currentTag == "Purchase")
   		{
   	 		$this->purchaseHash[$this->term_id][$this->CardType]=array();
   	 		$this->currentTxnType="Purchase";
   		}
  		elseif($this->currentTag == "Refund")
  	 	{
  	  		$this->refundHash[$this->term_id][$this->CardType]=array();
  	  		$this->currentTxnType="Refund";
  	 	}
  		elseif($this->currentTag == "Correction")
   		{
   	 		$this->correctionHash[$this->term_id][$this->CardType]=array();
   	 		$this->currentTxnType="Correction";
   		}
	}

	function endHandler($parser,$name)
	{

	 	$this->currentTag=$name;
	 	if($name == "BankTotals")
	  	{
	    		$this->isBatchTotals=0;
	   	}

 		$this->currentTag="/dev/null";
	}

}//end class mpgResponse

