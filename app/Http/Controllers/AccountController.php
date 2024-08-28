<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    //
    public function index()
    {
        $accounts = Account::all();
    }

    public function show($id)
    {
        return Account::find($id);
    }

    public function store(Request $request)
    {
        $account = Account::create($request->all());
        return response()->json($account, 201);
    }

    public function update(Request $request, $id)
    {
        $account = Account::findOrFail($id);
        $account->update($request->all());
        return response()->json($account, 200);
    }

    public function destroy($id)
    {
        Account::findOrFail($id)->delete();
        return response()->json(null, 204);
    }

    public function getAccountByUserId(Request $request)
    {
        // Lấy user_id từ query parameters
        $user_id = $request->query('user_id');

        // Truy vấn cơ sở dữ liệu để lấy tài khoản theo user_id
        $accounts = Account::where('user_id', $user_id)->get();

        // Trả về danh sách tài khoản dưới dạng JSON
        return response()->json($accounts, 200);
    }
    public function getTotalBalanceByUserId(Request $request)
    {
        // Lấy user_id từ query parameters
        $user_id = $request->query('user_id');

        // Truy vấn cơ sở dữ liệu để lấy tài khoản theo user_id và tính tổng số dư
        $totalBalance = Account::where('user_id', $user_id)->sum('balance');

        // Trả về tổng số dư dưới dạng JSON
        return response()->json(['total_balance' => $totalBalance], 200);
    }
}
