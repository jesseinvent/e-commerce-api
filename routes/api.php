<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductLikeController;
use App\Http\Controllers\ReviewLikeController;
use Symfony\Component\HttpFoundation\Response;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', function () {
    return response()->json(['Welcome to E-commerce API'], Response::HTTP_OK);
});

Route::group([
    'prefix' => 'auth'
], function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('signup', [AuthController::class, 'signup']);
        Route::post('logout', [AuthController::class, 'logout']);
});

Route::group([
    'prefix' => 'product'
], function() {

    Route::get('search', [ProductController::class, 'search']);
    Route::post('{product}/like', [ProductLikeController::class, 'store'])->name('product.like');
    Route::delete('{product}/like', [ProductLikeController::class, 'destroy'])->name('product.unlike');
    Route::get('{product}/review', [ReviewController::class, 'index'])->name('product.getReviews');
    Route::post('{product}/review', [ReviewController::class, 'store'])->name('product.createReviews');
    Route::get('{product}/order', [OrderController::class, 'index'])->name('product.getOrders');
    Route::post('{product}/order', [OrderController::class, 'store'])->name('product.createOrder');

});

Route::group([
    'prefix' => 'review'
], function() {

    Route::post('{review}/like', [ReviewLikeController::class, 'store'])->name('review.like');
    Route::delete('{review}/like', [ReviewLikeController::class, 'destroy'])->name('review.unlike');
    Route::post('{review}/reply', [ReplyController::class, 'store'])->name('reply.store');
    Route::delete('{review}/reply', [ReplyController::class, 'destroy'])->name('reply.destroy');

});


Route::group([
    'prefix' => 'order'
], function() {

    Route::post('{order}/accept', [OrderController::class, 'acceptOrder'])->name('order.accept');
    Route::post('{order}/processed', [OrderController::class, 'processedOrder'])->name('order.processed');
    Route::post('{order}/reject', [OrderController::class, 'rejectOrder'])->name('order.reject');
});

Route::apiResource('product', ProductController::class);
Route::apiResource('review', ReviewController::class);
Route::apiResource('reply', ReplyController::class);
Route::apiResource('order', OrderController::class);

