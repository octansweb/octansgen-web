<?php

namespace App\OctansGen\Generators;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Media
{

    /**
     * Crop an image using ffmpeg and automatically generate a unique file name in a specified directory.
     *
     * @param string $inputFile The input image file path.
     * @param string $outputDir The directory to save the cropped image.
     * @return string|void Returns the generated output file path or prints an error.
     */
    public static function cropImage($inputFile, $outputDir)
    {
        // Generate a unique filename
        $outputFile = $outputDir . '/' . uniqid('cropped_', true) . '.jpeg';

        $command = [
            'ffmpeg',
            '-i', $inputFile,
            '-vf', 'crop=1080:1920:(iw-1080)/2:(ih-1920)/2',
            '-frames:v', '1',
            $outputFile
        ];

        return self::runProcess($command, $outputFile);
    }

    /**
     * Run the ffmpeg process command.
     *
     * @param array $command The ffmpeg command to run.
     * @param string $outputFile The output file path.
     * @return string|void Returns the output file path or prints an error.
     */
    private static function runProcess($command, $outputFile)
    {
        $process = new Process($command);
        $process->setTimeout(3600);  // Set a timeout (in seconds)

        try {
            $process->mustRun();
            return $outputFile;  // Return the output file path on success
        } catch (ProcessFailedException $exception) {
            echo "An error occurred: " . $exception->getMessage();  // Print errors directly
        }
    }

    /**
     * Create a video from an image with specified duration and resolution, saving to a specified directory with a unique filename.
     *
     * @param string $imagePath The path to the input image file.
     * @param int $duration The duration of the video in seconds.
     * @param string $outputDir The directory to save the output video file.
     * @return string|void Returns the generated output video file path or prints an error.
     */
    public static function createVideoFromImage($imagePath, $duration, $outputDir)
    {
        // Generate a unique filename
        $outputVideoPath = $outputDir . '/' . uniqid('video_', true) . '.mp4';

        // Calculate padding to center the image
        $command = [
            'ffmpeg',
            '-loop', '1',
            '-i', $imagePath,
            '-vf', 'scale=1080:1920:force_original_aspect_ratio=decrease,pad=1080:1920:(ow-iw)/2:(oh-ih)/2',
            '-t', $duration,
            '-r', '30',  // Frame rate
            '-pix_fmt', 'yuv420p',
            $outputVideoPath
        ];

        return self::runProcess($command, $outputVideoPath);
    }


    public static function addTextToVideo($inputVideoPath, $text, $outputDir)
    {
        // Generate a unique filename for the output video
        $outputVideoPath = $outputDir . '/' . uniqid('text_video_', true) . '.mp4';
    
        // Call function to wrap text
        $wrappedText = self::wrapText($text, 40); // Assuming 30 characters per line as an example
    
        // Properly escape the text for shell execution and FFmpeg
        $escapedText = escapeshellarg($wrappedText);

        $fontSize = 40;
    
        $command = [
            'ffmpeg',
            '-i', $inputVideoPath,
            // '-vf', "drawtext=text={$escapedText}:fontcolor=white:fontsize={$fontSize}:x=(w-text_w)/2:y=(h-text_h)/2:box=1:boxcolor=black@0.5:boxborderw=5",
            '-vf', "drawtext=text={$escapedText}:line_spacing=30:fontfile='/Users/ranafaizahmad/Desktop/ffmpeg-videos/Rubik/static/Rubik-Regular.ttf':fontcolor=white:bordercolor=black:borderw=2:fontsize={$fontSize}:x=(w-text_w)/2:y=(h-text_h)/2",
            '-c:v', 'libx264',
            '-c:a', 'copy',
            '-preset', 'fast',
            '-movflags', '+faststart',
            '-r', '30',
            $outputVideoPath
        ];
    
        return self::runProcess($command, $outputVideoPath);
    }
    
    private static function wrapText($text, $maxLineLength) {
        $words = explode(' ', $text);
        $wrappedText = '';
        $currentLineLength = 0;
    
        foreach ($words as $word) {
            if ($currentLineLength + strlen($word) + 1 > $maxLineLength) {
                $wrappedText .= "\n"; // Correct escaping for new lines, note double backslashes
                $currentLineLength = 0; // Reset line length
            }
            $wrappedText .= ($currentLineLength > 0 ? ' ' : '') . $word;
            $currentLineLength += strlen($word) + 1; // Add one for the space
        }
    
        return $wrappedText;
    }    
    
}
