/**
 * Properties and methods specified in ts-functions.js in the themeshark-elementor plugin
 */
const themeshark = {

    get isEditMode() {
        if (typeof elementorAdmin !== 'undefined') return false
        return elementorFrontend ? elementorFrontend.isEditMode() : typeof elementor !== 'undefined'
    },

    /******************************
     * Height of WP Admin Bar in px. 
     * @returns {Number} Admin bar height or 0 if no admin bar
     *****************************/
    get adminBarHeight() {
        if (typeof elementorFrontend !== 'undefined') {
            return elementorFrontend.elements.$wpAdminBar?.height() || 0
        } else {
            return jQuery("#wpadminbar") && jQuery("#wpadminbar").height() ? jQuery("#wpadminbar").height() : 0
        }
    },

    widgetHandlerEditorData: {},

    get scrollListenerTarget() {
        const hasParallax = this.allowedParallax
        const scrollTarget = hasParallax === 'yes' ? this.pageContentWrap : this.currentModeWindow
        return scrollTarget
    },

    get pageContentWrap() {
        return this.currentModeWindow.jQuery('.elementor[data-elementor-id]')[0]
    },

    get currentModeWindow() {
        return this.isEditMode ? elementor.$preview[0].contentWindow : window
    },

    get defaultScrollObserverRoot() {
        if (this.allowedParallax) return this.pageContentWrap
        const responsiveWrapper = this.currentModeWindow.jQuery('.elementor-preview-responsive-wrapper')[0]
        return this.isEditMode ? responsiveWrapper : this.currentModeWindow.document
    },

    get scrollObervers() {
        return Object.values(this.themesharkFrontend._storedValues).filter(val => {
            if (typeof val === 'object' && val !== null) {
                //TODO find a better way of checking if scroll observer
                let isScrollObserver = val.scrollListenerTarget !== undefined
                if (isScrollObserver) return val
            }
        })
    },

    /**
     * updates root and scroll listener target for all scroll observers
     */
    updateScrollObserverTargets() {
        this.scrollObervers.forEach(observer => {
            observer.scrollListenerTarget = this.scrollListenerTarget
            observer.updateIntersectSettings({ root: this.defaultScrollObserverRoot })
        })
    },

    set allowedParallax(val) {
        if (!(val === 'yes' || val === '')) console.error(`invalid allowedParallax value: ${val}`)
        this.currentModeWindow._themesharkAllowedParallax = val
        this.updateScrollObserverTargets()
    },


    get allowedParallax() {
        return this.currentModeWindow._themesharkAllowedParallax
    },

    /**
     * Gets themesharkFrontend if not accessible directly from another file
     */
    get themesharkFrontend() {
        if (this.isEditMode) return elementor.$preview[0].contentWindow.themesharkFrontend
        return window.themesharkFrontend
    },

    get themesharkLocalizedData() {
        if (this.isEditMode) return window.parent.themesharkLocalizedData
        return window.themesharkLocalizedData
    },

    get themesharkControlsHandler() {
        if (!this.isEditMode) return console.error('you can only access controls handler in edit mode')
        return window.parent.themesharkControlsHandler || window.themesharkControlsHandler
    },

    /******************************
     * sets visibility: hidden to element. used when need to fire function before element is visible, like getting it's clientBoundingRect. 
     *****************************/
    classWaitVisibility: 'ts--wait-visibility',


    /******************************
     * Removes a class from an element, then adds it back after 50ms
     * @param {HTMLElement} element Element that you want to remove and re-add the class to
     * @param {String} resetClassName name of class to be reset
     *****************************/
    resetClass: function (element, resetClassName) {
        const removeClassTimeout = (element, className) => {
            return new Promise(resolve => {
                element.classList.remove(className)
                setTimeout(() => resolve(className), 50);
            })
        }

        async function asyncClassReset() {
            let className = await removeClassTimeout(element, resetClassName);
            element.classList.add(className)
        }

        asyncClassReset()
    },


    /******************************
     * Separates a number from it's unit '100px' {size: 100, unit: 'px'}
     * @returns {Object} {size: 100, unit: 'px'}
     * @param {String} sizeUnitString string with number and unit - ex: '50%'
     *****************************/
    parseSizeUnit: function (sizeUnitString) {
        const parsedString = sizeUnitString.match(/[-\d\.]+|\D+/g),
            size = parseFloat(parsedString[0]),
            unit = parsedString[1]
        return { size: size, unit: unit }
    },


    /******************************
     * Takes the bounding rects from an array of elements and returns a rect object that contains all the elements
     * @returns {Object} {top, right, bottom, left, width, height, x, y}
     * @param {Array} elements Array of HTMLElements
     *****************************/
    getCollectiveClientRect: function (elements) {
        const rects = elements.map(element => element.getBoundingClientRect())
        const collectiveRect = rects.reduce((acc, cur) => {
            return {
                top: Math.min(acc.top, cur.top),
                right: Math.max(acc.right, cur.right),
                bottom: Math.max(acc.bottom, cur.bottom),
                left: Math.min(acc.left, cur.left),
                width: Math.max(acc.width, cur.width),
                height: Math.max(acc.height, cur.height),
                x: Math.min(acc.x, cur.x),
                y: Math.min(acc.y, cur.y)
            }
        })
        return collectiveRect
    },


    /******************************
     * Measures the amount of overflow in px that the furthest-overflowing child elements are in each direction. (includes all nested elements)
     * @returns {Object} {top, right, bottom, left}
     * @param {HTMLElement} element element that will have it's children checked for overflow 
     *****************************/
    getElementOverflow: function (element) {
        const elements = Array.from(element.querySelectorAll('*'))
        const collectiveRect = this.getCollectiveClientRect(elements)
        const elementRect = element.getBoundingClientRect()

        return {
            top: Math.max(elementRect.top - collectiveRect.top, 0),
            right: Math.max(collectiveRect.right - elementRect.right, 0),
            bottom: Math.max(collectiveRect.bottom - elementRect.bottom, 0),
            left: Math.max(elementRect.left - collectiveRect.left, 0)
        }
    },

    getRestUrl: function (action) {
        const restRoute = themesharkLocalizedData?.restRoute
        if (!restRoute) return void console.error('themesharkLocalizedData.restRoute must be defined')
        return `${restRoute}/${action}`
    },

    postRequest: function (action, data = {}, ajaxOptions = {}) {
        const { done, fail, always } = ajaxOptions
        const restRoute = this.getRestUrl(action)

        return jQuery.post(restRoute, data)
            .done(res => { if (done) done(res) })
            .fail(res => { if (fail) fail(res) })
            .always(res => { if (always) always(res) })
    },

    HOUR_IN_SECONDS: 3600,
    DAY_IN_SECONDS: 86400,
    WEEK_IN_SECONDS: 604800,

    isJsonString: function (str) {
        if (typeof str !== 'string') return false
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    },

    secondsPassed: function (timeInMS) {
        if (typeof timeInMS !== 'number') return void console.error(`time must be number, recieved ${typeof timeInMS}`)
        return (Date.now() - timeInMS) / 1000
    },

    hooks: {

        didActions: [],

        actions: {},

        ensureAction(actionHook) {
            let didAddAction = false
            if (!Array.isArray(this.actions[actionHook])) {
                this.actions[actionHook] = []
                didAddAction = true
            }
            return didAddAction
        },

        addAction(actionHook, callback) {
            this.ensureAction(actionHook)
            this.actions[actionHook].push(callback)
        },

        doAction(actionHook, ...args) {
            const actionFunctions = this.actions[actionHook]
            if (!Array.isArray(actionFunctions)) return
            actionFunctions.forEach(f => f(...args))
            if (!this.didActions.includes(actionHook)) {
                this.didActions.push(actionHook)
            }
        },
    }
}