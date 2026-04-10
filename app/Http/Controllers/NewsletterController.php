<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use App\Services\BrevoMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    protected $brevoMail;

    public function __construct(BrevoMailService $brevoMail)
    {
        $this->brevoMail = $brevoMail;
    }

    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:subscribers,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first('email')
            ], 422);
        }

        $subscriber = Subscriber::create([
            'email' => $request->email,
            'subscribed_at' => now(),
            'is_active' => true
        ]);

        // Send welcome email using Brevo API
        try {
            $htmlContent = view('emails.newsletter-welcome', ['subscriber' => $subscriber])->render();

            $this->brevoMail->send(
                $request->email,
                'Valued Subscriber',
                'Welcome to MMRatePro Daily Rate Alerts!',
                $htmlContent
            );

            Log::info('Welcome email sent to: ' . $request->email);
        } catch (\Exception $e) {
            Log::error('Welcome email failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully subscribed! Check your email for confirmation.'
        ]);
    }

    public function unsubscribe($email)
    {
        $subscriber = Subscriber::where('email', $email)->first();

        if (!$subscriber) {
            return redirect()->to('/')
                ->with('error', 'Email not found in our system.');
        }

        if ($subscriber->is_active == false) {
            return redirect()->to('/')
                ->with('info', 'You were already unsubscribed from our newsletter.');
        }

        // Update subscriber
        $subscriber->update(['is_active' => false]);

        // Send unsubscribe confirmation email using Brevo API
        try {
            $htmlContent = view('emails.newsletter-unsubscribe', ['subscriber' => $subscriber])->render();

            $this->brevoMail->send(
                $email,
                'Valued Subscriber',
                'You have been unsubscribed from MMRatePro',
                $htmlContent
            );

            Log::info('Unsubscribe confirmation sent to: ' . $email);
        } catch (\Exception $e) {
            Log::error('Unsubscribe email failed: ' . $e->getMessage());
        }

        return redirect()->to('/')
            ->with('success', 'You have been unsubscribed from MMRatePro daily rate alerts.');
    }
}
