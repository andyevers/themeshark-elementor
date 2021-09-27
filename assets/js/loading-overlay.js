class TSLoadingOverlay extends themeshark.RenderHTML {
    constructor(options, output) {
        super(output)

        const blueTrans = '#17587db8',
            redTrans = '#5d0202b8',
            blackTrans = '#0a0a0ab8',
            greenTrans = '#134812b8',
            blue = '#16c0ec',
            red = '#ca1b1b'

        const _defaultOptions = {
            loading: {
                text: 'Loading...',
                textColor: '#fff',
                colorOverlay: blackTrans,
                colorIcon: 'white',
            },
            success: {
                text: 'Success!',
                textColor: '#fff',
                colorOverlay: blueTrans,
                colorIcon: blue
            },
            failure: {
                text: 'Error',
                textColor: '#fff',
                colorOverlay: redTrans,
                colorIcon: red,
            },

            iconSize: 80,
            elementClasses: {},
            transition: 1,

            //callbacks
            onDestroy: null,
            onFadeOut: null,
            onStateChange: null,
            onIconAnimationEnd: null
        }

        this.curState = null

        const updateObject = (origObject, updateObject) => {
            if (!updateObject) updateObject = {}
            return Object.entries(origObject).reduce((newObj, [key, val]) => {
                newObj[key] = key in updateObject ? updateObject[key] : val
                return newObj
            }, {})
        }

        this.options = updateObject(_defaultOptions, options)
        this.options.loading = updateObject(_defaultOptions.loading, options.loading)
        this.options.success = updateObject(_defaultOptions.success, options.success)
        this.options.failure = updateObject(_defaultOptions.failure, options.failure)

        this.validStates = ['success', 'loading', 'failure']
        this._verifyValidState = (state) => {
            if (!this.validStates.includes(state)) {
                console.error(`${state} is not a valid state`)
                return false
            }
            return true
        }


        /**
         * Adds delay and fade out before removing element from DOM
         * @param {Int} delay how long in MS before initiating fade out. 
         * @param {Int} fadeOutDuration how long fade out should last. 
         */
        this.fadeDestroy = (duration = 500, delay = 1500) => {
            const _this = this
            setTimeout(() => {
                if (_this.options.onFadeOut) _this.options.onFadeOut(_this.curState, _this)
                const exitAnimation = this.element.animate([{ opacity: 0 }], { duration: duration })
                exitAnimation.onfinish = function () { _this.destroy() }
            }, delay)
        }

        this.destroy = () => {
            if (!this.element.parentNode) return
            this.element.parentNode.removeChild(this.element)
            if (this.options.onDestroy) this.options.onDestroy(this.curState, this)
            this.reset()
        }

        this.setState = (state) => {
            if (!this._verifyValidState(state)) return
            const { overlay, textWrap } = this.elements

            //set overlay class
            this.validStates.forEach(state => overlay.classList.remove(`state-${state}`))
            overlay.classList.add(`state-${state}`)

            const textClassActive = 'loading-text-active'
            const textClassDeactivate = 'loading-text-deactivate'

            // remove previous text
            const previousText = textWrap.querySelector(`.${textClassActive}`)
            previousText.classList.remove(textClassActive)
            previousText.classList.add(textClassDeactivate)

            //add new state text
            const targetText = textWrap.querySelector(`.ts-text-${state}`)
            targetText.classList.remove('no-animation')
            targetText.classList.remove(textClassDeactivate)
            targetText.classList.add(textClassActive)

            // set check or x
            const checkmarkPath = "M14.1 27.2l7.1 7.2 16.7-16.8"
            const xPath = "M15 15L37 37M15 37L37 15"
            const pathString = state === 'failure' ? xPath : checkmarkPath
            this.elements.check.setAttribute('d', pathString)

            this.curState = state

            if (this.options.onStateChange) this.options.onStateChange(state, this)

            //update stylesheet
            this.updateStyles(state)
        }

        this.reset = () => {
            const { loading, success, failure } = this.options
            const { textLoading, textSuccess, textFailure } = this.elements
            textLoading.innerText = loading.text
            textSuccess.innerText = success.text
            textFailure.innerText = failure.text

            this.statusTextEls.forEach(el => el.classList.add('no-animation'))
            this.setState('loading')
        }



        this.createKeyframesString = (name, keyframesObj) => {
            let keyframesString = Object.entries(keyframesObj).reduce((keyframesString, [percentInt, props]) => {
                let propsString = this.createCSSString(`${percentInt}%`, props)
                keyframesString += propsString
                return keyframesString
            }, '')
            return `@keyframes ${name} { ${keyframesString} }`
        }

        this.createCSSString = (selectorText, props) => {
            const propsString = Object.entries(props).reduce((propsString, [prop, val]) => {
                propsString += `${prop}: ${val}; `
                return propsString
            }, '')
            return `${selectorText} { ${propsString}} `
        }
        this.createCSSStrings = (rulesObj) => {
            let cssStrings = ''
            for (let [selector, props] of Object.entries(rulesObj)) {
                let cssString = this.createCSSString(selector, props)
                cssStrings += cssString
            }
            return cssStrings
        }

        this.updateStyles = (state) => {
            if (!this._verifyValidState(state)) return
            const { colorOverlay, colorIcon } = this.options[state]
            const { transition, iconSize } = this.options
            const cssStyles = this.createCSSStrings({
                '.ts-loading-overlay': {
                    'background-color': colorOverlay,
                    'transition': `${transition * 1.3}s`,
                    'position': 'absolute',
                    'top': 0,
                    'right': 0,
                    'bottom': 0,
                    'left': 0,
                    'display': 'flex',
                    'flex-direction': 'column',
                    'justify-content': 'center',
                    'text-align': 'center',
                    'z-index': 99999
                },
                '.ts-loading-content': {
                    'margin': 'auto'
                },
                '.ts-loading-text': {
                    'color': 'white',
                    'font-size': '35px',
                    position: 'absolute',
                    display: 'block',
                    width: '100%',
                    opacity: 0,
                },
                '.loading-text-deactivate:not(.no-animate)': {
                    animation: `loading-text-deactivate ${transition * .5}s forwards`
                },
                '.loading-text-deactivate.no-animate': {
                    animation: 'none',
                    opacity: 0,
                },
                '.loading-text-active': {
                    animation: 'loading-text-active .5s forwards'
                },
                '.ts-loader-checkmark__circle': {
                    'stroke-dasharray': 166,
                    'stroke-width': 7,
                    'stroke-miterlimit': 10,
                    'fill': 'none',
                    'transition': `${transition}s`,
                    'animation': 'rotate 1s linear forwards infinite',
                    'transform-origin': 'center center',
                    'stroke': colorIcon
                },
                '.ts-loading-overlay.state-loading .ts-loader-checkmark__circle': {
                    'stroke-dashoffset': 140
                },
                '.ts-loading-overlay:not(.state-loading) .ts-loader-checkmark_circle': {
                    'stroke-width': 0,
                    'transition': `${transition}s`,
                    'stroke-dashoffset': 0,
                    'animation': `stroke ${transition} cubic-bezier(0.65, 0, 0.45, 1) forwards infinite`,
                },

                '.ts-loader-checkmark': {
                    'width': `${iconSize}px`,
                    'height': `${iconSize}px`,
                    'border-radius': '50%',
                    'display': 'block',
                    'stroke-width': 2,
                    'stroke-miterlimit': 10,
                    'margin': '50px auto',
                    'box-shadow': `inset 0px 0px 0px ${colorIcon}`,
                },
                '.ts-loading-overlay:not(.state-loading) .ts-loader-checkmark': {
                    'animation': `fill ${transition * .4}s ease-in-out ${transition * .3}s forwards, scale .${transition * .3}s ease-in-out ${transition * .3}s both`,
                    'transition': `${transition * .4}s`,
                },
                '.ts-loader-checkmark__check': {
                    'transform-origin': '50% 50%',
                    'stroke-dasharray': 48,
                    'stroke-dashoffset': 48,
                    'stroke': '#fff',
                },
                '.ts-loading-overlay:not(.state-loading) .ts-loader-checkmark__check': {
                    'animation': `stroke ${transition * .3}s cubic-bezier(0.65, 0, 0.45, 1) ${transition * .8}s forwards`,
                    'animation-fill-mode': 'forwards',
                },
                '.ts-loading-text-wrap': {
                    'margin-top': '50px'
                },
            })

            const kfRotate = this.createKeyframesString('rotate', {
                0: { transform: 'rotate(0)' },
                100: { transform: 'rotate(360deg)' }
            })

            const kfStroke = this.createKeyframesString('stroke', {
                100: {
                    'stroke-dashoffset': 0,
                    transform: 'rotate(0)'
                }
            })
            const kfStrokeCircle = this.createKeyframesString('strokeCircle', {
                100: {
                    'stroke-dashoffset': 0,
                    stroke: colorIcon,
                    transform: 'rotate(0)'
                }
            })
            const kfScale = this.createKeyframesString('scale', {
                0: { transform: 'none' },
                50: { transform: 'scale3d(1.1, 1.1, 1)' },
                100: { transform: 'none' },
            })
            const kfFill = this.createKeyframesString('fill', {
                0: { 'box-shadow': `inset 0 0 0 ${colorIcon}` },
                100: { 'box-shadow': `inset 0 0 0 ${iconSize / 2 + 2}px ${colorIcon}` },
            })
            const kfTextActive = this.createKeyframesString('loading-text-active', {
                0: { opacity: 0, transform: 'translateY(50px)' },
                100: { opacity: 1, transform: 'translateY(0px)' },
            })
            const kfTextDeactivate = this.createKeyframesString('loading-text-deactivate', {
                0: { opacity: 1, transform: 'translateY(0px)' },
                100: { opacity: 0, transform: 'translateY(-50px)' },
            })

            const keyframes = kfRotate + kfStroke + kfStrokeCircle + kfScale + kfFill + kfTextActive + kfTextDeactivate

            this.elements.styleEl.innerHTML = cssStyles + keyframes

        }

        this.init()
        this.setState('loading')
    }

    get activeTextEl() {
        return this.elements.textWrap.querySelector('.loading-text-active')
    }

    get statusTextEls() {
        return this.elements.textWrap.querySelectorAll('.ts-loading-text')
    }

    onBeforeOutput() {
        for (let [selector, classes] of Object.entries(this.options.elementClasses)) {
            this.element.parentNode.querySelectorAll(selector).forEach(el => {
                classes.forEach(c => el.classList.add(c))
            })
        }

        const _this = this
        this.element.querySelector('.ts-loader-checkmark__check').addEventListener('animationend', e => {
            if (_this.options.onIconAnimationEnd) _this.options.onIconAnimationEnd(this.curState, this)
        })

        this.updateStyles('loading')
    }

    render() {
        const $ = this.$
        const { loading, success, failure } = this.options

        $('div', { _key: 'overlay', class: 'ts-loading-overlay' })._(
            $('style', { _key: 'styleEl' }),
            $('div', { class: 'ts-loading-spinner' })._(
                $('svg', { class: 'ts-loader-checkmark', xmlns: 'http://www.w3.org/2000/svg', viewBox: '0 0 52 52' })._(
                    $('circle', { class: "ts-loader-checkmark__circle", cx: "26", cy: "26", r: "25", fill: "none" }),
                    $('path', { _key: 'check', class: 'ts-loader-checkmark__check', d: "M14.1 27.2l7.1 7.2 16.7-16.8", fill: 'none' }),
                )
            ),
            $('div', { _key: 'textWrap', class: 'ts-loading-text-wrap' })._(
                $('div', { _key: 'textLoading', class: 'ts-loading-text ts-text-loading loading-text-active' })._(loading.text),
                $('div', { _key: 'textSuccess', class: 'ts-loading-text ts-text-success no-animation' })._(success.text),
                $('div', { _key: 'textFailure', class: 'ts-loading-text ts-text-failure no-animation' })._(failure.text),
            )
        )
    }
}