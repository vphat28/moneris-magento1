<?php

############# MpiResponse #####################################################


class Monerisus_MpiResponse {

	var $responseData;

	var $p; //parser

	var $currentTag;
	var $receiptHash = array();
	var $currentTxnType;

	var $ACSUrl;

	function Monerisus_MpiResponse($xmlString)
	{

		$this->p = xml_parser_create();
		xml_parser_set_option($this->p,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($this->p,XML_OPTION_TARGET_ENCODING,"UTF-8");
		xml_set_object($this->p,$this);
		xml_set_element_handler($this->p,"startHandler","endHandler");
		xml_set_character_data_handler($this->p,"characterHandler");
		xml_parse($this->p,$xmlString);
		xml_parser_free($this->p);


	}//end of constructor



//vbv start

	function getMpiMessage(){

		return ($this->responseData['message']);

	}


	function getMpiSuccess(){

		return ($this->responseData['success']);

	}

	function getMpiPAResVerified(){

		return ($this->responseData['PAResVerified']);
	}

	function getMpiAcsUrl(){

		return ($this->responseData['ACSUrl']);
	}

	function getMpiPaReq(){

		return ($this->responseData['PaReq']);
	}
	function getMpiTermUrl(){

		return ($this->responseData['TermUrl']);
	}

	function getMpiMD(){

		return ($this->responseData['MD']);
	}


	function getMpiCavv(){

		return ($this->responseData['cavv']);
	}



	function getMpiResponseData(){

		return($this->responseData);

	}

	function getMpiPopUpWindow(){

		$popUpForm ='<html><head><title>Title for Page</title></head><SCRIPT LANGUAGE="Javascript" >' .
			"<!--
function OnLoadEvent()
{
window.name='mainwindow';
//childwin = window.open('about:blank','popupName','height=400,width=390,status=yes,dependent=no,scrollbars=yes,resizable=no');
//document.downloadForm.target = 'popupName';
document.downloadForm.submit();
}
-->
</SCRIPT>" .
			'<body onload="OnLoadEvent()">
<form name="downloadForm" action="' . $this->getMpiAcsUrl() .
			'" method="POST">
<noscript>
<br>
<br>
<center>
<h1>Processing your 3-D Secure Transaction</h1>
<h2>
JavaScript is currently disabled or is not supported
by your browser.<br>
<h3>Please click on the Submit button to continue
the processing of your 3-D secure
transaction.</h3>
<input type="submit" value="Submit">
</center>
</noscript>
<input type="hidden" name="PaReq" value="' . $this->getMpiPaReq() . '">
<input type="hidden" name="MD" value="' . $this->getMpiMD() . '">
<input type="hidden" name="TermUrl" value="' . $this->getMpiTermUrl() .'">
</form>
</body>
</html>';

		return $popUpForm;

	}


	function getMpiInLineForm(){

		$inLineForm ='<html><head><title>Title for Page</title></head><SCRIPT LANGUAGE="Javascript" >' .
			"<!--
function OnLoadEvent()
{
document.downloadForm.submit();
}
-->
</SCRIPT>" .
			'<body onload="OnLoadEvent()">
<form name="downloadForm" action="' . $this->getMpiAcsUrl() .
			'" method="POST">
<noscript>
<br>
<br>
<center>
<h1>Processing your 3-D Secure Transaction</h1>
<h2>
JavaScript is currently disabled or is not supported
by your browser.<br>
<h3>Please click on the Submit button to continue
the processing of your 3-D secure
transaction.</h3>
<input type="submit" value="Submit">
</center>
</noscript>
<input type="hidden" name="PaReq" value="' . $this->getMpiPaReq() . '">
<input type="hidden" name="MD" value="' . $this->getMpiMD() . '">
<input type="hidden" name="TermUrl" value="' . $this->getMpiTermUrl() .'">
</form>
</body>
</html>';

		return $inLineForm;
	}

	function characterHandler($parser,$data){

		$this->responseData[$this->currentTag] =
			isset($this->responseData[$this->currentTag]) ?
				$this->responseData[$this->currentTag] : '';
		$this->responseData[$this->currentTag] .=trim($data);

	}//end characterHandler



	function startHandler($parser,$name,$attrs){

		$this->currentTag=$name;

	}


	function endHandler($parser,$name){

	}


}//end class MpiResponse

