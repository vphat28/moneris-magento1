<?php

#################### mpgGlobals ###########################################


class Moneris_mpgGlobals
{
	var $Globals=array(
                'MONERIS_PROTOCOL' => 'https',
                'MONERIS_HOST' => 'esqa.moneris.com',
                'MONERIS_PORT' =>'443',
                'MONERIS_FILE' => '/gateway2/servlet/MpgRequest',
                'API_VERSION'  =>'MpgApi Version 2.03(php)',
                'CLIENT_TIMEOUT' => '60'
                 	);

 	function Moneris_mpgGlobals()
 	{
 		// default
         $this->Globals['MONERIS_HOST'] = 'www3.moneris.com';
         if(defined('MONERIS_TEST') && MONERIS_TEST == 1)
         {
             $this->Globals['MONERIS_HOST'] = 'esqa.moneris.com';
         }


 	}


 	function getGlobals()
 	{
  		return($this->Globals);
 	}

    function setUsMode()
 	{
         $this->Globals['MONERIS_HOST'] = 'esplus.moneris.com';
         // what is the host for US production
         if(defined('MONERIS_TEST') && MONERIS_TEST == 1)
         {
             $this->Globals['MONERIS_HOST'] = 'esplusqa.moneris.com';
         }

 	}


}//end class mpgGlobals
