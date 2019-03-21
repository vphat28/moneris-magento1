<?php


###################### MpiHttpsPost #########################################

class Moneris_MpiHttpsPost{
 
 var $api_token;
 var $store_id;
 var $mpiRequest;
 var $mpiResponse;

 function Moneris_MpiHttpsPost($storeid,$apitoken,$mpiRequestOBJ)
 {
  
  $this->store_id=$storeid;
  $this->api_token= $apitoken; 
  $this->mpiRequest=$mpiRequestOBJ;
  $dataToSend=$this->toXML();

  $g=new Moneris_MpiGlobals();
  $gArray=$g->getGlobals();

     $gArray['MONERIS_HOST'] = 'www3.moneris.com';
     if(defined('MONERIS_TEST') && MONERIS_TEST == 1)
     {
         $gArray['MONERIS_HOST'] = 'esqa.moneris.com';
     }
     $url=$gArray['MONERIS_PROTOCOL']."://".
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

  $response=curl_exec ($ch);

  curl_close ($ch);

  if(!$response)
   {
        
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



  $this->mpiResponse=new Moneris_MpiResponse($response);
     
 }



 function getMpiResponse()
 {
  return $this->mpiResponse;

 }

 function toXML( )
 {
  $xmlString = '';
  $req=$this->mpiRequest ;
  $reqXMLString=$req->toXML();

  $xmlString .="<?xml version=\"1.0\"?>".
               "<MpiRequest>".
               "<store_id>$this->store_id</store_id>".
               "<api_token>$this->api_token</api_token>".
                $reqXMLString.
                "</MpiRequest>";
 
  return ($xmlString); 
 
 }

}//end class mpiHttpsPost

