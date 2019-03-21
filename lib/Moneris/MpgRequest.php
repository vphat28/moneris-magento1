<?php
################## mpgRequest ###########################################################

class Moneris_MpgRequest
{

 	var $txnTypes =array(
			'purchase'          => array('order_id','cust_id', 'amount', 'pan', 'expdate', 'crypt_type','dynamic_descriptor'),
		    'refund'            => array('order_id', 'amount', 'txn_number', 'crypt_type'),
		    'idebit_purchase'   =>array('order_id', 'cust_id', 'amount','idebit_track2','dynamic_descriptor'),
		    'idebit_refund'     =>array('order_id','amount','txn_number'),
		    'ind_refund'        => array('order_id','cust_id', 'amount','pan','expdate', 'crypt_type','dynamic_descriptor'),
		    'preauth'           =>array('order_id','cust_id', 'amount', 'pan', 'expdate', 'crypt_type','dynamic_descriptor'),
		    'reauth'            =>array('order_id','cust_id', 'amount', 'orig_order_id', 'txn_number', 'crypt_type'),
		    'completion'        => array('order_id', 'comp_amount','txn_number', 'crypt_type'),
		    'purchasecorrection'=> array('order_id', 'txn_number', 'crypt_type'),
		    'opentotals'        => array('ecr_number'),
		    'batchclose'        => array('ecr_number'),
            'card_verification' =>array('order_id','cust_id','pan','expdate'),
		    'cavv_purchase'     => array('order_id','cust_id', 'amount', 'pan','expdate', 'cavv','dynamic_descriptor'),
			'cavv_preauth'      =>array('order_id','cust_id', 'amount', 'pan','expdate', 'cavv','dynamic_descriptor'),
			'recur_update'      => array('order_id','cust_id','pan','expdate','recur_amount','add_num_recurs','total_num_recurs','hold','terminate'),
			'res_add_cc'        => array('cust_id','phone','email','note','pan','expdate','crypt_type'),
			'res_update_cc'     => array('data_key','cust_id','phone','email','note','pan','expdate','crypt_type'),
			'res_delete'        => array('data_key'),
			'res_lookup_full'   => array('data_key'),
			'res_lookup_masked' => array('data_key'),
			'res_get_expiring'  => array(),
			'res_purchase_cc'   => array('data_key','order_id','cust_id','amount','crypt_type','dynamic_descriptor'),
			'res_preauth_cc'    => array('data_key','order_id','cust_id','amount','crypt_type','dynamic_descriptor'),
			'res_ind_refund_cc' => array('data_key','order_id','cust_id','amount','crypt_type','dynamic_descriptor'),
			'res_iscorporatecard' => array('data_key'),
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

	function Moneris_MpgRequest($txn)
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
        if (isset($this->txnArray['type'])) {
            return $this->txnArray['type'];
        }

        return null;
	}

	function toXML()
	{
        $xmlString = '';
 		$tmpTxnArray=$this->txnArray;
 		$txnArrayLen=count($tmpTxnArray); //total number of transactions

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
				//Will only add to the XML if the tag was passed in by merchant
				if(array_key_exists($txnTypeArray[$i], $txn))
                {
				 	$txnXMLString  .="<$txnTypeArray[$i]>"   //begin tag
									.$txn[$txnTypeArray[$i]] // data
									. "</$txnTypeArray[$i]>"; //end tag
				}
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

   			$txnXMLString .="</$txnType>";
   			$xmlString .=$txnXMLString;

 		}

 		return $xmlString;

	}//end toXML



}//end class

