jQuery(window).on('elementor:init', () => {
    const selectConditionalView = elementor.modules.controls.Select.extend({
        /* option_sets => [my_set=>[$opts_array]], defaults => [set=>'my_set',options=>['my_set'=>'my_opt']], conditional => 'conditional_id' */

        // used for repeater controls to reference controls outside of the repeater. ex: "EXTERNAL.conditional_control"
        REPEATER_EXTERNAL_PREFIX: 'EXTERNAL.',

        // if the control is inside a repeater control
        isRepeaterControl: function () {
            return this.container.type === 'repeater'
        },

        // if the both this control and conditional control are in the same repeater control item
        isLocalRepeaterConditional: function () {
            if (!this.isRepeaterControl()) return false
            const conditionalAtt = this.getControlAttributes('conditional')
            return conditionalAtt.lastIndexOf(this.REPEATER_EXTERNAL_PREFIX, 0) !== 0
        },

        // control id of the conditional control
        getConditionalKey: function () {
            const conditionalAtt = this.getControlAttributes('conditional')
            return this.isLocalRepeaterConditional()
                ? conditionalAtt : conditionalAtt.replace(this.REPEATER_EXTERNAL_PREFIX, '')
        },

        // current control value of conditional control
        getConditionalValue: function () {
            const settingsContainer = this.isRepeaterControl() && !this.isLocalRepeaterConditional()
                ? this.container.parent.parent : this.container

            const settings = settingsContainer.settings.attributes
            return settings[this.getConditionalKey()]
        },

        // conditional control view (only returns view when conditional control is in the same section)
        getConditionalView: function () {
            const conditionalKey = this.getConditionalKey()
            if (this.isLocalRepeaterConditional()) return this.getRepeaterControlView(conditionalKey, this.container.id)
            const currentPageViews = Object.values(elementor.getPanelView().getCurrentPageView().children._views)
            return currentPageViews.filter(view => view.model?.name === conditionalKey)[0]
        },

        // gets control view by ID in specified repeater control group
        getRepeaterControlView: function (controlId, repeaterItemId) {
            return elementor.getPanelView().getCurrentPageView()._getNestedViews().filter(v => {
                return v.container?.id === repeaterItemId // is in repeater group
                    && v.model.attributes.name === controlId // has view name
            })[0]
        },

        // returns control args
        getControlAttributes: function (attribute) {
            const attributes = this.model.attributes
            return attribute ? attributes[attribute] : attributes
        },

        // returns 'set' and 'options' from 'defaults' arg, or the first set/option keys in each option group if not set
        getDefaults: function (key) {
            const optionSets = this.getOptionSets()
            const defaults = this.getControlAttributes('defaults') || {}
            const setKeys = Object.keys(optionSets)

            //if key/options not set in control, returns the first found option/key in the option sets
            const defaultSetKey = defaults.set !== undefined ? defaults.set : setKeys[0]
            const defaultOptions = setKeys.reduce((defaultOpts, setKey) => {
                let availableOptions = Object.keys(optionSets[setKey])
                let defaultOption = defaults.options ? defaults.options[setKey] : undefined

                // uses first option if defaultOption is not set or is not valid
                defaultOpts[setKey] = availableOptions.includes(defaultOption) ? defaultOption : availableOptions[0]
                return defaultOpts
            }, {})

            const controlDefaults = { set: defaultSetKey, options: defaultOptions }
            return key ? controlDefaults[key] : controlDefaults
        },

        //returns option sets provided in the option_sets control arg
        getOptionSets: function (setKey) {
            const optionSets = this.getControlAttributes('option_sets') || {}
            return setKey ? optionSets[setKey] : optionSets
        },

        // sets control select element options to option setKey provided - uses default if no matching option set
        setOptionSet: function (setKey, selectedOption = null) {
            const optionSet = this.getOptionSets(setKey) || this.getOptionSets(getDefaults('set'))
            if (!optionSet) return console.error('Could not find option set for select conditional control')
            const $select = this.ui.select
            $select.empty()

            for (let [val, label] of Object.entries(optionSet)) {
                let atts = `value='${val}'`
                if (selectedOption === val) atts += ' selected'
                $select.append(`<option ${atts}>${label}</option>`)
            }
        },

        // sets the control option set based on the conditional control value
        updateOptionSet: function () {
            const controlValue = this.getControlValue()
            const conditionalValue = this.getConditionalValue()
            const defaultValues = this.getDefaults()
            const optionSets = this.getOptionSets()

            const currentSetKey = Object.keys(optionSets).includes(conditionalValue) // set key is present
                ? conditionalValue : defaultValues.set //uses default set if no matching option group

            const selectValue = Object.keys(optionSets[currentSetKey]).includes(controlValue) // option is present
                ? controlValue : defaultValues.options[currentSetKey] //uses default option if no matching option key

            this.setOptionSet(currentSetKey, selectValue)
        },

        //if conditional and control are in the same section, add change listener to conditional
        addConditionalListener: function () {
            const conditionalView = this.getConditionalView()
            if (conditionalView) this.listenTo(conditionalView, 'settings:change', this.onConditionalChange)
        },

        //fires when conditional control settings change
        onConditionalChange: function () {
            this.updateOptionSet()
        },

        //fires when this control changes
        onInputChange: function () {
            this.applySavedValue()
        },

        //fires when view is loaded
        onReady: function () {
            setTimeout(() => {
                this.addConditionalListener()
                this.updateOptionSet()
            })
        },
    })

    elementor.addControlView('select-conditional', selectConditionalView);
})
