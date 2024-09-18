<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CategoryController;
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
});

Route::post('register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('login', [App\Http\Controllers\AuthController::class, 'login']);
Route::get('user', [App\Http\Controllers\AuthController::class, 'getAuthenticatedUser']);


// Route lấy categories từ database
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories_by_type', [CategoryController::class, 'getCategoriesByType']);
Route::get('categories/{id}', [CategoryController::class, 'show']);

// Route tạo category
Route::post('create_category', [CategoryController::class, 'store']);

// Route lấy accounts từ database
Route::get('accounts_by_user_id', [AccountController::class, 'getAccountByUserId']);
Route::get('total_balance', [AccountController::class, 'getTotalBalanceByUserId']);
Route::post('create_account', [AccountController::class, 'store']);


// Route lấy transactions từ database
//Route::get('transactions_by_user_id', [TransactionController::class, 'getTransactionByUserId']);
Route::get('transactions/{user_id}/{account_id}', [TransactionController::class, 'getTransactionByAccountId']);
Route::get('transactions_by_date', [TransactionController::class, 'getTransactionsByDate']);
Route::get('transactions_for_current_week', [TransactionController::class, 'getTransactionsForCurrentWeek']);
Route::get('transactions_for_current_month', [TransactionController::class, 'getTransactionsForCurrentMonth']);
//Route::get('transactions_by_date_range', [TransactionController::class, 'getTransactionsByDateRange']);
Route::get('transactions_for_current_week', [TransactionController::class, 'getTransactionsForCurrentWeek']);
Route::get('transactions_for_current_month', [TransactionController::class, 'getTransactionsForCurrentMonth']);
Route::get('transactions_by_user_id', [TransactionController::class, 'getTransactionsByUserId']);
Route::get('total_income_and_expense_for_current_month', [TransactionController::class, 'getTotalIncomeAndExpenseForCurrentMonth']);
Route::get('income_by_week_of_current_month', [TransactionController::class, 'getIncomeByWeekOfCurrentMonth']);
Route::get('expense_by_week_of_current_month', [TransactionController::class, 'getExpenseByWeekOfCurrentMonth']);


//Route tạo transaction
Route::post('create_transaction', [TransactionController::class, 'store']);
Route::post('/tour-suggestion', [TourSuggestionController::class, 'getSuggestion']);
