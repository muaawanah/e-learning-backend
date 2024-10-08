<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = ['chapter_id', 'type', 'question_text', 'explanation'];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function mcq_options()
    {
        return $this->hasMany(McqOption::class);
    }

    public function user_mcq_answer()
    {
        return $this->hasOne(UserMcqAnswer::class)
            ->whereHas('user_exam', function ($query) {
                $query->where('user_id', auth('sanctum')->id());
            })
            ;
    }

}
