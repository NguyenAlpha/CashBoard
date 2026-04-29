<?php

namespace App\Http\Controllers;

use App\Jobs\ParseEmailJob;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class InboundEmailController extends Controller
{
    /**
     * Nhận webhook từ Mailgun Inbound Email.
     * URL: POST /api/inbound-email/{token}
     * {token} là inbound_email_token của store.
     */
    public function receive(Request $request, string $token): Response
    {
        $store = Store::where('inbound_email_token', $token)
            ->where('is_active', true)
            ->first();

        if (! $store) {
            // Trả 200 để Mailgun không retry — token không hợp lệ
            return response('', 200);
        }

        if (! $this->verifyMailgunSignature($request)) {
            Log::warning('InboundEmail: invalid Mailgun signature', ['store_id' => $store->id]);

            return response('', 200);
        }

        $subject   = $request->input('subject', '');
        $bodyText  = $request->input('body-plain', $request->input('stripped-text', ''));
        $bodyHtml  = $request->input('body-html', '');
        $fromEmail = $request->input('sender', $request->input('from', ''));
        $receivedAt = now()->toIso8601String();

        if (empty($bodyText) && empty($bodyHtml)) {
            return response('', 200);
        }

        ParseEmailJob::dispatch(
            $store->id,
            $subject,
            $bodyText,
            $bodyHtml,
            $fromEmail,
            $receivedAt,
        );

        return response('', 200);
    }

    private function verifyMailgunSignature(Request $request): bool
    {
        $signingKey = config('services.mailgun.webhook_signing_key');

        // Nếu chưa cấu hình key → bỏ qua verify (dev environment)
        if (empty($signingKey)) {
            return true;
        }

        $timestamp = $request->input('timestamp', '');
        $token     = $request->input('token', '');
        $signature = $request->input('signature', '');

        if (empty($timestamp) || empty($token) || empty($signature)) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp . $token, $signingKey);

        return hash_equals($expected, $signature);
    }
}
