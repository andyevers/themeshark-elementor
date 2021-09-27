jQuery(window).on('elementor:init', () => {
    themesharkControlsHandler.widgetHandlerEditorFunctions.addSVGTextEditorFunctions = svgTextHandler => {

        svgTextHandler.prototype.onElementChange = function (controlName, controlView) {
            this.updateNoTransitionClass(controlName)
            this.updateRenderSVGText(controlName, controlView)
            this.updateBeforeStateClass(controlName)
        }

        svgTextHandler.prototype.updateRenderSVGText = function (controlName, controlView) {
            const renderKeys = ['animation_duration_custom', 'text']
            if (renderKeys.includes(controlName)) {
                const $svg = controlView.getOption('element').$el.find('svg')

                if (controlName === 'animation_duration_custom') {
                    $svg.attr('data-duration', controlView.getControlValue())
                    $svg.attr('style', `animation-duration: ${controlView.getControlValue()}s `)
                }
                this.createSVGText($svg)
            }
        }

        svgTextHandler.prototype.updateNoTransitionClass = function (controlName) {
            const noTransitionKeys = ['text', 'font_size']
            const noTransitionClass = 'themeshark-no-transition'
            const $element = this.$element

            if (noTransitionKeys.includes(controlName)) {

                if (this._removeNoTransitionStateTO) clearTimeout(this._removeNoTransitionStateTO)

                if (!$element.hasClass(noTransitionClass)) $element.addClass(noTransitionClass)

                this._removeNoTransitionStateTO = setTimeout(() => {
                    $element.removeClass(noTransitionClass)
                }, this.elements.$svg.data('duration') * 1000)

            } else $element.removeClass(noTransitionClass)

        }

        svgTextHandler.prototype.updateBeforeStateClass = function (controlName) {
            const beforeStateKeys = ['stroke_before', 'stroke_width_before']
            const beforeStateClass = 'themeshark-svg-text-before-state'
            const $element = this.$element

            if (beforeStateKeys.includes(controlName)) {
                if (this._removeBeforeStateTO) clearTimeout(this._removeBeforeStateTO)

                if (!$element.hasClass(beforeStateClass)) $element.addClass(beforeStateClass)

                this._removeBeforeStateTO = setTimeout(() => {
                    $element.removeClass(beforeStateClass)
                }, 3000)

            } else this.$element.removeClass(beforeStateClass)
        }
    }
})