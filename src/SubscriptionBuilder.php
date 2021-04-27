<?php


    namespace Digikraaft\PaystackSubscription;

    use Digikraaft\Paystack\Paystack;
    use Digikraaft\Paystack\Plan;
    use Digikraaft\Paystack\Subscription as PaystackSubscription;
    use Digikraaft\Paystack\Transaction;
    use Digikraaft\PaystackSubscription\Exceptions\PaymentFailure;
    use Digikraaft\PaystackSubscription\Exceptions\SubscriptionUpdateFailure;

    class SubscriptionBuilder
    {

        /**
         * The model that is subscribing.
         *
         * @var \Illuminate\Database\Eloquent\Model
         */
        protected $owner;

        /**
         * The name of the subscription.
         *
         * @var string
         */
        protected string $name;

        /**
         * The ID or Code of the plan being subscribed to.
         *
         * @var string
         */
        protected string $plan;

        /**
         * The authorization code used to create subscription.
         *
         * @var string
         */
        protected string $authorization;

        /**
         * Create a new subscription builder instance.
         *
         * @param  mixed  $owner
         * @param  string  $name
         * @param  string  $plan
         * @return void
         */
        public function __construct($owner, $name, $plan)
        {
            $this->owner = $owner;
            $this->name = $name;
            $this->plan = $plan;
        }

        /**
         * Add a new Paystack subscription to the Paystack model.
         *
         * @param string|null $authorization
         * @param string $transactionId
         * @param array $customerOptions
         * @return \Digikraaft\PaystackSubscription\Subscription
         * @throws \Digikraaft\PaystackSubscription\Exceptions\PaymentFailure
         */
        public function add(string $transactionId = null, string $authorization = null, array $customerOptions = [])
        {
            return $this->create($transactionId, $authorization, $customerOptions);
        }

        /**
         * Create a new Paystack subscription.
         *
         * @param string|null $authorization
         * @param string|null $transactionId
         * @param array $customerOptions
         * @return \Digikraaft\PaystackSubscription\Subscription
         *
         * @throws Exceptions\PaymentFailure
         */
        public function create(string $transactionId = null, string $authorization = null, array $customerOptions = [])
        {
            $customer = $this->getPaystackCustomer($customerOptions);

            if (is_null($authorization)) {
                $paystackSubscription = $this->createSubscriptionFromTransaction($transactionId, $customer);
            } else {
                $paystackSubscription = $this->createSubscriptionFromAuthorization($authorization, $customer);
                $this->authorization = $paystackSubscription->authorization;
            }

            // save authorization to owner
            $this->owner->paystack_authorization = $paystackSubscription->authorization->authorization_code;
            $this->owner->save();


            //retrieve plan code
            $plan = Plan::fetch($paystackSubscription->plan->id);

            /** @var \Digikraaft\PaystackSubscription\Subscription $subscription */
            $subscription = Subscription::create([
                'name' => $this->name,
                'paystack_id' => $paystackSubscription->subscription_code,
                'paystack_status' => $paystackSubscription->status,
                'paystack_plan' => $plan->data->plan_code,
                'quantity' => $paystackSubscription->quantity ?? '1',
                'email_token' => $paystackSubscription->email_token,
                'authorization' => $this->authorization,
                'ends_at' => $paystackSubscription->next_payment_date,
                'user_id' => $this->owner->id,
            ]);

//        return dd("sssse3");

            return $subscription;
        }

        /**
         * Get the Paystack customer instance for the current user and payment method.
         *
         * @param  array  $options
         * @return \Digikraaft\Paystack\Customer
         */
        protected function getPaystackCustomer(array $options = [])
        {
            return $this->owner->createOrGetPaystackCustomer($options);
        }

        /**
         * Create a new Paystack subscription using authorization code.
         *
         * @param string|null $authorization
         * @param $customer
         * @return \Digikraaft\PaystackSubscription\Subscription
         */
        protected function createSubscriptionFromAuthorization(string $authorization, $customer)
        {
            $payload = array_merge(
                ['customer' => $customer->data->customer_code],
                ['authorization' => $authorization],
                ['plan' => $this->plan],
            );

            Paystack::setApiKey(config('paystacksubscription.secret', env('PAYSTACK_SECRET')));

            return PaystackSubscription::create(
                $payload,
            )->data;
        }

        /**
         * Create a new Paystack subscription using transaction reference.
         *
         * @param string $transactionId
         * @param $customer
         * @return \Digikraaft\PaystackSubscription\Subscription
         * @throws \Digikraaft\PaystackSubscription\Exceptions\PaymentFailure
         * @throws SubscriptionUpdateFailure
         */
        protected function createSubscriptionFromTransaction(string $transactionId, $customer)
        {
            //verify from Paystack that the transaction was successful
            Paystack::setApiKey(config('paystacksubscription.secret', env('PAYSTACK_SECRET')));
            $transaction = Transaction::fetch($transactionId);
            if (! $transaction->status || $transaction->data->status != 'success') {
                throw PaymentFailure::incompleteTransaction($transaction);
            }

            if ($transaction->data->plan->plan_code != $this->plan) {
                throw PaymentFailure::invalidTransactionPlan($transaction, $this->plan);
            }

            if ($transaction->data->customer->customer_code != $customer->data->customer_code) {
                throw SubscriptionUpdateFailure::invalidCustomer();
            }



            //check if user is already subscribed to plan. This is to guard against multiple subscriptions with the same transactionId
            if ($this->owner->subscribedToPlan($transaction->data->plan->plan_code)) {
                throw SubscriptionUpdateFailure::duplicateSubscription($this->owner, $transaction->data->plan->name);
            }

            $this->authorization = $transaction->data->authorization->authorization_code;

            $paystackSubscription = \Digikraaft\Paystack\Subscription::list(
                [
                    'perPage' => 1,
                    'page' => 1,
                    'customer' => $transaction->data->customer->id,
                    'plan' => $transaction->data->plan->id,
                ]
            );

            return $paystackSubscription->data->{0};
        }
    }
