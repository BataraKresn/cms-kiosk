<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" id="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">
    <title>Schedule Live</title>
    <link rel="stylesheet" href="/gridstack/gridstack.min.css">
    <link rel="stylesheet" href="/gridstack/gridstack-extra.min.css">
    <link rel="stylesheet" href="/vjs/video-js.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <link rel="stylesheet" href="/cms/style.css">
</head>

<body>

    <div class="grid-stack"></div>
    <div class="marquee"></div>

    <script src="/jquery/jquery.min.js"></script>
    <script src="/gridstack/gridstack-all.js"></script>
    <script src="/vjs/video.min.js"></script>
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    @vite('resources/js/app.js')

    <script>
        let currentDisplay = undefined;
        let currentRunningText = null;
        let grid, content;

        var data = @json($data);
        var display = @json($display);
        var vp = document.getElementById('viewport');

        // Log data awal
        console.log('Data:', data);
        console.log('Display:', display);

        var height = 1920;
        if (screen.height > 1920) {
            height = screen.height;
        }

        var width = 1080;
        if (screen.width > 1080) {
            width = screen.width;
        }

        var screenAspect = display.screen.mode == 'portrait' ? height : width;
        console.log('Screen Aspect:', screenAspect);
        console.log('Screen Height:', height);
        console.log('Screen Width:', width);

        // SCRIPT

        const _0x23423c = _0x1f2f;
        
        // Log array dari fungsi _0x3d46
        console.log('_0x3d46 array:', _0x3d46());
        
        // Log mapping dari fungsi _0x1f2f
        console.log('_0x1f2f mapping example:');
        for (let i = 0x181; i < 0x181 + 20; i++) {
            console.log(`${i} (0x${i.toString(16)}) => ${_0x1f2f(i, '')}`);
        }
        
        (function(_0x5a0a62, _0x1330a8) {
            const _0x4167c3 = _0x1f2f,
                _0x2eaa27 = _0x5a0a62();
            while (!![]) {
                try {
                    const _0x13c903 = -parseInt(_0x4167c3(0x181)) / 0x1 * (-parseInt(_0x4167c3(0x1b3)) / 0x2) +
                        parseInt(_0x4167c3(0x1aa)) / 0x3 + parseInt(_0x4167c3(0x1b4)) / 0x4 * (parseInt(_0x4167c3(
                            0x192)) / 0x5) + -parseInt(_0x4167c3(0x193)) / 0x6 + parseInt(_0x4167c3(0x1a8)) / 0x7 +
                        parseInt(_0x4167c3(0x1b7)) / 0x8 + parseInt(_0x4167c3(0x185)) / 0x9 * (-parseInt(_0x4167c3(
                            0x18c)) / 0xa);
                    if (_0x13c903 === _0x1330a8) break;
                    else _0x2eaa27['push'](_0x2eaa27['shift']());
                } catch (_0x9e6c8c) {
                    _0x2eaa27['push'](_0x2eaa27['shift']());
                }
            }
        }(_0x3d46, 0x90310));

        function loadRunningText(_0x4467d7) {
            const _0x1faf56 = _0x1f2f,
                _0x2f07af = _0x4467d7[_0x1faf56(0x18f)][_0x1faf56(0x19b)],
                _0x41aa43 = _0x4467d7[_0x1faf56(0x18f)][_0x1faf56(0x1b1)] * 0x3e8,
                _0x224c00 = _0x4467d7[_0x1faf56(0x18f)][_0x1faf56(0x18b)],
                _0xbbff43 = _0x4467d7[_0x1faf56(0x18f)][_0x1faf56(0x1b0)],
                _0x3f2848 = _0x4467d7[_0x1faf56(0x18f)][_0x1faf56(0x18e)];
                
            // Log parameter fungsi loadRunningText
            console.log('loadRunningText parameters:', {
                content: _0x4467d7,
                direction: _0x2f07af,
                duration: _0x41aa43,
                text: _0x224c00,
                backgroundColor: _0xbbff43,
                textColor: _0x3f2848
            });
            
            $('.marquee')[_0x1faf56(0x182)](_0x224c00), $(_0x1faf56(0x186))[_0x1faf56(0x191)]({
                'direction': _0x2f07af,
                'duplicated': !![],
                'duration': _0x41aa43,
                'gap': '20px'
            }), $('.marquee')['css']({
                'backgroundColor': _0xbbff43,
                'color': _0x3f2848,
                'fontSize': '45px'
            });
        }

        function _0x1f2f(_0x1471c0, _0x4efd1a) {
            const _0x3d4621 = _0x3d46();
            return _0x1f2f = function(_0x1f2fd2, _0x3910dc) {
                _0x1f2fd2 = _0x1f2fd2 - 0x181;
                let _0x201dac = _0x3d4621[_0x1f2fd2];
                return _0x201dac;
            }, _0x1f2f(_0x1471c0, _0x4efd1a);
        }

        function loadSlider(_0x44f1b6, _0x28a101) {
            const _0x28a47b = _0x1f2f;
            
            // Log parameter fungsi loadSlider
            console.log('loadSlider parameters:', {
                container: _0x44f1b6,
                content: _0x28a101,
                contentOptionsId: _0x28a101[_0x28a47b(0x1b8)]['id'],
                effect: _0x28a101['content_options'][_0x28a47b(0x1ac)][_0x28a47b(0x1b6)]
            });
            
            return _0x44f1b6[_0x28a101[_0x28a47b(0x1b8)]['id']] = new Swiper('#' + _0x28a101[_0x28a47b(0x1b8)]['id'], {
                'effect': _0x28a101['content_options'][_0x28a47b(0x1ac)][_0x28a47b(0x1b6)],
                'slidesPerView': 0x1,
                'spaceBetween': 0x0,
                'autoplay': {
                    'disableOnInteraction': ![]
                },
                'slidesPerView': _0x28a47b(0x18a),
                'centeredSlides': !![],
                'mousewheel': !![],
                'keyboard': !![],
                'on': {
                    'slideChange': function() {
                        const _0x2e0529 = _0x28a47b;
                        var _0x44a8a5 = this[_0x2e0529(0x1af)],
                            _0x272364 = this['slides'][_0x44a8a5];
                            
                        // Log event slideChange
                        console.log('slideChange event:', {
                            activeIndex: _0x44a8a5,
                            slideType: _0x272364['getAttribute'](_0x2e0529(0x199)),
                            videoId: _0x272364[_0x2e0529(0x196)]('data-video-id')
                        });
                        
                        _0x272364['getAttribute'](_0x2e0529(0x199)) == _0x2e0529(0x19f) && (this[_0x2e0529(
                                0x1ae)] = videojs(_0x272364[_0x2e0529(0x196)]('data-video-id')), this[
                                _0x2e0529(0x1ae)][_0x2e0529(0x1a1)](0x0), this['player'][_0x2e0529(0x1a9)]
                            ());
                    },
                    'beforeSlideChangeStart': function() {
                        const _0x4ebc04 = _0x28a47b;
                        var _0x2d9137 = this[_0x4ebc04(0x1af)],
                            _0x3f29e4 = this[_0x4ebc04(0x1a7)][_0x2d9137];
                            
                        // Log event beforeSlideChangeStart
                        console.log('beforeSlideChangeStart event:', {
                            activeIndex: _0x2d9137,
                            slideType: _0x3f29e4[_0x4ebc04(0x196)](_0x4ebc04(0x199)),
                            videoId: _0x3f29e4['getAttribute'](_0x4ebc04(0x1a4))
                        });
                        
                        _0x3f29e4[_0x4ebc04(0x196)](_0x4ebc04(0x199)) == _0x4ebc04(0x19f) && (this[_0x4ebc04(
                            0x1ae)] = videojs(_0x3f29e4['getAttribute'](_0x4ebc04(0x1a4))), this[
                            _0x4ebc04(0x1ae)][_0x4ebc04(0x195)]());
                    }
                }
            }), _0x44f1b6;
        }

        function displayScreen(_0x1146da) {
            const _0x353c54 = _0x1f2f,
                _0x5a25ab = new Date(),
                _0x54e24d = _0x5a25ab[_0x353c54(0x18d)](),
                _0x51d6f1 = _0x5a25ab[_0x353c54(0x1bb)](_0x353c54(0x1ad), {
                    'hour12': ![]
                });
                
            // Log parameter dan variabel dalam displayScreen
            console.log('displayScreen parameters:', {
                data: _0x1146da,
                currentDate: _0x5a25ab,
                currentDay: _0x54e24d,
                currentTime: _0x51d6f1
            });
            
            for (const _0x2db1c9 of _0x1146da['data']['playlists']) {
                // Log playlist yang sedang diproses
                console.log('Processing playlist:', {
                    playlist: _0x2db1c9,
                    startDay: _0x2db1c9['start_day'],
                    endDay: _0x2db1c9[_0x353c54(0x1ab)]
                });
                
                if (_0x54e24d >= _0x2db1c9['start_day'] && _0x54e24d <= _0x2db1c9[_0x353c54(0x1ab)])
                    for (const _0x53bebc of _0x2db1c9[_0x353c54(0x184)]) {
                        // Log layout yang sedang diproses
                        console.log('Processing layout:', {
                            layout: _0x53bebc,
                            startTime: _0x53bebc['start_time'],
                            endTime: _0x53bebc[_0x353c54(0x19d)]
                        });
                        
                        if (_0x51d6f1 >= _0x53bebc['start_time'] && _0x51d6f1 <= _0x53bebc[_0x353c54(0x19d)]) {
                            const _0x512835 = _0x53bebc['id'] + _0x353c54(0x19a) + _0x53bebc[_0x353c54(0x1b5)];
                            
                            // Log displayId yang akan digunakan
                            console.log('Display ID:', _0x512835);
                            console.log('Current Display:', currentDisplay);
                            
                            if (currentDisplay === _0x512835) break;
                            content = JSON[_0x353c54(0x1b2)](_0x53bebc['content']);
                            
                            // Log content yang di-parse
                            console.log('Parsed content:', content);
                            
                            if (currentDisplay === undefined) {
                                console.log('Initializing grid for the first time');
                                grid = GridStack[_0x353c54(0x1b9)](content), sw = {};
                                for (const _0x43008f of content['children']) {
                                    console.log('Processing child:', _0x43008f);
                                    _0x43008f['media_type'] == 'Slider' && loadSlider(sw, _0x43008f);
                                }
                            } else {
                                console.log('Destroying and reinitializing grid');
                                grid[_0x353c54(0x1a6)](!![]), $(_0x353c54(0x1ba))[_0x353c54(0x1a0)](_0x353c54(0x190)),
                                    grid = GridStack[_0x353c54(0x1b9)](content), sw = {};
                                for (const _0x143bd0 of content[_0x353c54(0x183)]) {
                                    console.log('Processing child after reinit:', _0x143bd0);
                                    _0x143bd0[_0x353c54(0x19e)] == _0x353c54(0x1a3) && loadSlider(sw, _0x143bd0);
                                }
                            }
                            currentDisplay = _0x512835;
                            
                            if (content[_0x353c54(0x18f)]) {
                                console.log('Running text found:', content[_0x353c54(0x18f)]);
                                if (currentRunningText === null) {
                                    console.log('Initializing running text for the first time');
                                    loadRunningText(content), currentRunningText = content[_0x353c54(0x18f)]['id'];
                                    continue;
                                }
                                
                                if (currentRunningText !== content['running_text']['id']) {
                                    console.log('Updating running text');
                                    loadRunningText(content),
                                    currentRunningText = content[_0x353c54(0x18f)]['id'];
                                }
                            }
                        }
                    }
            }
        }

        function _0x3d46() {
            const _0x2af09d = ['auto', 'description', '1343610ojrFZk', 'getDay', 'text_color', 'running_text',
                '<div\x20class=\x22grid-stack\x22></div>', 'marquee', '14255krNtNw', '1320006oPvtsd', 'css', 'pause',
                'getAttribute', 'addEventListener', 'height', 'data-slider-type', '___', 'direction', 'max', 'end_time',
                'media_type', 'slider-video', 'prepend', 'currentTime', 'content', 'Slider', 'data-video-id', 'width',
                'destroy', 'slides', '4287136MxPwBl', 'play', '3136032wIQCBB', 'end_day', 'options', 'en-US', 'player',
                'activeIndex', 'background_color', 'speed', 'parse', '372248MIXLCd', '916UiRgRX', 'name', 'effect',
                '4783608cUaGVV', 'content_options', 'init', 'body', 'toLocaleTimeString', '1gPNhxg', 'text', 'children',
                'layouts', '153buKYjs', '.marquee', '80px', '45px', ',\x20user-scalable=no'
            ];
            _0x3d46 = function() {
                return _0x2af09d;
            };
            return _0x3d46();
        }

        function autoScaleViewport() {
            const _0x545b5d = _0x1f2f;
            var _0x379d52 = Math[_0x545b5d(0x19c)](screen[_0x545b5d(0x1a5)], screen[_0x545b5d(0x198)]) / screenAspect;
            
            // Log scale calculation
            console.log('autoScaleViewport calculation:', {
                screenWidth: screen[_0x545b5d(0x1a5)],
                screenHeight: screen[_0x545b5d(0x198)],
                screenAspect: screenAspect,
                scale: _0x379d52
            });
            
            vp['setAttribute'](_0x545b5d(0x1a2), 'width=device-width,\x20initial-scale=' + _0x379d52 +
                ',\x20maximum-scale=' + _0x379d52, +'minimum-scale=' + _0x379d52 + _0x545b5d(0x189));
        }
        
        // Log initial display screen call
        console.log('Initial displayScreen call');
        displayScreen(data);
        
        // Log interval setup
        console.log('Setting up interval for displayScreen (10000ms)');
        setInterval(() => {
            console.log('Interval triggered - calling displayScreen');
            displayScreen(data);
        }, 0x2710); // 10000ms
        
        // Log viewport scaling
        console.log('Initial autoScaleViewport call');
        autoScaleViewport();
        
        // Log resize event listener
        console.log('Adding resize event listener');
        window[_0x23423c(0x197)]('resize', function(_0xcfa871) {
            console.log('Window resize event triggered');
            autoScaleViewport();
        });
        
        // Log marquee styling based on screen aspect
        console.log('Setting marquee styling based on screen aspect:', screenAspect);
        if (screenAspect > 0x780) { // 1920
            console.log('Using large screen font size (screenAspect > 1920)');
            $(_0x23423c(0x186))[_0x23423c(0x194)]({
                'fontSize': '69.5px',
                'height': _0x23423c(0x187),
                'lineHeight': _0x23423c(0x187)
            });
        } else {
            console.log('Using standard font size');
            $(_0x23423c(0x186))[_0x23423c(0x194)]({
                'fontSize': _0x23423c(0x188)
            });
        }
        
    </script>
