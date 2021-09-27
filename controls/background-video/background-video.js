
/**
 * Code taken from BackgroundVideo handler in elementor frontend.js
 * @param {ElementBase} handler Widget Handler
 */
themesharkFrontend.addBackgroundVideoFunctions = function (handler) {
    handler.prototype._getBackgroundElements = function () {
        return {
            $backgroundVideoContainer: this.$element.find('.elementor-background-video-container'),
            $backgroundVideoEmbed: this.$element.find('.elementor-background-video-embed'),
            $backgroundVideoHosted: this.$element.find('.elementor-background-video-hosted')
        }
    }

    handler.prototype._bgVideoCalcVideosSize = function ($video) {
        const { $backgroundVideoContainer } = this._getBackgroundElements()

        var aspectRatioSetting = '16:9';

        if ('vimeo' === this.bgVideoType) {
            aspectRatioSetting = $video[0].width + ':' + $video[0].height;
        }

        var containerWidth = $backgroundVideoContainer.outerWidth(),
            containerHeight = $backgroundVideoContainer.outerHeight(),
            aspectRatioArray = aspectRatioSetting.split(':'),
            aspectRatio = aspectRatioArray[0] / aspectRatioArray[1],
            ratioWidth = containerWidth / aspectRatio,
            ratioHeight = containerHeight * aspectRatio,
            isWidthFixed = containerWidth / containerHeight > aspectRatio;
        return {
            width: isWidthFixed ? containerWidth : ratioHeight,
            height: isWidthFixed ? ratioWidth : containerHeight
        };
    }

    handler.prototype._bgVideoChangeVideoSize = function () {
        const { $backgroundVideoHosted } = this._getBackgroundElements()

        if (!('hosted' === this.bgVideoType) && !this.bgVideoPlayer) {
            return;
        }

        var $video;

        if ('youtube' === this.bgVideoType) {
            $video = jQuery(this.bgVideoPlayer.getIframe());
        } else if ('vimeo' === this.bgVideoType) {
            $video = jQuery(this.bgVideoPlayer.element);
        } else if ('hosted' === this.bgVideoType) {
            $video = $backgroundVideoHosted;
        }

        if (!$video) {
            return;
        }

        var size = this._bgVideoCalcVideosSize($video);
        $video.width(size.width).height(size.height);
    }


    handler.prototype._bgVideoStartVideoLoop = function (firstTime) {
        var _this = this;

        // If the section has been removed
        if (!this.bgVideoPlayer.getIframe().contentWindow) {
            return;
        }

        var elementSettings = this.getElementSettings(),
            startPoint = elementSettings.background_video_start || 0,
            endPoint = elementSettings.background_video_end;

        if (elementSettings.background_play_once && !firstTime) {
            this.bgVideoPlayer.stopVideo();
            return;
        }

        this.bgVideoPlayer.seekTo(startPoint);

        if (endPoint) {
            var durationToEnd = endPoint - startPoint + 1;
            setTimeout(function () {
                _this._bgVideoStartVideoLoop(false);
            }, durationToEnd * 1000);
        }
    }


    handler.prototype.hasBgVideo = function () {
        return this._getBackgroundElements().$backgroundVideoContainer[0] !== undefined
    }

    handler.prototype._bgVideoPrepareVimeoVideo = function (Vimeo, videoId) {
        const { $backgroundVideoContainer } = this._getBackgroundElements()

        var _this2 = this;

        var elementSettings = this.getElementSettings(),
            startTime = elementSettings.background_video_start ? elementSettings.background_video_start : 0,
            videoSize = $backgroundVideoContainer.outerWidth(),
            vimeoOptions = {
                id: videoId,
                width: videoSize.width,
                autoplay: true,
                loop: !elementSettings.background_play_once,
                transparent: false,
                background: true,
                muted: true,

                //themeshark added
                title: false,
                controls: false,
                sidedock: false

            };
        this.bgVideoPlayer = new Vimeo.Player($backgroundVideoContainer, vimeoOptions); // Handle user-defined start/end times

        this._bgVideoHandleVimeoStartEndTimes(elementSettings);

        let waitingOnReady = false//themeshark-added

        //themeshark added
        this.bgVideoPlayer.on('play', function () {
            _this2.bgVideoDidPlay = true
            if (waitingOnReady) _this2._bgVideoOnReady() //load and display video before firing onReady functions
            waitingOnReady = false
        })
        // / themeshark added

        this.bgVideoPlayer.ready().then(function (e) {
            jQuery(_this2.bgVideoPlayer.element).addClass('elementor-background-video-embed');
            _this2._bgVideoChangeVideoSize();
            waitingOnReady = true //themeshark added
        });
    }

    handler.prototype._bgVideoHandleVimeoStartEndTimes = function (elementSettings) {
        var _this3 = this;

        // If a start time is defined, set the start time
        if (elementSettings.background_video_start) {
            this.bgVideoPlayer.on('play', function (data) {
                if (0 === data.seconds) {
                    _this3.bgVideoPlayer.setCurrentTime(elementSettings.background_video_start);
                }
            });
        }

        this.bgVideoPlayer.on('timeupdate', function (data) {
            // If an end time is defined, handle ending the video
            if (elementSettings.background_video_end && elementSettings.background_video_end < data.seconds) {
                if (elementSettings.background_play_once) {
                    // Stop at user-defined end time if not loop
                    _this3.bgVideoPlayer.pause();
                } else {
                    // Go to start time if loop
                    _this3.bgVideoPlayer.setCurrentTime(elementSettings.background_video_start);
                }
            } // If start time is defined but an end time is not, go to user-defined start time at video end.
            // Vimeo JS API has an 'ended' event, but it never fires when infinite loop is defined, so we
            // get the video duration (returns a promise) then use duration-0.5s as end time


            _this3.bgVideoPlayer.getDuration().then(function (duration) {
                if (elementSettings.background_video_start && !elementSettings.background_video_end && data.seconds > duration - 0.5) {
                    _this3.bgVideoPlayer.setCurrentTime(elementSettings.background_video_start);
                }
            });
        });
    }

    handler.prototype._bgVideoPrepareYTVideo = function (YT, videoID) {
        const { $backgroundVideoContainer, $backgroundVideoEmbed } = this._getBackgroundElements()

        var _this4 = this

        var elementSettings = this.getElementSettings();
        var startStateCode = YT.PlayerState.PLAYING; // Since version 67, Chrome doesn't fire the `PLAYING` state at start time

        if (window.chrome) {
            startStateCode = YT.PlayerState.UNSTARTED;
        }

        var playerOptions = {
            videoId: videoID,
            events: {
                onReady: function onReady() {
                    _this4.bgVideoPlayer.mute();

                    _this4._bgVideoChangeVideoSize();

                    _this4._bgVideoStartVideoLoop(true);

                    _this4.bgVideoPlayer.playVideo();

                    _this4._bgVideoOnReady()
                },
                onStateChange: function onStateChange(event) {
                    if (event.data === 1) _this4.bgVideoDidPlay = true //themeshark added
                    switch (event.data) {
                        case startStateCode:

                            _this4._getBackgroundElements().$backgroundVideoContainer.removeClass('elementor-invisible elementor-loading');
                            break;

                        case YT.PlayerState.ENDED:
                            _this4.bgVideoPlayer.seekTo(elementSettings.background_video_start || 0);

                            if (elementSettings.background_play_once) {
                                _this4.bgVideoPlayer.destroy();
                            }

                    }
                }
            },
            playerVars: {
                controls: 0,
                rel: 0,
                playsinline: 1
            }
        }; // To handle CORS issues, when the default host is changed, the origin parameter has to be set.

        if (elementSettings.background_privacy_mode) {
            playerOptions.host = 'https://www.youtube-nocookie.com';
            playerOptions.origin = window.location.hostname;
        }

        $backgroundVideoContainer.addClass('elementor-loading elementor-invisible');
        this.bgVideoPlayer = new YT.Player($backgroundVideoEmbed[0], playerOptions);
    }

    handler.prototype._bgVideoActivate = function () {
        const { $backgroundVideoHosted } = this._getBackgroundElements()

        var _this5 = this;

        var videoLink = this.getElementSettings('background_video_link'),
            videoID;
        var playOnce = this.getElementSettings('background_play_once');

        if (-1 !== videoLink.indexOf('vimeo.com')) {
            this.bgVideoType = 'vimeo';
            this.bgVideoApiProvider = elementorFrontend.utils.vimeo;
        } else if (videoLink.match(/^(?:https?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com)/)) {
            this.bgVideoType = 'youtube';
            this.bgVideoApiProvider = elementorFrontend.utils.youtube;
        }

        if (this.bgVideoApiProvider) {
            videoID = this.bgVideoApiProvider.getVideoIDFromURL(videoLink);
            this.bgVideoApiProvider.onApiReady(function (apiObject) {
                if ('youtube' === _this5.bgVideoType) {
                    _this5._bgVideoPrepareYTVideo(apiObject, videoID);
                }

                if ('vimeo' === _this5.bgVideoType) {
                    _this5._bgVideoPrepareVimeoVideo(apiObject, videoID);
                }
            });
        } else {
            this.bgVideoType = 'hosted';
            var startTime = this.getElementSettings('background_video_start'),
                endTime = this.getElementSettings('background_video_end');

            if (startTime || endTime) {
                videoLink += '#t=' + (startTime || 0) + (endTime ? ',' + endTime : '');
            }

            $backgroundVideoHosted.attr('src', videoLink).one('canplay', this._bgVideoChangeVideoSize.bind(this));

            if (playOnce) {
                $backgroundVideoHosted.on('ended', function () {
                    _this5._getBackgroundElements().$backgroundVideoHosted.hide()
                });
            }
            // this._bgVideoOnReady()
        }

        elementorFrontend.elements.$window.on('resize', this._bgVideoChangeVideoSize);
    }

    handler.prototype._bgVideoDeactivate = function () {
        const { $backgroundVideoHosted } = this._getBackgroundElements()

        if ('youtube' === this.bgVideoType && this.bgVideoPlayer.getIframe() || 'vimeo' === this.bgVideoType) {
            this.bgVideoPlayer.destroy();
        } else {
            $backgroundVideoHosted.removeAttr('src').off('ended');
        }

        elementorFrontend.elements.$window.off('resize', this._bgVideoChangeVideoSize);
    }

    handler.prototype._bgVideoRun = function () {
        var elementSettings = this.getElementSettings();

        if (!elementSettings.background_play_on_mobile && 'mobile' === elementorFrontend.getCurrentDeviceMode()) {
            return;
        }

        if ('video' === elementSettings.background_background && elementSettings.background_video_link) {
            this._bgVideoActivate();
        } else {
            this._bgVideoDeactivate();
        }
    }

    //THEMESHARK ADDED
    handler.prototype._onBgVideoLoadHandlers = []
    handler.prototype.bgVideoIsReady = false
    handler.prototype.bgVideoDidPlay = true

    handler.prototype.addBgVideoReadyHandler = function (callback) {
        this._onBgVideoLoadHandlers.push(callback)
    }

    handler.prototype.bgVideoOnElementChange = function (controlName) {
        if (controlName === undefined) return console.error('bgVideoOnElementsChange expects the controlName arg.')
        if ('background_background' === controlName) this._bgVideoRun()
    }

    handler.prototype.bgVideoBindEvents = function () {
        this._bgVideoChangeVideoSize = this._bgVideoChangeVideoSize.bind(this)
        this._bgVideoRun()
    }

    handler.prototype.bgVideoPause = function (waitForReady = true) {
        if (this.bgVideoIsPaused === true) return console.log('already paused')//return if already paused

        const pauseVideo = () => {
            switch (this.bgVideoType) {
                case 'youtube': this.bgVideoPlayer.pauseVideo(); break;
                case 'vimeo': this.bgVideoPlayer.pause(); break;
                default: this.bgVideoPlayer.pause()
            }
            this.bgVideoIsPaused = true
        }

        if (this.bgVideoIsReady) pauseVideo()
        else if (waitForReady) this.addBgVideoReadyHandler(pauseVideo)
        else console.error('cannot pause video before it is ready')
    }

    handler.prototype.bgVideoPlay = function (waitForReady = true) {

        if (this.bgVideoIsPaused === false) return console.log('already playing')//return if already playing

        const playVideo = () => {

            switch (this.bgVideoType) {
                case 'youtube': this.bgVideoPlayer.playVideo(); break;
                case 'vimeo': this.bgVideoPlayer.play(); break;
                default: this.bgVideoPlayer.play()
            }
            this.bgVideoIsPaused = false
        }

        if (this.bgVideoIsReady) playVideo()
        else if (waitForReady) this.addBgVideoReadyHandler(playVideo)
        else console.error('cannot play video before it is ready')
    }

    handler.prototype._bgVideoGetElement = function () {
        const { $backgroundVideoEmbed, $backgroundVideoHosted } = this._getBackgroundElements()
        return $backgroundVideoEmbed[0] || $backgroundVideoHosted[0]
    }

    handler.prototype._bgVideoOnReady = function () {
        const _this = this
        const bgVideoElement = this._bgVideoGetElement()
        if (bgVideoElement.tagName === 'IFRAME') jQuery(bgVideoElement).on('load', _this._bgVideoOnLoad)
        else _this._bgVideoOnLoad()
    }

    handler.prototype._bgVideoOnLoad = function () {
        this.bgVideoIsReady = true
        this._onBgVideoLoadHandlers.forEach(func => func())
        this._onBgVideoLoadHandlers = []
    }
}