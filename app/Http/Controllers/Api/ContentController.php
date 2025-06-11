<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Content;

class ContentController extends Controller
{
    public function getHomeContents(Request $request)
    {
        $perPage = (int) $request->query('per_page', 6);
        $page = (int) $request->query('page', 1);

        $categories = ['jav' => 'Jav', 'thai' => 'Thai', 'chinese' => 'Chinese', 'mm_sub' => 'MMsub', 'no_sub' => 'NoSub'];

        $selectColumns = ['id', 'title', 'profileImg', 'content', 'tags', 'isvip', 'created_at'];
        $results = [];
        $pagination = [];

        foreach ($categories as $key => $category) {
            $content = Content::where('category', $category)
                ->select($selectColumns)
                ->paginate($perPage, ['*'], 'page', $page);

            $results[$key] = $content->items();
            $pagination['total_' . $key] = $content->total();
        }

        $vipContents = Content::where('isvip', true)
            ->select($selectColumns)
            ->paginate($perPage, ['*'], 'page', $page);

        $results['vip_contents'] = $vipContents->items();

        return response()->json(array_merge($results, ['pagination' => $pagination]));
    }

    public function getContentsByCategory(Request $request, $category)
    {
        // Get pagination parameters (default: page=1, per_page=15)
        $perPage = $request->query('per_page', 15);
        $page = $request->query('page', 1);

        // Search for contents with the category
        $contents = Content::where('category', $category)
            ->select('id', 'title', 'profileImg', 'coverImg', 'tags', 'isvip', 'created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'category' => $category,
            'contents' => $contents->items(),
            'pagination' => [
                'total' => $contents->total(),
                'per_page' => $contents->perPage(),
                'current_page' => $contents->currentPage(),
                'last_page' => $contents->lastPage(),
                'from' => $contents->firstItem(),
                'to' => $contents->lastItem()
            ]
        ]);
    }
    public function getContentsByTag(Request $request, $tag)
    {
        // Get pagination parameters (default: page=1, per_page=15)
        $perPage = $request->query('per_page', 15);
        $page = $request->query('page', 1);

        // Search for contents with the tag
        $contents = Content::whereJsonContains('tags', $tag)
            ->select('id', 'title', 'profileImg', 'coverImg', 'tags', 'isvip', 'created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'tag' => $tag,
            'contents' => $contents->items(),
            'pagination' => [
                'total' => $contents->total(),
                'per_page' => $contents->perPage(),
                'current_page' => $contents->currentPage(),
                'last_page' => $contents->lastPage(),
                'from' => $contents->firstItem(),
                'to' => $contents->lastItem()
            ]
        ]);
    }
    //
    public function listContents(Request $request)
    {
        // Show all contents to both normal and VIP users
        $contents = Content::select('id', 'title', 'profileImg', 'coverImg', 'tags', 'content', 'isvip', 'created_at')
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
        // $response = [
        //     'content' => $content->makeHidden(['created_at', 'updated_at']),
        //     'subscription_status' => $this->getSubscriptionStatus($device)
        // ];

        // // Hide files for non-VIP if content is VIP
        // if ($content->isvip && !$device->isVip()) {
        //     $response['content'] = $content->makeHidden(['files', 'created_at', 'updated_at']);
        // }

        return response()->json($content);
    }

    public function normalContents(Request $request)
    {
        // Show only non-VIP content to all users
        $contents = Content::where('isvip', false)
            ->select('id', 'title', 'profileImg', 'coverImg', 'content', 'tags', 'isvip', 'created_at')
            ->get();

        return response()->json($contents);
    }

    public function vipContents(Request $request)
    {
        // Only VIP users can access this route (enforced by middleware)
        $contents = Content::where('isvip', true)
            ->select('id', 'title', 'profileImg', 'content', 'coverImg', 'tags', 'isvip', 'created_at')
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
