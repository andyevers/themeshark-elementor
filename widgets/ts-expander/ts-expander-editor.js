jQuery(window).on('elementor:init', () => {
    themesharkControlsHandler.widgetHandlerEditorFunctions.addExpanderEditorFunctions = expanderHandler => {

        expanderHandler.prototype.bindEventsEditMode = function () {
            themeshark.themesharkFrontend.setWidgetStoredValue(this, 'renderStart', true)

            this.addRenderListener() // checks if render was fired & prevents rerender
            const _this = this

            setTimeout(() => {
                _this.elements.$expander.removeClass('ts-expander-rendering').removeClass('themeshark-no-transition')
                themeshark.themesharkFrontend.setWidgetStoredValue(_this, 'handleCurrentSection', () => _this.handleCurrentSection())//allow use outside handler

                //check if new slide was added & activate it
                const activateNewSlide = themeshark.themesharkFrontend.getWidgetStoredValue(_this, 'activateNewSlide')
                if (activateNewSlide) {
                    activateNewSlide()
                    themeshark.themesharkFrontend.deleteWidgetStoredValue(_this, 'activateNewSlide')
                }

                //if expander is active but no slides are visible, fire handleCurrentSection to make one visible
                const isSlideNotVisible = () => {
                    const { editedElementView } = themeshark.themesharkControlsHandler
                    const $expander = editedElementView ? editedElementView.$el : _this.elements.$expander
                    return $expander.hasClass('themeshark-scrolled') && !$expander.find('[data-slide_num].active')[0]
                }

                //if slide still not visible while active after handling section, remove scrolled class
                if (isSlideNotVisible()) _this.handleCurrentSection()
                if (isSlideNotVisible()) _this.$element.removeClass('themeshark-scrolled')
            })

            //delete all stored values on destroy
            const destroyKey = themeshark.themesharkFrontend.getWidgetKey(this, 'destroyRemoveKeys')
            themeshark.themesharkFrontend.addOnceDestroyListener(this.$element[0], destroyKey, () => {
                themeshark.themesharkFrontend.deleteAllWidgetStoredValues(this)
            })
        }

        //used to check if widget has rendered. if not, fires render using ensureRender() (defined in control handlers below)
        expanderHandler.prototype.addRenderListener = function () {
            // ensure this listener isn't already added
            if (themeshark.themesharkFrontend.getWidgetStoredValue(this, 'hasRenderListener')) return
            const container = elementor.getContainer(this.$element.data('id'))
            if (!container) return

            // notifies that render has fired, so it doesn't get forced again by the ensureRender() timeout 
            container.view.on('render', () => { themeshark.themesharkFrontend.deleteWidgetStoredValue(this, 'waitingRender') })
            themeshark.themesharkFrontend.setWidgetStoredValue(this, 'hasRenderListener', true)
        }

        //stops script from continuing if it just ran this function within the timeout
        expanderHandler.prototype.preventedRetrigger = function (key = 'expanderPreventTrigger', removeSecondCheck = true, timeoutDuration = 20) {
            let timeout = themeshark.themesharkFrontend.getWidgetStoredValue(this, key)
            if (timeout) {
                if (removeSecondCheck) {
                    clearTimeout(timeout)
                    themeshark.themesharkFrontend.deleteWidgetStoredValue(this, key)
                }
                return true
            }

            timeout = setTimeout(() => { themeshark.themesharkFrontend.deleteWidgetStoredValue(this, key) }, timeoutDuration)
            return false
        }

        expanderHandler.prototype.handleCurrentSection = function (controlName = null) {
            //avoid triggering twice
            if (this.preventedRetrigger()) return

            //make sure there is a section active
            const { currentSectionName } = themeshark.themesharkControlsHandler
            if (!currentSectionName) return

            const scrollObserver = themeshark.themesharkControlsHandler.getEditedElementStoredValue('scrollObserver')
            const $expander = jQuery(scrollObserver.observedElements[0])
            if ($expander.hasClass('ts-expander-rendering')) return //exit if rendering

            // sections names that require frames to be active / inactive
            const frameSections = ['section_heading', 'frame_styles', 'section_slides_background', 'section_heading_styles']
            const noFrameSections = ['section_slide_styles']

            //controls in frame section but require noFrame visibility
            const frameControlExceptions = ['background_overlay_color_after', 'bg_overlay_opacity_after']

            // forces deintersect top to show frames
            function showFrames() {
                const pseudoEntry = scrollObserver.getPseudoEntry($expander[0])
                scrollObserver.intersectSettings.onDeintersect(pseudoEntry, 0)
                $expander.removeClass('themeshark-scrolled')
            }

            // shows slide at current depth, otherwise forces show first slide if not intersecting
            function hideFrames() {
                scrollObserver.trigger()
                if ($expander.hasClass('themeshark-scrolled')) return
                scrollObserver.intersectSettings.onIntersecting(scrollObserver.observedElements[0], null, 0)
                $expander.addClass('themeshark-scrolled')
            }

            //return if the control is an exception to the section visibility requirements (background "after active" tab controls)
            if (frameControlExceptions.includes(controlName)) {
                //editor-hide-slides shows active state but hides individual slides
                $expander.addClass('editor-hide-slides').addClass('themeshark-scrolled')
                return
            } else $expander.removeClass('editor-hide-slides')


            // choose which state to activate based on active section
            if (frameSections.includes(currentSectionName)) showFrames()
            else if (noFrameSections.includes(currentSectionName)) hideFrames()
            else scrollObserver.trigger()
        }

        // fires when any control changes in edit mode
        expanderHandler.prototype.onElementChange = function (controlName, controlView) {
            //check required slide state for the edited control
            this.handleCurrentSection(controlName)
            this.bgVideoOnElementChange(controlName)

            //if frame dimension controls - update frame size
            if (!(controlName.match('frame_inner_width') || controlName.match('frame_inner_height'))) return
            const controlValue = controlView.getControlValue()
            const dimensions = JSON.parse(this.elements.$innerWrap.attr('data-framedimensions'))

            //sets the new data-framedimensions of the frames parent (innerWrap)
            dimensions[controlName] = controlValue.size + controlValue.unit
            this.elements.$innerWrap.attr('data-framedimensions', JSON.stringify(dimensions))
            this.setFrameSize(this.elements.$frames)
        }

        expanderHandler.prototype.onIntersectingEditMode = function (_this, element, setActiveSlideFunction = null) {
            //editor-hide-slides hides frames and shows active state but hides slides
            if (element.classList.contains('editor-hide-slides')) element.classList.remove('editor-hide-slides')
            if (setActiveSlideFunction !== null) setActiveSlideFunction()

            let renderStart = themeshark.themesharkFrontend.getWidgetStoredValue(_this, 'renderStart')
            if (renderStart) {
                const { currentSectionName } = themeshark.themesharkControlsHandler
                const frameSections = [
                    'section_heading',
                    'frame_styles',
                    'section_slides_background',
                    'section_heading_styles'
                ]
                const sectionFramedUnscrolled = frameSections.includes(currentSectionName)
                    && !element.classList.contains('themeshark-scrolled')

                themeshark.themesharkFrontend.setWidgetStoredValue(_this, 'renderStart', false)
                if (sectionFramedUnscrolled) return true
            }

            if (setActiveSlideFunction !== null) return true

            const scrollLocked = themeshark.themesharkFrontend.getWidgetStoredValue(_this, 'lockScroll')
            if (scrollLocked) return true
            return false
        }
    }
})

//-- SECTION HANDLER --//
// When section changes, set the current slide to the correct one given the scroll depth
themesharkControlsHandler.addWidgetSectionHandler('ts-expander', 'expander_handle_section', function (controlsHandler) {
    const handleCurrentSection = controlsHandler.getEditedElementStoredValue('handleCurrentSection')
    if (handleCurrentSection) handleCurrentSection()
})

//-- BACKGROUND TABS HANDLER --//
//Fires when either "Before Active" or "After Active" tab is clicked inside the "Background" section. 
//hides frames when "After Active" is clicked 
themesharkControlsHandler.addHandler('expander_handle_bg_state_tabs', function (controlsHandler, controlView, value) {
    controlView.on('control:tab:clicked', function () {
        const $expander = controlsHandler.editedElementView.$el
        if (value === 'show_frames') $expander.removeClass('themeshark-scrolled')
        if (value === 'hide_frames') $expander.addClass('themeshark-scrolled').addClass('editor-hide-slides')
    })
})

//-- REPEATER CONTROLS HANDLER --//  
// When a slide tab is clicked on or a control within a slide is edited, activates the edited slide
themesharkControlsHandler.addHandler('expander_handle_repeater_controls', function (controlsHandler, controlView) {

    controlView.on('childview:childview:settings:change', activateTabSlide) // on slide settings change
    controlView.on('childview:click:edit', onTabClick)
    controlView.on('remove:child', onRemoveSlide)
    controlView.on('add:child', onAddSlide)

    //activates clicked tab if expanded, otherwise activates tab for current scroll depth
    function onTabClick(view) {
        unlockScroll()
        const isExpanded = view.$childViewContainer.hasClass('editable')
        if (!isExpanded) {
            const handleCurrentSection = controlsHandler.getEditedElementStoredValue('handleCurrentSection')
            if (handleCurrentSection) handleCurrentSection()
        }
        else activateTabSlide(view)
    }

    //activates the new slide during next render
    function onAddSlide(view) {
        controlsHandler.setEditedElementStoredValue('activateNewSlide', () => activateTabSlide(view))
        ensureRender(view)
        lockScroll()
    }

    //ensure render on remove slide
    function onRemoveSlide(view) {
        unlockScroll()
        ensureRender(view)
    }

    // if expander doesn't render after change, force render
    function ensureRender(view) {
        controlsHandler.setEditedElementStoredValue('waitingRender', true)
        setTimeout(() => {
            const isWaitingRender = controlsHandler.getEditedElementStoredValue('waitingRender')
            if (isWaitingRender === true) {
                const container = controlsHandler.editedElementView.getOption('container')
                if (container.view.isDestroyed) return
                container.view.render()
            }
        })
    }

    //activates slide that the current control is for
    function activateTabSlide(view) {
        lockScroll()
        const scrollObserver = controlsHandler.getEditedElementStoredValue('scrollObserver')
        if (!scrollObserver) return

        // gets expander from scroll observer
        const tsExpander = scrollObserver.observedElements[0]
        const { onIntersecting } = scrollObserver.intersectSettings
        const $tsExpander = jQuery(tsExpander)

        //get current slide repeater id and activates slide el using the id
        const slideId = controlsHandler.getRepeaterSlideId(view)
        const controlSlideNum = $tsExpander.find(`[data-slide='${slideId}']`).data('slide_num')

        //fires onIntersecting requesting specific slide to be active
        onIntersecting(scrollObserver.getPseudoEntry(tsExpander), null, controlSlideNum)
        $tsExpander.addClass('themeshark-scrolled')
    }

    //prevents onIntersecting from activating current slide. used when adding new slides & editing slides
    function lockScroll() {
        let scrollLockListener = themeshark.themesharkControlsHandler.getEditedElementStoredValue('scrollLockListener')
        if (!scrollLockListener) {

            scrollLockListener = () => {
                const editedElementId = themeshark.themesharkControlsHandler.editedElementId
                const lockKey = themeshark.themesharkFrontend.getWidgetKeyById(editedElementId, 'lockScroll')
                const lockListener = themeshark.themesharkFrontend.getWidgetKeyById(editedElementId, 'scrollLockListener')

                themeshark.themesharkFrontend.deleteStoredValue(lockKey)
                themeshark.themesharkFrontend.deleteStoredValue(lockListener)
                elementor.$preview[0].contentWindow.removeEventListener('scroll', scrollLockListener)
            }

            elementor.$preview[0].contentWindow.addEventListener('scroll', scrollLockListener)
            themeshark.themesharkControlsHandler.setEditedElementStoredValue('scrollLockListener', scrollLockListener)
            themeshark.themesharkControlsHandler.setEditedElementStoredValue('lockScroll', true)
        }
    }

    function unlockScroll() {
        let scrollLockListener = themeshark.themesharkControlsHandler.getEditedElementStoredValue('scrollLockListener')
        elementor.$preview[0].contentWindow.removeEventListener('scroll', scrollLockListener)
        themeshark.themesharkControlsHandler.setEditedElementStoredValue('scrollLockListener', null)
        themeshark.themesharkControlsHandler.setEditedElementStoredValue('lockScroll', false)
    }
})