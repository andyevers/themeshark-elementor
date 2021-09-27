/* 
Author: Andrew Evers - ThemeShark & Brand Evolution Corporation
ThemeShark: https://www.themeshark.com
Brand Evolution: https://www.brandevolutionco.com
RenderHTML available for free use: https://github.com/andyevers/render-html/
*/
//TODO: 
//make observed elements and unobserved elements arrays, not targetElements, because they will not be in both at the same time
; (function (exports) {
    /**
    * @typedef {Object} IntersectSettingsObject
    * @property {String} marginTop [0] accepts string (px/%) or number - =rootMargin top. Distance the top scroll trigger is above top of the viewport
    * @property {String} marginBottom [0] accepts string (px/%) or number - =rootMargin bottom. Distance the bottom scroll trigger is below from bottom of the viewport
    * @property {Function} onIntersect(entry, percentScrolled) [null] - function that fires when intersect starts
    * @property {Function} onDeintersect(entry, percentScrolled) [null] - function that fires when intersect ends
    * @property {Function} onIntersecting(percentScrolled) [null] - function that fires on scroll while intersecting
    */

    /**
     * Adds an IntersectionObserver to trigger functions onIntersect, onIntersecting, onDeintersect, and onTriggerCrossover
     */
    exports.ScrollObserver = class ScrollObserver {
        /**
         * @param {Element|Array} targetElements The element that is observed by the IntersectionObserver
         * @param {IntersectSettingsObject} intersectSettings (marginTop, marginBottom, onIntersect, onDeintersect, onIntersecting) trigger positions & intersection functions
         * @param {Boolean} usePseudoObserver [false] Whether an IntersectionObserver or a window scroll event should be used to detect intersection
         * @param {Boolean} initOnConstruct [true] Whether init() should fire when creating an instance of ScrollObserver
         */
        constructor(targetElements = [], intersectSettings, observerSettings = {}) {
            this.targetElements = Array.isArray(targetElements) ? targetElements : [targetElements]
            this.observedElements = [] // holds all elements that are currently being observed
            this.unobservedElements = [] //NOT IN USE YET
            this.intersectionObserver = null // becomes instance of IntersectionObserver on _init
            this.isInitialized = false
            this.intersectSettings = intersectSettings

            const {
                usePseudoObserver = false,
                scrollListenerTarget = window, // what the scroll listener is applied to for onIntersecting and pseudoObserver
                throttlePseudoObserver = 50,
            } = observerSettings

            this.usePseudoObserver = usePseudoObserver
            this.scrollListenerTarget = scrollListenerTarget // element that the scroll listener is added to for the pseudo observer
            this.throttlePseudoObserver = throttlePseudoObserver // interval in ms the pseudo observer fires the scroll event listener while scrolling

            this._isObserving = false
            this._hasPseudoObserverListener = false
            this._hasScrollListener = false // true if the scroll event listener for the onIntersecting function is active
            this._intersectSettings = {}
            this._observerSettings = {}

            //________METHODS_________//

            /**
             * Intersection observer start observing the element
             * @param {Element} element 
             */
            this.observe = (element) => {
                this._verifyElement(element)
                if (this.isObservingElement(element)) return
                if (this.intersectionObserver) {
                    this.intersectionObserver.observe(element)
                    this.observedElements.push(element)
                }
                this._updateIsObserving()
            }

            this._verifyElement = (element) => {
                try { element.tagName }
                catch (err) { throw console.error(`ScrollObserver expected an element, but was given ${typeof element}:`, element, err) }
            }
            /**
             * Stops the IntersectionObserver from observing element
             * @param {Element} element 
             */
            this.unobserve = (element) => {
                if (this.intersectionObserver) {
                    this.intersectionObserver.unobserve(element)
                    if (!this.targetElements.includes(element)) this.targetElements.push(element)
                    this.observedElements = this._removeFromArray(this.observedElements, element)
                }
                this._updateIsObserving()
            }

            /**
             * Unobserves element and removes it from both observedElements and unobservedElements arrays
             * @param {Element} element 
             */
            this.forget = (element) => {
                this.unobserve(element)
                this.targetElements = this._removeFromArray(targetElements, element)
            }

            /**
             * Whether the Intersection observer is observing the element
             * @param {Element} element 
             */
            this.isObservingElement = (element) => {
                return this.observedElements.includes(element)
            }

            /**
             * Observes all unobserved elements
             */
            this.observeAll = () => {
                this.targetElements.forEach(element => {
                    if (!this.isObservingElement(element)) this.observe(element)
                })
            }

            /**
             * Unobserves all observed elements
             */
            this.unobserveAll = () => {
                this.observedElements.forEach(element => this.unobserve(element))
            }

            /**
             * Unobserves all elements and disconnects observer (removes scroll listener if pseudoObserver)
             */
            this.disconnect = () => {
                this.unobserveAll()
                this.intersectionObserver.disconnect()
                this._removeIntersectingListener(this._onIntersectingScroll)
            }


            /**
             * Gets % scrolled from when root bottom enters element to where root top exits the element where exiting the bottom = 1
             * @param {Element} element 
             * @param {Boolean} constrainRange whether to allow negatives and values > 1
             * @returns {Float}
             */
            this.getPercentScrolled = (element, constrainRange = true) => {
                this._verifyElement(element)

                const { rootBounds } = this

                const elementRect = element.getBoundingClientRect()
                const elementTopPageDepth = scrollY + elementRect.top
                const elementBottomPageDepth = scrollY + elementRect.bottom
                const rootTopPageDepth = scrollY + rootBounds.top

                const intersectStartPosition = elementTopPageDepth - rootBounds.height
                const intersectAreaHeight = elementBottomPageDepth - intersectStartPosition

                const distanceBelowElementTop = rootTopPageDepth - intersectStartPosition
                const percentScrolled = distanceBelowElementTop / intersectAreaHeight
                if (constrainRange) return percentScrolled > 1 ? 1 : percentScrolled < 0 ? 0 : percentScrolled
                else return percentScrolled
            }

            /**
             * gets entry for element that matches the properties of IntersectionObserverEntry
             * @returns {Object}
             */
            this.getPseudoEntry = (element) => {
                const getRectArea = rect => rect.width * rect.height
                const elementRect = element.getBoundingClientRect()
                const intersectionRect = this.getIntersectionRect(element)
                const isIntersecting = this.isIntersecting(element)
                let intersectionRatio = getRectArea(intersectionRect) / getRectArea(elementRect)

                // NaN when using pseudo observer and elementRect area is 0. if is intersecting or below intersect, set to 1, else 0
                if (isNaN(intersectionRatio)) {
                    const isBelow = this.rootBounds.top >= elementRect.top && isIntersecting === false
                    intersectionRatio = isBelow || isIntersecting ? 1 : 0
                }

                return {
                    boundingClientRect: elementRect,
                    intersectionRatio: intersectionRatio,
                    intersectionRect: intersectionRect,
                    isIntersecting: isIntersecting,
                    rootBounds: this.rootBounds,
                    target: element,
                    time: performance.now()
                }
            }

            /**
             * Disconnects current observer/removes scroll listener and creates a new one matching the previous observe state
             */
            this.resetObserver = () => {
                const { isObserving, _createObserver, observeAll, disconnect } = this
                if (isObserving) disconnect()
                this.intersectionObserver = _createObserver()
                if (isObserving) observeAll()
            }


            /**
             * changes the intersect settings for the ones provided and applies new settings
             * @param {IntersectSettingsObject} intersectSettings 
             */
            this.updateIntersectSettings = intersectSettings => {
                const newIntersectSettings = Object.assign({}, this._intersectSettings)
                for (let [key, val] of Object.entries(intersectSettings)) {
                    newIntersectSettings[key] = val
                }
                this.intersectSettings = newIntersectSettings
            }

            /**
             * Gets rect over intersection between root and provided element
             * @param {Element} element 
             * @returns 
             */
            this.getIntersectionRect = (element) => {
                if (!this.isIntersecting) return { top: 0, right: 0, bottom: 0, left: 0, height: 0, width: 0, x: 0, y: 0 }

                const rr = this.rootBounds // root rect
                const er = element.getBoundingClientRect() //element rect

                const top = Math.max(rr.top, er.top),
                    right = Math.min(rr.right, er.right),
                    bottom = Math.min(rr.bottom, er.bottom),
                    left = Math.max(rr.left, er.left)

                const intersectionRect = {
                    top: top,
                    right: right,
                    bottom: bottom,
                    left: left,
                    height: bottom - top,
                    width: right - left,
                    x: left,
                    y: top
                }

                // don't allow negatives
                Object.entries(intersectionRect).forEach(([k, v]) => intersectionRect[k] = Math.max(v, 0))
                return intersectionRect
            }

            /**
             * whether observer is intersecting with provided element
             * @param {Element} element 
             * @returns {Boolean}
             */
            this.isIntersecting = (element) => {
                const { getPercentScrolled } = this
                const percentScrolled = getPercentScrolled(element, false)
                return percentScrolled >= 0 && percentScrolled <= 1
            }

            /**
             * Forces onIntersecting function to fire on intersecting elements
             */
            this.trigger = () => {
                if (!this._onIntersectingScroll) return
                this.intersectingElements.forEach(el => {
                    let pseudoEntry = this.getPseudoEntry(el)
                    let percentScrolled = this.getPercentScrolled(el)
                    this._onIntersectingScroll(pseudoEntry, percentScrolled)
                })
            }

            /**
             * Checks each intersecting element and fires the onIntersecting listener. 
             * @returns {Function}
             */
            this._createIntersectingFunction = () => {
                let onIntersectingScroll = () => {
                    const { onIntersecting } = this.intersectSettings

                    // cancel if no onIntersecting function
                    if (!onIntersecting) return

                    // remove listener if no more intersecting elements
                    if (this.intersectingElements.length === 0) {
                        this._removeIntersectingListener(this._onIntersectingScroll)
                        return
                    }
                    // fire onIntersecting to each element
                    this.intersectingElements.forEach(element => {
                        const percentScrolled = this.getPercentScrolled(element)
                        const pseudoEntry = this.getPseudoEntry(element)
                        onIntersecting(pseudoEntry, percentScrolled)
                    })
                }

                // apply throttle 
                if (this.intersectSettings.throttle) onIntersectingScroll = this._throttle(onIntersectingScroll, this.intersectSettings.throttle)
                return onIntersectingScroll
            }

            /**
             * creates either IntersectionObserver or pseudoObserver depending on usePseudoObserver
             * @returns {IntersectionObserver|Object}
             */
            this._createObserver = () => {
                const { _createIntersectionObserver, _createPseudoObserver, usePseudoObserver } = this
                const createRequestedObserver = usePseudoObserver === true ? _createPseudoObserver : _createIntersectionObserver
                return createRequestedObserver()
            }

            /**
             * Creates the observer responsible for handling functions and intersection status of elements
             * @returns {IntersectionObserver}
             */
            this._createIntersectionObserver = () => {
                const { intersectSettings: { marginTop, marginBottom, thresholds, root }, _toMarginString, _observerCallback } = this

                const observerOptions = {
                    rootMargin: `${_toMarginString(marginTop)} 0px ${_toMarginString(marginBottom)} 0px`,
                    threshold: thresholds,
                    root: root === document ? null : root //safari fix... typical.
                }

                const onIntersectingScroll = this._createIntersectingFunction()
                const observer = new IntersectionObserver(entries => _observerCallback(entries, onIntersectingScroll), observerOptions)
                return observer
            }



            /**
             * Applys a scroll listener instead of using an IntersectionObserver.
             * @returns {Object}
             */
            this._createPseudoObserver = () => {
                const { _observerCallback, getPseudoEntry, intersectSettings: { thresholds } } = this

                let ratiosHit = [] // if crossed threshold is greater than observed threshold, stored here
                const isCrossedThreshold = (ratio) => { // for checking triggered elements in onScroll
                    let foundMatch = false
                    thresholds.forEach(t => {
                        // if provided threshold moved above or below observed thresholds
                        const crossedAbove = ratio >= t && !ratiosHit.includes(t)
                        const crossedBelow = ratio < t && ratiosHit.includes(t)
                        if (crossedAbove) {
                            ratiosHit.push(t)
                            foundMatch = true
                        }
                        if (crossedBelow) {
                            ratiosHit = this._removeFromArray(ratiosHit, t)
                            foundMatch = true
                        }
                    })
                    return foundMatch
                }

                // used for onIntersecting function when elements are triggered
                const onIntersectingScroll = this._createIntersectingFunction()

                const getObservedElements = () => this.observedElements

                // uses this to compare which elements were previously intersecting vs. which are now intersecting
                const _intersectingElements = []

                let onScroll = () => {
                    //checks each observed element for trigger
                    const triggeredEntries = []
                    const observedElements = getObservedElements()
                    observedElements.forEach(element => {

                        const pseudoEntry = getPseudoEntry(element)
                        const { isIntersecting } = pseudoEntry

                        // checks intersecting state
                        const wasIntersecting = _intersectingElements.includes(element)
                        const intersectTriggered = isIntersecting && !wasIntersecting
                        const deintersectTriggered = !isIntersecting && wasIntersecting

                        // checks threshold to determine where to fire intersect/deintersect
                        const intersectionRatio = pseudoEntry.intersectionRatio
                        const crossedThreshold = isCrossedThreshold(intersectionRatio)

                        // check intersect state & add/remove from _intersectingElements
                        if (intersectTriggered || deintersectTriggered || crossedThreshold) {
                            triggeredEntries.push(pseudoEntry) // prepare to fire onIntersect/onDeintersect

                            if (intersectTriggered) _intersectingElements.push(element)
                            if (deintersectTriggered) {
                                const index = _intersectingElements.indexOf(element);
                                if (index > -1) _intersectingElements.splice(index, 1);
                            }
                        }
                    })

                    //fire callback on each triggered entry
                    if (triggeredEntries.length > 0) _observerCallback(triggeredEntries, onIntersectingScroll)
                }

                // apply onScroll throttle
                if (this.throttlePseudoObserver) onScroll = this._throttle(onScroll, this.throttlePseudoObserver)

                const pseudoObserver = {}

                //addds scroll listener if not already present
                pseudoObserver.observe = (element) => {
                    if (!this._hasPseudoObserverListener) {
                        this.scrollListenerTarget.addEventListener('scroll', onScroll)
                        this._hasPseudoObserverListener = true
                    }
                }
                //no purpose, just serves as a holder to match intersectionObserver. unobserve elements by using this.unobserve
                pseudoObserver.unobserve = (element) => {
                    return
                }
                //removes scroll listener
                pseudoObserver.disconnect = () => {
                    this.scrollListenerTarget.removeEventListener('scroll', onScroll)
                    this._hasPseudoObserverListener = false
                }

                return pseudoObserver
            }

            /**
             * Limits a function call to improve performance (for scroll listeners)
             * @param {Function} callback function to be throttled
             * @param {Number} limit integer (ms)
             * @returns {Function} Throttled function
             */
            this._throttle = (callback, limit) => {
                let wait = false
                return function (...args) {
                    if (!wait) {
                        callback(...args)
                        wait = true
                        setTimeout(function () { wait = false }, limit)
                    }
                }
            }

            /**
             * Checks if any elements are being observed and sets this._isObserving accordingly
             */
            this._updateIsObserving = () => {
                this._isObserving = this.observedElements.length > 0 ? true : false
            }

            /**
             * Used to remove elements from observedElements array
             */
            this._removeFromArray = (array, item) => {
                const index = array.indexOf(item)
                if (index > -1) array.splice(index, 1)
                return array
            }




            /**
             * Callback that fires when elements cross intersect/deintersect threshold 
             * @param {IntersectionObserverEntry|Object} entries Object if using pseudoObserver
             * @param {Function} onIntersectingScroll function that holds onIntersecting listener with throttle applied and check for intersecting elements
             */
            this._observerCallback = (entries, onIntersectingScroll) => {
                const {
                    intersectSettings: { onIntersect, onIntersecting, onDeintersect },
                    getPercentScrolled,
                    _hasScrollListener
                } = this

                //fire callback on each entry
                for (let entry of entries) {
                    const element = entry.target
                    const percentScrolled = getPercentScrolled(element)

                    // intersect triggered
                    if (entry.isIntersecting) {
                        if (onIntersect) onIntersect(entry, percentScrolled)
                        if (onIntersecting && !_hasScrollListener) {
                            onIntersecting(entry, percentScrolled)
                        }
                    }
                    // deintersect triggered
                    else if (!entry.isIntersecting) {
                        if (onIntersecting) {
                            if (_hasScrollListener) onIntersecting(entry, percentScrolled)
                        }
                        if (onDeintersect) onDeintersect(entry, percentScrolled)
                    }
                }

                //add/remove listener if not applied
                if (onIntersectingScroll) {
                    this._updateIntersectingListener(onIntersectingScroll)
                }
            }

            /**
             * Adds/removes onIntersectingScroll function if there are any intersecting elements and prevents listener from being applied more than once
             * @param {Function} onIntersectingScroll 
             */
            this._updateIntersectingListener = (onIntersectingScroll) => {
                if (this.intersectingElements.length > 0 && !this._hasScrollListener) {
                    this._addIntersectingListener(onIntersectingScroll)
                }
                if (this.intersectingElements.length === 0) {
                    this._removeIntersectingListener(onIntersectingScroll)
                }
            }

            /**
             * Adds scroll listener for onIntersectingScroll function
             * @param {Function} onIntersectingScroll 
             */
            this._addIntersectingListener = (onIntersectingScroll) => {
                this._onIntersectingScroll = onIntersectingScroll
                this.scrollListenerTarget.addEventListener('scroll', onIntersectingScroll)
                this._hasScrollListener = true
            }

            /**
             * Removes scroll listener for onIntersectingScroll function
             * @param {Function} onIntersectingScroll 
             */
            this._removeIntersectingListener = (onIntersectingScroll) => {
                this.scrollListenerTarget.removeEventListener('scroll', onIntersectingScroll)
                this._onIntersectingScroll = null
                this._hasScrollListener = false
            }

            /**
             * Converts a margin string ex: '10px' to int ex '10', if in % takes % of root height
             * @param {String} margin 
             */
            this._toMarginNumber = margin => {
                const { root } = this.intersectSettings
                if (typeof margin === 'number') return margin
                const parsedMargin = margin.match(/[-\d\.]+|\D+/g),
                    num = parseFloat(parsedMargin[0]),
                    unit = parsedMargin[1]

                if (unit === '%') {
                    const rootHeight = root instanceof Element ? root.getBoundingClientRect().height : innerHeight
                    return num / 100 * rootHeight
                }
                else return num
            }

            /**
             * Converts number to px string ex: 10 to '10px'
             * @param {Number} margin 
             */
            this._toMarginString = margin => {
                return typeof margin === 'number' ? `${margin}px` : margin
            }

            /**
             * creates observer and starts observing
             */
            this._init = () => {
                this.intersectionObserver = this._createObserver()
                this.observeAll()
                this.isInitialized = true
            }
            this._init()
        }


        get intersectingElements() {
            return this.observedElements.filter(el => this.isIntersecting(el))
        }

        get usePseudoObserver() {
            return this._usePseudoObserver
        }

        set usePseudoObserver(bool) {
            const { isInitialized, usePseudoObserver, resetObserver } = this
            this._usePseudoObserver = bool
            if (bool === usePseudoObserver || !isInitialized) return
            resetObserver()
        }

        get scrollListenerTarget() {
            return this._scrollListenerTarget
        }

        set scrollListenerTarget(element) {
            const { isObserving, isInitialized, _createObserver, observeAll, disconnect } = this
            if (!isInitialized) {
                this._scrollListenerTarget = element
                return
            }
            if (isObserving) disconnect()
            this._scrollListenerTarget = element
            this.intersectionObserver = _createObserver()
            if (isObserving) observeAll()
        }

        get rootBounds() {
            let { marginTop, marginBottom, root } = this.intersectSettings
            const { _toMarginNumber } = this

            marginTop = _toMarginNumber(marginTop)
            marginBottom = _toMarginNumber(marginBottom)

            let defaults = root instanceof Element ? root.getBoundingClientRect() : {
                top: 0,
                right: innerWidth,
                bottom: innerHeight,
                left: 0,
                height: innerHeight,
                width: innerWidth,
                x: 0,
                y: 0
            }

            const height = defaults.height + marginTop + marginBottom
            const top = defaults.top - marginTop
            const bottom = top + height

            return {
                top: top,
                right: defaults.right,
                bottom: bottom,
                left: defaults.left,
                height: height,
                width: defaults.width,
                x: defaults.x,
                y: defaults.y
            }
        }

        get isObserving() {
            return this._isObserving
        }

        get intersectSettings() {
            const {
                marginTop = 0,
                marginBottom = 0,
                onIntersect = null,
                onIntersecting = null,
                onDeintersect = null,
                thresholds = [0, 1],
                throttle = 0,
                root = document
            } = this._intersectSettings

            return {
                marginTop: marginTop || 0,
                marginBottom: marginBottom || 0,
                onIntersect: onIntersect,
                onIntersecting: onIntersecting,
                onDeintersect: onDeintersect,
                thresholds: thresholds,
                throttle: throttle,
                root: root || document
            }
        }

        set intersectSettings(intersectSettings) {
            const { intersectionObserver, resetObserver } = this
            const allowedKeys = [
                'marginTop',
                'marginBottom',
                'onIntersect',
                'onIntersecting',
                'onDeintersect',
                'thresholds',
                'throttle',
                'root'
            ]

            // Check that each key is allowed
            const newIntersectSettings = {}
            for (let [key, val] of Object.entries(intersectSettings)) {
                if (!allowedKeys.includes(key)) console.error(`'${key}' is not a valid intersectSettings key for ScrollObserver`)
                newIntersectSettings[key] = val
            }
            this._intersectSettings = newIntersectSettings
            if (intersectionObserver) resetObserver() // reset the observer to adopt the new intersectSettings
        }
    }
})(themeshark)
