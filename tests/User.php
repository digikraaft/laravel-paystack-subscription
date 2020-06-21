<?php

namespace Digikraaft\PaystackSubscription\Tests;

use Digikraaft\PaystackSubscription\Billable;
use Illuminate\Foundation\Auth\User as Model;
use Illuminate\Notifications\Notifiable;

class User extends Model
{
    use Billable, Notifiable;

    protected $guarded = [];
}
