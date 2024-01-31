<?php
namespace ANet\PaymentProfile;

use net\authorize\api\controller as AnetControllers;
use net\authorize\api\contract\v1 as AnetAPI;
use ANet\Transactions\Transaction;
use ANet\AuthorizeNet;

class PaymentTransaction extends AuthorizeNet
{
    public function void(string $transactionId) {
        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("voidTransaction");
        $transactionRequestType->setRefTransId($transactionId);

        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($this->getMerchantAuthentication());
        $request->setRefId($this->getRefId());
        $request->setTransactionRequest($transactionRequestType);

        $controller = new AnetControllers\CreateTransactionController($request);
        return $this->execute($controller);
    }

    public function capture(string $transactionId) {
        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("priorAuthCaptureTransaction");
        $transactionRequestType->setRefTransId($transactionId);

        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($this->getMerchantAuthentication());
        $request->setRefId($this->getRefId());
        $request->setTransactionRequest($transactionRequestType);

        $controller = new AnetControllers\CreateTransactionController($request);
        return $this->execute($controller);
    }
}
