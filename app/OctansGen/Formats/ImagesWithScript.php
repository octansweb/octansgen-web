<?php


namespace App\OctansGen\Formats;

use App\OctansGen\Generators\Image;
use App\OctansGen\Generators\Media;
use App\OctansGen\Generators\Script;


class ImagesWithScript
{
    protected $mediaGenerator;
    protected $scriptGenerator;
    protected $imageGenerator;

    public function __construct(Media $mediaGenerator, Script $scriptGenerator, Image $imageGenerator)
    {
        $this->mediaGenerator = $mediaGenerator;
        $this->scriptGenerator = $scriptGenerator;
        $this->imageGenerator = $imageGenerator;
    }

    public function generate($options = [])
    {
        // $options should have script prompt, image prompt, brand_id, format_id


        $prompt = "Please generate a script in the genre of jungian psychology.";
        $prompt .= "Here are a few example outputs:";
        $prompt .= "Carl Jung once said, 'The shadow is a moral problem that challenges the whole ego-personality.' Did you know Jung often depicted his own shadow in his paintings, revealing deeper insights into his psyche? Today, let's paint our own shadows—not on canvas, but by acknowledging the parts of us we usually hide. Confronting our shadows isn't just revealing; it's a step towards wholeness. Let's explore this together.\n";
        $prompt .= "Archetypes are the living system of reactions and aptitudes that determine the individual's life in invisible ways.' Jung's discovery of archetypes was partly inspired by his vivid dreams, which he meticulously recorded. These weren't just dreams; they were windows into universal truths. Today, we delve into how these patterns influence our personal narratives. Join me in uncovering the archetypes active in your life.\n";
        $prompt .= "In discussing the anima and animus, Jung stated, 'The syzygy: anima and animus, is a couple of opposites, hence a symbol of wholeness.' Jung once experienced a transformative dream where he conversed with a figure representing his anima, profoundly impacting his theories. Let's explore how these inner figures influence us and how engaging with them can lead to greater internal balance and understanding.\n";
        $prompt .= "Jung coined the term synchronicity after a patient’s dream about a golden scarab; the next day, during their session, a real scarab beetle appeared at his window. He described synchronicity as 'acausal connecting principles.' Let’s look for the synchronicities in our lives. Are they mere coincidences, or could they be something more? Share your 'golden scarab' moments with us!\n";
        $prompt .= "Jung famously said, 'The privilege of a lifetime is to become who you truly are.' The idea came to him during a period of self-imposed isolation, which he used to dive deep into his own psyche. This process of individuation isn't easy, but it's essential. Join me as we explore practical steps to pursue your true self, inspired by Jung's own journey of transformation.\n";

        $script = $this->scriptGenerator->generate($prompt);


        $imagePrompt = "Create an image that symbolizes the journey of individuation, depicting the integration of the conscious and unconscious aspects of the psyche. Let the image reflect the idea of uncovering one's true self amidst the complexities of the human mind, drawing inspiration from Jung's concepts of archetypes, the shadow, and the anima/animus.";

        $imagePrompt2 = "Generate an image that embodies the transformative power of the collective unconscious, capturing the interconnectedness of all beings through universal symbols and archetypes. Let the image evoke a sense of mystery and depth, inviting viewers to explore the hidden realms of the psyche and contemplate their place within the greater tapestry of existence.";

        $imagePrompt3 = "Produce an image that serves as a visual metaphor for the process of psychological integration, portraying the journey towards self-realization and wholeness. Consider incorporating symbols of growth, renewal, and inner conflict, drawing from Jung's theories of the psyche's inherent drive towards completeness and harmony.";

        $image1 = $this->imageGenerator->generate($imagePrompt);
        $image2 = $this->imageGenerator->generate($imagePrompt);
        $image3 = $this->imageGenerator->generate($imagePrompt);

        // Resize them:
        $image1 = $this->mediaGenerator->scaleAndCropImage($image1, storage_path('app/public'));
        $image2 = $this->mediaGenerator->scaleAndCropImage($image2, storage_path('app/public'));
        $image3 = $this->mediaGenerator->scaleAndCropImage($image3, storage_path('app/public'));

        // Make the video
        $video = $this->mediaGenerator->createVideoFromImages([$image1, $image2, $image3], storage_path('app/public'));

        echo "Generated video: $video\n";

        $resizedVideo = $this->mediaGenerator->resizeVideoAspectRatio($video, storage_path('app/public'));

        echo "Generated cropped video: $resizedVideo\n";

        return $resizedVideo;
    }
}
