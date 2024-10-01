<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PopularSuggestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'location',
        'days',
        'people',
        'cost_estimate',
        'hotels',
        'description',
        'transportation',
    ];

    // Quan hệ một-nhiều với Activity
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}
