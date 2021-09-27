/**
 * Used in all link element handlers
 * @param {*} controlView 
 * @param {*} handleVal 
 * @returns 
 */
themesharkControlsHandler.getLinkElementData = function (controlView, handleVal) {
    const {
        selector = null,
        multiple = true,
        attribute = null,
        value = controlView.getControlValue()
    } = handleVal

    if (!selector) console.error('selector is required to link element', controlView, handleVal)

    const formattedSelector = this.formatControlSelector(controlView, selector)
    const formattedValue = this.formatControlValue(controlView, value)
    const $selectorResult = this.getSelectorElements(formattedSelector)
    const $targets = multiple ? $selectorResult : jQuery($selectorResult[0])

    if (!$selectorResult[0]) console.error(`can't link element. no selector results for ${formattedSelector}`)

    return {
        $targets: $targets,
        selector: formattedSelector,
        value: formattedValue,
        attribute: attribute
    }
}

/**
 * Updates inner text whenever the value of the control changes
 */
themesharkControlsHandler.addHandler('link_text', function (controlsHandler, controlView, handleVal) {
    const onSettingsChange = () => {
        const { $targets, value } = controlsHandler.getLinkElementData(controlView, handleVal)
        $targets.text(value)
    }
    controlsHandler.addSettingsChangeCallback(controlView, onSettingsChange)
})

/**
 * Updates html whenever the value of the control changes
 */
themesharkControlsHandler.addHandler('link_html', function (controlsHandler, controlView, handleVal) {
    const onSettingsChange = () => {
        const { $targets, value } = controlsHandler.getLinkElementData(controlView, handleVal)
        $targets.html(value)
    }
    controlsHandler.addSettingsChangeCallback(controlView, onSettingsChange)
})

/**
 * Updates an attribute whenever the value of the control changes
 */
themesharkControlsHandler.addHandler('link_attribute', function (controlsHandler, controlView, handleVal) {
    const onSettingsChange = () => {
        const { $targets, attribute, value } = controlsHandler.getLinkElementData(controlView, handleVal)
        $targets.attr(attribute, value)
    }
    controlsHandler.addSettingsChangeCallback(controlView, onSettingsChange)
})


/**
 * Updates an attribute whenever the value of the control changes
 */
themesharkControlsHandler.addHandler('link_class', function (controlsHandler, controlView, handleVal) {
    const onSettingsChange = () => {
        const { $targets, value } = controlsHandler.getLinkElementData(controlView, handleVal)
        const previousClass = $targets.data('editor-toggled-class') || value
        if (previousClass) $targets.removeClass(previousClass)
        $targets.addClass(value)
        $targets.data('editor-toggled-class', value)
    }
    onSettingsChange()
    controlsHandler.addSettingsChangeCallback(controlView, onSettingsChange)
})


/**
 * Replaces element with a copy with updated tag whenever the value of the control changes. NOTE: This will remove event listeners
 */
themesharkControlsHandler.addHandler('link_replace_tag', function (controlsHandler, controlView, handleVal) {
    const onSettingsChange = () => {
        const { $targets, value } = controlsHandler.getLinkElementData(controlView, handleVal)
        $targets.each(function () {
            let $target = jQuery(this)
            let attString = Object.values(this.attributes)
                .reduce((attrs, attribute) => attrs += ` ${attribute.name}='${attribute.value}'`, '')

            $target.replaceWith(`<${value} ${attString}>${$target.html()}</${value}>`)
        })
    }
    controlsHandler.addSettingsChangeCallback(controlView, onSettingsChange)
})
