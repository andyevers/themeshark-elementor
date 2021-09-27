/**
 * Adds scrolling animations to widgets
 */
jQuery(window).on('elementor/frontend/init', () => {

    //----------- GLOBALS ------------//
    //classes given when the element is animated/deanimated
    const INACTIVE_CLASSES = ['elementor-invisible', 'themeshark-invisible']
    const ACTIVE_CLASSES = ['animated'] //also includes the selected animation name
    const ANIMATING_CLASS = 'animating'
    const GLOBAL_OBSERVER_KEY = 'globalScrollObserver'
    const ONCHANGE_EVENT_CONTROL_KEYS = [ // onControlsChange fires when any of these controls change in the editor
        '_animation',
        'animation',
        'animation_duration',
        '_animation_duration',
        'animation_delay',
        '_animation_delay',
        'animation_repeat',
        '_animation_repeat',
        'animation_threshold',
        '_animation_threshold',
        'animation_observed_element',
        '_animation_observed_element'
    ]
    //----------- /GLOBALS ------------//

    //Create global scroll observer immediately if in edit mode
    if (themeshark.isEditMode) createObserverIfNotExists()

    // checks for scroll animations for each widget/section/column element when it loads
    elementorFrontend.hooks.addAction('frontend/element_ready/global', onElementReady)


    /**
     * Fires when the element first loads
     * @param {Object} $element widget/section/column element
     */
    function onElementReady($element) {

        const element = $element[0]

        // destroyAnimationWaypoints() //TODO: find a better way of doing this rather than removing them every time they are added

        // EDITOR ONLY
        if (themeshark.isEditMode) {
            const hasElContainer = themesharkFrontend.getElementContainer(element) !== null
            if (!hasElContainer) return
            // adds onchange listeners when any of the provided keys change
            themesharkFrontend.onElementControlsChange(element, ONCHANGE_EVENT_CONTROL_KEYS, model => {
                onControlsChange(element, model)
            })
        }

        const animation = getElementSettings(element, 'animation')
        const hasAnimation = animation && animation !== 'none'

        //destroy waypoint if has animation threshold
        const hasAnimationThreshold = Object.keys(getElementSettings(element))
            .some(k => ['animation_threshold', '_animation_threshold'].includes(k))

        if (hasAnimationThreshold) destroyElementWaypoints(element)

        // Fires on frontend - makes sure scroll observer exists as long as 1 element has animation
        if (!themeshark.isEditMode && hasAnimation) {
            createObserverIfNotExists()
        }

        // starts observing element if it has an animation
        if (hasAnimation) {
            const scrollObserver = getGlobalObserver()
            let observedElement = getObservedElement(element)
            ensureElementThreshold(scrollObserver, element) // makes sure scroll observer is watching the element's threshold
            scrollObserver.observe(observedElement)

            //stop observing when widget is deleted
            if (themeshark.isEditMode) {
                const elId = element.dataset.id
                themesharkFrontend.addOnceDestroyListener(element, `${elId}_animationOnDestroy`, (model) => {
                    unobserveElement(element)
                })
            }
        }
    }



    /**
     * EDITOR ONLY Fires when controls for the element change
     * @param {HTMLElement} element widget/section/column main element
     */
    function onControlsChange(element, model) {
        const scrollObserver = getGlobalObserver()

        // removes element from scroll observer if it is no longer the observed element or in the DOM
        checkObservedElement(element, model)
        // destroyAnimationWaypoints()
        destroyElementWaypoints(element)

        ensureElementThreshold(scrollObserver, element)

        const observedElement = getObservedElement(element)
        setInitialObserveState(scrollObserver, observedElement)
    }


    /**
     * makes sure the threshold for the element is set in the intersect settings threshold array in the scroll observer
     * @param {*} scrollObserver global scroll observer
     * @param {*} element onReady element
     */
    function ensureElementThreshold(scrollObserver, element) {
        const animationThreshold = parseFloat(getElementSettings(element, 'animation_threshold'))
        const currentThresholds = scrollObserver.intersectSettings.thresholds
        if (!animationThreshold) return

        // add threshold to observer if not already present
        let updatedThresholds = [animationThreshold]
        if (!currentThresholds.includes(animationThreshold)) {
            updatedThresholds = updatedThresholds.concat(currentThresholds)
            scrollObserver.updateIntersectSettings({
                thresholds: updatedThresholds
            })
        }
    }


    /**
     * Returns the global scroll observer
     * @returns {Object} global scroll observer
     */
    function getGlobalObserver() {
        return themeshark.themesharkFrontend.getStoredValue(GLOBAL_OBSERVER_KEY)
    }


    /**
     * Stops observing element for scroll events
     * @param {HTMLElement} element element observed by the scroll observer
     */
    function unobserveElement(element) {
        const scrollObserver = getGlobalObserver()
        if (!scrollObserver) return
        const hasObservedElements = scrollObserver.observedElements.length > 0
        scrollObserver.unobserve(element)

        // remove scroll observer if not in editor and no more elements being observed
        if (!themeshark.isEditMode && !hasObservedElements) {
            scrollObserver.disconnect()
            themesharkFrontend.deleteStoredValue(GLOBAL_OBSERVER_KEY)
        }
    }


    /**
     * EDITOR ONLY Unobserve elements that were removed from DOM
     * @param {HTMLElement} element element being observed
     * @param {ElementModel} model Backbone.js model for element
     */
    function checkObservedElement(element, model) {
        const scrollObserver = getGlobalObserver()

        // if changing observed element, remove it from the scroll observer
        if (Object.keys(model.changed).includes('animation_observed_element')) {
            // check to see if observed element main container data-id's are the same to see if it's a clone, 
            // or remove if it doesn't have a main element
            const mainId = getMainElement(element).dataset.id
            scrollObserver.observedElements.forEach(el => {
                let foundMainEl = getMainElement(el)
                let foundDataId = foundMainEl?.dataset.id
                if (foundDataId === mainId || !foundMainEl) {
                    unobserveElement(el)
                }
            })
        }
    }


    const _FALLBACK_ELEMENTS = []

    /**
     * If element has 'animation_observed_element' in data-settings, Returns the child element of the selector provided. otherwise returns element given.
     * @param {HTMLElement} element 
     * @returns {HTMLElement}
     */
    function getObservedElement(element) {
        const observedElementSelector = getElementSettings(element, 'animation_observed_element')
        if (!observedElementSelector || observedElementSelector.length === 0) {
            return element
        }

        if (themeshark.isEditMode && getElementSettings(element, 'animation_advanced_popover') !== 'yes') {
            return element
        }

        let observedElement = element.querySelector(observedElementSelector)
        if (!observedElement) {
            if (!_FALLBACK_ELEMENTS.includes(element)) {
                console.error(`could not find element to observe with selector: ${observedElementSelector}, fallback to using widget:`, element)
                _FALLBACK_ELEMENTS.push(element)
            }
            observedElement = element
        }
        return observedElement
    }

    function getWaypoints() {
        const waypoints = Waypoint.Context.findByElement(window)?.waypoints
        if (!waypoints) return null
        const mergedWaypoints = Object.values({ ...waypoints.vertical, ...waypoints.horizontal })
        return mergedWaypoints
    }

    /**
     * 
     * @param {Element} element 
     * @param {Boolean} onlyThemesharkAnimations if true, only destroys waypoint if has class "themeshark-observed-animation"
     */
    function destroyElementWaypoints(element, onlyThemesharkAnimations = true) {
        const isThemesharkAnimation = element.classList.contains('themeshark-observed-animation')
        if (onlyThemesharkAnimations && !isThemesharkAnimation) return

        const waypoints = getWaypoints()
        if (!waypoints) return

        const elementWaypoints = waypoints.filter(wp => wp.element === element)
        if (!elementWaypoints || elementWaypoints.length < 1) return

        elementWaypoints.forEach(wp => wp.destroy())
    }

    /**
     * Finds elementor waypoints (Waypoints.js) for themeshark scroll animations and destroys (using themeshark scroll observer instead)
     * @returns void
     */
    function destroyAnimationWaypoints() {
        const waypoints = getWaypoints()
        if (!waypoints) return
        for (let waypoint of waypoints) {
            //destroy waypoints with themeshark custom animations
            let isThemesharkAnimation = waypoint.element.classList.contains('themeshark-observed-animation')
            if (isThemesharkAnimation) waypoint.destroy()
        }
    }


    /**
     * creates scroll observer and adds it as a stored value if it doesn't exists
     * @returns void
     */
    function createObserverIfNotExists() {
        // console.log('creating')
        if (themesharkFrontend.getStoredValue(GLOBAL_OBSERVER_KEY)) return
        themesharkFrontend.getOrSetStoredReturnValue(GLOBAL_OBSERVER_KEY, () => {
            return createGlobalScrollObserver()
        })
    }


    /**
     * creates scroll observer to watch for all scroll animations
     * @returns void
     */
    function createGlobalScrollObserver() {
        // console.log('creating')
        // fires when scroll observers thresholds are crossed for an element
        const onIntersect = entry => {
            const element = getMainElement(entry.target)
            if (!element) return
            if (jQuery(element).hasClass(ANIMATING_CLASS)) return // return if in the middle of animation

            const observer = getGlobalObserver()
            const isRemoved = !element || !document.querySelector(`[data-element_type][data-id='${element.dataset.id}']`)
            if (isRemoved) { //TODO: sometimes elements do not get removed when checkObservedElements() fires. 
                // this removes them on scroll if still present. get them to be unobserved when they are removed from DOM
                observer.unobserve(entry.target)
                return
            }
            const {
                animation_threshold = 0,
                animation_repeat,
                animation
            } = getElementSettings(element, ['animation_threshold', 'animation_repeat', 'animation'])

            //animate if past threshold
            if (entry.intersectionRatio >= parseFloat(animation_threshold)) animate(element)

            //stop observing if animation_repeat is not turned on
            const isEnded = animation_repeat !== 'yes' || animation === 'none' || !animation
            if (isEnded) {
                const isInvisible = element.classList.contains('elementor-invisible')
                if (isInvisible) animate(jQuery(element))
                observer.unobserve(entry.target)
            }
        }

        //fires when scrolled out of view
        const onDeintersect = entry => {
            const element = getMainElement(entry.target)
            if (!element) return
            const $element = jQuery(element)
            const animation_repeat = getElementSettings(element, 'animation_repeat')

            if ($element.hasClass(ANIMATING_CLASS)) return
            if ($element.hasClass(ACTIVE_CLASSES[0]) && animation_repeat !== 'yes') unobserveElement(entry.target)
            else deanimate(element)

            removeCurrentAnimation(element)
        }

        // console.log('creating')
        const globalScrollObserver = themesharkFrontend.getOrSetScrollObserver(GLOBAL_OBSERVER_KEY, {
            intersectSettings: {
                marginTop: themeshark.adminBarHeight,
                onIntersect: onIntersect,
                onDeintersect: onDeintersect,
            },
        })


        return globalScrollObserver
    }


    /**
     * Activates the animation.
     * @param {HTMLElement} element the main element (not necessarily the observed element)
     */
    function animate(element) {
        const $element = jQuery(element)

        //return if it is already animated
        if (!$element.hasClass(INACTIVE_CLASSES[0])) return

        //get animation settings
        const {
            animation,
            animation_delay,
            animation_repeat,
        } = getElementSettings($element[0], ['animation', 'animation_delay', 'animation_repeat'])

        //remove invisible class and return if animation is set to 'none'
        const removeInactiveClasses = () => INACTIVE_CLASSES.forEach(c => $element.removeClass(c))
        const addActiveClasses = () => ACTIVE_CLASSES.forEach(c => $element.addClass(c))

        // show element if animation is 'none'
        const hasAnimation = animation && animation !== 'none'
        if (!hasAnimation) return removeInactiveClasses()

        //remove old animation
        removeCurrentAnimation($element)
        setCurrentAnimation($element, animation)
        $element.removeClass(animation)

        const animationDuration = parseFloat(getComputedStyle($element[0])
            .getPropertyValue('--animation-duration')) * 1000 || 0

        //activate the animation
        setTimeout(() => {
            removeInactiveClasses()
            addActiveClasses()

            const scrollObserver = getGlobalObserver()
            const observedElement = getObservedElement($element[0])

            const endAnimation = (e) => {
                $element.removeClass(ANIMATING_CLASS)

                if (!scrollObserver) return //if all page animations have ended and scroll observer removed

                const isIntersecting = scrollObserver.isIntersecting(observedElement)
                const isRepeat = animation_repeat && animation_repeat === 'yes'

                // stop observing unless is repeating animation 
                if (!isRepeat) unobserveElement(observedElement)
                if (isRepeat && !isIntersecting) deanimate($element[0]) // deanimate if not in view 
            }

            // const animationEnd = 'animationend webkitAnimationEnd oAnimationEnd'
            // $element.on(animationEnd, endAnimation)
            $element.addClass(animation).addClass(ANIMATING_CLASS)
            setTimeout(endAnimation, animationDuration)

        }, animation_delay)
    }


    /**
     * Used to reset animation when scrolling out of view
     * @param {HTMLElement} element 
     */
    function deanimate(element) {
        const $element = jQuery(element)
        INACTIVE_CLASSES.forEach(c => $element.addClass(c))
        ACTIVE_CLASSES.forEach(c => $element.removeClass(c))
    }


    /**
     * removes the class for the last used animation
     * @param {*} element main element
     */
    function removeCurrentAnimation(element) {
        //current animation provides access to the last set animation so the class can be removed
        function getCurrentAnimation(element) {
            const $element = jQuery(element)
            const currentAnimationKey = `_current_animation_${$element.data('id')}`
            const currentAnimation = themesharkFrontend.getStoredValue(currentAnimationKey)
            return currentAnimation
        }

        const currentAnimation = getCurrentAnimation(element)
        jQuery(element).removeClass(currentAnimation)
    }


    /**
     * allows animation class to be accessed and removed later
     * @param {*} element main element
     * @param {String} animation name of animation
     */
    function setCurrentAnimation(element, animation) {
        const $element = jQuery(element)
        const currentAnimationKey = `_current_animation_${$element.data('id')}`
        themesharkFrontend.setStoredValue(currentAnimationKey, animation)
    }


    /**
     * Sets the observe state of the element when it first loads & adds on destroy listener to main element
     * @param {*} scrollObserver global scroll observer
     * @param {HTMLElement} element observedElement
     */
    function setInitialObserveState(scrollObserver, element) {
        //observe / unobserve based on if element has animation settings
        const mainElement = getMainElement(element)
        themesharkFrontend.addOnceDestroyListener(mainElement, 'unobserveOnDestroy', () => {
            scrollObserver.unobserve(element)
        })

        const animation = getElementSettings(mainElement, 'animation')
        const hasAnimation = animation && animation !== 'none'
        const isObservingElement = scrollObserver.isObservingElement(element)

        if (!hasAnimation && isObservingElement) scrollObserver.unobserve(element)
        else if (hasAnimation && !isObservingElement) scrollObserver.observe(element)
    }


    /**
     * Returns animation settings for the provided element. Gets data-settings or control settings if in editor
     * @param {HTMLElement} element 
     * @param {Array} keys if keys provided, returns value for each key as object
     * @returns {Object|Mixed} object if multiple keys requested
     */
    function getElementSettings(element, settingKeys = null, format = true) {
        // pulled from elementor frontend.js. gets settings for a specified device mode (screen size)
        //TODO: Moved to themesharkFrontend
        function getCurrentDeviceSetting(settings, settingKey) {
            //pulled from elementor frontend.js

            function getDeviceSetting(deviceMode, settings, settingKey) {
                var devices = ['desktop', 'tablet', 'mobile']
                var deviceIndex = devices.indexOf(deviceMode)
                while (deviceIndex > 0) {
                    var currentDevice = devices[deviceIndex],
                        fullSettingKey = settingKey + '_' + currentDevice,
                        deviceValue = settings[fullSettingKey]

                    if (deviceValue) return deviceValue
                    deviceIndex--
                }
                return settings[settingKey]
            }

            return getDeviceSetting(elementorFrontend.getCurrentDeviceMode(), settings, settingKey);
        }

        // adds '_' before setting key if it is used in widgets and it has a custom animation
        function getAnimationSettingKey(element, settingKey) {

            const elType = element.dataset.element_type
            const settings = getElementSettings(element)
            const isWidget = elType !== 'section' && elType !== 'column'

            if (isWidget) {
                if (!settings) return settingKey
                //animation_duration is flipped. prefixed if using widget custom duration
                else settingKey = settings.animation ? settingKey : `_${settingKey}`
            }
            return settingKey
        }

        //used for formatting value
        function extractDeviceSetting(element, settings, settingKey) {
            const formattedSettingKey = getAnimationSettingKey(element, settingKey)
            const curDeviceSetting = getCurrentDeviceSetting(settings, formattedSettingKey)
            return curDeviceSetting
        }

        // In edit mode, data-settings are not present, so use the control settings instead.
        let settings = null
        if (themeshark.isEditMode) {
            const containerObject = themesharkFrontend.getElementContainer(element)
            // if (!containerObject) return console.error('cant get container object for element:', element)
            if (!containerObject) return null // happens if container has been deleted
            settings = containerObject.settings.attributes
        }
        else {
            settings = jQuery(element).data('settings')
        }

        if (!settings) {
            if (typeof settingKeys === 'string') return null
            else return {}
        }

        // return all settings if no specific setting requsted
        if (!settingKeys) return settings

        const getSetting = key => format ? extractDeviceSetting(element, settings, key) : settings[key]

        // if single setting requested
        if (typeof settingKeys === 'string') return getSetting(settingKeys)

        //if multiple settings requested
        const targetSettings = {}
        settingKeys.forEach(key => {
            if (format) targetSettings[key] = extractDeviceSetting(element, settings, key)
            else targetSettings[key] = settings[key]
        })
        return targetSettings
    }


    /**
     * Returns the parent main element if the provided element is not the main one for the widget/section. otherwise returns the provided element
     * @param {HTMLElement} element 
     * @returns {HTMLElement}
     */
    function getMainElement(element) {
        const $element = jQuery(element)
        const mainElement = $element.data('element_type') ? element : $element.closest('[data-element_type]')[0]
        return mainElement
    }
})