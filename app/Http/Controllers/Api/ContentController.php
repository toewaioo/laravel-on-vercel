<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Content;

class ContentController extends Controller
{
    //
    public function listContents(Request $request)
    {
        // Show all contents to both normal and VIP users
        $contents = Content::select('id', 'title', 'profileImg', 'coverImg', 'tags','content', 'isvip', 'created_at')
            ->get();

        return response()->json($contents);
    }

    public function getContentDetails(Request $request, $id)
    {
        $content = Content::findOrFail($id);
        
        // Check VIP access
        if ($content->isvip && !$request->device->is_vip) {
            return response()->json([
                'error' => 'VIP content requires VIP access',
                'upgrade_url' => '/api/upgrade-info'
            ], 403);
        }

        return response()->json($content);
    }

    public function normalContents(Request $request)
    {
        // Show only non-VIP content to all users
        $contents = Content::where('isvip', false)
            ->select('id', 'title', 'profileImg', 'coverImg','content', 'tags', 'isvip', 'created_at')
            ->get();

        return response()->json($contents);
    }

    public function vipContents(Request $request)
    {
        // Only VIP users can access this route (enforced by middleware)
        $contents = Content::where('isvip', true)
            ->select('id', 'title', 'profileImg','content', 'coverImg', 'tags', 'isvip', 'created_at')
            ->get();

        return response()->json($contents);
    }

    public function upgradeInfo()
    {
        return response()->json([
            'message' => 'Upgrade to VIP for full access',
            'contact' => 'support@example.com'
        ]);
    }
}
