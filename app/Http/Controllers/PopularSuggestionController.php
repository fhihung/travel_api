<?php

namespace App\Http\Controllers;

use App\Models\PopularSuggestion;
use App\Models\Activity;
use Illuminate\Http\Request;

class PopularSuggestionController extends Controller
{
    // Lấy tất cả các popular suggestions cùng các hoạt động liên quan
    public function index()
    {
        // Lấy tất cả các popular suggestions cùng với các activities liên quan
        $popularSuggestions = PopularSuggestion::with('activities')->get();

        // Trả về kết quả dưới dạng JSON
        return response()->json($popularSuggestions);
    }

    // Tạo một popular suggestion mới
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'location' => 'required|string|max:255',
            'days' => 'required|integer',
            'people' => 'required|integer',
            'cost_estimate' => 'required|numeric',
            'description' => 'nullable|string',
            'hotels' => 'nullable|json',
            'transportation' => 'nullable|json',
            'activities' => 'nullable|array', // Các hoạt động trong chuyến đi
        ]);

        $popularSuggestion = PopularSuggestion::create($validatedData);

        // Thêm hoạt động nếu có
        if (isset($validatedData['activities'])) {
            foreach ($validatedData['activities'] as $activityData) {
                $popularSuggestion->activities()->create($activityData);
            }
        }

        return response()->json($popularSuggestion->load('activities'), 201);
    }

    // Cập nhật một popular suggestion
    public function update(Request $request, PopularSuggestion $popularSuggestion)
    {
        $validatedData = $request->validate([
            'location' => 'sometimes|string|max:255',
            'days' => 'sometimes|integer',
            'people' => 'sometimes|integer',
            'cost_estimate' => 'sometimes|numeric',
            'hotels' => 'nullable|json',
            'transportation' => 'nullable|json',
            'activities' => 'nullable|array',
        ]);

        $popularSuggestion->update($validatedData);

        // Cập nhật hoạt động nếu có
        if (isset($validatedData['activities'])) {
            $popularSuggestion->activities()->delete(); // Xóa hết các hoạt động cũ

            foreach ($validatedData['activities'] as $activityData) {
                $popularSuggestion->activities()->create($activityData);
            }
        }

        return response()->json($popularSuggestion->load('activities'));
    }

    // Xóa một popular suggestion và các hoạt động liên quan
    public function destroy(PopularSuggestion $popularSuggestion)
    {
        $popularSuggestion->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
