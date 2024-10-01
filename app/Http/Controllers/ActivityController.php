<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    // Lấy tất cả các hoạt động cho một gợi ý du lịch cụ thể
    public function index($popularSuggestionId)
    {
        $activities = Activity::where('popular_suggestion_id', $popularSuggestionId)->get();
        return response()->json($activities);
    }

    // Tạo mới một activity
    public function store(Request $request, $popularSuggestionId)
    {
        $validatedData = $request->validate([
            'day' => 'required|date',
            'place' => 'required|string|max:255',
            'meal' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $activity = Activity::create(array_merge($validatedData, [
            'popular_suggestion_id' => $popularSuggestionId,
        ]));

        return response()->json($activity, 201);
    }

    // Cập nhật một activity
    public function update(Request $request, Activity $activity)
    {
        $validatedData = $request->validate([
            'day' => 'sometimes|date',
            'place' => 'sometimes|string|max:255',
            'meal' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $activity->update($validatedData);

        return response()->json($activity);
    }

    // Xóa một activity
    public function destroy(Activity $activity)
    {
        $activity->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
