<?php

#################### mpgGlobals ###########################################


class Moneris_MpiGlobals{

    var $Globals=array(
        'MONERIS_PROTOCOL' => 'https',
        'MONERIS_HOST' => 'esqa.moneris.com',
        'MONERIS_PORT' =>'443',
        'MONERIS_FILE' => '/mpi/servlet/MpiServlet',
        'API_VERSION'  =>'MPI Version 1.00(php)',
        'CLIENT_TIMEOUT' => '60'
    );

 function Moneris_MpiGlobals()
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

}//end class mpgGlobals
