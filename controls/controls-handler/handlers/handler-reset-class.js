themesharkControlsHandler.addHandler('reset_wrapper_class', function (controlsHandler, controlView, handleVal) {
    /******************************
     * Removes a class from an element, then adds it back after 50ms
     * @param {HTMLElement} element Element that you want to remove and re-add the class to
     * @param {String} resetClassName name of class to be reset
     *****************************/
    function resetClass(element, resetClassName) {
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
    }

    const className = handleVal
    const element = controlView.getOption('element')?.$el[0]
    if (!element) console.error('cannot get element for control: ', controlView)

    // Adds change event for the control
    controlView.on('settings:change', () => {
        resetClass(element, className)
    })
})
