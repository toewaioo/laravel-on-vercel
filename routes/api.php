<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Middleware\ApiTokenAuth;
use App\Http\Middleware\VipAccess;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\SubscriptionController;
use Illuminate\Support\Facades\DB;
/*--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------*/

Route::post('/register', [AuthController::class, 'register']);
Route::get('/db-con', function () {
    try {
        $dbconnect = DB::connection()->getPDO();
        //test
        $dbname = DB::connection()->getDatabaseName();
        echo "Connected successfully to the database. Database name is :" . $dbname;
    } catch (Exception $e) {
        echo "Error in connecting to the database" . $e->getMessage();
    }
});

/*--------------------------------------------------------------------------
| Authenticated Routes (API Token Required)
|--------------------------------------------------------------------------*/
Route::middleware([ApiTokenAuth::class])->group(function () {
    // Content Access
    Route::get('/contents', [ContentController::class, 'listContents']);
    Route::get('/normal-contents', [ContentController::class, 'normalContents']);
    Route::get('/contents/{id}', [ContentController::class, 'getContentDetails'])->where('id', '[0-9]+');
    Route::get('/contents/tag/{tag}', [ContentController::class, 'getContentsByTag']);
    Route::get('/contents/category/{category}', [ContentController::class, 'getContentsByCategory']);
    Route::get('/contents/search', [ContentController::class, 'searchContents']);
    Route::get('/contents/latest', [ContentController::class, 'latestContents']);
    Route::get('/contents/home', [ContentController::class, 'getHomeContents']);
    // Subscription Management
    Route::post('/verify-key', [SubscriptionController::class, 'verifyKey']);
    Route::get('/subscription-status', [SubscriptionController::class, 'subscriptionStatus']);

    /*----------------------------------------------------------------------
    | VIP-Only Routes (VIP Subscription Required)
    |----------------------------------------------------------------------*/
    Route::middleware([VipAccess::class])->group(function () {
        Route::get('/vip-contents', [ContentController::class, 'vipContents']);
        // Add other VIP-only routes here
    });
});

/*--------------------------------------------------------------------------
| Admin Routes (Should be protected with admin auth in production)
|--------------------------------------------------------------------------*/
Route::prefix('admin')->group(function () {
    // VIP Management
    Route::post('/upgrade', [AdminController::class, 'upgradeToVip']);
    Route::post('/create-key', [AdminController::class, 'createSubscriptionKey']);

    // Content Management
    Route::post('/contents', [AdminController::class, 'createContent']);
    Route::put('/contents/{id}', [AdminController::class, 'updateContent']);
    Route::delete('/contents/{id}', [AdminController::class, 'deleteContent']);

    // Subscription Management
    Route::get('/subscriptions', [AdminController::class, 'listSubscriptions']);
    Route::put('/subscriptions/{id}/deactivate', [AdminController::class, 'deactivateSubscription']);
    Route::get('/subscription-keys', [AdminController::class, 'listSubscriptionKeys']);
});

/*--------------------------------------------------------------------------
| Fallback Route
|--------------------------------------------------------------------------*/
Route::fallback(function () {
    return response()->json([
        'error' => 'Endpoint not found',
        'available_endpoints' => [
            'POST /register',
            'GET /upgrade-info',
            'GET /contents',
            'GET /normal-contents',
            'GET /contents/{id}',
            'POST /verify-key',
            'GET /subscription-status',
            'GET /vip-contents (VIP)',
            'POST /admin/upgrade',
            'POST /admin/create-key',
            'POST /admin/contents',
            'PUT /admin/contents/{id}'
        ]
    ], 404);
});
