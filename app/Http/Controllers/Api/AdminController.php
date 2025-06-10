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
            'device_id' => 'required|exists:devices,device_id',
            'type' => 'required|in:1month,3months,lifetime'
        ]);

        $device = Device::where('device_id', $request->device_id)->first();

        $subscription = Subscription::create([
            'device_id' => $device->id,
            'type' => $request->type,
            'expires_at' => Subscription::calculateExpiration($request->type),
            'verification_key' => Subscription::generateVerificationKey(),
            'is_active' => true
        ]);

        return response()->json([
            'message' => 'Subscription key created',
            'verification_key' => $subscription->verification_key,
            'type' => $subscription->type,
            'expires_at' => $subscription->expires_at
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
            'isvip' => 'required|boolean'
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
