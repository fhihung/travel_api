<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PopularSuggestionController;
use App\Http\Controllers\SuggestionController;
use App\Http\Controllers\TourSuggestionController;
use App\Http\Controllers\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['middleware' => ['jwt.auth']], function () {
    Route::get('user', [App\Http\Controllers\AuthController::class, 'getAuthenticatedUser']);
    Route::post('logout', [App\Http\Controllers\AuthController::class, 'logout']);
    Route::delete('user/delete', [App\Http\Controllers\AuthController::class, 'deleteAccount']); // Add this route for account deletion
    Route::post('refresh-token', [App\Http\Controllers\AuthController::class, 'refreshToken']);
});

Route::post('register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('login', [App\Http\Controllers\AuthController::class, 'login']);
Route::get('user', [App\Http\Controllers\AuthController::class, 'getAuthenticatedUser']);

// Route lấy accounts từ database
Route::post('/tour-suggestion', [TourSuggestionController::class, 'getSuggestion']);

Route::apiResource('suggestions', SuggestionController::class);
Route::get('suggestions/user/{user_id}', [SuggestionController::class, 'getSuggestionByUserId']);

// Route tạo activity cho một popular suggestion cụ thể
Route::post('popular-suggestions/{popular_suggestion_id}/activities', [ActivityController::class, 'store']);

// Route lấy các activities cho một popular suggestion cụ thể
//Route::get('popular-suggestions/{popular_suggestion_id}/activities', [ActivityController::class, 'index']);

// Route tạo suggestion mới (PopularSuggestion)
Route::post('popular-suggestions', [PopularSuggestionController::class, 'store']);
Route::get('popular-suggestions', [PopularSuggestionController::class, 'index']);
