<?php

namespace Omnipay\Bkm\Message;

/**
 * BKM Express Void Request
 * 
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-bkm
 */
class VoidRequest extends PurchaseRequest {
    
    protected $actionType = 'CC.RV';
   
}
