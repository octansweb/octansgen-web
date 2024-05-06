<?php

namespace App\Models;

use App\OctansGen\Formats\SingleQuote;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public function execute()
    {
        $files = [];
        foreach (range(1, $this->amount) as $i) {
            if (Format::find($this->format_id)->name === 'Single Quote') {
                $files[] = app(SingleQuote::class)->generate([
                    'brand_id' => $this->brand_id,
                    'format_id' => $this->format_id,
                ]);
            }
        }
        return $files;
    }
}
