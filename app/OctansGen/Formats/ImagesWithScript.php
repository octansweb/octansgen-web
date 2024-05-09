<?php


namespace App\OctansGen\Formats;

use App\Models\Brand;
use App\Models\BrandFormat;
use App\Models\FormatField;
use App\OctansGen\Generators\Audio;
use App\OctansGen\Generators\Image;
use App\OctansGen\Generators\Media;
use App\OctansGen\Generators\Script;
use Illuminate\Support\Facades\Cache;
use App\OctansGen\Generators\Subtitles;


class ImagesWithScript
{
    protected $mediaGenerator;
    protected $scriptGenerator;
    protected $imageGenerator;
    protected $audioGenerator;
    protected $subtitlesGenerator;

    public function __construct(Media $mediaGenerator, Script $scriptGenerator, Image $imageGenerator, Audio $audioGenerator, Subtitles $subtitlesGenerator)
    {
        $this->mediaGenerator = $mediaGenerator;
        $this->scriptGenerator = $scriptGenerator;
        $this->imageGenerator = $imageGenerator;
        $this->audioGenerator = $audioGenerator;
        $this->subtitlesGenerator = $subtitlesGenerator;
    }

    public function generate($options = [])
    {
        $brand = Brand::find($options['brand_id']);
        $logoPath = storage_path('app/public/' . $brand->logo);

        $brandFormat = BrandFormat::where('brand_id', $options['brand_id'])
            ->where('format_id', $options['format_id'])
            ->first();

        // Write now quote prompt exists in this format field:
        $scriptPromptField = FormatField::whereFormatId($options['format_id'])->whereName('Script Prompt')->first();
        $imagePrompt1Field = FormatField::whereFormatId($options['format_id'])->whereName('Image Prompt 1')->first();
        $imagePrompt2Field = FormatField::whereFormatId($options['format_id'])->whereName('Image Prompt 2')->first();
        $imagePrompt3Field = FormatField::whereFormatId($options['format_id'])->whereName('Image Prompt 3')->first();

        // $formatFields = FormatField::whereFormatId($options['format_id'])->get();
        $formatFieldData = json_decode($brandFormat->fields);
        $prompt = $formatFieldData->{$scriptPromptField->id};


        $imagePrompt1 = $formatFieldData->{$imagePrompt1Field->id};
        $imagePrompt2 = $formatFieldData->{$imagePrompt2Field->id};
        $imagePrompt3 = $formatFieldData->{$imagePrompt3Field->id};


        $script = $this->scriptGenerator->generate($prompt);
        $audioFile = $this->audioGenerator->generate($script);
        $subtitlesFile = $this->subtitlesGenerator->generate($audioFile);
        $audioFileDuration = $this->mediaGenerator->getAudioDuration($audioFile);

        $eachImageDuration = $audioFileDuration / 3;

        $image1 = $this->imageGenerator->generate($imagePrompt1);
        $image2 = $this->imageGenerator->generate($imagePrompt2);
        $image3 = $this->imageGenerator->generate($imagePrompt3);

        // Resize them:
        $image1 = $this->mediaGenerator->scaleAndCropImage($image1, storage_path('app/public'));
        $image2 = $this->mediaGenerator->scaleAndCropImage($image2, storage_path('app/public'));
        $image3 = $this->mediaGenerator->scaleAndCropImage($image3, storage_path('app/public'));

        $image1 = $this->mediaGenerator->applyBlackOverlayWithOpacity($image1, 0.7, storage_path('app/public'));
        $image2 = $this->mediaGenerator->applyBlackOverlayWithOpacity($image2, 0.7, storage_path('app/public'));
        $image3 = $this->mediaGenerator->applyBlackOverlayWithOpacity($image3, 0.7, storage_path('app/public'));

        // Make the video
        $video = $this->mediaGenerator->createVideoFromImages([$image1, $image2, $image3], $eachImageDuration, storage_path('app/public'));

        $resizedVideo = $this->mediaGenerator->resizeVideoAspectRatio($video, storage_path('app/public'));
        $finalVideo = $this->mediaGenerator->addAudioToVideo($resizedVideo, $audioFile, storage_path('app/public'));
        $finalVideo = $this->mediaGenerator->burnSubtitlesInVideo($finalVideo, $subtitlesFile, storage_path('app/public'));


        $finalVideo = $this->mediaGenerator->addLogoToVideo($finalVideo, $logoPath, storage_path('app/public'));

        $automationId = $options['automation_id'];
        $musicList = [
            app_path('OctansGen/Assets/Music/Calming/1.mp3'),
            app_path('OctansGen/Assets/Music/Calming/2.mp3'),
            app_path('OctansGen/Assets/Music/Calming/3.mp3'),
            app_path('OctansGen/Assets/Music/Calming/4.mp3'),
            app_path('OctansGen/Assets/Music/Calming/5.mp3'),
            app_path('OctansGen/Assets/Music/Calming/6.mp3'),
            app_path('OctansGen/Assets/Music/Calming/7.mp3'),
            app_path('OctansGen/Assets/Music/Calming/8.mp3'),
            // app_path('OctansGen/Assets/Music/Calming/9.mp3'), // too loud
            app_path('OctansGen/Assets/Music/Calming/10.mp3'),
            app_path('OctansGen/Assets/Music/Calming/11.mp3'),
        ];

        // Retrieve the current music index from the cache, default to 0 if not set
        $currentIndex = Cache::get('music_index_automation_' . $automationId, 0);

        echo "Current music index: $currentIndex\n";

        // Add a background music
        $finalVideo = $this->mediaGenerator->addBackgroundMusic($finalVideo, $musicList[$currentIndex], storage_path('app/public'));

        // Increment and cycle the music index
        $nextIndex = ($currentIndex + 1) % count($musicList);

        // Store the incremented index back to the cache
        Cache::put('music_index_automation_' . $automationId, $nextIndex, now()->addDays(1)); // Cache for 1 day or suitable duration

        echo "Generated cropped video: $finalVideo\n";

        return $finalVideo;
    }
}
