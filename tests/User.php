<?php

namespace Digikraaft\PaystackSubscription\Tests;

use Illuminate\Foundation\Auth\User as Model;
use Illuminate\Notifications\Notifiable;
use Digikraaft\PaystackSubscription\Billable;

class User extends Model
{
    use Billable, Notifiable;

    protected $guarded = [];
}
