/**
 *  Makes a control require "Sticky" to be turned on in motion effects for the current item or any of the parent sections, otherwise it is disabled and will show a "Requires Sticky" popup when hovering over it.
 */
themesharkControlsHandler.addHandler('sticky', function (controlsHandler, controlView, handleVal) {
    if (handleVal !== true) return

    const $controlEl = controlView.$el

    // if (!panelView) return
    if (!controlsHandler._stickyData) controlsHandler._stickyData = {}
    const stickyData = () => controlsHandler._stickyData

    /******************************
    * Says the element for the current view is sticky or has a sticky parent
    ******************************/
    function isStickyView() {
        const $currentEl = controlView.getOption('element').$el
        if (!$currentEl) return false
        const isSticky = $currentEl.parents('[data-model-cid].elementor-sticky')[0]
            || $currentEl.hasClass('elementor-sticky') ? true : false
        return isSticky
    }

    function openStickySection() {
        const $panelView = elementor.getPanelView().$el
        const $advancedTab = $panelView.find(`.elementor-panel-navigation-tab.elementor-tab-control-advanced`)
        $advancedTab.click()

        const $stickySectionTab = $panelView.find(`.elementor-control-type-section.elementor-control-section_effects`)
        $stickySectionTab.click()
    }

    function $currentStickyNotice() {
        const currentView = elementor.getPanelView().getCurrentPageView()
        if (!currentView) return null
        const $stickyNotice = currentView.$childViewContainer.find('.themeshark-sticky-notice')
        return $stickyNotice
    }

    /******************************
    * Creates notification alerting that sticky needs to be active, includes a link to motion effects section
    ******************************/
    function $getPopupNotification() {
        const $stickyPopup = stickyData()._$stickyPopup || jQuery('<div class="sticky-popup-notification">Requires </div>')
        const $stickyLink = $stickyPopup.find('.sticky-control-link')[0] ?
            $stickyPopup.find('.sticky-control-link') : jQuery('<a class="sticky-control-link">Sticky</a>')

        $stickyLink.off('click')
        $stickyLink.on('click', openStickySection)
        $stickyPopup.append($stickyLink)

        stickyData()._$stickyPopup = $stickyPopup
        return $stickyPopup
    }

    /******************************
    * Makes all sticky controls active 
    ******************************/
    function enableStickyControl() {
        const $stickyNotice = $currentStickyNotice()
        if ($stickyNotice) $stickyNotice.hide()
        $controlEl.removeClass('disabled')
    }

    /******************************
    * Removes pointer events from sticky controls, fades their color and adds the 'requires sticky' popup
    ******************************/
    function disableStickyControl() {
        const $stickyPopup = $getPopupNotification()

        stickyData()._exitTimeout = stickyData().exitTimeout || null

        //show & remove popup hover events
        const onMouseOverControl = e => {
            if (stickyData().exitTimeout) clearTimeout(stickyData().exitTimeout)
            $controlEl.append($stickyPopup)
            $stickyPopup.animate({ opacity: 1 }, 300)
        }

        const onMouseOutControl = e => {
            stickyData().exitTimeout = setTimeout(() => {
                $stickyPopup.animate(
                    { opacity: 0 }, 300,
                    () => $controlEl.remove($stickyPopup))
            }, 300)
        }

        $controlEl.addClass('disabled')
        $controlEl.hover(onMouseOverControl, onMouseOutControl)
    }

    /******************************
    * Sets the sticky controls to active or inactive based on if the current view has sticky
    ******************************/
    if (isStickyView()) enableStickyControl()
    else disableStickyControl()
})
