<?php

namespace App\Models;

use App\Models\Brand;
use App\Models\FormatField;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Format extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
    ];

    public function brandFormats(): HasMany
    {
        return $this->hasMany(BrandFormat::class);
    }

    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'brand_formats');
    }

    public function formatFields()
    {
        return $this->hasMany(FormatField::class);
    }
}
