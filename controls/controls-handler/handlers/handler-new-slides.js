/**
 * Adds handlers to repeater slides as they are added
 */
themesharkControlsHandler.addHandler('new_slides_add_handlers', function (controlsHandler, controlView, handleVal) {
    if (!handleVal === true) return
    controlView.on('add:child', (model, view) => controlsHandler.runControlHandlers(view))
})