<?php

namespace Digikraaft\PaystackSubscription\Commands;

use Illuminate\Console\Command;

class PaystackSubscriptionCommand extends Command
{
    public $signature = 'paystack.subscription';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
