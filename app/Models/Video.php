<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Video extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'brand_id',
        'format_id',
        'file_path',
        'metadata',
        'brand_format_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'brand_id' => 'integer',
        'format_id' => 'integer',
        'metadata' => 'array',
        'brand_format_id' => 'integer',
    ];

    public function brandFormat(): BelongsTo
    {
        return $this->belongsTo(BrandFormat::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function format(): BelongsTo
    {
        return $this->belongsTo(Format::class);
    }
}
