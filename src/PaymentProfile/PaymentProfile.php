<?php
namespace ANet\PaymentProfile;

use ANet\AuthorizeNet;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetControllers;

class PaymentProfile extends AuthorizeNet
{
    public function create($opaqueData, array $source)
    {
        $merchantKeys = $this->getMerchantAuthentication();

        $opaqueDataType = new AnetAPI\OpaqueDataType();
        $opaqueDataType->setDataDescriptor($opaqueData['dataDescriptor']);
        $opaqueDataType->setDataValue($opaqueData['dataValue']);

        $paymentType = new AnetAPI\PaymentType();
        $paymentType->setOpaqueData($opaqueDataType);

        $billTo = new AnetAPI\CustomerAddressType();

        if ($source['first_name']) {
            $billTo->setFirstName($source['first_name']);
        }

        if ($source['last_name']) {
            $billTo->setLastName($source['last_name']);
        }

        if ($source['address']) {
            $billTo->setAddress($source['address']);
        }

        if ($source['city']) {
            $billTo->setCity($source['city']);
        }

        if ($source['state']) {
            $billTo->setState($source['state']);
        }

        if ($source['zip']) {
            $billTo->setZip($source['zip']);
        }



        $customerPaymentProfileType = new AnetAPI\CustomerPaymentProfileType;
        $customerPaymentProfileType->setPayment($paymentType);
        $customerPaymentProfileType->setBillTo($billTo);

        // Assemble the complete transaction request
        $paymentProfileRequest = new AnetAPI\CreateCustomerPaymentProfileRequest();
        $paymentProfileRequest->setMerchantAuthentication($merchantKeys);

        // Add an existing profile id to the request
        $paymentProfileRequest->setCustomerProfileId($this->user->anet()->getCustomerProfileId());
        $paymentProfileRequest->setPaymentProfile($customerPaymentProfileType);

        // Create the controller and get the response
        $controller = new AnetControllers\CreateCustomerPaymentProfileController($paymentProfileRequest);
        $response = $this->execute($controller);

        if(!is_null($response->getCustomerPaymentProfileId()))  {
            $this->storeInDatabase($response, $source);
        }

        return $response;
    }

    /**
     * @param $response
     * @param array $source
     * @return mixed
     */
    public function storeInDatabase($response, $source)
    {
        // DAVID_TODO: Look to see if we can use Eloquent instead of Query Builder
        // May require us to package Models inside this laravel package
        return \DB::table('user_payment_profiles')->insert([
            'user_id'               => $this->user->id,
            'payment_profile_id'    => $response->getCustomerPaymentProfileId(),
            'last_4'                => $source['last_4'],
            'brand'                 => $source['brand'],
            'type'                  => $source['type'],
            'created_at'            => \Carbon\Carbon::now(),
            'updated_at'            => \Carbon\Carbon::now()
        ]);
    }




}
