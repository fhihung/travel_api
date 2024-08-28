<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    //
    public function index()
    {
        return Category::all();
    }

    public function show($id)
    {
        return Category::find($id);
    }

    public function store(Request $request)
    {
        $account = Category::create($request->all());
        return response()->json($account, 201);
    }

    public function getCategoriesByType(Request $request)
    {
        $type = $request->query('type');

        $categories = Category::where('type', $type)->get();
        return response()->json($categories);
    }
}
