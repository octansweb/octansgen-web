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
    public function cropImage($inputFile, $outputDir)
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


    public function scaleAndCropImage($inputFile, $outputDir)
    {
        $outputFile = $outputDir . '/' . uniqid('scaled_cropped_', true) . '.jpeg';

        $command = [
            'ffmpeg',
            '-i', $inputFile,
            '-vf', 'scale=-1:1920, crop=1080:1920',
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
    private function runProcess($command, $outputFile)
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
    public function createVideoFromImage($imagePath, $duration, $outputDir)
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


    public function addTextToVideo($inputVideoPath, $text, $outputDir)
    {
        // Generate a unique filename for the output video
        $outputVideoPath = $outputDir . '/' . uniqid('text_video_', true) . '.mp4';

        // Call function to wrap text
        $wrappedText = self::wrapText($text, 35); // Assuming 35 characters per line as an example

        // Properly escape the text for shell execution and FFmpeg
        $escapedText = escapeshellarg($wrappedText);

        $fontSize = 60;

        $command = [
            'ffmpeg',
            '-i', $inputVideoPath,
            // '-vf', "drawtext=text={$escapedText}:fontcolor=white:fontsize={$fontSize}:x=(w-text_w)/2:y=(h-text_h)/2:box=1:boxcolor=black@0.5:boxborderw=5",
            // '-vf', "drawtext=text={$escapedText}:line_spacing=30:fontfile='/Users/ranafaizahmad/Desktop/ffmpeg-videos/Rubik/static/Rubik-Regular.ttf':fontcolor=white:bordercolor=black:borderw=2:fontsize={$fontSize}:x=(w-text_w)/2:y=(h-text_h)/2",
            '-vf', "drawtext=text={$escapedText}:line_spacing=30:fontfile='/Users/ranafaizahmad/Desktop/Sedan_SC/SedanSC-Regular.ttf':fontcolor=white:borderw=6:fontsize={$fontSize}:x=(w-text_w)/2:y=(h-text_h)/2",
            '-c:v', 'libx264',
            '-c:a', 'copy',
            '-preset', 'fast',
            '-movflags', '+faststart',
            '-r', '30',
            $outputVideoPath
        ];

        return self::runProcess($command, $outputVideoPath);
    }

    private function wrapText($text, $maxLineLength)
    {
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

    public function addAudioToVideo($inputVideoPath, $audioTrackPath, $outputDir)
    {
        // Generate a unique filename for the output video
        $outputVideoPath = $outputDir . '/' . uniqid('merged_video_', true) . '.mp4';

        // Construct the FFmpeg command using the specific structure provided
        $command = [
            'ffmpeg',
            '-i', $inputVideoPath,  // Input video file
            '-i', $audioTrackPath,  // Input audio file
            '-c', 'copy',           // Copy all codecs without re-encoding
            '-map', '0:v:0',        // Map the video stream from the first input (video)
            '-map', '1:a:0',        // Map the audio stream from the second input (audio)
            '-shortest',            // Ensures the output duration matches the shortest of the video or audio streams
            $outputVideoPath
        ];

        return self::runProcess($command, $outputVideoPath);
    }

    /**
     * Apply a black overlay with adjustable opacity to an image.
     *
     * @param string $inputFile The input image file path.
     * @param float $opacity The opacity of the overlay (0.0 to 1.0).
     * @param string $outputDir The directory to save the output image.
     * @return string|void Returns the generated output file path or prints an error.
     */
    public function applyBlackOverlayWithOpacity($inputFile, $opacity, $outputDir)
    {
        // Generate a unique filename
        $outputFile = $outputDir . '/' . uniqid('overlay_', true) . '.png';

        // Construct the FFmpeg command with dynamic opacity
        $command = [
            'ffmpeg',
            '-i', $inputFile,
            '-filter_complex', "[0:v]split=2[orig][toOverlay]; [toOverlay]drawbox=c=black:t=fill:color=black@{$opacity}[overlay]; [orig][overlay]overlay=format=auto",
            '-y', $outputFile
        ];

        return self::runProcess($command, $outputFile);
    }


    /**
     * Apply noise to an image using ffmpeg and automatically generate a unique file name in a specified directory.
     *
     * @param string $inputFile The input image file path.
     * @param string $outputDir The directory to save the noised image.
     * @return string|void Returns the generated output file path or prints an error.
     */
    public function addNoiseToImage($inputFile, $outputDir)
    {
        // Generate a unique filename
        $outputFile = $outputDir . '/' . uniqid('noised_', true) . '.jpeg';

        $command = [
            'ffmpeg',
            '-i', $inputFile,
            '-vf', 'noise=c0s=45:allf=t+u',
            $outputFile
        ];

        return self::runProcess($command, $outputFile);
    }


    public function createVideoFromImages($imagePaths, $duration, $outputDir)
    {
        // Generate a unique filename
        $outputVideoPath = $outputDir . '/' . uniqid('video_', true) . '.mp4';

        // Base part of the command
        $command = [
            'ffmpeg'
        ];

        // Dynamic generation of input and filter settings based on images
        $inputParts = [];
        $filterParts = [];
        foreach ($imagePaths as $index => $path) {
            $command[] = '-loop';
            $command[] = '1';
            $command[] = '-t';
            $command[] = "{$duration}"; // duration for each image
            $command[] = '-i';
            $command[] = $path;

            $filter = sprintf(
                "[%d:v]scale=1920:1080:force_original_aspect_ratio=decrease,pad=1920:1080:(ow-iw)/2:(oh-ih)/2,setsar=1,fps=25[v%d]",
                $index,
                $index
            );
            $filterParts[] = $filter;
        }

        // Concatenation part of the command
        $concatInput = implode('', array_map(function ($index) {
            return "[v$index]";
        }, range(0, count($imagePaths) - 1)));

        $filterComplex = implode(";", $filterParts) . ';' . $concatInput . 'concat=n=' . count($imagePaths) . ':v=1:a=0';

        $command[] = '-filter_complex';
        $command[] = $filterComplex;

        $command[] = '-c:v';
        $command[] = 'libx264';
        $command[] = '-pix_fmt';
        $command[] = 'yuv420p';
        $command[] = '-movflags';
        $command[] = '+faststart';
        $command[] = $outputVideoPath;

        return self::runProcess($command, $outputVideoPath);
    }


    /**
     * Resize a video to a 1080x1920 aspect ratio.
     *
     * @param string $inputVideoPath The path to the input video file.
     * @param string $outputDir The directory to save the resized video file.
     * @return string|void Returns the path of the resized video or prints an error.
     */
    public function resizeVideoAspectRatio($inputVideoPath, $outputDir)
    {
        // Generate a unique filename for the output video
        $outputVideoPath = $outputDir . '/' . uniqid('resized_video_', true) . '.mp4';

        // Construct the FFmpeg command to resize the video
        $command = [
            'ffmpeg',
            '-i', $inputVideoPath,
            '-vf', 'scale=-2:1920,crop=1080:1920',
            '-c:v', 'libx264',
            '-crf', '20',
            '-preset', 'fast',
            '-pix_fmt', 'yuv420p',
            '-movflags', '+faststart',
            $outputVideoPath
        ];

        return self::runProcess($command, $outputVideoPath);
    }


    /**
     * Burns subtitles into a video file using an external subtitles file.
     *
     * @param string $inputVideoPath The path to the input video file.
     * @param string $subtitlesPath The path to the subtitles file.
     * @param string $outputDir The directory to save the output video.
     * @return string|void Returns the path of the video with subtitles or prints an error.
     */
    public function burnSubtitlesInVideo($inputVideoPath, $subtitlesPath, $outputDir, $alignment = 10, $fontSize = 16, $marginLeft = 20, $marginRight = 20)
    {
        // Generate a unique filename for the output video
        $outputVideoPath = $outputDir . '/' . uniqid('subtitled_video_', true) . '.mp4';
    
        // Construct the FFmpeg command to burn subtitles into the video
        $command = [
            'ffmpeg',
            '-i', $inputVideoPath,
            '-vf', "subtitles=" . escapeshellarg($subtitlesPath) .
                //    ":fontsdir=" . escapeshellarg(dirname($fontFile)) . 
                   ":force_style='Alignment=" . intval($alignment) .
                   ",FontName=The Bold Font" .
                   ",FontSize=" . intval($fontSize) .
                   ",MarginL=" . intval($marginLeft) .
                   ",MarginR=" . intval($marginRight) . "'",
            '-c:a', 'copy',  // Copy audio without re-encoding
            $outputVideoPath
        ];
    
        return self::runProcess($command, $outputVideoPath);
    }
     
    /**
     * Get the duration of an MP3 file in seconds, rounded down to the nearest whole second.
     *
     * @param string $audioFilePath The path to the MP3 file.
     * @return int Returns the duration in seconds.
     */
    public function getAudioDuration($audioFilePath)
    {
        // Construct the command using ffprobe to get the duration
        $command = [
            'ffprobe',
            '-v', 'error',
            '-show_entries', 'format=duration',
            '-of', 'default=noprint_wrappers=1:nokey=1',
            $audioFilePath
        ];

        $process = new Process($command);
        $process->setTimeout(60); // Set a reasonable timeout

        try {
            $process->mustRun();
            $output = trim($process->getOutput());
            // Round down the duration to the nearest whole number
            return $output;
        } catch (ProcessFailedException $exception) {
            echo "An error occurred: " . $exception->getMessage();
            return 0; // Return 0 or handle error appropriately
        }
    }


    /**
     * Converts subtitle files from one format to another using ffmpeg.
     *
     * @param string $inputSubtitlePath The path to the input subtitle file.
     * @param string $outputDir The directory to save the converted subtitle file.
     * @param string $inputFormat The format of the input subtitle file (e.g., 'srt').
     * @param string $outputFormat The format of the output subtitle file (e.g., 'ass').
     * @return string|void Returns the path of the converted subtitle file or prints an error.
     */
    public function convertSubtitlesToAssFormat($inputSubtitlePath, $outputDir)
    {
        // Generate a unique filename for the output subtitle file
        $outputSubtitlePath = $outputDir . '/' . uniqid('converted_', true) . '.ass';

        // Construct the ffmpeg command to convert the subtitle format
        $command = [
            'ffmpeg',
            '-i', $inputSubtitlePath,
            $outputSubtitlePath
        ];

        return self::runProcess($command, $outputSubtitlePath);
    }
}
