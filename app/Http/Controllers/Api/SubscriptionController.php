<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    //
    public function verifyKey(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verification_key' => 'required|string|exists:subscriptions,verification_key'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $subscription = Subscription::where('verification_key', $request->verification_key)
            ->where('is_active', true)
            ->first();

        if (!$subscription) {
            return response()->json(['error' => 'Invalid or expired verification key'], 400);
        }

        // Update device VIP status
        $device = $subscription->device;
        $device->update([
            'is_vip' => true,
            'vip_expires_at' => $subscription->expires_at
        ]);

        // Deactivate key after use
        $subscription->update(['is_active' => false]);

        return response()->json([
            'message' => 'VIP subscription activated!',
            'type' => $subscription->type,
            'expires_at' => $subscription->expires_at,
            'is_lifetime' => $subscription->type === 'lifetime'
        ]);
    }

    public function subscriptionStatus(Request $request)
    {
        $device = $request->device;
        $subscription = $device->activeSubscription;

        return response()->json([
            'is_vip' => $device->isVip(),
            'vip_expires_at' => $device->vip_expires_at,
            'active_subscription' => $subscription ? [
                'type' => $subscription->type,
                'expires_at' => $subscription->expires_at
            ] : null
        ]);
    }
}
