<?php

namespace Omnipay\Bkm\Message;

use DOMDocument;
use Omnipay\Common\Message\AbstractRequest;

/**
 * BKM Express Purchase Request
 * 
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-bkm
 */
class PurchaseRequest extends AbstractRequest {

    protected $endpoints = [
        'preprod' => 'https://preprod.bkmexpress.com.tr:9620/BKMExpress/BkmExpressPaymentService.ws',
        'prod' => 'https://www.bkmexpress.com.tr:9620/BKMExpress/BkmExpressPaymentService.ws',
    ];
    protected $str;
    
    public function getData() {

        $timestamp = strftime("%Y%m%d-%H:%M:%S", time());
        
        $data = array(
            'mId' => $this->getMid(),
            'sUrl' => "http://local.instore/?success",
            'cUrl' => "http://local.instore/?fail",
            
            'sAmount' => $this->getAmountInteger(),
            'cAmount' => "", //shipping price
            'instOpst' => array(
                'bank' => array(
                    'id' => 0046,
                    'name' => 'AKBANK',
                    'expBank' => 'AKBANK',
                    'bins' => array(
                        'bins' => array(
                            'value' => '557113',
                            'insts' => array(
                                'inst' => array(
                                    'nofInst' => '1',
                                    'amountInst' => '80,99',
                                    'cAmount' => '5,00',
                                    'cAmount' => '80,99',
                                    'cPaid1stInst' => true,
                                    'expInst' => 'test'
                                ),  
                                'inst' => array(
                                    'nofInst' => '2',
                                    'amountInst' => '42,99',
                                    'cAmount' => '5,00',
                                    'cAmount' => '84,99',
                                    'cPaid1stInst' => false,
                                    'expInst' => 'test'
                                ),  
                            ),
                        ),
                    ),
                ),
            ),
            'ts' => $timestamp,
        );
 
        $signData = function ($data) use (&$signData) {
            
            foreach ($data as $value) {
                if (is_array($value)) {
                    $signData($value);
                } else {
                    $this->str .= $value;
                }
            }
           
        };
        $signData($data);
         
        $data += array(
            's' => $this->getSign(
                      $this->str
                   ),
        );
        
        return $data;
    }

    public function sendData($data) {
        
        $document = new DOMDocument('1.0', 'utf-8');
        
        $root = $document->createElement('bkm:initializePaymentWSRequest');
        
        // recursive array to xml
        $xml = function ($root, $data) use ($document, &$xml) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $subs = $document->createElement($key);
                    $root->appendChild($subs);
                    $xml($subs, $value);
                } else {
                    $root->appendChild($document->createElement($key, $value));
                }
            }
        };

        $xml($root, $data);
                
        $bkm = $document->createElement('bkm:initializePayment');
        $bkm->appendChild($root);

        $envelope = $document->appendChild(
            $document->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soapenv:Envelope')
        );
        
        $envelope->setAttribute('xmlns:bkm', 'http://www.bkmexpress.com.tr');
        $body = $envelope->appendChild($document->createElement('soapenv:Body'));
        $body->appendChild($bkm);
        
        $headers = array('Content-Type' => 'text/xml; charset=utf-8');
        
        $endpoint = $this->getTestMode() ? $this->endpoints["preprod"] : $this->endpoints["prod"];
        
        // Register the payment
        $this->httpClient->setConfig(array(
            'curl.options' => array(
                'CURLOPT_SSL_VERIFYHOST' => 2,
                'CURLOPT_SSLVERSION' => 0,
                'CURLOPT_SSL_VERIFYPEER' => 0,
                'CURLOPT_RETURNTRANSFER' => 1,
                'CURLOPT_SSL_CIPHER_LIST' => 'TLSv1'
            )
        ));
        
        $httpResponse = $this->httpClient->post($endpoint, $headers, $document->saveXML())->send();
        
        return $this->response = new Response($this, $httpResponse->getBody());

    }

    function getSign($data) {
        $priKey = file_get_contents(__DIR__ . '/bkm/bkm_client_sign_certificate_test.pem');
        $res = openssl_get_privatekey($priKey);
        openssl_sign($data, $sign, $res);
        openssl_free_key($res);
        $sign = base64_encode($sign);
        return $sign;
    }

    function getVerify($data, $sign)  {
        $pubKey = file_get_contents(__DIR__ . '/bkm/bkm.pem');
        $res = openssl_get_publickey($pubKey);
        $result = (bool)openssl_verify($data, base64_decode($sign), $res);
        openssl_free_key($res);    
        return $result;
    }

    public function getBank() {
        return $this->getParameter('bank');
    }

    public function setBank($value) {
        return $this->setParameter('bank', $value);
    }

    public function getMid() {
        return $this->getParameter('mid');
    }

    public function setMid($value) {
        return $this->setParameter('mid', $value);
    }


    public function getInstallment() {
        return $this->getParameter('installment');
    }

    public function setInstallment($value) {
        return $this->setParameter('installment', $value);
    }

}
