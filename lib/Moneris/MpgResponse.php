<?php


class Moneris_MpgResponse
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

	//specifically for Resolver transactions
 	var $resolveData;
 	var $isResolveData = 0;
 	var $resolveDataHash;
 	var $data_key="";
 	var $DataKeys = array();

 	var $ACSUrl;

 	function Moneris_MpgResponse($xmlString)
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
   		return isset($this->responseData) ? ($this->responseData) : false;
 	}

	function getRecurSuccess()
	{
 		return isset($this->responseData['RecurSuccess']) ? ($this->responseData['RecurSuccess']) : false;
	}

	function getStatusCode()
	{
	 	return isset($this->responseData['status_code']) ? ($this->responseData['status_code']) : false;
	}

	function getStatusMessage()
	{
	 	return isset($this->responseData['status_message'])? ($this->responseData['status_message']) : false;
	}

	function getAvsResultCode()
	{
		return isset($this->responseData['AvsResultCode']) ? ($this->responseData['AvsResultCode']) : false;
	}

	function getCvdResultCode()
	{
		return isset($this->responseData['CvdResultCode']) ? ($this->responseData['CvdResultCode']) : false;
	}

	function getCardType()
	{
 		return isset($this->responseData['CardType']) ? ($this->responseData['CardType']) : false;
	}

	function getTransAmount()
	{
 		return isset($this->responseData['TransAmount']) ? ($this->responseData['TransAmount']) : false;
	}

	function getTxnNumber()
	{
 		return isset($this->responseData['TransID']) ? ($this->responseData['TransID']) : false;
	}

	function getReceiptId()
	{
 		return isset($this->responseData['ReceiptId']) ? ($this->responseData['ReceiptId']) : false;
	}

	function getTransType()
	{
 		return isset($this->responseData['TransType']) ? ($this->responseData['TransType']) : false;
	}

	function getReferenceNum()
	{
 		return isset($this->responseData['ReferenceNum']) ? ($this->responseData['ReferenceNum']) : false;
	}

	function getResponseCode()
	{
 		return isset($this->responseData['ResponseCode']) ? ($this->responseData['ResponseCode']) : false;
	}

	function getISO()
	{
 		return isset($this->responseData['ISO']) ? ($this->responseData['ISO']) : false;
	}

	function getBankTotals()
	{
 		return isset($this->responseData['BankTotals']) ? ($this->responseData['BankTotals']) : false;
	}

	function getMessage()
	{
 		return isset($this->responseData['Message']) ? ($this->responseData['Message']) : false;
	}

	function getAuthCode()
	{
 		return isset($this->responseData['AuthCode']) ? ($this->responseData['AuthCode']) : false;
	}

	function getComplete()
	{
 		return isset($this->responseData['Complete']) ? ($this->responseData['Complete']) : false;
	}

	function getTransDate()
	{
 		return isset($this->responseData['TransDate']) ? ($this->responseData['TransDate']) : false;
	}

	function getTransTime()
	{
 		return isset($this->responseData['TransTime']) ? ($this->responseData['TransTime']) : false;
	}

	function getTicket()
	{
 		return isset($this->responseData['Ticket']) ? ($this->responseData['Ticket']) : false;
	}

	function getTimedOut()
	{
 		return isset($this->responseData['TimedOut']) ? ($this->responseData['TimedOut']) : false;
	}

	function getCorporateCard()
	{
		return isset($this->responseData['CorporateCard']) ? ($this->responseData['CorporateCard']) : false;
    }

    function getCavvResultCode()
    {
		return isset($this->responseData['CavvResultCode']) ? ($this->responseData['CavvResultCode']) : false;
	}

	function getCardLevelResult()
	{
		return isset($this->responseData['CardLevelResult']) ? ($this->responseData['CardLevelResult']) : false;
	}

	function getITDResponse()
	{
		return isset($this->responseData['ITDResponse']) ? ($this->responseData['ITDResponse']) : false;
	}

	//--------------------------- RecurUpdate response fields ----------------------------//

	function getRecurUpdateSuccess()
	{
		return isset($this->responseData['RecurUpdateSuccess']) ? ($this->responseData['RecurUpdateSuccess']) : false;
	}

	function getNextRecurDate()
	{
		return isset($this->responseData['NextRecurDate']) ? ($this->responseData['NextRecurDate']) : false;
	}

	function getRecurEndDate()
	{
		return isset($this->responseData['RecurEndDate']) ? ($this->responseData['RecurEndDate']) : false;
	}

	//-------------------------- Resolver response fields --------------------------------//

	function getDataKey()
	{
		return isset($this->responseData['DataKey']) ? ($this->responseData['DataKey']) : false;
	}

	function getResSuccess()
	{
		return isset($this->responseData['ResSuccess']) ? ($this->responseData['ResSuccess']) : false;
	}

	function getPaymentType()
	{
		return isset($this->responseData['PaymentType']) ? ($this->responseData['PaymentType']) : false;
	}

	//------------------------------------------------------------------------------------//

	function getResolveData()
	{
		if($this->responseData['ResolveData']!='null'){
			return ($this->resolveData);
		}

		return isset($this->responseData['ResolveData']) ? ($this->responseData['ResolveData']) : false;
	}

	function setResolveData($data_key)
	{
		$this->resolveData=$this->resolveDataHash[$data_key];
	}

	function getResolveDataHash()
	{
		return isset($this->resolveDataHash) ? ($this->resolveDataHash) : false;
	}

	function getDataKeys()
	{
	 	return isset($this->DataKeys) ? ($this->DataKeys) : false;
 	}

 	function getResDataDataKey()
	{
		return isset($this->resolveData['data_key']) ? ($this->resolveData['data_key']) : false;
	}

	function getResDataPaymentType()
	{
		return isset($this->resolveData['payment_type']) ? ($this->resolveData['payment_type']) : false;
	}

	function getResDataCustId()
	{
		return isset($this->resolveData['cust_id']) ? ($this->resolveData['cust_id']) : false;
	}

	function getResDataPhone()
	{
		return isset($this->resolveData['phone']) ? ($this->resolveData['phone']) : false;
	}

	function getResDataEmail()
	{
		return isset($this->resolveData['email']) ? ($this->resolveData['email']) : false;
	}

	function getResDataNote()
	{
		return isset($this->resolveData['note']) ? ($this->resolveData['note']) : false;
	}

	function getResDataPan()
	{
		return isset($this->resolveData['pan']) ? ($this->resolveData['pan']) : false;
	}

	function getResDataMaskedPan()
	{
		return isset($this->resolveData['masked_pan']) ? ($this->resolveData['masked_pan']) : false;
	}

	function getResDataExpDate()
	{
		return isset($this->resolveData['expdate']) ? ($this->resolveData['expdate']) : false;
	}

	function getResDataAvsStreetNumber()
	{
		return isset($this->resolveData['avs_street_number']) ? ($this->resolveData['avs_street_number']) : false;
	}

	function getResDataAvsStreetName()
	{
		return isset($this->resolveData['avs_street_name']) ? ($this->resolveData['avs_street_name']) : false;
	}

	function getResDataAvsZipcode()
	{
		return isset($this->resolveData['avs_zipcode']) ? ($this->resolveData['avs_zipcode']) : false;
	}

	function getResDataCryptType()
	{
		return isset($this->resolveData['crypt_type']) ? ($this->resolveData['crypt_type']) : false;
	}

	//--------------------------- BatchClose response fields -----------------------------//

	function getTerminalStatus($ecr_no)
	{
 		return isset($this->ecrHash[$ecr_no]) ? ($this->ecrHash[$ecr_no]) : false;
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
 		return isset($this->ecrs) ? ($this->ecrs) : false;
	}

	function getCreditCardsAll()
	{
 		return (array_keys($this->cards));
	}

	function getCreditCards($ecr)
	{
 		return isset($this->cardHash[$ecr]) ? ($this->cardHash[$ecr]) : false;
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
 		elseif($this->isResolveData && $this->currentTag != "ResolveData")
 		{
			if($this->currentTag == "data_key")
			{
				$this->data_key=$data;
				array_push($this->DataKeys,$this->data_key);
                if($this->resolveData[$this->currentTag])
                {
                    $this->resolveData[$this->currentTag] .=$data;
                }
                else
                {
                    $this->resolveData[$this->currentTag] =$data;
                }
            }
   			else
   			{
                if(isset($this->resolveData[$this->currentTag]))
                {
                    $this->resolveData[$this->currentTag] .= $data;
                }
               else
               {
                   $this->resolveData[$this->currentTag] = $data;
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

		if($this->currentTag == "ResolveData")
		{
			$this->isResolveData=1;
  	 	}
  	 	elseif($this->isResolveData)
  	 	{
  	 		$this->resolveData[$this->currentTag]="";
  	 	}

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
	 	if($this->currentTag == "ResolveData")
		{
			$this->isResolveData=0;
			if($this->data_key!="")
			{
				$this->resolveDataHash[$this->data_key]=$this->resolveData;
				$this->resolveData=array();
			}
	 	}
	 	if($name == "BankTotals")
	  	{
	    	$this->isBatchTotals=0;
	   	}

 		$this->currentTag="/dev/null";
	}

}//end class mpgResponse

