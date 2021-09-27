/**
 * Handles control views in the editor. 
 * Control views are Marionette.js view objects that Elementor extends each control type from
 */
const themesharkControlsHandler = {
    handlerControlKey: 'themeshark_settings', //name of the control arg to check for the settings in php file
    isListening: false,
    currentPageView: null,
    currentSectionName: null,
    currentHandleKey: null,

    widgetHandlerEditorFunctions: {}, // addon functions for widget handlers for edit mode

    // control handlers that can be added in any control inside themeshark_settings
    _handlers: {
        /* my_control_handler_key: myFunction */
    },

    // handlers fired runs when section changes for a specific widget (key is widget name). added in widget handler file
    _widgetSectionHandlers: {
        all: {} // runs for all widgets
        /* 'ts-expander': {
            'some_section_handler': myFunction
            'another_handler': yourFunction
        } */
    },

    get editedElementView() {
        if (!this.currentPageView) return null
        return this.currentPageView.getOption('editedElementView')
    },

    get editedElementId() {
        return this.editedElementView?.container.id
    },

    get editedElementName() {
        const elAttributes = this.editedElementView?.options.model.attributes
        if (!elAttributes) return

        const widgetType = elAttributes.widgetType
        const elType = elAttributes.elType
        return widgetType || elType
    },



    getEditedElementStoredValueKey(key = null) {
        return themeshark.themesharkFrontend.getWidgetKeyById(this.editedElementId, key)
    },

    getEditedElementStoredValue(key) {
        if (!key) return console.error('key is required to get edited element stored value')
        const storedValueKey = this.getEditedElementStoredValueKey(key)
        return themeshark.themesharkFrontend.getStoredValue(storedValueKey)
    },

    setEditedElementStoredValue(key, val) {
        if (val === undefined) return console.error('value is needed to set edited element stored value')
        const storedValueKey = this.getEditedElementStoredValueKey(key)
        themeshark.themesharkFrontend.setStoredValue(storedValueKey, val)
    },


    getOrSetEditedElementStoredValue(key, val) {
        let storedValue = this.getEditedElementStoredValue(key)
        if (storedValue !== undefined) return storedValue
        this.setEditedElementStoredValue(key, val)
        return val
    },

    /**
     * returns object set in themeshark_settings in the control php file
     * @param {*} controlView 
     * @returns {Object} themeshark_settings control setting
     */
    getThemesharkSettings(controlView) {
        if (!controlView) return null
        return controlView.model.attributes[this.handlerControlKey] || null
    },


    getControlSettings(controlView) {
        return controlView.container?.settings?.attributes || null
    },


    getControlAttributes(controlView) {
        return controlView.model?.attributes || null
    },

    /**
     * Returns the selector for the element. Value of {{WRAPPER}} in selectors
     * @param {ControlView} controlView instance of control view
     */
    getControlWrapperSelector(controlView) {
        const pageId = elementor.config.document.id
        const elementId = this.getElementId(controlView)
        const wrapperSelector = `.elementor-${pageId} .elementor-element.elementor-element-${elementId}`
        return wrapperSelector
    },

    isRepeaterControl(controlView) {
        const container = controlView.getOption('container')
        return container.type === 'repeater'
    },

    /**
     * Returns the ID ex: 'f66c1c0'. Value of {{ID}} in selectors
     * @param {ControlView} controlView instance of control view
     */
    getElementId(controlView) {
        const container = controlView.getOption('container')
        return container.type === 'repeater' ? container.renderer.id : container.id
    },

    /**
     * Returns id of a repeater item
     * @param {ControlView} controlView 
     */
    getRepeaterSlideId(controlView) {
        const container = controlView.getOption('container')
        if (!container.type === 'repeater') console.error('controlView must be a repeater item to get the repeater id: ', controlView)
        return container.id
    },

    getRepeaterSlideView(repeaterItemId) {
        return elementor.getPanelView().getCurrentPageView()._getNestedViews().filter(v => {
            return v.attributes?._id === repeaterItemId
        })[0]
    },

    getRepeaterControlView(controlId, repeaterItemId) {
        return elementor.getPanelView().getCurrentPageView()._getNestedViews().filter(v => {
            return v.container?.id === repeaterItemId // is in repeater group
                && v.model.attributes.name === controlId // has view name
        })[0]
    },



    getControlView(controlId) {
        return elementor.getPanelView().getCurrentPageView().getControlViewByName(controlId)
    },

    /**
     * Returns selector for repeater item HTML Element
     * @param {ControlView} controlView 
     * @returns 
     */
    getControlRepeaterSelector(controlView) {
        const repeaterId = this.getRepeaterSlideId(controlView)
        return `.elementor-repeater-item-${repeaterId}`
    },

    /**
     * Returns true if control variables are found in the string
     * @param {String} string checked string
     * @returns 
     */
    hasVariables(string) {
        return string.match(/{{[A-Z]*}}/) !== null
    },

    /**
     * Query selector 
     * @param {*} selectors 
     */
    getSelectorElements(selector) {
        return elementor.$preview[0].contentWindow.jQuery(selector)
        // return jQuery(elementor.$preview[0].contentDocument).find(selector)
    },

    addSettingsChangeCallback(controlView, callback) {
        controlView.on('settings:change', callback)
        const element = this.getControlElement(controlView)

        if (!element) return
        element.container.view.on('render', () => {
            controlView.off('settings:change', callback)
            controlView.on('settings:change', callback)
        })
    },

    /**
     * Returns selectors with values for the control. if format is true, replaces the variables with the current control values and selectors
     * @param {ControlView} controlView 
     * @param {Boolean} format 
     * @returns {Object} {selector: value}
     */
    getControlSelectorVals(controlView, format = true) {
        const selectors = controlView.model?.get('selectors')
        if (!selectors) {
            console.error('there are no selectors for control view: ', controlView)
            return
        }

        if (!format) return selectors

        const formattedSelectorVals = {}
        for (let [selector, val] of Object.entries(selectors)) {
            let formattedSelector = this.formatControlSelector(controlView, selector)
            let formattedVal = this.formatControlValue(controlView, val)
            formattedSelectorVals[formattedSelector] = formattedVal
        }

        return formattedSelectorVals
    },

    /**
     * Replaces variables in the control value(s)
     * @param {ControlView} controlView 
     * @param {String} selectorString 
     * @returns {String}
     */
    formatControlValue(controlView, valueString) {
        const controlValue = controlView.getControlValue()
        const formattedValue = typeof controlValue === 'object'
            ? Object.entries(controlValue)
                .reduce((formatted, [key, val]) => {
                    return formatted.replace(`{{${key.toUpperCase()}}}`, val)
                }, valueString)
            : valueString.replace('{{VALUE}}', controlValue)

        if (this.hasVariables(formattedValue)) console.error(`could not replace all variables: `, formattedValue)
        return formattedValue
    },


    /**
     * Replaces variables in the control selectors
     * @param {ControlView} controlView 
     * @param {Strong} selectorString 
     * @returns {String}
     */
    formatControlSelector(controlView, selectorString) {
        const replacedVars = {
            '{{WRAPPER}}': this.getControlWrapperSelector(controlView),
            '{{ID}}': this.getElementId(controlView),
            '{{CURRENT_ITEM}}': this.getControlRepeaterSelector(controlView)
        }

        const formattedSelector = Object.entries(replacedVars)
            .reduce((formatted, [selVar, replacedVal]) => {
                return formatted.replace(selVar, replacedVal)
            }, selectorString)
            .replace(/(?:\r\n|\r|\n)/g, '')
            .replace(/\s\s+/g, ' ');

        if (this.hasVariables(formattedSelector)) console.error(`could not replace all variables: `, formattedSelector)
        return formattedSelector
    },

    /**
     * Returns the element that the control is targetting (not the element of the control itself)
     * @param {*} controlView 
     */
    getControlElement(controlView) {
        if (this.isRepeaterControl(controlView)) return controlView._parent._parent.getOption('element')
        return controlView.getOption('element')
    },


    /**
     * Adds control handler that fires when section changes
     * @param {String} key used to access the handler function 
     * @param {ThemesharkControlHandler} handler (this, controlView, handleVal)
     */
    addHandler(key, handler) {
        this._handlers[key] = handler
    },

    /**
     * executes a function when section changes while editing a specified widget
     * @param {String} widgetName name of widget ex: ts-expander
     * @param {String} key handler key
     * @param {Function} handler function executed on section (this, panelView, sectionName)
     */
    addWidgetSectionHandler(widgetName, key, handler) {
        if (!this._widgetSectionHandlers[widgetName]) this._widgetSectionHandlers[widgetName] = {}
        this._widgetSectionHandlers[widgetName][key] = handler
    },

    /**
     * Adds event listener that fires handlers when section changes
     */
    listen() {
        if (this.isListening) return void console.log('themesharkControlsHandler is already listening')

        // fires whenever section is changed in the editor panel

        //page settings fire when page settings sections change, otherwise section:activated
        const pageTriggerEvents = [
            'page_settings:section_page_style:activated',
            'page_settings:document_settings:activated',
            'page_settings:section_custom_css:activated'
        ]

        pageTriggerEvents.forEach(eName => {
            elementor.channels.editor.on(eName, (panelView) => {
                let sectionName = panelView.activeSection
                this.onPanelSectionChange(sectionName, panelView)
            })
        })

        //trigger event for all widgets and sections
        elementor.channels.editor.on('section:activated', (sectionName, panelView) => {
            this.onPanelSectionChange(sectionName, panelView)
        })
        // elementor.channels.deviceMode
        this.isListening = true
    },

    /**
    * Fires whenever a control section is opened and the controls are rendered
    * @param {*} sectionName id of the section defined in start_controls_section($id) in php file 
    * @param {*} panelView View object of the editor panel
    */
    onPanelSectionChange(sectionName, panelView) {
        // allows these to be accessed within handlers
        this.currentSectionName = sectionName
        this.currentPageView = panelView

        //runs section handlers when section changes
        this.runWidgetSectionHandlers(sectionName, panelView)

        // loop through current sections control views to check for handlers
        const controlViews = panelView._getNestedViews()
        controlViews.forEach(view => this.runControlHandlers(view))
    },

    runWidgetSectionHandlers(sectionName, panelView) {
        const elHandlers = this._widgetSectionHandlers[this.editedElementName] || {}
        const sectionHandlers = { ...this._widgetSectionHandlers.all, ...elHandlers }

        for (let [handleKey, handler] of Object.entries(sectionHandlers)) {
            // run handle
            this.currentSectionHandleKey = handleKey
            handler(this, panelView, sectionName)
            this.currentSectionHandleKey = null
        }
    },

    runControlHandlers(controlView) {
        // get themeshark_settings from control
        let themeshark_settings = this.getThemesharkSettings(controlView)
        if (!themeshark_settings) return

        // execute all handles for the control
        for (let [handleKey, handleVal] of Object.entries(themeshark_settings)) {
            const handler = this._handlers[handleKey]
            if (!handler) {
                console.error(`control handler key: ${handleKey} is not a recognized themesharkControlsHandler`)
                continue
            }

            // run handle
            this.currentHandleKey = handleKey
            handler(this, controlView, handleVal)
            this.currentHandleKey = null

        }
    },
}

// activates onPanelSectionChange listener & starts listening for handlers
jQuery(window).on('elementor:init', () => {
    themesharkControlsHandler.listen()
})

window.themesharkControlsHandler = themesharkControlsHandler

