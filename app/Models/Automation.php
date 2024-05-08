<?php

namespace App\Models;

use App\Models\Video;
use App\OctansGen\Formats\SingleQuote;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
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

                $generated = app(SingleQuote::class)->generate([
                    'brand_id' => $this->brand_id,
                    'format_id' => $this->format_id,
                    'automation_id' => $this->id,
                ]);

                // Assuming $generated is a full path, convert it to a relative path from public path
                $relativePath = str_replace(storage_path('app/public') . '/', '', $generated);

                // Use asset() to create the public URL
                $publicUrl = asset('storage/' . $relativePath);

                Video::create([
                    'brand_id' => $this->brand_id,
                    'format_id' => $this->format_id,
                    'user_id' => $this->user_id,
                    'file_path' => $publicUrl,
                ]);

                $files[] = $publicUrl;
            }
        }

        return $files;
    }
}
