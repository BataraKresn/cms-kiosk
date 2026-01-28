<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" id="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">
    <title>Live</title>
    <link rel="stylesheet" href="/gridstack/gridstack.min.css">
    <link rel="stylesheet" href="/gridstack/gridstack-extra.min.css">
    <link rel="stylesheet" href="/vjs/video-js.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <link rel="stylesheet" href="/cms/style.css">
</head>

<body>
    <script>
        function myFunction() {
            location.replace("https://emedia.dpr.go.id");
        }

        window.onload = function() {
            const hiddenButton = document.getElementById("hiddenButton");
            if (hiddenButton) {
                hiddenButton.click();
                hiddenButton.style.display = "none"; // Hide the button
            }
        };
    </script>

    <div class="grid-stack"></div>

    <!-- TODO IF LAYOUT HAS RUNNING TEXT -->
    @if ($layout->running_text_is_include && $layout->running_text_id)
        <div class="marquee"></div>
    @endif

    <script src="/jquery/jquery.min.js"></script>
    <script src="/gridstack/gridstack-all.js"></script>
    <script src="/vjs/video.min.js"></script>
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script>
        var options = @json($options);
        var layout = @json($layout);

        var height = 1920;
        if (screen.height > 1920) {
            height = screen.height;
        }

        var width = 1080;
        if (screen.width > 1080) {
            width = screen.width;
        }

        var screenAspect = layout.screen.mode == 'portrait' ? height : width;

        // SCRIPT

        var grid = GridStack.init(options);
        var vp = document.getElementById('viewport');

        function autoScaleViewport() {
            var scale = Math.max(screen.width, screen.height) / screenAspect;
            vp.setAttribute('content', 'width=device-width, initial-scale=' + scale + ', maximum-scale=' + scale +
                ', minimum-scale=' + scale + ', user-scalable=no');
        }

        autoScaleViewport();

        window.addEventListener('resize', function(event) {
            autoScaleViewport();
        });

        if (layout.running_text_is_include && layout.running_text_id) {
            $('.marquee').text(layout.running_text.description);

            let duplicated = layout.running_text.description.length > 40;

            $('.marquee').marquee({
                'direction': layout.running_text.direction,
                'duplicated': duplicated,
                'duration': layout.running_text.speed * 1000,
                'gap': '20px'
            });

            $('.marquee').css({
                'backgroundColor': layout.running_text.background_color,
                'color': layout.running_text.text_color,
                'fontSize': '45px',
            });

            if (screenAspect > 1920) {
                $('.marquee').css({
                    'fontSize': '69.5px',
                    'height': '80px',
                    'lineHeight': '80px'
                });
            } else {
                $('.marquee').css({
                    'fontSize': '45px',
                });
            }
        }

        let sw = {};
        for (const content of options.children) {
            if (!content.content_options || !content.content_options.id) {
                console.warn('Skipping invalid content item:', content);
                continue;
            }

            try {
                sw[content.content_options.id] = new Swiper('#' + content.content_options.id, {
                    'effect': content.content_options?.options?.effect || 'slide',
                    'slidePerView': 1,
                    'spaceBetween': 0,
                    'autoplay': {
                        'disableOnInteraction': false,
                    },
                    'slidesPerView': 'auto',
                    'centeredSlides': true,
                    'mousewheel': true,
                    'keyboard': true,
                    'on': {
                        'slideChange': function() {
                            try {
                                let activeIndex = this.activeIndex;
                                let slide = this.slides[activeIndex];
                                if (slide && slide.getAttribute && slide.getAttribute('data-slider-type') ==
                                    'slider-video') {
                                    const videoId = slide.getAttribute('data-slider-id');
                                    if (videoId) {
                                        this.player = videojs(videoId);
                                        this.player.currentTime(0);
                                        this.player.play();
                                    }
                                }
                            } catch (error) {
                                console.error('Error in slideChange event:', error);
                            }
                        },
                        'beforeSlideChangeStart': function() {
                            try {
                                let activeIndex = this.activeIndex;
                                let slide = this.slides[activeIndex];
                                if (slide && slide.getAttribute && slide.getAttribute('data-slider-type') ==
                                    'slider-video') {
                                    const videoId = slide.getAttribute('data-slider-id');
                                    if (videoId) {
                                        this.player = videojs(videoId);
                                        this.player.pause();
                                    }
                                }
                            } catch (error) {
                                console.error('Error in beforeSlideChangeStart event:', error);
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Error initializing Swiper for element #' + content.content_options.id, error);
            }
        }
    </script>
</body>

</html>
