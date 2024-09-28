<?php

namespace App\Http\Controllers;

use App\Models\Suggestion;
use Illuminate\Http\Request;

class SuggestionController extends Controller
{
    //
    public function index()
    {
        $suggestions = Suggestion::all();
    }

    public function show($id)
    {
        return Suggestion::find($id);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'data' => 'required|array',
        ]);

        $suggestion = Suggestion::create([
            'user_id' => $validatedData['user_id'],
            'data' => json_encode($validatedData['data']), // Chuyển đổi data thành JSON
        ]);

        return response()->json($suggestion, 201);
    }


    public function update(Request $request, $id)
    {
        $account = Suggestion::findOrFail($id);
        $account->update($request->all());
        return response()->json($account, 200);
    }

    public function destroy($id)
    {
        Suggestion::findOrFail($id)->delete();
        return response()->json(null, 204);
    }

    public function getSuggestionByUserId(Request $request)
    {
        $user_id = $request->query('user_id');

        $suggestions = Suggestion::where('user_id', $user_id)->get();

        return response()->json($suggestions, 200);
    }
}
