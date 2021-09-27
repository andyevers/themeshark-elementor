class TSTimelineHandler extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                timeline: '.themeshark-timeline',
                vertLineFront: '.vert-line-front',
                circlesAndLinesFront: '.hor-line-front, .circle-front',
                columns: '.tl-col'
            },

            effectClasses: {
                fadein: 'themeshark-timeline--fadein'
            },

            dataAttributes: {
                scrollOffset: 'scrolloffset',
                moveRate: 'speed',
            }
        }
    }
    getDefaultElements() {
        const selectors = this.getSettings('selectors')
        return {
            $timeline: this.$element.find(selectors.timeline),
            $vertLineFront: this.$element.find(selectors.vertLineFront),
            $circlesAndLinesFront: this.$element.find(selectors.circlesAndLinesFront),
            $columns: this.$element.find(selectors.columns),
        }
    }

    getElementSettings() {
        const { effectClasses, dataAttributes } = this.getSettings()
        const { scrollOffset, moveRate } = dataAttributes
        const { $timeline } = this.elements

        return {
            fadein: $timeline.hasClass(effectClasses.fadein),
            scrollOffset: $timeline.data(scrollOffset), // % offset on viewport. 0 = bottom, 100 = top
            moveRate: $timeline.data(moveRate) //rate in which the lines move on scroll. 1 = same as scrolling rate
        }
    }

    bindEvents() {
        this.addScrollListener()
        jQuery(window).on('resize', () => this.addScrollListener())

        if (!this.isEdit) return
        //delete all stored values on destroy
        const destroyKey = themeshark.themesharkFrontend.getWidgetKey(this, 'destroyRemoveKeys')
        themeshark.themesharkFrontend.addOnceDestroyListener(this.$element[0], destroyKey, () => {
            themeshark.themesharkFrontend.deleteAllWidgetStoredValues(this)
        })
    }

    addScrollListener() {
        const vertLineFront = this.elements.$vertLineFront[0]
        const { $timeline, $circlesAndLinesFront, $columns } = this.elements
        const { scrollOffset, moveRate, fadein } = this.getElementSettings()

        const isCircle = el => el.classList.contains("circle-front")
        const getTop = el => el.getBoundingClientRect().top + scrollY
        const getCenter = el => el.getBoundingClientRect().height / 2 + getTop(el)
        const getDistancePast = (bottomPoint, topPoint) => {
            const distance = bottomPoint - topPoint
            return distance > 0 ? distance : 0
        }

        const computedTimelineStyles = getComputedStyle(vertLineFront)
        const maxHeight = $timeline.height()
        const maxCircle = parseFloat(computedTimelineStyles.getPropertyValue('--tl-item-circle-diam-front'))
        const maxHorLine = parseFloat(getComputedStyle($circlesAndLinesFront.filter('.hor-line-front')[0]).width)

        const getAdjustedScrollDepth = () => { // = position of bottom of window with adjustment applied starting at top of timeline
            const screenStartPosition = innerHeight * (scrollOffset / 100) // % of innerHeight position where the line animation should start in viewport
            const timelineScrollDepth = getDistancePast(scrollY + innerHeight, getTop(vertLineFront)) // distance bottom of viewport is past top of timeline
            const adjustedTimelineScrollDepth = timelineScrollDepth * moveRate - screenStartPosition // apply adjustment starting at timeline top
            return adjustedTimelineScrollDepth + getTop(vertLineFront) // add bouding rect top of timeline
        }

        // Sets the sizes of front circles and lines based on scroll depth
        function adjustLineSizes() {
            const scrollPoint = getAdjustedScrollDepth()
            const distancePast = getDistancePast(scrollPoint, getTop(vertLineFront))
            const heightPercent = Math.min(distancePast / maxHeight, 1)
            vertLineFront.style.setProperty('--vert-scale-y', `${heightPercent}`)

            for (let el of $circlesAndLinesFront) {
                let elReferencePoint = isCircle(el) ? getTop(el) : getCenter(el) // measure from center for lines, top for circles
                let distancePast = getDistancePast(scrollPoint, elReferencePoint)

                if (isCircle(el)) {
                    let scalePercent = Math.min(distancePast / maxCircle, 1)
                    el.style.setProperty('--circle-scale', `${scalePercent}`)
                } else {
                    let scalePercent = Math.min(distancePast / maxHorLine, 1)
                    el.style.setProperty('--hor-scale-x', `${scalePercent}`)
                }
            }
        }

        // Activates fadein to with activeClass if scrollPoint is past top of column
        function adjustColumnsFade() {
            const scrollPoint = getAdjustedScrollDepth()
            const activeClass = 'active'
            for (let col of $columns) {
                let distancePast = getDistancePast(scrollPoint, getTop(col))
                if (distancePast > 0) col.classList.add(activeClass)
                else col.classList.remove(activeClass)
            }
        }

        const onIntersecting = fadein ?
            () => { adjustLineSizes(); adjustColumnsFade(); } :
            () => { adjustLineSizes() }

        themesharkFrontend.getOrSetWidgetScrollObserver(this, 'timelineObserver', {
            targetElements: $timeline[0],
            intersectSettings: {
                onIntersecting: onIntersecting
            }
        })
    }
}
themesharkFrontend.addInitCallback(() => {
    themesharkFrontend.addWidgetHandler('ts-timeline', TSTimelineHandler)
})