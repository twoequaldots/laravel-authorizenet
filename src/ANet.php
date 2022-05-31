<?php
namespace ANet;

use ANet\CustomerProfile\CustomerProfile;
use ANet\PaymentProfile\PaymentProfile;
use ANet\PaymentProfile\PaymentProfileCharge;
use ANet\PaymentProfile\PaymentProfileRefund;
use ANet\Transactions\Card;
use ANet\Transactions\Transactions;
use DB;
use Exception;
use Illuminate\Support\Collection;
use net\authorize\api\contract\v1\CreateTransactionResponse;

class ANet
{
    protected $user;
    /** @var ANetMock */
    public $mock;

    /**
     * ANet constructor.
     * @param $user
     */
    public function __construct($user)
    {
        $this->user = $user;
        $this->mock = new ANetMock();
    }

    /**
     * It will create customer profile on authorize net
     * and store payment profile id in the system.
     * @return mixed
     * @throws Exception
     */
    public function createCustomerProfile()
    {
        return (new CustomerProfile($this->user))->create();
    }

    /**
     * @return mixed
     */
    public function getCustomerProfileId()
    {
        $data = DB::table('user_gateway_profiles')
            ->where('user_id', $this->user->id)
            ->first();
        return optional($data)->profile_id;
    }

    /**
     * @param $opaqueData
     * @param array $source
     * @return mixed
     */
    public function createPaymentProfile($opaqueData, array $source)
    {
        return (new PaymentProfile($this->user))->create($opaqueData, $source);
    }

    /**
     * @return mixed
     */
    public function getPaymentProfiles()
    {
        $data = DB::table('user_payment_profiles')
            ->where('user_id', $this->user->id)
            ->get();
        return $data->map(function ($profile) {
            return $profile->payment_profile_id;
        });
    }

    /**
     * @param $cents
     * @param null $paymentProfileId
     * @param null $transactionType
     * @return
     */
    public function charge($cents, $paymentProfileId, $transactionType)
    {
        return (new PaymentProfileCharge($this->user))->charge($cents, $paymentProfileId, $transactionType);
    }

    /**
     * @param $cents
     * @param $refTransId
     * @param $paymentProfileId
     * @return mixed
     */
    public function refund($cents, $refTransId, $paymentProfileId)
    {
        return (new PaymentProfileRefund($this->user))->handle($cents, $refTransId, $paymentProfileId);
    }

    /**
     * @return Collection
     */
    public function getPaymentMethods()
    {
        return DB::table('user_payment_profiles')->where('user_id', $this->user->id)->get();
    }

    /**
     * @return Collection
     */
    public function getPaymentCardProfiles()
    {
        $paymentMethods = $this->getPaymentMethods();
        return collect($paymentMethods->where('type', 'card')->all());
    }

    /**
     * @return Collection
     */
    public function getPaymentBankProfiles()
    {
        $paymentMethods = $this->getPaymentMethods();
        return collect($paymentMethods->where('type', 'bank')->all());
    }

    /**
     * It will return transaction class instance to help with transaction related queries
     * @return Transactions
     */
    public function transactions()
    {
        return new Transactions($this->user, new CreateTransactionResponse());
    }

    /**
     * It will return instance of mock class to mock responses
     * @return ANetMock
     */
    public function mock()
    {
        return $this->mock;
    }

    public function card()
    {
        return new Card($this);
    }

    public function subs()
    {
        return $this->subscription();
    }


    public function recurring()
    {
        return $this->subscription();
    }

    public function subscription()
    {
        return new Subscription($this);
    }
}
