/**
 * Makes transition-duration: 0s for the contro's selector while the control is changing
 */
themesharkControlsHandler.addHandler('onchange_no_transition', function (controlsHandler, controlView, handleVal) {
    // Note: 'element' refers to the widget/section/column element, not what is being targetted in the control's 'selectors' argument

    //gets the <style> element for control's element where the control's output styles are being held.
    function getControlStylesheet(controlView) {
        const id = controlsHandler.getElementId(controlView)
        const styleEl = elementor.$previewContents.find(`#elementor-style-${id}`)[0]
        return styleEl.sheet || null
    }

    //adds CSS style rule for the control's selector
    function createStyleRule(controlView, ruleString) {
        const stylesheet = getControlStylesheet(controlView)
        const index = stylesheet.cssRules.length
        try {
            stylesheet.insertRule(ruleString, index)
        } catch (err) {
            console.error(`the value for ${controlsHandler.currentHandle} must be in valid css format. ex: "body { font-size: 10px; }"`, err)
        }

        const newRule = stylesheet.cssRules[index]
        return newRule
    }

    /**
     * Removes a style rule from the <style> element that houses the styles for the controls. The cssRule is accessible by being returned in the createStyleRule function.
     * @param {Object} controlView Control View instance
     * @param {CSSStyleRule} cssRule instance of CSSStyleRule (not the selector string)
     */
    function deleteStyleRule(controlView, cssRule) {
        const stylesheet = getControlStylesheet(controlView)
        const index = Array.from(stylesheet.cssRules).indexOf(cssRule)
        if (index >= 0) stylesheet.deleteRule(index)
    }

    // Adds change event for the control
    const onSettingsChange = () => {
        if (handleVal === true && !controlView.model.get('selectors')) {
            return void console.error('cannot handle "onchange_no_transition". no selectors provided from control view: ', controlView)
        }
        let selectorStrings = handleVal === true ? Object.keys(controlView.model.get('selectors')) : handleVal
        if (typeof selectorStrings === 'string') selectorStrings = [selectorStrings]

        if (!selectorStrings) return

        const storedTOKey = `noTransitionTO_${controlView.cid}`
        const tempRulesKey = `noTransitionRules_${controlView.cid}`

        const tempCssRules = themeshark.themesharkFrontend.getOrSetStoredValue(tempRulesKey, [])

        selectorStrings.forEach(selectorString => {
            let selectors = selectorString.split(',') //ex: ['.mySelector', '.yourSelector']

            for (let selector of selectors) {
                if (!selector.match(/[A-Za-z+]/g)) continue
                let cssSelector = controlsHandler.formatControlSelector(controlView, selector)

                const tempCssRule = createStyleRule(controlView, `${cssSelector} { transition: 0s!important; transition-delay: 0s!important; animation-duration:0s!important; --animation-duration: 0s!important;}`)
                tempCssRules.push(tempCssRule)
            }
        })

        themeshark.themesharkFrontend.setStoredValue(tempRulesKey, tempCssRules)
        if (themeshark.themesharkFrontend.getStoredValue(storedTOKey)) return

        let deleteRulesTO = setTimeout(() => {
            let tempRules = themeshark.themesharkFrontend.getOrSetStoredValue(tempRulesKey, [])
            tempRules.forEach(tempCssRule => deleteStyleRule(controlView, tempCssRule))
            themeshark.themesharkFrontend.deleteStoredValue(storedTOKey)
            themeshark.themesharkFrontend.deleteStoredValue(tempRulesKey)
        }, 300)

        themeshark.themesharkFrontend.setStoredValue(storedTOKey, deleteRulesTO)
    }

    controlsHandler.addSettingsChangeCallback(controlView, onSettingsChange)
})
