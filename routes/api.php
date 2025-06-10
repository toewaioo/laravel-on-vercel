<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Middleware\ApiTokenAuth;
use App\Http\Middleware\VipAccess;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\SubscriptionController;
/*--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------*/
Route::post('/register', [AuthController::class, 'register']);
Route::get('/upgrade-info', [ContentController::class, 'upgradeInfo']);

/*--------------------------------------------------------------------------
| Authenticated Routes (API Token Required)
|--------------------------------------------------------------------------*/
Route::middleware([ApiTokenAuth::class])->group(function () {
    // Content Access
    Route::get('/contents', [ContentController::class, 'listContents']);
    Route::get('/normal-contents', [ContentController::class, 'normalContents']);
    Route::get('/contents/{id}', [ContentController::class, 'getContentDetails']);
    
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