<?php


namespace App\OctansGen\Formats;

use App\Models\Brand;
use App\Models\Automation;
use App\Models\BrandFormat;
use App\Models\FormatField;
use App\OctansGen\Generators\Audio;
use App\OctansGen\Generators\Image;
use App\OctansGen\Generators\Media;
use App\OctansGen\Generators\Script;
use Illuminate\Support\Facades\Cache;
use App\OctansGen\Generators\Subtitles;
use App\OctansGen\Generators\ImagePrompt;
use App\OctansGen\Generators\InstagramDescription;
use App\OctansGen\Generators\OneWordSubtitles;

class ImagesWithScript
{
    protected $videoURL;
    protected $script;
    protected $instagramDescription;

    public function __construct(
        protected Media $mediaGenerator,
        protected Script $scriptGenerator,
        protected Image $imageGenerator,
        protected Audio $audioGenerator,
        protected Subtitles $subtitlesGenerator,
        protected InstagramDescription $instagramDescriptionGenerator,
        protected ImagePrompt $imagePromptGenerator,
        protected OneWordSubtitles $oneWordSubtitlesGenerator
    ) {
        // Initialization is automatically done
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
        // $imagePrompt1Field = FormatField::whereFormatId($options['format_id'])->whereName('Image Prompt 1')->first();
        // $imagePrompt2Field = FormatField::whereFormatId($options['format_id'])->whereName('Image Prompt 2')->first();
        // $imagePrompt3Field = FormatField::whereFormatId($options['format_id'])->whereName('Image Prompt 3')->first();

        // $formatFields = FormatField::whereFormatId($options['format_id'])->get();
        $formatFieldData = json_decode($brandFormat->fields);
        $prompt = $formatFieldData->{$scriptPromptField->id};


        // $imagePrompt1 = $formatFieldData->{$imagePrompt1Field->id};
        // $imagePrompt2 = $formatFieldData->{$imagePrompt2Field->id};
        // $imagePrompt3 = $formatFieldData->{$imagePrompt3Field->id};



        $script = $this->scriptGenerator->generate($prompt, Automation::find($options['automation_id']));
        $this->script = $script;



        $length = strlen($script);
        $partLength = ceil($length / 3);

        $partialScript1 = substr($script, 0, $partLength);
        $partialScript2 = substr($script, $partLength, $partLength);
        $partialScript3 = substr($script, 2 * $partLength);

        $imagePrompt1 = $this->imagePromptGenerator->generate($partialScript1, $script);
        $imagePrompt2 = $this->imagePromptGenerator->generate($partialScript2, $script);
        $imagePrompt3 = $this->imagePromptGenerator->generate($partialScript3, $script);

        $this->instagramDescription = $this->generateInstagramDescription($script, $brand);

        $audioFile = $this->audioGenerator->generate($script);
        $subtitlesFile = $this->subtitlesGenerator->generate($audioFile);
        // $subtitlesFile = $this->oneWordSubtitlesGenerator->generate($audioFile);

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

        $this->videoURL = $finalVideo;

        return $this;
    }

    protected function generateInstagramDescription($script, $brand)
    {
        return $this->instagramDescriptionGenerator->generate($script, $brand);
    }

    public function getVideoURL()
    {
        return $this->videoURL;
    }

    public function getScript()
    {
        return $this->script;
    }

    public function getInstagramDescription()
    {
        return $this->instagramDescription;
    }
}
