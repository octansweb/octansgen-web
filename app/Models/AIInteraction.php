<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIInteraction extends Model
{
    use HasFactory;

    protected $table = 'ai_interactions';

    protected $fillable = ['automation_id', 'prompt', 'response'];
}
