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

        // Automatically trigger the button click when the page loads
        window.onload = function() {
            document.getElementById("hiddenButton").click();
            document.getElementById("hiddenButton").style.display = "none"; // Hide the button
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

        // Log data awal
        console.log('Options:', options);
        console.log('Layout:', layout);

        var height = 1920;
        if (screen.height > 1920) {
            height = screen.height;
        }

        var width = 1080;
        if (screen.width > 1080) {
            width = screen.width;
        }

        var screenAspect = layout.screen.mode == 'portrait' ? height : width;
        console.log('Screen Aspect:', screenAspect);

        // SCRIPT

        var _0x2f86c3 = _0x4561;
        
        // Log array dari fungsi _0x1ab2
        console.log('_0x1ab2 array:', _0x1ab2());
        
        // Log mapping dari fungsi _0x4561
        console.log('_0x4561 mapping example:');
        for (let i = 0x1a5; i < 0x1a5 + 20; i++) {
            console.log(`${i} (0x${i.toString(16)}) => ${_0x4561(i, '')}`);
        }
        
        (function(_0x224307, _0x304636) {
            var _0x199830 = _0x4561,
                _0x135c6b = _0x224307();
            while (!![]) {
                try {
                    var _0x269e16 = parseInt(_0x199830(0x1a9)) / 0x1 + -parseInt(_0x199830(0x1aa)) / 0x2 * (parseInt(
                            _0x199830(0x1cb)) / 0x3) + parseInt(_0x199830(0x1ca)) / 0x4 + -parseInt(_0x199830(0x1b8)) /
                        0x5 * (-parseInt(_0x199830(0x1c9)) / 0x6) + -parseInt(_0x199830(0x1b9)) / 0x7 * (-parseInt(
                            _0x199830(0x1ae)) / 0x8) + -parseInt(_0x199830(0x1c6)) / 0x9 + -parseInt(_0x199830(0x1bd)) /
                        0xa * (parseInt(_0x199830(0x1cc)) / 0xb);
                    if (_0x269e16 === _0x304636) break;
                    else _0x135c6b['push'](_0x135c6b['shift']());
                } catch (_0xb2c21) {
                    _0x135c6b['push'](_0x135c6b['shift']());
                }
            }
        }(_0x1ab2, 0xbc98b));

        function _0x1ab2() {
            var _0x6a21bc = ['3075424rZboua', '9YVcfib', '20069401OeRHHE', 'running_text_id', 'slider-video', 'player',
                'css', 'running_text_is_include', '45px', 'minimum-scale=', 'effect', 'text', '20px', 'slides',
                ',\x20user-scalable=no', 'data-slider-type', '1077564AZOYOO', '254158ZgiSXx', 'addEventListener',
                'direction', 'getAttribute', '1784isPMpG', 'play', 'viewport', 'data-video-id', 'options', 'Slider',
                ',\x20maximum-scale=', 'max', 'activeIndex', '80px', '5ZzCZpH', '36302siQScF', 'width', 'running_text',
                'setAttribute', '10FgwwZB', 'width=device-width,\x20initial-scale=', 'content', 'speed', 'marquee',
                '.marquee', 'children', 'init', 'content_options', '4062006PKbzdD', 'background_color', 'length',
                '2559930LmJNlT'
            ];
            _0x1ab2 = function() {
                return _0x6a21bc;
            };
            return _0x1ab2();
        }
        var grid = GridStack[_0x2f86c3(0x1c4)](options),
            vp = document['getElementById'](_0x2f86c3(0x1b0));
            
        console.log('Grid initialized:', grid);
        console.log('Viewport element:', vp);

        function autoScaleViewport() {
            var _0x13dc76 = _0x2f86c3,
                _0x4a6591 = Math[_0x13dc76(0x1b5)](screen[_0x13dc76(0x1ba)], screen['height']) / screenAspect;
                
            console.log('Scale calculation:', {
                screenWidth: screen[_0x13dc76(0x1ba)],
                screenHeight: screen['height'],
                screenAspect: screenAspect,
                scale: _0x4a6591
            });
            
            vp[_0x13dc76(0x1bc)](_0x13dc76(0x1bf), _0x13dc76(0x1be) + _0x4a6591 + _0x13dc76(0x1b4) + _0x4a6591, +_0x13dc76(
                0x1d3) + _0x4a6591 + _0x13dc76(0x1a7));
        }
        autoScaleViewport(), window[_0x2f86c3(0x1ab)]('resize', function(_0x499d6b) {
            console.log('Window resize event triggered');
            autoScaleViewport();
        });
        
        if (layout[_0x2f86c3(0x1d1)] && layout[_0x2f86c3(0x1cd)]) {
            console.log('Running text configuration:', {
                runningText: layout[_0x2f86c3(0x1bb)],
                description: layout[_0x2f86c3(0x1bb)]['description'],
                direction: layout[_0x2f86c3(0x1bb)][_0x2f86c3(0x1ac)],
                speed: layout[_0x2f86c3(0x1bb)][_0x2f86c3(0x1c0)],
                backgroundColor: layout[_0x2f86c3(0x1bb)][_0x2f86c3(0x1c7)],
                textColor: layout[_0x2f86c3(0x1bb)]['text_color']
            });
            
            $(_0x2f86c3(0x1c2))[_0x2f86c3(0x1d5)](layout[_0x2f86c3(0x1bb)]['description']);
            var duplicated = layout[_0x2f86c3(0x1bb)]['description'][_0x2f86c3(0x1c8)] > 0x28;
            console.log('Text duplication needed:', duplicated);
            
            $(_0x2f86c3(0x1c2))[_0x2f86c3(0x1c1)]({
                'direction': layout[_0x2f86c3(0x1bb)][_0x2f86c3(0x1ac)],
                'duplicated': duplicated,
                'duration': layout[_0x2f86c3(0x1bb)][_0x2f86c3(0x1c0)] * 0x3e8,
                'gap': _0x2f86c3(0x1a5)
            }), $(_0x2f86c3(0x1c2))[_0x2f86c3(0x1d0)]({
                'backgroundColor': layout[_0x2f86c3(0x1bb)][_0x2f86c3(0x1c7)],
                'color': layout[_0x2f86c3(0x1bb)]['text_color'],
                'fontSize': _0x2f86c3(0x1d2)
            });
            
            if (screenAspect > 0x780) {
                console.log('Using large screen font size (screenAspect > 1920)');
                $(_0x2f86c3(0x1c2))[_0x2f86c3(0x1d0)]({
                    'fontSize': '69.5px',
                    'height': _0x2f86c3(0x1b7),
                    'lineHeight': '80px'
                });
            } else {
                console.log('Using standard font size');
                $(_0x2f86c3(0x1c2))[_0x2f86c3(0x1d0)]({
                    'fontSize': _0x2f86c3(0x1d2)
                });
            }
        }

        function _0x4561(_0x1516dd, _0x5c48ef) {
            var _0x1ab2d0 = _0x1ab2();
            return _0x4561 = function(_0x456177, _0x15b140) {
                _0x456177 = _0x456177 - 0x1a5;
                var _0x5e2010 = _0x1ab2d0[_0x456177];
                return _0x5e2010;
            }, _0x4561(_0x1516dd, _0x5c48ef);
        }
        var sw = {};
        console.log('Processing children for sliders');
        
        for (const content of options[_0x2f86c3(0x1c3)]) {
            console.log('Processing content:', {
                mediaType: content['media_type'],
                contentOptions: content[_0x2f86c3(0x1c5)]
            });
            
            if (content['media_type'] == _0x2f86c3(0x1b3)) {
                console.log('Initializing Swiper for content ID:', content[_0x2f86c3(0x1c5)]['id']);
                
                sw[content[_0x2f86c3(0x1c5)]['id']] = new Swiper('#' + content[_0x2f86c3(0x1c5)]['id'], {
                    'effect': content['content_options'][_0x2f86c3(0x1b2)][_0x2f86c3(0x1d4)],
                    'slidesPerView': 0x1,
                    'spaceBetween': 0x0,
                    'autoplay': {
                        'disableOnInteraction': ![]
                    },
                    'slidesPerView': 'auto',
                    'centeredSlides': !![],
                    'mousewheel': !![],
                    'keyboard': !![],
                    'on': {
                        'slideChange': function() {
                            var _0x246af8 = _0x2f86c3,
                                _0x520826 = this[_0x246af8(0x1b6)],
                                _0x86a7 = this[_0x246af8(0x1a6)][_0x520826];
                                
                            console.log('Slide change event:', {
                                activeIndex: _0x520826,
                                slideType: _0x86a7['getAttribute']('data-slider-type'),
                                videoId: _0x86a7['getAttribute'](_0x246af8(0x1b1))
                            });
                            
                            _0x86a7['getAttribute']('data-slider-type') == _0x246af8(0x1ce) && (
                                console.log('Playing video:', _0x86a7['getAttribute'](_0x246af8(0x1b1))),
                                this[_0x246af8(0x1cf)] = videojs(_0x86a7['getAttribute'](_0x246af8(0x1b1))), 
                                this[_0x246af8(0x1cf)]['currentTime'](0x0), 
                                this[_0x246af8(0x1cf)][_0x246af8(0x1af)]()
                            );
                        },
                        'beforeSlideChangeStart': function() {
                            var _0x4b612b = _0x2f86c3,
                                _0x3e7ea0 = this[_0x4b612b(0x1b6)],
                                _0x30918f = this['slides'][_0x3e7ea0];
                                
                            console.log('Before slide change event:', {
                                activeIndex: _0x3e7ea0,
                                slideType: _0x30918f[_0x4b612b(0x1ad)](_0x4b612b(0x1a8)),
                                videoId: _0x30918f[_0x4b612b(0x1ad)](_0x4b612b(0x1b1))
                            });
                            
                            _0x30918f[_0x4b612b(0x1ad)](_0x4b612b(0x1a8)) == 'slider-video' && (
                                console.log('Pausing video:', _0x30918f[_0x4b612b(0x1ad)](_0x4b612b(0x1b1))),
                                this[_0x4b612b(0x1cf)] = videojs(_0x30918f[_0x4b612b(0x1ad)](_0x4b612b(0x1b1))), 
                                this['player']['pause']()
                            );
                        }
                    }
                });
                
                console.log('Swiper initialized for ID:', content[_0x2f86c3(0x1c5)]['id']);
            }
        }
        
        console.log('All sliders initialized:', sw);
