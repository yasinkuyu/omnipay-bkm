<?php

namespace Omnipay\Bkm;

use Omnipay\Common\AbstractGateway;

/**
 * BKM Express Gateway
 * 
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-bkm
 */
class Gateway extends AbstractGateway {

    public function getName() {
        return 'Bkm';
    }

    public function getDefaultParameters() {
        return array(
            'mId' => '',
            'installment' => '',
            'currency' => 'TRY',
            'testMode' => false
        );
    }

    public function authorize(array $parameters = array()) {
        return $this->createRequest('\Omnipay\Bkm\Message\AuthorizeRequest', $parameters);
    }

    public function capture(array $parameters = array()) {
        return $this->createRequest('\Omnipay\Bkm\Message\CaptureRequest', $parameters);
    }

    public function purchase(array $parameters = array()) {
        return $this->createRequest('\Omnipay\Bkm\Message\PurchaseRequest', $parameters);
    }

    public function refund(array $parameters = array()) {
        return $this->createRequest('\Omnipay\Bkm\Message\RefundRequest', $parameters);
    }

    public function void(array $parameters = array()) {
        return $this->createRequest('\Omnipay\Bkm\Message\VoidRequest', $parameters);
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
