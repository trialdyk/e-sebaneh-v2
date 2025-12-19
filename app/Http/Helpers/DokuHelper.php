<?php

namespace App\Helpers;

use Carbon\Carbon;

class DokuHelper
{
    /**
     * generateSignature
     *
     * @return void
     */
    public static function generateSignature($targetPath, $requestBody, $requestId, $requestDate, $clientId)
    {
        $secretKey = env('SECRET_KEY');
        $requestBody;
        $digestValue = base64_encode(hash('sha256', json_encode($requestBody), true));


        // Prepare Signature Component
        $componentSignature = "Client-Id:" . $clientId . "\n" .
            "Request-Id:" . $requestId . "\n" .
            "Request-Timestamp:" . $requestDate . "\n" .
            "Request-Target:" . $targetPath . "\n" .
            "Digest:" . $digestValue;

        $signature = base64_encode(hash_hmac('sha256', $componentSignature, $secretKey, true));

        return "HMACSHA256=" . $signature;
    }
}
