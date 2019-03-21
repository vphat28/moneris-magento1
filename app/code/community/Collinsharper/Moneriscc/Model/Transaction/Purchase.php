<?php

/**
 * Just like a preauth but with a different requestType and is refundable, not voidable
 */
class Collinsharper_Moneriscc_Model_Transaction_Purchase extends Collinsharper_Moneriscc_Model_Transaction_Preauth
{
    protected $_requestType     = 'purchase';
    protected $_isVoidable      = false;
    protected $_isRefundable    = true;
}
