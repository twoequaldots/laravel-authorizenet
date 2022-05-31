<?php
namespace ANet\PaymentProfile;

use net\authorize\api\controller as AnetControllers;
use net\authorize\api\contract\v1 as AnetAPI;
use ANet\Transactions\Transaction;
use ANet\AuthorizeNet;

class PaymentProfileCharge extends AuthorizeNet
{
    public function charge(int $cents, int $paymentProfileId, string $transactionType) {
        $amount = $this->convertCentsToDollar($cents);

        // Set the transaction's refId
        $profileToCharge = new AnetAPI\CustomerProfilePaymentType();
        $profileToCharge->setCustomerProfileId($this->user->anet()->getCustomerProfileId());

        $paymentProfile = new AnetAPI\PaymentProfileType();
        $paymentProfile->setPaymentProfileId($paymentProfileId);

        $profileToCharge->setPaymentProfile($paymentProfile);

        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType($transactionType);

        $transactionRequestType->setAmount($amount);
        $transactionRequestType->setProfile($profileToCharge);

        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($this->getMerchantAuthentication());
        $request->setRefId($this->getRefId());
        $request->setTransactionRequest( $transactionRequestType);
        $controller = new AnetControllers\CreateTransactionController($request);
        return $this->execute($controller);
    }



}
