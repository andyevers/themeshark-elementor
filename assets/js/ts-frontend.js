/**
 * Helpers for elementorFrontend object. Properties and methods used in widget handlers. Not active in admin dashboard
 */
const themesharkFrontend = {

    /**
     * Stored values are saved in here by their keys
     */
    _storedValues: {},


    /******************************
     * Verifies that the given argument is an instance of elementorModules.frontend.handlers.Base
     * @param {WidgetHandler} widgetHandler argument to be tested
     *****************************/
    verifyWidgetHandler: function (widgetHandler) {
        if (!(widgetHandler instanceof elementorModules.frontend.handlers.Base)) {
            console.error(`widgetHandler must be an instance of elementorModules.frontend.handlers.Base, found ${typeof widgetHandler}`, widgetHandler)
            return false
        }
        return true
    },


    /******************************
     * Callback function set using 'jQuery(window).on('elementor/frontend/init', callback)'
     * @param {Function} callback Function that fires on 'elementor/frontend/init'
     *****************************/
    addInitCallback: function (callback) {
        jQuery(window).on('elementor/frontend/init', callback)
    },


    /*****************************
     * Adds widget handler using hook elementorFrontend.hooks.addAction(`frontend/element_ready/${widgetName}.${skin}`, addHandler)
     * @param {String} widgetName Name of the widget returned by get_name() in the widget php file
     * @param {elementorModules.frontend.handlers.Base} widgetHandler Widget handler class
     * @param {String} skin [default] Name of skin for the widget.
     *****************************/
    addWidgetHandler: function (widgetName, widgetHandler, skin = 'default') {
        const addHandler = $element => {
            elementorFrontend.elementsHandler.addHandler(widgetHandler, { $element })
        }
        elementorFrontend.hooks.addAction(`frontend/element_ready/${widgetName}.${skin}`, addHandler)
    },


    /*****************************
     * Adds widget init callback using hook elementorFrontend.hooks.addAction(`frontend/element_ready/${widgetName}.${skin}`, addHandler) (could also use onInit method)
     * @param {String} widgetName Name of the widget returned by get_name() in the widget php file
     * @param {Function} callback($element) callback function that receives jQuery $element for the widget
     * @param {String} skin [default] Name of skin for the widget.
     *****************************/
    addWidgetInitCallback: function (widgetName, callback, skin = 'default') {
        elementorFrontend.hooks.addAction(`frontend/element_ready/${widgetName}.${skin}`, $element => callback($element))
    },


    getWidgetHandlerId(widgetHandler) {
        return widgetHandler.$element.data('id')
    },

    /**
     * Returns data-id of the widget element prefixed by '_widget_'. if key provided returns _widget_${data-id}_${key}_
     * @param {WidgetHandler} widgetHandler 
     * @param {String} key 
     */
    getWidgetKey: function (widgetHandler, key) {
        const widgetId = this.getWidgetHandlerId(widgetHandler)
        return this.getWidgetKeyById(widgetId, key)
    },

    /**
     * Returns the widget key for the id provided
     * @param {String} widgetElementId The data-id for the widget $element
     * @param {String} key the key used when setting / getting the saved value
     */
    getWidgetKeyById: function (widgetElementId, key) {
        const baseKey = `_widget_${widgetElementId}`
        if (!key) return baseKey
        return `${baseKey}_${key}_`
    },


    /**
     * Returns value stored in themesharkFrontend._storedValues object
     * @param {String} key used to access the value
     * @returns {Mixed} stored value or undefined if key does not exist
     */
    getStoredValue: function (key) {
        return this._storedValues[key]
    },


    getActiveSection: function () {
        if (!themeshark.isEditMode) console.error('you can only get the current section in edit mode')
        const currentPageView = elementor.getPanelView().getCurrentPageView()
        if (!currentPageView) return null
        return currentPageView.activeSection
    },

    /******************************
     * returns a value stored in _widgetStoredValues by a widgetHandler using its key
     * @param {WidgetHandler} widgetHandler widget handler that was used to set the stored value
     * @param {String} key index key that was used to store the value
     *****************************/
    getWidgetStoredValue: function (widgetHandler, key) {
        this.verifyWidgetHandler(widgetHandler)
        const widgetKey = this.getWidgetKey(widgetHandler, key)
        return this.getStoredValue(widgetKey)
    },


    /**
     * Stores value in themesharkFrontend._storedValues object
     * @param {String} key used to access the value
     * @returns void
     */
    setStoredValue: function (key, value) {
        if (value === undefined) console.error(`passed stored value of undefined with key: ${key}`)
        this._storedValues[key] = value
    },

    /******************************
     * Stores a value in _widgetStoredValues and is indexed using the both widgetHandlerID and the 'key' argument.
     * @param {WidgetHandler} widgetHandler widget handler that is used to set the stored value
     * @param {String} key index key that will be used to retrieve the value
     * @param val Value to be stored
     *****************************/
    setWidgetStoredValue: function (widgetHandler, key, val) {
        this.verifyWidgetHandler(widgetHandler)
        const widgetKey = this.getWidgetKey(widgetHandler, key)
        this.setStoredValue(widgetKey, val)
    },

    deleteAllWidgetStoredValues: function (widgetIdOrHandler) {
        const widgetKey = typeof widgetIdOrHandler === 'string'
            ? this.getWidgetKeyById(widgetIdOrHandler)
            : this.getWidgetKey(widgetIdOrHandler)

        Object.keys(this._storedValues).forEach(key => {
            if (key.match(widgetKey)) this.deleteStoredValue(key)
        })
    },

    /**
     * Returns the value if the key is set, otherwise sets the key to the provided value and returns it.
     * @param {String} key 
     * @param {Mixed} value 
     * @returns 
     */
    getOrSetStoredValue: function (key, value) {
        if (this.getStoredValue(key) === undefined) this.setStoredValue(key, value)
        return this.getStoredValue(key)
    },

    /******************************
     * Returns the stored value for the widgetHandler & key provided. If there is no existing value found, the provided value is stored and returned
     * @param {WidgetHandler} widgetHandler widget handler that is used to set the stored value
     * @param {String} key index key that is used to set the stored value
     * @param valueIfNotSet value that will be stored & returned for the widget & key if no current value.
     *****************************/
    getOrSetWidgetStoredValue: function (widgetHandler, key, valueIfNotSet) {
        this.verifyWidgetHandler(widgetHandler)
        const widgetKey = this.getWidgetKey(widgetHandler, key)
        return this.getOrSetStoredValue(widgetKey, valueIfNotSet)
    },


    /**
     * Returns value for the key if it exists. If not, uses the return value of the callback to set the value and returns it
     * @param {String} key 
     * @param {Function} callback 
     * @returns 
     */
    getOrSetStoredReturnValue: function (key, callback) {
        let storedValue = this.getStoredValue(key)
        if (storedValue === undefined) {
            const value = callback()
            this.setStoredValue(key, value)
            storedValue = this.getStoredValue(key)
        }
        return storedValue
    },

    /******************************
     * Returns the stored value for the widgetHandler & key provided. If there is no existing value found, the provided function is called, and the returned value of that function is stored and returned
     * @param {WidgetHandler} widgetHandler widget handler that is used to set the stored value
     * @param {String} key index key that is used to set the stored value
     * @param {Function} callback function that returns the value that will be stored & returned for the widget & key if no current value.
     *****************************/
    getOrSetWidgetStoredReturnValue: function (widgetHandler, key, callback) {
        this.verifyWidgetHandler(widgetHandler)
        const widgetKey = this.getWidgetKey(widgetHandler, key)
        return this.getOrSetStoredReturnValue(widgetKey, callback)
    },


    /**
     * Deletes stored value from _storedValues
     * @param {String} key stored value key
     */
    deleteStoredValue: function (key) {
        delete this._storedValues[key]
    },

    /******************************
     * Deletes a stored value from _widgetStoredValues
     * @param {WidgetHandler} widgetHandler widget handler that is used to set the stored value
     * @param {String} key index key that was used to set the stored value
     *****************************/
    deleteWidgetStoredValue: function (widgetHandler, key) {
        const widgetKey = this.getWidgetKey(widgetHandler, key)
        this.deleteStoredValue(widgetKey)
    },


    /******************************
     * Calls elementor.getContainer(dataId) using the element data-id of the provided widget handler
     * @param {WidgetHandler} widgetHandler widget handler for the container that will be returned
     *****************************/
    getHandlerContainer: function (widgetHandler) {
        if (!widgetHandler.isEdit) console.error('you can only get handler container in edit mode')
        const dataId = `${widgetHandler.$element.data('id')}`
        const container = elementor.getContainer(dataId)
        return container
    },

    /******************************
     * returns the ElementModel for the provided widgetHandler
     * @param {WidgetHandler} widgetHandler handler for the ElementModel that will be returned
     * @returns {ElementModel} instance of ElementModel
     *****************************/
    getHandlerModel: function (widgetHandler) {
        if (!widgetHandler.isEdit) console.error('you can only get handler model in edit mode')
        const container = this.getHandlerContainer(widgetHandler)
        const model = container.model
        return model
    },


    /******************************
     * returns an object with the keys and values of the widget controls. The keys for the widget controls are the IDs of the controls set in the widget PHP file.
     * @param {WidgetHandler} widgetHandler handler for the widget controls that will be returned
     * @returns {Object} {my_number_control: 120, some_text_input: 'hello'}
     *****************************/
    getControlValues: function (widgetHandler) {
        const model = this.getHandlerModel(widgetHandler)
        const modelSettings = model.get('settings')
        return modelSettings.attributes
    },


    /******************************
     * Returns the ScrollObserver instance for the key provided. If there is no existing ScrollObserver found, the provided scrollObserverSettings are used to create one to be stored and returned https://github.com/andyevers/scroll-observer
     * @param {WidgetHandler} widgetHandler handler for the widget that is using the ScrollObserver
     * @param {String} key Key used to store and retrieve the ScrollObserver
     * @param {Object} scrollObserverSettings {observedElement: Object, intersectSettings: IntersectSettingsObject, usePseudoObserver: Boolean}
     *****************************/
    getOrSetScrollObserver: function (key, scrollObserverSettings) {

        //check that ScrollObserver class has been loaded
        if (!themeshark.ScrollObserver) console.error('themeshark.ScrollObserver must be defined')

        //check if valid observer settings
        const allowedObserverSettings = ['targetElements', 'intersectSettings', 'usePseudoObserver']
        Object.keys(scrollObserverSettings).forEach(k => {
            if (!allowedObserverSettings.includes(k)) {
                throw console.error(`"${k}" is not a valid setting for ScrollObserver from settings:`, scrollObserverSettings, 'allowed keys:', allowedObserverSettings,)
            }
        })

        // get or create scroll observer
        let { targetElements = [], intersectSettings, observerSettings } = scrollObserverSettings
        if (!Array.isArray(targetElements)) targetElements = [targetElements]
        if (!observerSettings) observerSettings = {}

        const scrollObserver = this.getOrSetStoredReturnValue(key, () => {
            observerSettings.scrollListenerTarget = observerSettings.scrollListenerTarget || themeshark.scrollListenerTarget
            intersectSettings.root = intersectSettings.root || themeshark.defaultScrollObserverRoot
            return new themeshark.ScrollObserver(targetElements, intersectSettings, observerSettings)
        })

        //update observedElement if they don't match
        if (targetElements !== scrollObserver.targetElements) {
            scrollObserver.unobserveAll()
            scrollObserver.targetElements = targetElements
            scrollObserver.observeAll()
        }

        //update intersect settings if they don't match
        if (intersectSettings !== scrollObserver.intersectSettings) {
            scrollObserver.updateIntersectSettings(intersectSettings)
        }

        // add scroll observer on destroy event if listener doesn't exist
        targetElements.forEach(el => {
            const destroyKey = `${key}_destroy_${el.dataset.id}`
            const mainElement = this.getMainElement(el)
            this.addOnceDestroyListener(mainElement, destroyKey, () => {
                scrollObserver.disconnect()
                this.deleteStoredValue(destroyKey)
                this.deleteStoredValue(key)
            })
        })
        return scrollObserver
    },



    /******************************
     * Returns the ScrollObserver instance for the widgetHandler & key provided. If there is no existing ScrollObserver found, the provided scrollObserverSettings are used to create one to be stored and returned https://github.com/andyevers/scroll-observer
     * @param {WidgetHandler} widgetHandler handler for the widget that is using the ScrollObserver
     * @param {String} key Key used to store and retrieve the ScrollObserver
     * @param {Object} scrollObserverSettings {observedElement: Object, intersectSettings: IntersectSettingsObject, usePseudoObserver: Boolean}
     *****************************/
    getOrSetWidgetScrollObserver: function (widgetHandler, key, scrollObserverSettings) {
        this.verifyWidgetHandler(widgetHandler)
        const widgetKey = this.getWidgetKey(widgetHandler, key)
        const scrollObserver = this.getOrSetScrollObserver(widgetKey, scrollObserverSettings)
        return scrollObserver
    },


    /**
     * Fires function when element is removed in the editor
     * @param {*} element the element that's model will receive the on destroy listener
     * @param {*} key stored value key
     * @param {*} callback function called on destroy
     * @param {*} deleteListenerAfterDestroy whether the listener should be deleted from the stored values after destroy
     */
    addOnceDestroyListener: function (element, key, callback, deleteListenerAfterDestroy = true) {
        // add remove scroll observer on destroy event if listener doesn't exist
        const onDestroyListenerKey = `_hasOnDestroy_${key}`
        if (!this.getStoredValue(onDestroyListenerKey) && themeshark.isEditMode) {
            const container = this.getElementContainer(element)
            if (!container) return
            const model = container.model
            this.onModelDestroy(model, (eventTarget) => {
                callback(eventTarget)
                if (deleteListenerAfterDestroy) {
                    this.deleteStoredValue(onDestroyListenerKey)
                }
            })
            this.setStoredValue(onDestroyListenerKey, true)
        }
    },


    /******************************
     * Adds a destroy event listener if it is not already present. prefer onDestroy method in widgetHandler
     * @param {ElementModel} widgetHandler handler for the model that the destroy event is applied to
     *****************************/
    addOnceWidgetDestroyListener: function (widgetHandler, key, callback, deleteListenerAfterDestroy = true) {
        this.verifyWidgetHandler(widgetHandler)
        const widgetKey = getWidgetKey(widgetHandler, key)
        const element = widgetHandler.$element[0]
        this.addOnceDestroyListener(element, widgetKey, callback, deleteListenerAfterDestroy)
    },



    /**
     * Returns the Marionette.js Container for the element 
     * @param {HTMLElement} element 
     * @returns {Object} Marionette.js/Backbone.js container
     */
    getElementContainer: function (element) {
        if (!themeshark.isEditMode) return console.error('you can only use getElementContainer in edit mode.')
        try {
            const container = elementor.getContainer(element.dataset.id)
            return container || null
        } catch (error) {
            console.error('could not get element container for: ', element, error)
        }
    },


    /******************************
     * Callback function set using 'jQuery(window).on('elementor/frontend/init', callback)'
     * @param {ElementModel} model ElementModel for the widgetHandler
     * @param {Function} callback function that fires when model is destroyed
     *****************************/
    onModelDestroy: function (model, callback) {
        model.on('destroy', callback)
    },



    /**
     * Returns the parent main element if the provided element is not the main one for the widget/section/column. otherwise returns the provided element
     * @param {HTMLElement} element 
     * @returns {HTMLElement}
     */
    getMainElement: function (element) {
        const $element = jQuery(element)
        const mainElement = $element.data('element_type') ? element : $element.closest('[data-element_type]')[0]
        return mainElement
    },

    /**
     * Fires callback when controls change
     * @param {*} element 
     * @param {*} callback receives (panel, model, view)
     * @returns 
     */
    onElementControlsChange: function (element, controlKeys, callback) {
        const onChange = (model) => {
            // const modelSettings = model.get('settings')
            const hasChangedControl = controlKeys.some(k => Object.keys(model.changed).includes(k))
            if (hasChangedControl) callback(model)
        }

        if (!themeshark.isEditMode) return console.error('onElementControlsChange can only be used in edit mode')
        const container = this.getElementContainer(element)
        const modelSettings = container.model.get('settings')
        modelSettings.on('change', onChange)
    },


    /**
     * extracts value given an object with device mode suffixes
     * @param {Object} settings ex: {height:100px, height_tablet: 80px, height_mobile: 30px}
     * @param {String} settingKey ex: 'height'
     * @returns ex: 100px
     */
    getCurrentDeviceSetting(settings, settingKey) {
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
    },

}

window.themesharkFrontend = themesharkFrontend