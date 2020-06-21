<?php

namespace Digikraaft\PaystackSubscription\Http\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VerifyWebhookSignature
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function handle($request, Closure $next)
    {
        // validate that callback is coming from Paystack
        if ((! $request->isMethod('post')) || ! $request->header('HTTP_X_PAYSTACK_SIGNATURE', null)) {
            throw new AccessDeniedHttpException("Invalid Request");
        }

        $input = $request->getContent();
        $paystack_key = config('paystacksubscription.secret', env('PAYSTACK_SECRET'));
        if ($request->header('HTTP_X_PAYSTACK_SIGNATURE') !== hash_hmac('sha512', $input, $paystack_key)) {
            throw new AccessDeniedHttpException("Access Denied");
        }

        return $next($request);
    }
}
