<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    //
    public function index()
    {
        return Transaction::all();
    }

    public function show($id)
    {
        return Transaction::find($id);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'note' => 'nullable|string',
            'transaction_date' => 'required|date',
            'account_id' => 'required|integer|exists:accounts,id',
            'user_id' => 'required|integer|exists:users,id',
            'category_id' => 'required|integer|exists:categories,id'
        ]);

        $transaction = Transaction::create($validatedData);

        // Cập nhật số dư tài khoản
        $account = Account::findOrFail($request->account_id);
        if ($transaction->category->type == 1) { // Giả sử type 1 là chi tiêu
            $account->balance -= $transaction->amount;
        } else { // Ngược lại là thu nhập
            $account->balance += $transaction->amount;
        }
        $account->save();

        return response()->json($transaction, 200);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'note' => 'nullable|string',
            'transaction_date' => 'required|date',
            'account_id' => 'required|integer|exists:accounts,id',
            'user_id' => 'required|integer|exists:users,id',
            'category_id' => 'required|integer|exists:categories,id'
        ]);

        $transaction = Transaction::findOrFail($id);

        // Lưu lại số dư ban đầu của tài khoản
        $account = Account::findOrFail($transaction->account_id);
        if ($transaction->category->type == 1) { // Giả sử type 1 là chi tiêu
            $account->balance += $transaction->amount; // Hoàn lại số tiền ban đầu
        } else { // Ngược lại là thu nhập
            $account->balance -= $transaction->amount; // Hoàn lại số tiền ban đầu
        }
        $account->save();

        // Cập nhật giao dịch
        $transaction->update($validatedData);

        // Cập nhật lại số dư tài khoản dựa trên thông tin giao dịch mới
        if ($transaction->category->type == 1) { // Giả sử type 1 là chi tiêu
            $account->balance -= $transaction->amount;
        } else { // Ngược lại là thu nhập
            $account->balance += $transaction->amount;
        }
        $account->save();

        return response()->json($transaction, 200);
    }

    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);

        // Cập nhật lại số dư tài khoản trước khi xóa
        $account = Account::findOrFail($transaction->account_id);
        if ($transaction->category->type == 1) { // Giả sử type 1 là chi tiêu
            $account->balance += $transaction->amount;
        } else { // Ngược lại là thu nhập
            $account->balance -= $transaction->amount;
        }
        $account->save();

        $transaction->delete();

        return response()->json(null, 204);
    }

    public function getTransactionByUserId(Request $request)
    {
        // Lấy user_id từ query parameters
        $user_id = $request->query('user_id');

        // Truy vấn cơ sở dữ liệu để lấy giao dịch theo user_id
        $transactions = Transaction::where('user_id', $user_id)
            ->with(['account', 'category'])
            ->orderBy('transaction_date', 'desc')
            ->get();

        // Trả về danh sách giao dịch dưới dạng JSON
        return response()->json($transactions, 200);
    }

    public function getTransactionByAccountId($account_id, $user_id)
    {
        // Truy vấn cơ sở dữ liệu để lấy giao dịch theo account_id và $user_id
        $transactions = Transaction::where('account_id', $account_id)
            ->where('user_id', $user_id)
            ->orderBy('transaction_date', 'desc')
            ->get();

        // Trả về danh sách giao dịch dưới dạng JSON
        return response()->json($transactions, 200);
    }

    public function getTransactionsByDate(Request $request)
    {
        // Validate the request data
        $request->validate([
            'start_date' => 'required_without:end_date|date',
            'end_date' => 'required_without:start_date|date',
            'user_id' => 'required|integer'
        ]);

        // Retrieve data from the request
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');
        $user_id = $request->query('user_id');

        // Query the database to get transactions
        if ($start_date && $end_date) {
            // Case for date range
            $transactions = Transaction::whereBetween('transaction_date', [$start_date, $end_date])
                ->where('user_id', $user_id)->with(['account', 'category'])
                ->orderBy('transaction_date', 'desc')
                ->get();
        } elseif ($start_date) {
            // Case for single day
            $transactions = Transaction::whereDate('transaction_date', $start_date)
                ->where('user_id', $user_id)->with(['account', 'category'])
                ->orderBy('transaction_date', 'desc')
                ->get();
        } elseif ($end_date) {
            // Case for single day
            $transactions = Transaction::whereDate('transaction_date', $end_date)
                ->where('user_id', $user_id)->with(['account', 'category'])
                ->orderBy('transaction_date', 'desc')
                ->get();
        } else {
            // Handle case where no date is provided (optional)
            return response()->json(['error' => 'Date is required'], 400);
        }

        // Return the list of transactions as JSON
        return response()->json($transactions, 200);
    }
    public function getTransactionsForCurrentWeek(Request $request)
    {
        // Validate the request data
        $request->validate([
            'user_id' => 'required|integer'
        ]);

        // Retrieve data from the request
        $user_id = $request->query('user_id');

        // Get the start and end of the current week
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // Query the database to get transactions
        $transactions = Transaction::whereBetween('transaction_date', [$startOfWeek, $endOfWeek])
            ->where('user_id', $user_id)->with(['account', 'category'])
            ->orderBy('transaction_date', 'desc')
            ->get();

        // Return the list of transactions as JSON
        return response()->json($transactions, 200);
    }
    public function getTransactionsForCurrentMonth(Request $request)
    {
        // Validate the request data
        $request->validate([
            'user_id' => 'required|integer'
        ]);

        // Retrieve data from the request
        $user_id = $request->query('user_id');

        // Get the start and end of the current month
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Query the database to get transactions
        $transactions = Transaction::whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->where('user_id', $user_id)->with(['account', 'category'])
            ->orderBy('transaction_date', 'desc')
            ->get();

        // Return the list of transactions as JSON
        return response()->json($transactions, 200);
    }
    public function getTransactionsByUserId(Request $request)
    {
        $user_id = $request->query('user_id');

        $transactions = Transaction::where('user_id', $user_id)
            ->with('category') // Load the category relationship
            ->orderBy('transaction_date', 'desc')
            ->get();

        $groupedTransactions = $transactions->groupBy(function ($transaction) {
            $transactionDate = Carbon::parse($transaction->transaction_date);
            return $transactionDate->toDateString();
        });

        $result = [];
        foreach ($groupedTransactions as $date => $transactions) {
            $totalExpense = 0;
            $totalIncome = 0;

            foreach ($transactions as $transaction) {
                if ($transaction->category->type == 1) {
                    $totalExpense += $transaction->amount;
                } else {
                    $totalIncome += $transaction->amount;
                }
            }

            $result[] = [
                'date' => $date,
                'transactions' => $transactions,
                'total_expense' => $totalExpense,
                'total_income' => $totalIncome,
                'total_balance' => $totalIncome - $totalExpense,
            ];
        }

        return response()->json($result, 200);
    }

    public function getTotalIncomeAndExpenseForCurrentMonth(Request $request)
    {
        // Validate the request data
        $request->validate([
            'user_id' => 'required|integer'
        ]);

        // Retrieve data from the request
        $user_id = $request->query('user_id');

        // Get the start and end of the current month
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Query the database to get income and expenses for the current month
        $totalIncome = Transaction::where('user_id', $user_id)
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->whereHas('category', function ($query) {
                $query->where('type', 0); // Assuming 0 indicates income
            })
            ->sum('amount');

        $totalExpense = Transaction::where('user_id', $user_id)
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->whereHas('category', function ($query) {
                $query->where('type', 1); // Assuming 1 indicates expense
            })
            ->sum('amount');

        // Return the total income and expense as JSON
        return response()->json([
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
        ], 200);
    }
    public function getIncomeByWeekOfCurrentMonth(Request $request)
    {
        // Validate the request data
        $request->validate([
            'user_id' => 'required|integer'
        ]);

        // Retrieve data from the request
        $user_id = $request->query('user_id');

        // Get the start and end of the current month
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Get the start and end of each week in the current month
        $weeks = [];
        for ($weekStart = $startOfMonth->copy(); $weekStart->lte($endOfMonth); $weekStart->addWeek()) {
            $weekEnd = $weekStart->copy()->endOfWeek();
            if ($weekEnd->gt($endOfMonth)) {
                $weekEnd = $endOfMonth;
            }
            $weeks[] = ['start' => $weekStart->format('Y-m-d'), 'end' => $weekEnd->format('Y-m-d')];
        }

        $result = [];
        foreach ($weeks as $week) {
            $totalIncome = Transaction::where('user_id', $user_id)
                ->whereBetween('transaction_date', [$week['start'], $week['end']])
                ->whereHas('category', function ($query) {
                    $query->where('type', 0); // Assuming type 0 is for income, 1 for expense
                })
                ->sum('amount');

            $result[] = [
                'week_start' => $week['start'],
                'week_end' => $week['end'],
                'total_income' => number_format($totalIncome, 2, '.', ''),
            ];
        }

        return response()->json($result, 200);
    }

    public function getExpenseByWeekOfCurrentMonth(Request $request)
    {
        // Validate the request data
        $request->validate([
            'user_id' => 'required|integer'
        ]);

        // Retrieve data from the request
        $user_id = $request->query('user_id');

        // Get the start and end of the current month
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Get the start and end of each week in the current month
        $weeks = [];
        for ($weekStart = $startOfMonth->copy(); $weekStart->lte($endOfMonth); $weekStart->addWeek()) {
            $weekEnd = $weekStart->copy()->endOfWeek();
            if ($weekEnd->gt($endOfMonth)) {
                $weekEnd = $endOfMonth;
            }
            $weeks[] = ['start' => $weekStart->format('Y-m-d'), 'end' => $weekEnd->format('Y-m-d')];
        }

        $result = [];
        foreach ($weeks as $week) {
            $totalIncome = Transaction::where('user_id', $user_id)
                ->whereBetween('transaction_date', [$week['start'], $week['end']])
                ->whereHas('category', function ($query) {
                    $query->where('type', 1); // Assuming type 0 is for income, 1 for expense
                })
                ->sum('amount');

            $result[] = [
                'week_start' => $week['start'],
                'week_end' => $week['end'],
                'total_expense' => number_format($totalIncome, 2, '.', ''),
            ];
        }

        return response()->json($result, 200);
    }


}
