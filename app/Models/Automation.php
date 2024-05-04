<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Automation extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'schedule', 'brand_id', 'format_id', 'user_id', 'amount'];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function format()
    {
        return $this->belongsTo(Format::class);
    }
}
