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

class VideosWithScript
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

        // Make the whole script uppercase:
        $script = strtoupper($script);

        $this->script = $script;


        $this->instagramDescription = $this->generateInstagramDescription($script, $brand);

        $audioFile = $this->audioGenerator->generate($script);
        // $subtitlesFile = $this->subtitlesGenerator->generate($audioFile);
        $subtitlesFile = $this->oneWordSubtitlesGenerator->generate($audioFile);

        $audioFileDuration = $this->mediaGenerator->getAudioDuration($audioFile);





        $motivationalBRollClips = [
            'app/OctansGen/Assets/Videos/Motivational/1.mp4',
            'app/OctansGen/Assets/Videos/Motivational/2.mp4',
            'app/OctansGen/Assets/Videos/Motivational/3.mp4',
            'app/OctansGen/Assets/Videos/Motivational/4.mp4',
            'app/OctansGen/Assets/Videos/Motivational/5.mp4',
            'app/OctansGen/Assets/Videos/Motivational/6.mp4',
            'app/OctansGen/Assets/Videos/Motivational/7.mp4',
            'app/OctansGen/Assets/Videos/Motivational/8.mp4',
            'app/OctansGen/Assets/Videos/Motivational/9.mp4',
            'app/OctansGen/Assets/Videos/Motivational/10.mp4',
            'app/OctansGen/Assets/Videos/Motivational/11.mp4',
            'app/OctansGen/Assets/Videos/Motivational/12.mp4',
        ];

        $clipsToUseCount = 7;

        // Shuffle the array and get the first 7 items
        $selectedClips = collect($motivationalBRollClips)->shuffle()->take($clipsToUseCount)->all();

        $eachVideoDuration = $audioFileDuration / $clipsToUseCount;

        $video = $this->mediaGenerator->createVideoFromVideos($selectedClips, array_fill(0, 7, $eachVideoDuration), storage_path('app/public'), true);

        echo "The generated video: " . $video . "\n";

        // $resizedVideo = $this->mediaGenerator->resizeVideoAspectRatio($video, storage_path('app/public'));
        $finalVideo = $this->mediaGenerator->addAudioToVideo($video, $audioFile, storage_path('app/public'));
        $finalVideo = $this->mediaGenerator->burnSubtitlesInVideo($finalVideo, $subtitlesFile, storage_path('app/public'), 10, 10, 20, 20, 'The Bold Font');


        $finalVideo = $this->mediaGenerator->addLogoToVideo($finalVideo, $logoPath, storage_path('app/public'));

        $automationId = $options['automation_id'];
        $musicList = [
            app_path('OctansGen/Assets/Music/Motivational/1.mp3'),
            app_path('OctansGen/Assets/Music/Motivational/2.mp3'),
            app_path('OctansGen/Assets/Music/Motivational/3.mp3'),
            // app_path('OctansGen/Assets/Music/Motivational/4.mp3'),
            app_path('OctansGen/Assets/Music/Motivational/5.mp3'),
        ];

        // Retrieve the current music index from the cache, default to 0 if not set
        $currentIndex = Cache::get('motivational_music_index_' . $automationId, 0);

        echo "Current music index: $currentIndex\n";

        // Add a background music
        $finalVideo = $this->mediaGenerator->addBackgroundMusic($finalVideo, $musicList[$currentIndex], storage_path('app/public'), 0.05);

        // Increment and cycle the music index
        $nextIndex = ($currentIndex + 1) % count($musicList);

        // Store the incremented index back to the cache
        Cache::put('motivational_music_index_' . $automationId, $nextIndex, now()->addDays(1)); // Cache for 1 day or suitable duration

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
