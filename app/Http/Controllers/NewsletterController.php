<?php

namespace App\Http\Controllers;

use App\Mail\NewsletterUnsubscribe;
use App\Mail\NewsletterWelcome;
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
        Log::info('Subscribe attempt for: ' . $request->email);

        // Check if email already exists
        $existingSubscriber = Subscriber::where('email', $request->email)->first();

        // If email exists and is inactive (unsubscribed), reactivate them
        if ($existingSubscriber && $existingSubscriber->is_active == false) {
            Log::info('Re-activating existing subscriber: ' . $request->email);

            $existingSubscriber->update([
                'is_active' => true,
                'subscribed_at' => now()
            ]);

            // Send welcome back email
            try {
                $htmlContent = view('emails.newsletter-welcome', ['subscriber' => $existingSubscriber])->render();

                $this->brevoMail->send(
                    $request->email,
                    'Valued Subscriber',
                    'Welcome Back to MMRatePro Daily Rate Alerts!',
                    $htmlContent
                );
                Log::info('Welcome back email sent to: ' . $request->email);
            } catch (\Exception $e) {
                Log::error('Welcome back email failed: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Welcome back! You have been re-subscribed.'
            ]);
        }

        // If email exists and is active
        if ($existingSubscriber && $existingSubscriber->is_active == true) {
            return response()->json([
                'success' => false,
                'message' => 'This email is already subscribed to our newsletter.'
            ], 422);
        }

        // New subscriber
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

        // Send welcome email
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
        Log::info('Unsubscribe attempt for: ' . $email);

        $email = urldecode($email);
        $subscriber = Subscriber::where('email', $email)->first();

        if (!$subscriber) {
            Log::warning('Unsubscribe failed: Email not found - ' . $email);
            return redirect()->to('/')
                ->with('error', 'Email not found in our system.');
        }

        if ($subscriber->is_active == false) {
            Log::info('Unsubscribe: Already inactive - ' . $email);
            return redirect()->to('/')
                ->with('info', 'You were already unsubscribed from our newsletter.');
        }

        // Mark as inactive (soft unsubscribe)
        $subscriber->update(['is_active' => false]);
        Log::info('Unsubscribe successful: ' . $email);

        // Send unsubscribe confirmation email
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
