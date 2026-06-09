<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromptGeneration extends Model
{
    protected $fillable = [
        'user_id',
        'img_path',
        'generated_prompt',
        'original_filename',
        'image_size',
        'mime_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
