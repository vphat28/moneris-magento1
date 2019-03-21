<?php

#################### MpiGlobals ###########################################


class Monerisus_MpiGlobals{

	var $Globals=array(
		'MONERIS_PROTOCOL' => 'https',
		'MONERIS_HOST' => 'esplusqa.moneris.com',
		'MONERIS_PORT' =>'443',
		'MONERIS_FILE' => '/mpi/servlet/MpiServlet',
		'API_VERSION'  =>'MPI Version 1.1.0(PHP MPI)',
		'CLIENT_TIMEOUT' => '60'
	);

	function Monerisus_MpiGlobals()
	{
		// default
		$this->Globals['MONERIS_HOST'] = 'esplus.moneris.com';
		if(defined('MONERIS_TEST') && MONERIS_TEST == 1) {
			$this->Globals['MONERIS_HOST'] = 'esplusqa.moneris.com';
		}

	}


	function getGlobals()
	{
		return($this->Globals);
	}

}//end class mpgGlobals
