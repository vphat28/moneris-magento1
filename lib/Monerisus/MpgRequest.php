<?php



################## mpgRequest ###########################################################

class Monerisus_MpgRequest
{

 	var $txnTypes =array(
			'us_preauth' => array('order_id','cust_id', 'amount', 'pan', 'expdate', 'crypt_type'),
			'us_completion' => array('order_id', 'comp_amount','txn_number', 'crypt_type',
                                            'commcard_invoice','commcard_tax_amount'),
			'us_purchase'=> array('order_id','cust_id', 'amount', 'pan', 'expdate', 'crypt_type',
                                            'commcard_invoice','commcard_tax_amount'),
            'us_forcepost'=> array('order_id','cust_id','amount','pan','expdate','auth_code','crypt_type'),
			'us_purchasecorrection' => array('order_id', 'txn_number', 'crypt_type'),
			'us_refund' => array('order_id', 'amount', 'txn_number', 'crypt_type'),
			'us_ind_refund' => array('order_id','cust_id', 'amount','pan','expdate', 'crypt_type'),
			'us_cavv_preauth' => array('order_id','cust_id', 'amount', 'pan','expdate', 'cavv'),
			'us_cavv_purchase'=> array('order_id','cust_id','amount','pan','expdate', 'cavv',
                                                 'commcard_invoice','commcard_tax_amount'),
			'us_track2_preauth' => array('order_id','cust_id','amount','track2','pan','expdate','pos_code'),
			'us_track2_completion' => array('order_id', 'comp_amount','txn_number','pos_code',
                                            'commcard_invoice','commcard_tax_amount'),
			'us_track2_forcepost'=>array('order_id','cust_id', 'amount', 'track2','pan','expdate','pos_code','auth_code'),
			'us_track2_purchase' =>array('order_id','cust_id','amount','track2','pan','expdate',
                                                 'commcard_invoice','commcard_tax_amount','pos_code'),
			'us_track2_purchasecorrection' => array('order_id', 'txn_number'),
			'us_track2_refund' => array('order_id', 'amount', 'txn_number'),
			'us_track2_ind_refund' => array('order_id','amount','track2','pan','expdate','cust_id','pos_code'),
			'us_ach_debit' => array('order_id','cust_id','amount'),
			'us_ach_credit' => array('order_id','cust_id','amount'),
			'us_ach_reversal' => array('order_id','txn_number'),
            'us_ach_fi_enquiry' => array('routing_num'),
            'us_pinless_debit_purchase' => array('order_id','amount','pan','expdate','cust_id','presentation_type','intended_use','p_account_number'),
			'us_pinless_debit_refund' => array('order_id', 'amount', 'txn_number'),
			'us_opentotals' => array('ecr_number'),
			'us_batchclose' => array('ecr_number'),
			'us_recur_update' => array('order_id', 'cust_id', 'pan', 'expdate', 'p_account_number', 'presentation_type',
										'recur_amount','add_num_recurs', 'total_num_recurs', 'hold', 'terminate',
                      					'avs_street_number', 'avs_street_name', 'avs_zipcode')
			          );

	var $txnArray;

	function Monerisus_MpgRequest($txn)
	{

 		if(is_array($txn))
   		{
    			$this->txnArray = $txn;
   		}
 		else
   		{
    			$temp[0]=$txn;
    			$this->txnArray=$temp;
   		}
	}

	function getTransactionType()
	{
  		$jtmp=$this->txnArray;
  		$jtmp1=$jtmp[0]->getTransaction();
  		$jtmp2=array_shift($jtmp1);
  		return $jtmp2;
	}

	function toXML()
	{

 		$tmpTxnArray=$this->txnArray;
 		$txnArrayLen=count($tmpTxnArray); //total number of transactions
		$xmlString = "";
 		for($x=0;$x < $txnArrayLen;$x++)
 		{
    			$txnObj=$tmpTxnArray[$x];
    			$txn=$txnObj->getTransaction();

    			$txnType=array_shift($txn);
    			$tmpTxnTypes=$this->txnTypes;
    			$txnTypeArray=$tmpTxnTypes[$txnType];
    			$txnTypeArrayLen=count($txnTypeArray); //length of a specific txn type

    			$txnXMLString="";

			for($i=0;$i < $txnTypeArrayLen ;$i++)
    			{
					$_data = '';
					if(isset($txnTypeArray[$i]) && isset($txn[$txnTypeArray[$i]]))
					{
						$_data = $txn[$txnTypeArray[$i]];
					}
     				 $txnXMLString  .="<{$txnTypeArray[$i]}>"   //begin tag
                       				.$_data 	// data
                       				. "</{$txnTypeArray[$i]}>"; //end tag
    			}

   			$txnXMLString = "<$txnType>$txnXMLString";


   			$recur  = $txnObj->getRecur();
  			if($recur != null)
   			{
         			$txnXMLString .= $recur->toXML();
   			}

			$avs  = $txnObj->getAvsInfo();
			if($avs != null)
			{
				$txnXMLString .= $avs->toXML();
			}

			$cvd  = $txnObj->getCvdInfo();
			if($cvd != null)
			{
				$txnXMLString .= $cvd->toXML();
			}

   			$custInfo = $txnObj->getCustInfo();
   			if($custInfo != null)
   			{
        		$txnXMLString .= $custInfo->toXML();
   			}

   			$ach = $txnObj->getAchInfo();
			if($ach != null)
			{
				$txnXMLString .= $ach->toXML();
   			}

   			$txnXMLString .="</$txnType>";
   			$xmlString .=$txnXMLString;

 		}

 		return $xmlString;

	}//end toXML



}//end class

