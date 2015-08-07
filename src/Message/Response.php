<?php

namespace Omnipay\Bkm\Message;

use SimpleXMLElement;
use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Common\Exception\InvalidResponseException;

/**
 * BKM Express Response
 *
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-bkm
 */
class Response extends AbstractResponse implements RedirectResponseInterface {

    /**
     * Constructor
     *
     * @param  RequestInterface         $request
     * @param  string                   $data / response data
     * @throws InvalidResponseException
     */
    public function __construct(RequestInterface $request, $data) {
        $this->request = $request;
        try {

            $this->data = new SimpleXMLElement($data);

            $this->data = $this->data->children("S", true)->Body->
                children("ns2", true)->initializePaymentResponse;
            
        } catch (\Exception $ex) {
            throw new InvalidResponseException();
        }
        
        
    }

    /**
     * Whether or not response is successful
     *
     * @return bool
     */
    public function isSuccessful() {
        
        if(isset($this->data->children()->InitializePaymentWSResponse))
        {
             $message = $this->data->
                children()->InitializePaymentWSResponse->
                children()->res->
                children()->resMsg;   
             
          return (string) $message === "Success";
      }else{
          return false;
      }
        
    }

    /**
     * Get is redirect
     *
     * @return bool
     */
    public function isRedirect() {
        return false; //todo
    }

    /**
     * Get a code describing the status of this response.
     *
     * @return string|null code
     */
    public function getCode() {
        
        $code = $this->data->
                children()->InitializePaymentWSResponse->
                children()->res->
                children()->resCode;
        
        return $code;
    }

    /**
     * Get transaction reference
     *
     * @return string
     */
    public function getTransactionReference() {

        if(isset($this->data->children()->InitializePaymentWSResponse))
        {
            $transId = $this->data->
                    children()->InitializePaymentWSResponse->
                    children()->t;

            return $transId;
      } 
        
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage() {
        if ($this->isSuccessful()) {
            $message = $this->data->
                       children("ns2", true)->initializePaymentWSResponse->
                       children()->result->resultMsg;
            
            return $message; 
        }
        return $this->getError();
    }

    /**
     * Get error
     *
     * @return string
     */
    public function getError() {
        
        $message = $this->data->
                       children("ns2", true)->initializePaymentWSResponse->
                       children()->result->resultMsg;
            
        return $message;
    }

    /**
     * Get Redirect url
     *
     * @return string
     */
    public function getRedirectUrl() {
        if ($this->isRedirect()) {
            $data = array(
                'TransId' => $this->getTransactionReference()
            );
            return $this->getRequest()->getEndpoint() . '/test/index?' . http_build_query($data);
        }
    }

    /**
     * Get Redirect method
     *
     * @return POST
     */
    public function getRedirectMethod() {
        return 'POST';
    }

    /**
     * Get Redirect url
     *
     * @return null
     */
    public function getRedirectData() {
        return null;
    }

}
