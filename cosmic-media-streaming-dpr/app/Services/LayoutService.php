<?php

namespace App\Services;

use App\Enums\MediaTypeEnum;
use App\Enums\MimeEnum;
use App\Models\Layout;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LayoutService
{
    public static $increment = 0;
    
    // Cache duration in minutes
    const CACHE_DURATION = 60;

    public static function build(Layout $layout, $is_content = false)
    {
        // Cache layout builds untuk improve performance
        $cacheKey = "layout_{$layout->id}_content_{$is_content}";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION * 60, function () use ($layout, $is_content) {
            return array_merge(static::options($layout, $is_content), static::children($layout, $is_content));
        });
    }

    /**
     * Clear cache untuk specific layout
     */
    public static function clearCache(Layout $layout): void
    {
        Cache::forget("layout_{$layout->id}_content_true");
        Cache::forget("layout_{$layout->id}_content_false");        
        // Clear all display caches that might use this layout
        Cache::tags(['display'])->flush();    }

    public static function getContent($spot)
    {
        return match (get_class($spot->media->mediable)) {
            MediaTypeEnum::IMAGE->value => static::getImage($spot),
            MediaTypeEnum::VIDEO->value => static::getVideo($spot),
            MediaTypeEnum::HLS->value => static::getHls($spot),
            MediaTypeEnum::HTML->value => static::getHtml($spot),
            MediaTypeEnum::LIVE_URL->value => static::getLiveUrl($spot),
            MediaTypeEnum::SLIDER->value => static::getSlider($spot),
            default => null
        };
    }

    public static function children(Layout $layout, $is_content = false)
    {
        $children = [];
        foreach ($layout->spots as $spot) {
            $children[] = [
                'x' => $spot->x,
                'y' => $spot->y,
                'w' => $spot->w,
                'h' => $spot->h,
                // Check if $spot->media exists before accessing its properties
                'media_type' => isset($spot->media) ? MediaTypeEnum::getAsOptions()[$spot->media->mediable_type] ?? null : null,
                // Insert data to tag HTML
                'content' => $is_content ? static::getContent($spot) : static::buildContent($spot),
                // Content options only if $is_content is true
                'content_options' => $is_content ? static::getContentOptions($spot) : null,
            ];
        }

        return [
            'children' => $children,
            'running_text' => $layout->running_text_is_include ? $layout->running_text : null,
        ];
    }

    public static function options(Layout $layout, $is_content = false)
    {
        $options = [
            'disableOneColumnMode' => true,
            'float' => false,
            'animate' => false,
            'margin' => $is_content ? 0 : 0.5,
            'disableResize' => true,
            'disableDrag' => true,
            'column' => $layout->screen->column,
            'row' => $layout->screen->row,
        ];

        return $options;
    }

    public static function buildContent($spot)
    {
        static::$increment++;

        $data = '
            <div style="text-align:center;text-align:middle;position: relative;color: #000;">
                    <span style="font-size: 12px;top: 2%;left: 2%;position: absolute;font-weight: bold;">{SPOT}</span>
                    <span style="font-size: 7px;font-weight:bold;">{WIDTH_HEIGHT_RATIO} | {WIDTH_HEIGHT_PIXEL}</span>
            </div>
        ';

        $perPixel = 60;

        if (request()->has('is_1440p')) {
            $perPixel = 80;
        }

        $data = str_replace('{SPOT}', static::$increment, $data);
        $data = str_replace('{WIDTH_HEIGHT_RATIO}', sprintf('%d:%d', $spot->w, $spot->h), $data);
        $data = str_replace('{WIDTH_HEIGHT_PIXEL}', sprintf('%d x %d', $spot->w * $perPixel, $spot->h * $perPixel), $data);
        $data = trim(preg_replace('/\s\s+/', ' ', $data));

        return $data;
    }

    public static function getImage($spot)
    {
        $imageTemplateStart = '<img src="';
        $imageTemplateEnd = '" width="100%" height="100%" loading="lazy" fetchpriority="high" />';
    
        $mediaPath = $spot->media->mediable->path;
        $imageUrl = null;
    
        try {
            // Check MinIO first (primary storage)
            if (Storage::disk('minio')->exists($mediaPath)) {
                $rawUrl = Storage::disk('minio')->url($mediaPath);
                // URL encode the filename part to handle spaces and special characters
                $parts = parse_url($rawUrl);
                $pathSegments = explode('/', $parts['path']);
                $filename = array_pop($pathSegments);
                $encodedFilename = rawurlencode($filename);
                $pathSegments[] = $encodedFilename;
                $parts['path'] = implode('/', $pathSegments);
                $imageUrl = $parts['scheme'] . '://' . $parts['host'] . $parts['path'] . (isset($parts['query']) ? '?' . $parts['query'] : '');
            } elseif (Storage::disk('public')->exists($mediaPath)) {
                // Fallback to public storage for legacy files
                $pathInfo = pathinfo($mediaPath);
                $webpPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';
                
                if (!Storage::disk('public')->exists($webpPath)) {
                    $originalPath = Storage::disk('public')->path($mediaPath);
                    $extension = strtolower($pathInfo['extension']);
    
                    if ($extension === 'jpeg' || $extension === 'jpg') {
                        $image = imagecreatefromjpeg($originalPath);
                    } elseif ($extension === 'png') {
                        $image = imagecreatefrompng($originalPath);
                    }
    
                    if (isset($image)) {
                        imagewebp($image, Storage::disk('public')->path($webpPath), 80);
                        imagedestroy($image);
                    }
                }
    
                $imageUrl = Storage::disk('public')->url($webpPath);
            }
        } catch (Exception $e) {
            Log::error('Image error', [
                'error' => $e->getMessage(),
                'file' => $mediaPath
            ]);
            return '<p>Error loading image.</p>';
        }
    
        return $imageUrl
            ? $imageTemplateStart . $imageUrl . $imageTemplateEnd
            : '<p>Image not found.</p>';
    }

    public static function getVideo($spot)
    {
        // Define the video template
        // Generate video HTML with optimized settings for Android WebView
        // - autoplay: Auto-start on load
        // - preload="auto": Buffer video fully before playing
        // - playsinline: Prevent fullscreen on mobile
        // - loop: Seamless continuous playback
        $videoTemplateStart = '<video autoplay playsinline preload="auto" loop style="width:100%; height:100%; object-fit:fill;" class="video-js" data-setup="{}" id="video-normal" fetchpriority="high">
                                <source src="';
        $videoTemplateEnd = '" type="video/mp4" />
                            <p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
                        </video>';

        // Generate the path for the media
        $mediaPath = $spot->media->mediable->path;

        // Try to get the video URL from MinIO first (primary storage)
        $videoUrl = self::getVideoUrlFromMinIO($mediaPath);

        // If video URL is still not found, fallback to public storage (legacy)
        if (!$videoUrl) {
            $videoUrl = self::getVideoUrlFromStorage($mediaPath);
        }

        // Return the HTML if video URL found, otherwise an error message
        return $videoUrl ? $videoTemplateStart . $videoUrl . $videoTemplateEnd : '<p>Video not found.</p>';
    }

    public static function getVideoChunk($spot)
    {
        $videoId = 'video-normal-' . $spot->id;

        // Generate video HTML with optimized caching settings
        // Using cached version with short URL to reduce parsing overhead
        $videoTemplateStart = '<video autoplay playsinline preload="auto" loop style="width:100%; height:100%; object-fit:fill;" 
        class="video-js" data-setup=\'{"fluid": true, "preload": "auto", "techOrder": ["html5"]}\' 
        id="' . $videoId . '" fetchpriority="high">
        <source src="';
        $videoTemplateEnd = '" type="video/mp4" /><p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p></video>';

        $mediaPath = $spot->media->mediable->path;

        // Try MinIO first (primary storage)
        $videoUrl = self::getVideoUrlFromMinIO($mediaPath);

        // Fallback to public storage (legacy)
        if (!$videoUrl) {
            $videoUrl = self::getVideoUrlFromStorage($mediaPath);
        }

        if ($videoUrl) {
            $videoUrl = $videoUrl . '?chunk=true&t=' . time();
        }

        return $videoUrl ? $videoTemplateStart . $videoUrl . $videoTemplateEnd : '<p>Video not found.</p>';
    }

    private static function getVideoUrlFromStorage($mediaPath)
    {
        $storage = Storage::disk('public');
        return $storage->exists($mediaPath) ? env('URL_APP') . '/api/video/' . $mediaPath : null;
    }

    private static function getVideoUrlFromMinIO($mediaPath)
    {
        $storage = Storage::disk('minio');
        if ($storage->exists($mediaPath)) {
            $url = $storage->url($mediaPath);
            // URL encode the filename part to handle spaces and special characters
            $parts = parse_url($url);
            $pathSegments = explode('/', $parts['path']);
            $filename = array_pop($pathSegments);
            $encodedFilename = rawurlencode($filename);
            $pathSegments[] = $encodedFilename;
            $parts['path'] = implode('/', $pathSegments);
            return $parts['scheme'] . '://' . $parts['host'] . $parts['path'] . (isset($parts['query']) ? '?' . $parts['query'] : '');
        }
        return null;
    }


    public static function getHls($spot)
    {
        // Use caching if the URL doesn't change frequently
        $videoUrl = $spot->media->mediable->url;
        
        // Proxy external DPR livestream to bypass CORS
        if (str_contains($videoUrl, 'ssv1.dpr.go.id/golive/livestream/')) {
            $videoUrl = str_replace(
                'https://ssv1.dpr.go.id/golive/livestream/',
                env('URL_APP') . '/proxy/dpr-livestream/',
                $videoUrl
            );
        }
        
        $cacheKey = 'video_hls_' . md5($videoUrl);
        $cachedVideo = Cache::get($cacheKey);
        if ($cachedVideo) {
            return $cachedVideo;
        }

        // Prepare the video tag for HLS livestream with optimized settings
        // - controls: Show controls for livestream (allow user pause/play if needed)
        $videoHtml = '<video controls autoplay playsinline preload="auto" loop style="width:100%%;height:100%%;" class="video-js video" data-setup="{}" id="video-hls" fetchpriority="high"><source src="%s" type="application/x-mpegURL" /><p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p></video>';


        // Cache the result for future use
        $output = sprintf($videoHtml, $videoUrl);
        Cache::put($cacheKey, $output, 3600); // Cache for 1 hour

        return $output;
    }


    public static function getHtml($spot)
    {
        // Generate unique container ID for this spot
        $containerId = 'html-lazy-' . $spot->id;
        
        // Generate the path for the media
        $mediaPath = $spot->media->mediable->path;

        // Check MinIO first, fallback to public storage
        $rawUrl = Storage::disk('minio')->exists($mediaPath) ?
            Storage::disk('minio')->url($mediaPath) : (Storage::disk('public')->exists($mediaPath) ?
                Storage::disk('public')->url($mediaPath) :
                null);
        
        // URL encode the filename part to handle spaces and special characters
        if ($rawUrl) {
            $parts = parse_url($rawUrl);
            $pathSegments = explode('/', $parts['path']);
            $filename = array_pop($pathSegments);
            $encodedFilename = rawurlencode($filename);
            $pathSegments[] = $encodedFilename;
            $parts['path'] = implode('/', $pathSegments);
            $iframeUrl = $parts['scheme'] . '://' . $parts['host'] . $parts['path'] . (isset($parts['query']) ? '?' . $parts['query'] : '');
        } else {
            $iframeUrl = null;
        }

        // Return lazy-loaded iframe container (JavaScript in display.blade.php will inject iframe)
        if ($iframeUrl) {
            return '<div id="' . $containerId . '" class="lazy-iframe-container" style="width:100%;height:100%;" data-lazy-iframe="' . htmlspecialchars($iframeUrl, ENT_QUOTES, 'UTF-8') . '"><div style="display:flex;align-items:center;justify-content:center;height:100%;color:#666;font-size:14px;">Loading content...</div></div>';
        }
        
        return '<p>Content not available.</p>';
    }


    public static function getLiveUrl($spot)
    {
        // Extract the video ID from the YouTube URL
        $videoUrl = $spot->media->mediable->url;
        $videoId = self::extractYoutubeId($videoUrl);

        // Prepare the base URL for the iframe
        if ($videoId) {
            $embedUrl = "https://www.youtube.com/embed/$videoId?autoplay=1&mute=1&rel=0&showinfo=0&controls=0&preload=auto&loop=1&playlist=$videoId";
            $iframeHTML = '<iframe id="youtube-iframe" width="100%" height="100%" src="' . $embedUrl . '" style="border:0;padding:0;margin:0;display:block;" title="Live Stream" frameborder="0" allow="autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>';
        } else if ($videoUrl == "https://emedia.dpr.go.id/" || $videoUrl == "https://emedia.dpr.go.id") {
            $iframeHTML = '<button id="hiddenButton" onclick="myFunction()">Replace document</button>';
        } else {
            $iframeHTML = '<div style="width:100%;height:100%;"><iframe width="100%" height="100%" src="' . $videoUrl . '" style="border:0;padding:0;margin:0;display:block;" title="Live URL" frameborder="0" allow="accelerometer; autoplay;" allowfullscreen loading="lazy" fetchpriority="high"/></div>';
        }

        // Return the HTML for the embedded video without the unnecessary JS
        return "<div style='width:100%;height:100%;'>$iframeHTML</div>";
    }


    /**
     * Extracts the YouTube video ID from a given URL.
     */
    private static function extractYoutubeId($url)
    {
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/', $url, $matches);
        return $matches[1] ?? null;
    }

    public static function getSlider($spot)
    {
        $id = sprintf('slider-container-%d%d%d', $spot->id, $spot->layout_id, $spot->media_id);

        if (count($spot->media->mediable->media_slider_contents) == 0) {
            return '';
        }

        $slide = '';

        $i = 1;
        foreach ($spot->media->mediable->media_slider_contents()->orderBy('sort', 'asc')->get() as $slider) {
            $s_id = $i . $slider->id;
            // Check MinIO first, fallback to public storage
            $rawSrc = Storage::disk('minio')->exists($slider->path) ?
                Storage::disk('minio')->url($slider->path) :
                Storage::disk('public')->url($slider->path);
            
            // URL encode the filename part to handle spaces and special characters
            $parts = parse_url($rawSrc);
            $pathSegments = explode('/', $parts['path']);
            $filename = array_pop($pathSegments);
            $encodedFilename = rawurlencode($filename);
            $pathSegments[] = $encodedFilename;
            $parts['path'] = implode('/', $pathSegments);
            $src = $parts['scheme'] . '://' . $parts['host'] . $parts['path'] . (isset($parts['query']) ? '?' . $parts['query'] : '');
            
            $autoplay = $slider->duration ? $slider->duration * 1000 : 1000;

            switch ($slider->mime) {
                case MimeEnum::JPG->value:
                case MimeEnum::JPEG->value:
                case MimeEnum::PNG->value:
                    $slide .= <<<SLIDE
                        <div data-slider-type="slider-image" class="swiper-slide" lazy="true" data-swiper-autoplay="$autoplay">
                            <img src="$src" style="width:100%;height:100%;" loading="lazy" fetchpriority="high">
                        </div>
                    SLIDE;

                    break;
                case MimeEnum::HTML->value:
                    $slide .= <<<IFRAME
                        <div data-slider-type="slider-html" class="swiper-slide" lazy="true" data-swiper-autoplay="$autoplay">
                            <div style="width:100%;height:100%;"><iframe width="100%" height="100%" src="$src" style="border:0;padding:0;margin:0;" loading="lazy" fetchpriority="high"/></div>
                        </div>
                    IFRAME;
                    break;

                case MimeEnum::MP4->value:
                    $slide .= <<<VIDEO
                        <div data-slider-type="slider-video" data-video-id="slider-video-$s_id" class="swiper-slide" lazy="true" data-swiper-autoplay="$autoplay">
                            <video playsinline preload="auto" loop style="width:100%;height:100%;object-fit:fill;" class="video-js" data-setup="{}" id="slider-video-$s_id" fetchpriority="high"><source src="$src" type="video/mp4" /><p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p></video>
                        </div>
                    VIDEO;

                default:
                    break;
            }

            $i++;
        }

        $html = <<<HTML
        <div class="swiper-container" id="$id">
            <div class="swiper-wrapper">
                $slide
            </div>
        </div>
        HTML;

        return $html;
    }

    public static function getContentOptions($spot)
    {
        if ($spot->media->mediable->animation_type) {
            $id = sprintf('slider-container-%d%d%d', $spot->id, $spot->layout_id, $spot->media_id);

            return [
                'id' => $id,
                'options' => [
                    'effect' => $spot->media->mediable->animation_type,
                    'slidesPerView' => 1,
                    'spaceBetween' => 0,
                    'autoplay' => [
                        'disableOnInteraction' => false,
                    ],
                    'pagination' => [
                        'el' => '.swiper-pagination',
                        'clickable' => true,
                    ],
                    'slidesPerView' => 'auto',
                    'centeredSlides' => true,
                    'mousewheel' => true,
                    'keyboard' => true,
                ],
            ];
        }

        return null;
    }
}
