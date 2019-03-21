<?php


##################### mpgAvsInfo #######################################################

class Moneris_mpgAvsInfo
{

    var $params;
    var $avsTemplate = array('avs_street_number','avs_street_name','avs_zipcode','avs_email','avs_hostname','avs_browser','avs_shiptocountry','avs_shipmethod','avs_merchprodsku','avs_custip','avs_custphone');

    function Moneris_mpgAvsInfo($params)
    {
        $this->params = $params;
    }

    function toXML()
    {
        $xmlString = "";

        foreach($this->avsTemplate as $tag)
        {
        	//will only add to the XML the tags from the template that were also passed in by the merchant
			if(array_key_exists($tag, $this->params))
			{
				$xmlString .= "<$tag>". $this->params[$tag] ."</$tag>";
			}
        }

        return "<avs_info>$xmlString</avs_info>";
    }

}//end class
