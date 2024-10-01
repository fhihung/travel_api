<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'popular_suggestion_id',
        'day',
        'place',
        'meal',
        'description',
    ];

    // Quan hệ nhiều-một với PopularSuggestion
    public function popularSuggestion()
    {
        return $this->belongsTo(PopularSuggestion::class);
    }
}
