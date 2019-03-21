<?php

###################### mpgHttpsPost #########################################

class Monerisus_MpgHttpsPost
{

 	var $api_token;
 	var $store_id;
 	var $mpgRequest;
 	var $mpgResponse;

 	function Monerisus_MpgHttpsPost($storeid,$apitoken,$mpgRequestOBJ)
 	{
  		$this->store_id=$storeid;
  		$this->api_token= $apitoken;
  		$this->mpgRequest=$mpgRequestOBJ;
  		$dataToSend=$this->toXML();

		//print("String to be sent = " . $dataToSend);

  		//do post

  		$g=new Monerisus_MpgGlobals();
  		$gArray=$g->getGlobals();
  		$transactionType=$mpgRequestOBJ->getTransactionType();

         $gArray['MONERIS_HOST'] = 'esplus.moneris.com';
         if(defined('MONERIS_TEST') && MONERIS_TEST == 1)
         {
             $gArray['MONERIS_HOST'] = 'esplusqa.moneris.com';
         }

         $url =  $gArray['MONERIS_PROTOCOL']."://".
       			$gArray['MONERIS_HOST'].":".
      			$gArray['MONERIS_PORT'].
       			$gArray['MONERIS_FILE'];

  		$ch = curl_init();
 		curl_setopt($ch, CURLOPT_URL,$url);
  		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
  		curl_setopt ($ch, CURLOPT_HEADER, 0);
  		curl_setopt($ch, CURLOPT_POST, 1);
  		curl_setopt($ch, CURLOPT_POSTFIELDS,$dataToSend);
  		curl_setopt($ch,CURLOPT_TIMEOUT,$gArray['CLIENT_TIMEOUT']);
  		curl_setopt($ch,CURLOPT_USERAGENT,$gArray['API_VERSION']);
  		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

  		$response=curl_exec ($ch);

  		//echo "RESPONSE = $response";

  		curl_close ($ch);

  		if(!$response)
  		{

     			$response="<?xml version=\"1.0\"?><response><receipt>".
          			"<ReceiptId>Global Error Receipt</ReceiptId>".
          			"<ReferenceNum>null</ReferenceNum><ResponseCode>null</ResponseCode>".
          			"<AuthCode>null</AuthCode><TransTime>null</TransTime>".
          			"<TransDate>null</TransDate><TransType>null</TransType><Complete>false</Complete>".
          			"<Message>Global Error Receipt</Message><TransAmount>null</TransAmount>".
          			"<CardType>null</CardType>".
          			"<TransID>null</TransID><TimedOut>null</TimedOut>".
          			"<CorporateCard>false</CorporateCard><MessageId>null</MessageId>".
          			"</receipt></response>";
   		}

  		$this->mpgResponse=new Monerisus_MpgResponse($response);

 	}



 	function getMpgResponse()
 	{
  		return $this->mpgResponse;

 	}

 	function toXML()
 	{
		$xmlString = '';
  		$req=$this->mpgRequest ;
  		$reqXMLString=$req->toXML();

  		$xmlString .= "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>".
               			"<request>".
               			"<store_id>{$this->store_id}</store_id>".
               			"<api_token>{$this->api_token}</api_token>".
                		$reqXMLString.
                		"</request>";

  		return ($xmlString);

 	}

}//end class mpgHttpsPost

