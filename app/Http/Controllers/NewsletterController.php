<?php

namespace App\Http\Controllers;

use App\Mail\NewsletterUnsubscribe;
use App\Mail\NewsletterWelcome;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
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

        // Send welcome email
        try {
            Mail::to($request->email)->send(new NewsletterWelcome($subscriber));
        } catch (\Exception $e) {
            Log::error('Welcome email failed: ' . $e->getMessage());
            // Don't fail the subscription - just log the error
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully subscribed! Check your email for confirmation.'
        ]);
    }

    // Optional: Unsubscribe method
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

        // Send unsubscribe confirmation email
        try {
            Mail::to($email)->send(new NewsletterUnsubscribe($subscriber));
        } catch (\Exception $e) {
            Log::error('Unsubscribe email failed: ' . $e->getMessage());
        }

        // Redirect to home page with success message
        return redirect()->to('/')
            ->with('success', 'You have been unsubscribed from MMRatePro daily rate alerts.');
    }
}
