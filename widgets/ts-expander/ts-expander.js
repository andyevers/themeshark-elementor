class TSExpanderHandler extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                expander: '.themeshark-expander',
                contentWrap: '.themeshark-expander-content-wrap',
                contentBlocks: '.themeshark-expander-content-block',
                frames: '.themeshark-expander-frame',
                innerWrap: '.themeshark-expander-inner',
            },
        }
    }

    getDefaultElements() {
        const selectors = this.getSettings('selectors')
        return {
            $expander: this.$element.find(selectors.expander),
            $contentWrap: this.$element.find(selectors.contentWrap),
            $contentBlocks: this.$element.find(selectors.contentBlocks),
            $frames: this.$element.find(selectors.frames),
            $innerWrap: this.$element.find(selectors.innerWrap),
        }
    }

    bindEvents() {
        this.bgVideoBindEvents()
        this.addResizeListener() // updates frame sizes
        this.addScrollListener() // activates slides when scrolling

        // EDIT MODE ONLY
        if (!this.isEdit) return
        this.bindEventsEditMode()
    }

    //updates frame sizes on window resize
    addResizeListener() {
        const { $frames } = this.elements
        const onResize = () => this.setFrameSize($frames)
        window.addEventListener('resize', onResize)
    }

    //sets size of frames (visible when expander is inactive)
    setFrameSize($frames) {
        const { getCurrentDeviceSetting } = themeshark.themesharkFrontend
        const $framesTopBottom = $frames.filter('.ts-frame-top, .ts-frame-bottom') // adjust height
        const $framesLeftRight = $frames.filter('.ts-frame-left, .ts-frame-right') // adjust width

        //$innerWrap holds frame dimensions json and used to compare height if set in %
        const $innerWrap = $frames.parent()
        const parentWidth = $innerWrap.width()
        const parentHeight = $innerWrap.height()

        //get dimensions for current device mode
        const deviceDimensions = JSON.parse($innerWrap.attr('data-framedimensions'))
        const framewidth = getCurrentDeviceSetting(deviceDimensions, 'frame_inner_width')
        const frameheight = getCurrentDeviceSetting(deviceDimensions, 'frame_inner_height')

        //ensures the dimensions are measured in pixels
        const getSizeAsPx = (sizeUnitString, percentOfSize) => {
            const { size, unit } = themeshark.parseSizeUnit(sizeUnitString)
            return unit === '%' ? size / 100 * percentOfSize : size
        }

        //dimensions of the space between the frames
        const innerFrameWidth = getSizeAsPx(framewidth, parentWidth)
        const innerFrameHeight = getSizeAsPx(frameheight, parentHeight)

        //new dimensions of the frames - width/height of parent less the empty space in middle
        const newLeftRightHeight = (parentWidth - innerFrameWidth) / 2
        const newTopBottomHeight = (parentHeight - innerFrameHeight) / 2

        $framesLeftRight.width(`${newLeftRightHeight}px`)
        $framesTopBottom.height(`${newTopBottomHeight}px`)
    }

    // adds scrollObserver
    addScrollListener() {
        const { $contentBlocks } = this.elements

        //classes added to active/inactive slides
        const activeClass = 'active'
        const aboveClass = 'ts-expander-above'
        const belowClass = 'ts-expander-below'

        const pauseOnDeintersect = this.hasBgVideo() && this.$element.hasClass('video-pause-on-deintersect')

        //percent = scroll depth
        function getSlideNumAtPercent(percent, $slides) {
            const interval = 100 / $slides.length
            return Math.floor(percent * 100 / interval)
        }

        //slide BG is separate element than the slide content
        const getSlideBG = $slideEl => {
            let slideId = $slideEl[0].dataset.slide
            return jQuery(`[data-bgid='${slideId}']`)[0]
        }

        //returns jQuery element of data-slide_num=slideNum
        const getSlideByNumber = slideNum => {
            return $contentBlocks.filter(`[data-slide_num='${slideNum}']`)
        }

        //removes inactive and adds active classes to both $slideEl and its background el
        function activateSlide($slideEl) {
            $slideEl.addClass(activeClass).removeClass(aboveClass).removeClass(belowClass)
            const slideBG = getSlideBG($slideEl)
            if (slideBG) slideBG.classList.add('active')
        }

        //removes active and adds inactive class (inactive class changes depending on if it's above or below active slide)
        function deactivateSlide($slideEl, inactiveClass = null) {
            $slideEl.removeClass(activeClass).removeClass(aboveClass).removeClass(belowClass).addClass(inactiveClass)
            const slideBG = getSlideBG($slideEl) //deactivate the slide bg element too
            if (slideBG) slideBG.classList.remove('active')
        }

        //activates slide num provided and deactivates all others with above or below inactive classes
        function setActiveSlideByNumber(slideNum) {
            const $targSlide = getSlideByNumber(slideNum)
            if (!$targSlide[0] || $targSlide.hasClass('active')) return
            activateSlide($targSlide)

            //deactivate all other slides
            for (let slide of $contentBlocks) {
                if (slide === $targSlide[0]) continue

                let $slide = jQuery(slide)
                let thisSlideNum = parseInt(slide.dataset.slide_num)

                //if inactive slide comes before active slide, receives aboveClass, else belowClass
                if (thisSlideNum < slideNum) deactivateSlide($slide, aboveClass)
                if (thisSlideNum > slideNum) deactivateSlide($slide, belowClass)
            }
        }

        //adjusts to check % completion top of vp is from exiting (instead of bottom enter to top exit)
        function adjustScrollPercent(entry, percentScrolled) {
            const expanderHeight = jQuery(entry.target).height()
            const calculatedHeight = expanderHeight + innerHeight // from enters bottom to exits top vp
            const distanceScrolled = percentScrolled * calculatedHeight // px scrolled since bottom enters
            const adjustedDistanceScrolled = distanceScrolled - innerHeight // px scrolled since top enters
            const adjustedPercent = adjustedDistanceScrolled / (expanderHeight - innerHeight) // % top is from exiting
            return adjustedPercent
        }

        //activates slide based on scroll depth. activateSlideNumber arg used in editor to force specific slide
        const _this = this
        function onIntersecting(entry, percentScrolled, activateSlideNumber = false) {
            percentScrolled = adjustScrollPercent(entry, percentScrolled)
            const element = entry.target || _this.elements.$expander[0]

            //if setting specific slide in edit mode, set that slide and return if locked
            if (themeshark.isEditMode) {
                const setActiveSlideFunction = activateSlideNumber === false
                    ? null : () => setActiveSlideByNumber(activateSlideNumber)

                const isLocked = _this.onIntersectingEditMode(_this, element, setActiveSlideFunction)
                if (isLocked) return
            }

            //if deintersecting from above
            if (percentScrolled < 0) {
                if (pauseOnDeintersect && !_this.bgVideoIsPaused) _this.bgVideoPause()
                onDeintersect(entry, percentScrolled)
                return
            }

            //ensure has scroll class
            const cl = element.classList
            if (!cl.contains('themeshark-scrolled')) {
                if (pauseOnDeintersect && _this.bgVideoIsPaused) _this.bgVideoPlay()
                cl.add('themeshark-scrolled')
            }

            //remove 100vh from percent scrolled to measure depth from bottom of window & activate slide at that depth
            const targetSlideNum = getSlideNumAtPercent(percentScrolled, $contentBlocks)
            setActiveSlideByNumber(targetSlideNum)
        }

        // deactivate slides if deintersecting on top (but not on bottom)
        function onDeintersect(entry, percentScrolled) {
            const cl = entry.target.classList
            if (percentScrolled <= 0) {
                if (cl.contains('themeshark-scrolled')) {
                    cl.remove('themeshark-scrolled')
                    deactivateSlide($contentBlocks, belowClass)
                }
            }
            else cl.add('themeshark-scrolled')
        }

        //create expander scroll observer.
        themesharkFrontend.getOrSetWidgetScrollObserver(this, 'scrollObserver', {
            targetElements: this.$element[0],
            intersectSettings: {
                onIntersecting: onIntersecting,
                onDeintersect: onDeintersect,
                throttle: 50 // max out firing scroll listener every 50 ms
            }
        })

        //pause video background if deintersecting and pauseondeintersect is true
        if (this.hasBgVideo()) {
            this.addBgVideoReadyHandler(() => {
                if (!_this.$element.hasClass('themeshark-scrolled') && pauseOnDeintersect) {
                    this.bgVideoPause()
                }
            })
        }
    }

    //functions defined in edit mode
    onIntersectingEditMode() { }
    bindEventsEditMode() { }
    addRenderListener() { }
    preventedRetrigger() { }
    handleCurrentSection() { }
    onElementChange() { }
}


themesharkFrontend.addInitCallback(function () {
    themesharkFrontend.addBackgroundVideoFunctions(TSExpanderHandler)
    if (themeshark.isEditMode) {
        themeshark.themesharkControlsHandler.widgetHandlerEditorFunctions.addExpanderEditorFunctions(TSExpanderHandler)
    }
    themesharkFrontend.addWidgetHandler('ts-expander', TSExpanderHandler)
})