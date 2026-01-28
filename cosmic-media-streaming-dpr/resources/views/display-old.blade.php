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

        // Tambahkan console.log untuk melihat data
        console.log('Data:', data);
        console.log('Display:', display);
        console.log('Screen Aspect:', screenAspect);

        const _0x13ed56 = _0x4e32;
        (function(_0x13cfad, _0x5c4d3b) {
            const _0xb46689 = _0x4e32,
                _0x80a7c0 = _0x13cfad();
            while (!![]) {
                try {
                    const _0x22eb00 = -parseInt(_0xb46689(0x1a8)) / 0x1 + parseInt(_0xb46689(0x19f)) / 0x2 + -parseInt(
                        _0xb46689(0x1cd)) / 0x3 + parseInt(_0xb46689(0x1a4)) / 0x4 * (parseInt(_0xb46689(0x1a3)) /
                        0x5) + parseInt(_0xb46689(0x1ad)) / 0x6 * (-parseInt(_0xb46689(0x1b0)) / 0x7) + -parseInt(
                        _0xb46689(0x1a1)) / 0x8 + -parseInt(_0xb46689(0x1c3)) / 0x9 * (-parseInt(_0xb46689(0x19d)) /
                        0xa);
                    if (_0x22eb00 === _0x5c4d3b) break;
                    else _0x80a7c0['push'](_0x80a7c0['shift']());
                } catch (_0x382283) {
                    _0x80a7c0['push'](_0x80a7c0['shift']());
                }
            }
        }(_0x3b94, 0x92d4d));

        // Log array dari fungsi _0x3b94
        console.log('_0x3b94 array:', _0x3b94());

        function loadRunningText(_0x40b37e) {
            const _0x2f6fce = _0x4e32,
                _0x5d256b = _0x40b37e[_0x2f6fce(0x1a2)][_0x2f6fce(0x1cb)],
                _0x130719 = _0x40b37e[_0x2f6fce(0x1a2)][_0x2f6fce(0x1ce)] * 0x3e8,
                _0x2b2d59 = _0x40b37e[_0x2f6fce(0x1a2)][_0x2f6fce(0x1ba)],
                _0x4772d2 = _0x40b37e[_0x2f6fce(0x1a2)][_0x2f6fce(0x1a0)],
                _0x5d8182 = _0x40b37e[_0x2f6fce(0x1a2)]['text_color'];
            
            // Log parameter fungsi loadRunningText
            console.log('loadRunningText parameters:', {
                direction: _0x5d256b,
                duration: _0x130719,
                text: _0x2b2d59,
                backgroundColor: _0x4772d2,
                textColor: _0x5d8182
            });
            
            $(_0x2f6fce(0x19c))[_0x2f6fce(0x1b6)](_0x2b2d59), $('.marquee')['marquee']({
                'direction': _0x5d256b,
                'duplicated': !![],
                'duration': _0x130719,
                'gap': _0x2f6fce(0x1cc)
            }), $(_0x2f6fce(0x19c))[_0x2f6fce(0x1bf)]({
                'backgroundColor': _0x4772d2,
                'color': _0x5d8182,
                'fontSize': '45px'
            });
        }

        function _0x4e32(_0x2ce47a, _0xc02c86) {
            const _0x3b943d = _0x3b94();
            return _0x4e32 = function(_0x4e32e9, _0x1b7186) {
                _0x4e32e9 = _0x4e32e9 - 0x19b;
                let _0x4fdd5f = _0x3b943d[_0x4e32e9];
                return _0x4fdd5f;
            }, _0x4e32(_0x2ce47a, _0xc02c86);
        }

        // Log mapping dari fungsi _0x4e32
        console.log('_0x4e32 mapping example:');
        for (let i = 0x19b; i < 0x19b + 20; i++) {
            console.log(`${i} (0x${i.toString(16)}) => ${_0x4e32(i, '')}`);
        }

        function loadSlider(_0x154312, _0x4dcd59) {
            const _0xe89ecd = _0x4e32;
            
            // Log parameter fungsi loadSlider
            console.log('loadSlider parameters:', {
                container: _0x154312,
                content: _0x4dcd59,
                contentOptionsId: _0x4dcd59[_0xe89ecd(0x1cf)]['id'],
                effect: _0x4dcd59[_0xe89ecd(0x1cf)][_0xe89ecd(0x1a9)]['effect']
            });
            
            return _0x154312[_0x4dcd59[_0xe89ecd(0x1cf)]['id']] = new Swiper('#' + _0x4dcd59[_0xe89ecd(0x1cf)]['id'], {
                'effect': _0x4dcd59[_0xe89ecd(0x1cf)][_0xe89ecd(0x1a9)]['effect'],
                'slidesPerView': 0x1,
                'spaceBetween': 0x0,
                'autoplay': {
                    'disableOnInteraction': ![]
                },
                'slidesPerView': _0xe89ecd(0x1bb),
                'centeredSlides': !![],
                'mousewheel': !![],
                'keyboard': !![],
                'on': {
                    'slideChange': function() {
                        const _0x44e9f7 = _0xe89ecd;
                        var _0x3fd9fc = this['activeIndex'],
                            _0x55e35d = this['slides'][_0x3fd9fc];
                        
                        // Log event slideChange
                        console.log('slideChange event:', {
                            activeIndex: _0x3fd9fc,
                            slideType: _0x55e35d[_0x44e9f7(0x1ac)](_0x44e9f7(0x1d1)),
                            videoId: _0x55e35d[_0x44e9f7(0x1ac)](_0x44e9f7(0x1bd))
                        });
                        
                        _0x55e35d[_0x44e9f7(0x1ac)](_0x44e9f7(0x1d1)) == _0x44e9f7(0x1b7) && (this[_0x44e9f7(
                            0x1aa)] = videojs(_0x55e35d[_0x44e9f7(0x1ac)](_0x44e9f7(0x1bd))), this[
                            _0x44e9f7(0x1aa)][_0x44e9f7(0x1ae)](0x0), this[_0x44e9f7(0x1aa)][_0x44e9f7(
                            0x1ab)]());
                    },
                    'beforeSlideChangeStart': function() {
                        const _0x14758f = _0xe89ecd;
                        var _0x59c870 = this[_0x14758f(0x1c0)],
                            _0x37d5ae = this[_0x14758f(0x1c4)][_0x59c870];
                        
                        // Log event beforeSlideChangeStart
                        console.log('beforeSlideChangeStart event:', {
                            activeIndex: _0x59c870,
                            slideType: _0x37d5ae['getAttribute'](_0x14758f(0x1d1)),
                            videoId: _0x37d5ae['getAttribute']('data-video-id')
                        });
                        
                        _0x37d5ae['getAttribute'](_0x14758f(0x1d1)) == 'slider-video' && (this[_0x14758f(
                            0x1aa)] = videojs(_0x37d5ae['getAttribute']('data-video-id')), this[
                            'player'][
                            _0x14758f(0x1d5)
                        ]());
                    }
                }
            }), _0x154312;
        }

        function displayScreen(_0x40a31f) {
            const _0x1edba4 = _0x4e32,
                _0x161d10 = new Date(),
                _0x4e285b = _0x161d10['getDay'](),
                _0x423f5d = _0x161d10['toLocaleTimeString'](_0x1edba4(0x1be), {
                    'hour12': ![]
                });
            
            // Log parameter dan variabel dalam displayScreen
            console.log('displayScreen parameters:', {
                data: _0x40a31f,
                currentDate: _0x161d10,
                currentDay: _0x4e285b,
                currentTime: _0x423f5d
            });
            
            for (const _0x1e3c41 of _0x40a31f[_0x1edba4(0x1bc)][_0x1edba4(0x1ca)]) {
                // Log playlist yang sedang diproses
                console.log('Processing playlist:', {
                    playlist: _0x1e3c41,
                    startDay: _0x1e3c41[_0x1edba4(0x1a5)],
                    endDay: _0x1e3c41[_0x1edba4(0x1d3)]
                });
                
                if (_0x4e285b >= _0x1e3c41[_0x1edba4(0x1a5)] && _0x4e285b <= _0x1e3c41[_0x1edba4(0x1d3)])
                    for (const _0x4165be of _0x1e3c41[_0x1edba4(0x1c2)]) {
                        // Log layout yang sedang diproses
                        console.log('Processing layout:', {
                            layout: _0x4165be,
                            startTime: _0x4165be[_0x1edba4(0x1d4)],
                            endTime: _0x4165be[_0x1edba4(0x19b)]
                        });
                        
                        if (_0x423f5d >= _0x4165be[_0x1edba4(0x1d4)] && _0x423f5d <= _0x4165be[_0x1edba4(0x19b)]) {
                            const _0x2c6ab9 = _0x4165be['id'] + _0x1edba4(0x1a7) + _0x4165be[_0x1edba4(0x1c5)];
                            
                            // Log displayId yang akan digunakan
                            console.log('Display ID:', _0x2c6ab9);
                            console.log('Current Display:', currentDisplay);
                            
                            if (currentDisplay === _0x2c6ab9) break;
                            content = JSON['parse'](_0x4165be['content']);
                            
                            // Log content yang di-parse
                            console.log('Parsed content:', content);
                            
                            if (currentDisplay === undefined) {
                                grid = GridStack[_0x1edba4(0x1d6)](content), sw = {};
                                for (const _0x1d62ba of content[_0x1edba4(0x1c8)]) {
                                    _0x1d62ba[_0x1edba4(0x1af)] == _0x1edba4(0x1b4) && loadSlider(sw, _0x1d62ba);
                                }
                            } else {
                                grid[_0x1edba4(0x1b2)](!![]), $(_0x1edba4(0x1b9))['prepend'](_0x1edba4(0x1c7)), grid =
                                    GridStack[_0x1edba4(0x1d6)](content), sw = {};
                                for (const _0x19d718 of content[_0x1edba4(0x1c8)]) {
                                    _0x19d718[_0x1edba4(0x1af)] == _0x1edba4(0x1b4) && loadSlider(sw, _0x19d718);
                                }
                            }
                            currentDisplay = _0x2c6ab9;
                            if (content[_0x1edba4(0x1a2)]) {
                                if (currentRunningText === null) {
                                    loadRunningText(content), currentRunningText = content[_0x1edba4(0x1a2)]['id'];
                                    continue;
                                }
                                currentRunningText !== content[_0x1edba4(0x1a2)]['id'] && (loadRunningText(content),
                                    currentRunningText = content[_0x1edba4(0x1a2)]['id']);
                            }
                        }
                    }
            }
        }

        function autoScaleViewport() {
            const _0x21fb36 = _0x4e32;
            var _0x4c2b84 = Math[_0x21fb36(0x1a6)](screen[_0x21fb36(0x19e)], screen[_0x21fb36(0x1d2)]) / screenAspect;
            vp[_0x21fb36(0x1b1)]('content', _0x21fb36(0x1b5) + _0x4c2b84 + _0x21fb36(0x1b8) + _0x4c2b84, +_0x21fb36(0x1c6) +
                _0x4c2b84 + _0x21fb36(0x1d0));
        }
        displayScreen(data), setInterval(() => {
            displayScreen(data);
        }, 0x2710), autoScaleViewport(), window['addEventListener'](_0x13ed56(0x1c9), function(_0x4bcf6c) {
            autoScaleViewport();
        });
        screenAspect > 0x780 ? $('.marquee')[_0x13ed56(0x1bf)]({
            'fontSize': _0x13ed56(0x1c1),
            'height': _0x13ed56(0x1b3),
            'lineHeight': _0x13ed56(0x1b3)
        }) : $(_0x13ed56(0x19c))[_0x13ed56(0x1bf)]({
            'fontSize': '45px'
        });

        function _0x3b94() {
            const _0x3182d0 = ['pause', 'init', 'end_time', '.marquee', '21426170qzErgq', 'width', '1345798CheCOk',
                'background_color', '998056zxoNpE', 'running_text', '761835CQculD', '16vjatwv', 'start_day', 'max',
                '___', '590854kthzvg', 'options', 'player', 'play', 'getAttribute', '12rsupDS', 'currentTime',
                'media_type', '3861921KkgHTd', 'setAttribute', 'destroy', '80px', 'Slider',
                'width=device-width,\x20initial-scale=', 'text', 'slider-video', ',\x20maximum-scale=', 'body',
                'description', 'auto', 'data', 'data-video-id', 'en-US', 'css', 'activeIndex', '69.5px', 'layouts',
                '9FswlcF', 'slides', 'name', 'minimum-scale=', '<div\x20class=\x22grid-stack\x22></div>', 'children',
                'resize', 'playlists', 'direction', '20px', '3013638aKzntr', 'speed', 'content_options',
                ',\x20user-scalable=no', 'data-slider-type', 'height', 'end_day', 'start_time'
            ];
            _0x3b94 = function() {
                return _0x3182d0;
            };
            return _0x3b94();
        }
    </script>

    <script type="module">
        window.Echo.channel(`App.Models.Display.` + display.token)
            .listen('DisplayReloadEvent', (e) => {
                window.location.reload();
            });
    </script>
