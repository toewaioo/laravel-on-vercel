<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Content;
use App\Models\Subscription;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function deactivateSubscription(Request $request, $id)
    {
        $subscription = Subscription::findOrFail($id);
        $subscription->update(['is_active' => false]);

        return response()->json(['message' => 'Subscription deactivated successfully']);
    }
    //
    public function createSubscriptionKey(Request $request)
    {
        $request->validate([
            'type' => 'required|in:1month,3months,lifetime',
            'max_devices' => 'required|integer|min:1|max:5' // Max 5 devices per key
        ]);

        $subscription = Subscription::create([
            'key' => Subscription::generateVerificationKey(),
            'type' => $request->type,
            'expires_at' => Subscription::calculateExpiration($request->type),
            'max_devices' => $request->max_devices,
            'devices_used' => 0,
            'is_active' => true
        ]);

        return response()->json([
            'message' => 'Subscription key created',
            'key' => $subscription->key,
            'type' => $subscription->type,
            'max_devices' => $subscription->max_devices,
            'expires_at' => $subscription->expires_at
        ]);
    }

    public function listSubscriptionKeys()
    {
        $keys = Subscription::all();

        return response()->json([
            'subscription_keys' => $keys->map(function ($key) {
                return [
                    'key' => $key->key,
                    'type' => $key->type,
                    'max_devices' => $key->max_devices,
                    'devices_used' => $key->devices_used,
                    'is_active' => $key->is_active,
                    'expires_at' => $key->expires_at
                ];
            })
        ]);
    }
    public function upgradeToVip(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:devices,device_id',
            'type' => 'required|in:1month,3months,lifetime'
        ]);

        $device = Device::where('device_id', $request->device_id)->first();

        $expiresAt = Subscription::calculateExpiration($request->type);

        $device->update([
            'is_vip' => true,
            'vip_expires_at' => $expiresAt
        ]);

        return response()->json([
            'message' => 'User upgraded to VIP',
            'type' => $request->type,
            'expires_at' => $expiresAt
        ]);
    }
    public function createContent(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'profileImg' => 'nullable|url',
            'coverImg' => 'nullable|url',
            'links' => 'nullable|array',
            'content' => 'required|string',
            'tags' => 'nullable|array',
            'isvip' => 'required|boolean',
            'files' => 'nullable|array',
            'files.trailer' => 'nullable|url',
            'files.stream' => 'nullable|url',
            'files.download' => 'nullable|url'
        ]);

        $content = Content::create($request->all());

        return response()->json($content, 201);
    }

    public function updateContent(Request $request, $id)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'profileImg' => 'nullable|url',
            'coverImg' => 'nullable|url',
            'links' => 'nullable|array',
            'content' => 'sometimes|string',
            'tags' => 'nullable|array',
            'isvip' => 'sometimes|boolean'
        ]);

        $content = Content::findOrFail($id);
        $content->update($request->all());

        return response()->json($content);
    }
}
