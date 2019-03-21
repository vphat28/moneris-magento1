<?php

#################### mpgGlobals ###########################################


//class Collinsharper_Monerisccus_Model_Moneriscc_mpgGlobals
class Monerisus_MpgGlobals
{

	var $Globals=array(
        	        'MONERIS_PROTOCOL' => 'https',
        	        'MONERIS_HOST' => 'esplusqa.moneris.com',
        	        'MONERIS_PORT' =>'443',
               	  	'MONERIS_FILE' => '/gateway_us/servlet/MpgRequest',
                  	'API_VERSION'  =>'US PHP Api v.1.1.2',
                  	'CLIENT_TIMEOUT' => '60'
                 	);

 	function Monerisus_MpgGlobals()
 	{
 		// default
         $this->Globals['MONERIS_HOST'] = 'esplus.moneris.com';
         if(defined('MONERIS_TEST') && MONERIS_TEST == 1)
         {
             $this->Globals['MONERIS_HOST'] = 'esplusqa.moneris.com';
         }
 	}


 	function getGlobals()
 	{
  		return($this->Globals);
 	}

}//end class mpgGlobals

