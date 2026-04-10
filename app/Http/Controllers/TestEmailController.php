<?php

namespace App\Http\Controllers;

use App\Services\BrevoMailService;
use Illuminate\Http\Request;

class TestEmailController extends Controller
{
    public function sendTestEmail(BrevoMailService $mailService)
    {
        $htmlContent = '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Test Email</title>
        </head>
        <body>
            <h1>🎉 Test from MMRatePro!</h1>
            <p>This email was sent via Brevo API.</p>
            <p>Your newsletter system is working!</p>
        </body>
        </html>
        ';

        $result = $mailService->send(
            'mmratepro@gmail.com',
            'MMRatePro Admin',
            'MMRatePro - API Test',
            $htmlContent
        );

        if ($result) {
            return response()->json(['success' => true, 'message' => 'Email sent!']);
        }

        return response()->json(['success' => false, 'message' => 'Email failed'], 500);
    }
}
