<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscriber;
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

        return response()->json([
            'success' => true,
            'message' => 'Successfully subscribed to daily rate alerts!'
        ]);
    }
}
