class TSSwiper extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                swiper: '.swiper-container',
            },
        }
    }

    getDefaultElements() {
        const selectors = this.getSettings('selectors')
        return {
            $swiper: this.$element.find(selectors.swiper),
        }
    }

    getSwiperSettings() {
        var elementSettings = this.getElementSettings(),
            slidesToShow = +elementSettings.slides_to_show || 3,
            isSingleSlide = 1 === slidesToShow,
            defaultLGDevicesSlidesCount = isSingleSlide ? 1 : 2,
            elementorBreakpoints = elementorFrontend.config.responsive.activeBreakpoints;

        var swiperOptions = {
            slidesPerView: slidesToShow,
            loop: 'yes' === elementSettings.infinite,
            speed: elementSettings.speed,
            handleElementorBreakpoints: true
        };

        swiperOptions.breakpoints = {};
        swiperOptions.breakpoints[elementorBreakpoints.mobile.value] = {
            slidesPerView: +elementSettings.slides_to_show_mobile || 1,
            slidesPerGroup: +elementSettings.slides_to_scroll_mobile || 1
        };
        swiperOptions.breakpoints[elementorBreakpoints.tablet.value] = {
            slidesPerView: +elementSettings.slides_to_show_tablet || defaultLGDevicesSlidesCount,
            slidesPerGroup: +elementSettings.slides_to_scroll_tablet || 1
        };

        if ('yes' === elementSettings.autoplay) {
            swiperOptions.autoplay = {
                delay: elementSettings.autoplay_speed,
                disableOnInteraction: 'yes' === elementSettings.pause_on_interaction
            };
        }

        if (isSingleSlide) {
            swiperOptions.effect = elementSettings.effect;

            if ('fade' === elementSettings.effect) {
                swiperOptions.fadeEffect = {
                    crossFade: true
                };
            }
        } else {
            swiperOptions.slidesPerGroup = +elementSettings.slides_to_scroll || 1;
        }

        if (elementSettings.image_spacing_custom) {
            swiperOptions.spaceBetween = elementSettings.image_spacing_custom.size;
        }

        var showArrows = 'arrows' === elementSettings.navigation || 'both' === elementSettings.navigation,
            showDots = 'dots' === elementSettings.navigation || 'both' === elementSettings.navigation;

        const ID = this.getID()
        const selPagination = `.swiper-pagination-${ID}`
        const selArrowsWrap = `.swiper-arrows-${ID}`
        const selArrowPrev = `${selArrowsWrap} .elementor-swiper-button-prev`
        const selArrowNext = `${selArrowsWrap} .elementor-swiper-button-next`

        if (showArrows) {
            swiperOptions.navigation = {
                prevEl: selArrowPrev,
                nextEl: selArrowNext
            };
        }

        if (showDots) {
            swiperOptions.pagination = {
                el: selPagination,
                type: 'bullets',
                clickable: true
            };
        }

        return swiperOptions;
    }


    // getSwiperSettings() {
    //     const {
    //         speed,
    //         effect,
    //         infinite,
    //         navigation,
    //         autoplay,
    //         autoplay_speed,
    //         slides_to_show,
    //         slides_to_show_tablet,
    //         slides_to_show_mobile,
    //         slides_to_scroll,
    //         slides_to_scroll_tablet,
    //         slides_to_scroll_mobile,
    //         pause_on_interaction,
    //         image_spacing_custom,
    //     } = this.getElementSettings()

    //     const slidesToShow = +slides_to_show || 3,
    //         isSingleSlide = 1 === slidesToShow,
    //         defaultLGDevicesSlidesCount = isSingleSlide ? 1 : 2,
    //         elementorBreakpoints = elementorFrontend.config.responsive.activeBreakpoints


    //     // SWIPER OPTIONS
    //     //-----------------------------------------------
    //     const swiperOptions = {
    //         slidesPerView: slidesToShow,
    //         loop: 'yes' === infinite,
    //         speed: speed,
    //         handleElementorBreakpoints: true
    //     }

    //     //breakpoints
    //     swiperOptions.breakpoints = {
    //         [elementorBreakpoints.mobile.value]: {
    //             slidesPerView: +slides_to_show_mobile || 1,
    //             slidesPerGroup: +slides_to_scroll_mobile || 1
    //         },
    //         [elementorBreakpoints.tablet.value]: {
    //             slidesPerView: +slides_to_show_tablet || defaultLGDevicesSlidesCount,
    //             slidesPerGroup: +slides_to_scroll_tablet || 1
    //         }
    //     }

    //     //autoplay
    //     swiperOptions.autoplay = 'yes' === autoplay ? {
    //         disableOnInteraction: 'yes' === pause_on_interaction,
    //         delay: autoplay_speed
    //     } : null

    //     //spacing
    //     swiperOptions.spaceBetween = image_spacing_custom ? image_spacing_custom.size : null

    //     //effect / slide groups
    //     if (isSingleSlide) {
    //         swiperOptions.effect = effect
    //         swiperOptions.fadeEffect = 'fade' === effect ? {
    //             crossFade: true
    //         } : null
    //     } else {
    //         swiperOptions.slidesPerGroup = +slides_to_scroll || 1
    //     }


    //     // NAVIGATION SETTINGS
    //     //-----------------------------------------------
    //     const showArrows = ['arrows', 'both'].includes(navigation)
    //     const showDots = ['dots', 'both'].includes(navigation)

    //     const ID = this.getID()
    //     const selPagination = `.swiper-pagination-${ID}`
    //     const selArrowsWrap = `.swiper-arrows-${ID}`
    //     const selArrowPrev = `${selArrowsWrap} .elementor-swiper-button-prev`
    //     const selArrowNext = `${selArrowsWrap} .elementor-swiper-button-next`

    //     swiperOptions.navigation = showArrows ? {
    //         prevEl: selArrowPrev,
    //         nextEl: selArrowNext
    //     } : null

    //     swiperOptions.pagination = showDots ? {
    //         el: selPagination,
    //         type: 'bullets',
    //         clickable: true
    //     } : null

    //     return swiperOptions
    // }

    onInit() {
        const swiperSettings = this.getSwiperSettings()
        const $swiper = this.getDefaultElements().$swiper

        this.swiper = new Swiper($swiper, swiperSettings)
    }
}

themesharkFrontend.addInitCallback(function () {

    const swiperWidgets = [
        'ts-testimonial-carousel'
    ]

    swiperWidgets.forEach(widget => {
        themesharkFrontend.addWidgetHandler(widget, TSSwiper)
    })
})