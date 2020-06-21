<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
         * Add columns to user table
         */
        Schema::table(config('paystacksubscription.user_table', 'users'), function (Blueprint $table) {
            $table->string('paystack_id')->nullable()->index();
            $table->string('paystack_authorization')->nullable();
            $table->string('paystack_email_token')->nullable();
        });


        /*
         * Create subscriptions table
         */
        Schema::create('dk_subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('paystack_id')->unique();
            $table->string('paystack_status');
            $table->string('paystack_plan')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('email_token')->nullable();
            $table->string('authorization')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'paystack_status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('paystacksubscription.user_table', 'users'));
        Schema::dropIfExists(config('paystacksubscription.subscription_table', 'dk_subscriptions'));
    }
}
