<?php

namespace App\Http\Middleware;

use App\Services\TwilioService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifies the X-Twilio-Signature header so only genuine Twilio webhooks are
 * accepted. Can be relaxed in local dev via TWILIO_VALIDATE_SIGNATURE=false.
 */
class VerifyTwilioSignature
{
    public function __construct(protected TwilioService $twilio) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->twilio->validateSignature($request)) {
            Log::warning('Rejected Twilio webhook with invalid signature', ['url' => $request->fullUrl()]);

            abort(403, 'Invalid Twilio signature.');
        }

        return $next($request);
    }
}
