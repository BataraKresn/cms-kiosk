<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" id="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">
    <title>Display Live</title>
    <link rel="stylesheet" href="/gridstack/gridstack.min.css">
    <link rel="stylesheet" href="/gridstack/gridstack-extra.min.css">
    <link rel="stylesheet" href="/vjs/video-js.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <link rel="stylesheet" href="/cms/style.css">
    @vite('resources/js/app.js')
</head>

<body>

    <div class="grid-stack"></div>
    <div class="marquee"></div>

    <script src="/jquery/jquery.min.js"></script>
    <script src="/gridstack/gridstack-all.js"></script>
    <script src="/vjs/video.min.js"></script>
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

    <script>
        let currentDisplay = undefined;
        let currentRunningText = null;
        let grid, content;

        var data = @json($data);
        var display = @json($display);

        var vp = document.getElementById('viewport');

        var height = 1920;
        if (screen.height > 1920) {
            height = screen.height;
        }

        var width = 1080;
        if (screen.width > 1080) {
            width = screen.width;
        }

        var screenAspect = display.screen.mode == 'portrait' ? height : width;

        //SCRIPT

        function loadRunningText(content) {
            const direction = content.running_text.direction;
            const duration = content.running_text.speed * 1000;
            const text = content.running_text.description;
            const backgroundColor = content.running_text.background_color;
            const textColor = content.running_text.text_color;

            $('.marquee').text(text);
            $('.marquee').marquee({
                'direction': direction,
                'duplicated': true,
                'duration': duration,
                'gap': '20px'
            });
            $('.marquee').css({
                'backgroundColor': backgroundColor,
                'color': textColor,
                'fontSize': '45px'
            });
        }

        function loadSlider(slideContainer, content) {
            sliderContainer[content.content_options.id] = new Swiper('#' + content.content_options.id, {
                'effect': content.content_options.effect,
                'sliderPerView': 1,
                'spaceBetween': 0,
                'autoplay': {
                    'disableOnInteraction': false
                },
                'slidesPerView': 'auto',
                'centeredSlides': true,
                'mousewheel': true,
                'keyboard': true,
                'on': {
                    'slideChange': function() {
                        let activeIndex = this.activeIndex;
                        let slide = this.slides[activeIndex];
                        if (slide.getAttribute('data-slider-type') == 'slider-video') {
                            this.player = videojs(slide.getAttribute('data-video-id'));
                            this.player.currentTime(0);
                            this.player.play();
                        }
                    },
                    'beforeSideChangeStart': function() {
                        let activeIndex = this.activeIndex;
                        let slide = this.slides[activeIndex];
                        if (slide.getAttribute('data-slider-type') == 'slider-video') {
                            this.player = videojs(slide.getAttribute('data-video-id'));
                            this.player.pause();
                        }
                    }
                }
            })
            return sliderContainer;
        }

        function displayScreen(data) {
            const currentDate = new Date();
            const currentDay = currentDate.getDay();
            const currentTime = currentDate.toLocaleTimeString('en-US', {
                'hour12': false
            });

            for (const playlist of data.data.playlists) {
                if (currentDay >= playlist.start_day && currentDay <= playlist.end_day) {
                    for (const layout of playlist.layouts) {
                        if (currentTime >= layout.start_time && currentTime <= layout.end_time) {
                            const displayId = layout.id + '___' + layout.name;
                            if (currentDisplay === displayId) break;

                            content = JSON.parse(layout.content);

                            if (currentDisplay === undefined) {
                                grid = GridStack.init(content);
                                sw = {};
                                for (const child of content.children) {
                                    if (child.media_type == 'Slider') {
                                        loadSlider(sw, child);
                                    }
                                }
                            } else {
                                grid.destroy(true);
                                $('body').prepend('<div class="grid-stack"></div>');
                                grid = GridStack.init(content);
                                sw = {};
                                for (const child of content.children) {
                                    if (child.media_type == 'Slider') {
                                        loadSlider(sw, child);
                                    }
                                }
                            }

                            currentDisplay = displayId;

                            if (content.running_text) {
                                if (currentRunningText === null) {
                                    loadRunningText(content);
                                    currentRunningText = content.running_text.id;
                                    continue;
                                }

                                if (currentRunningText !== content.running_text.id) {
                                    loadRunningtext(content);
                                    currentRunningText = content.running_text.id;
                                }
                            }
                        }
                    }
                }
            }
        }

        function autoScaleViewport() {

            let scale = Math.max(screen.width, screen.height) / screenAspect;
            vp.setAttribute('content', 'width=device-width, initial-scale=' + scale + ', maximum-scale=' + scale +
                ', minimum-scale=' +
                scale + ', user-scalable=no');
        }

        displayScreen(data);

        setInterval(() => {
            displayScreen(data);
        }, 10000);

        autoScaleViewport();

        window.addEventListener('resize', function(event) {
            autoScaleViewport();
        });

        if (screenAspect > 1920) {
            $('.marquee').css({
                'fonstSize': '69.5px',
                'height': '80px',
                'lineHeight': '80px'
            });
        } else {
            $('.marquee').css({
                'fontSize': '45px',
            })
        }
    </script>

    @if(isset($display) && $display->token)
    <script type="module">
        window.Echo.channel(`App.Models.Display.{{ $display->token }}`)
            .listen('DisplayReloadEvent', (e) => {
                window.location.reload();
            });
    </script>
    @endif
