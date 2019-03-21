<?php
###################### MpiHttpsPost #########################################

class Monerisus_MpiHttpsPost{

	var $api_token;
	var $store_id;
	var $mpiRequest;
	var $mpiResponse;

	function Monerisus_MpiHttpsPost($store_id,$api_token,$mpiRequestOBJ)
	{

		$this->store_id = $store_id;
		$this->api_token = $api_token;
		$this->mpiRequest = $mpiRequestOBJ;
		$dataToSend = $this->toXML();

		$g = new Monerisus_MpiGlobals();
		$gArray=$g->getGlobals();

		Mage::helper('moneriscc')->log(__METHOD__ . " LIB POST " . print_r($gArray,1));

		
		$url=$gArray['MONERIS_PROTOCOL']."://".
			$gArray['MONERIS_HOST'].":".
			$gArray['MONERIS_PORT'].
			$gArray['MONERIS_FILE'];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $dataToSend);
		curl_setopt($ch,CURLOPT_TIMEOUT, $gArray['CLIENT_TIMEOUT']);
		curl_setopt($ch,CURLOPT_USERAGENT, $gArray['API_VERSION']);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);

		$response = curl_exec ($ch);

		curl_close ($ch);

		if(!$response) {

			$response="<?xml version=\"1.0\"?>".
				"<MpiResponse>".
				"<type>null</type>".
				"<success>false</success>".
				"<message>null</message>".
				"<PaReq>null</PaReq>".
				"<TermUrl>null</TermUrl>".
				"<MD>null</MD>".
				"<ACSUrl>null</ACSUrl>".
				"<cavv>null</cavv>".
				"<PAResVerified>null</PAResVerified>".
				"</MpiResponse>";
		}

		// echo "$response";exit();

		$this->mpiResponse = new Monerisus_MpiResponse($response);

	}



	function getMpiResponse()
	{
		return $this->mpiResponse;

	}

	function toXML( )
	{

		$req = $this->mpiRequest ;
		$reqXMLString = $req->toXML();

		$xmlString = "<?xml version=\"1.0\"?>".
			"<MpiRequest>".
			"<store_id>{$this->store_id}</store_id>".
			"<api_token>{$this->api_token}</api_token>".
			$reqXMLString.
			"</MpiRequest>";

		return ($xmlString);

	}

}//end class mpiHttpsPost