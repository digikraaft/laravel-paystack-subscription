<?php


    namespace Digikraaft\PaystackSubscription;

    use Carbon\Carbon;
    use Digikraaft\Paystack\Paystack;
    use Digikraaft\Paystack\Subscription as PaystackSubscription;
    use Digikraaft\PaystackSubscription\PaystackSubscription as PaystackSub;
    use Illuminate\Database\Eloquent\Model;

    class Subscription extends Model
    {
        protected $table = "dk_subscriptions";

        protected $guarded = [];

        /**
         * The attributes that should be mutated to dates.
         *
         * @var array
         */
        protected $dates = [
            'ends_at','created_at',
            'updated_at',
        ];

        /**
         * Get the user that owns the subscription.
         *
         * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
         */
        public function user()
        {
            return $this->owner();
        }

        /**
         * Get the model related to the subscription.
         *
         * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
         */
        public function owner()
        {
            $model = config('paystacksubscription.model');

            return $this->belongsTo($model, (new $model)->getForeignKey());
        }

        /**
         * Determine if the subscription has a specific plan.
         *
         * @param  string  $plan
         * @return bool
         */
        public function hasPlan($plan)
        {
            return $this->paystack_plan === $plan;
        }

        /**
         * Determine if the subscription renews after current billing period.
         *
         * @return bool
         */
        public function renews()
        {
            $ends_at = Carbon::parse($this->endsAt());

            return $this->paystack_status === 'active' && $ends_at->isFuture();
        }

        /**
         * Determine if the subscription is valid.
         *
         * @return bool
         */
        public function valid()
        {
            return $this->active();
        }

        /**
         * Determine if the subscription is active.
         *
         * @return bool
         */
        public function active()
        {
            $ends_at = Carbon::parse($this->endsAt());

            return (
                ($this->paystack_status === PaystackSub::ACTIVE_STATUS || $this->isCancelled())
                && ($ends_at->isToday() || $ends_at->isFuture())
            );
        }

        /**
         * Determine if the subscription is past due.
         *
         * @return bool
         */
        public function pastDue()
        {
            $ends_at = Carbon::parse($this->endsAt());

            return (
                ($this->isCancelled())
                || ($ends_at->isPast())
            );
        }

        /**
         * Determine if the subscription is canceled.
         *
         * @return bool
         */
        public function isCancelled()
        {
            return $this->paystack_status === PaystackSub::CANCELLED_STATUS;
        }

        /**
         * Filter query by active.
         *
         * @param  \Illuminate\Database\Eloquent\Builder  $query
         * @return void
         */
        public function scopeActive($query)
        {
            $query->where(function ($query) {
                $query->where('paystack_status', '==', 'active');
            });
        }

        /**
         * Filter query by cancel.
         *
         * @param  \Illuminate\Database\Eloquent\Builder  $query
         * @return void
         */
        public function scopeCancelled($query)
        {
            $query->where(function ($query) {
                $query->where('paystack_status', '==', PaystackSub::CANCELLED_STATUS);
            });
        }

        /**
         * Sync the Paystack status of the subscription.
         *
         * @return void
         */
        public function syncPaystackStatus()
        {
            $subscription = $this->asPaystackSubscription();
            $this->paystack_status = $subscription->status;

            if ($subscription->next_payment_date != null) {
                $this->ends_at = $subscription->next_payment_date;
            }
            $this->save();
        }

        /**
         * Get the date when subscription ends
         * @return string
         */
        public function endsAt()
        {
            return $this->ends_at;
        }

        /**
         * Get the number of days left on current subscription
         * @return int
         */
        public function daysLeft()
        {
            $ends_at = Carbon::parse($this->endsAt());

            return $ends_at->diffInDays(Carbon::now());
        }


        /**
         * Cancel the subscription at the end of the current billing period.
         *
         * @return $this
         */
        public function cancel()
        {
            if ($this->isCancelled()) {
                return $this;
            }
            $subscription = $this->asPaystackSubscription();

            Paystack::setApiKey(config('paystacksubscription.secret', env('PAYSTACK_SECRET')));

            PaystackSubscription::disable(
                [
                    'code' => $subscription->subscription_code,
                    'token' => $subscription->email_token,
                ]
            );

            $this->syncPaystackStatus();

            return $this;
        }

        /**
         * Enable the subscription to renew at the end of the current billing period.
         *
         * @return $this
         */
        public function enable()
        {
            if (! $this->isCancelled()) {
                return $this;
            }

            $subscription = $this->asPaystackSubscription();

            Paystack::setApiKey(config('paystacksubscription.secret', env('PAYSTACK_SECRET')));

            PaystackSubscription::enable(
                [
                    'code' => $subscription->subscription_code,
                    'token' => $subscription->email_token,
                ]
            );

            $this->syncPaystackStatus();

            return $this;
        }

        /**
         * Get the subscription as a Paystack subscription object.
         *
         * @param  array  $expand
         * @return array|Object
         */
        public function asPaystackSubscription(array $expand = [])
        {
            Paystack::setApiKey(config('paystacksubscription.secret', env('PAYSTACK_SECRET')));

            return PaystackSubscription::fetch($this->paystack_id)->data;
        }
    }
