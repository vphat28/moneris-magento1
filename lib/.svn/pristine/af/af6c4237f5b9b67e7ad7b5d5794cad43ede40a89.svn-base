<?php

#################### mpgGlobals ###########################################



##################### mpgAvsInfo #######################################################

class Monerisus_MpgAvsInfo
{

    var $params;
    var $avsTemplate = array('avs_street_number','avs_street_name','avs_zipcode');

    function Monerisus_MpgAvsInfo($params)
    {
        $this->params = $params;
    }

    function toXML()
    {
        $xmlString = "";

        foreach($this->avsTemplate as $tag)
        {
            $xmlString .= "<{$tag}>". $this->params[$tag] ."</{$tag}>";
        }

        return "<avs_info>{$xmlString}</avs_info>";
    }

}//end class
