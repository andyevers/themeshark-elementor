
class TSNavMenuHandler extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                menu: '.elementor-nav-menu',
                anchorLink: '.elementor-nav-menu--main .elementor-item-anchor',
                dropdownMenu: '.elementor-nav-menu__container.elementor-nav-menu--dropdown',
                menuToggle: '.elementor-menu-toggle',
            }
        };
    }
    getDefaultElements() {
        var selectors = this.getSettings('selectors'),
            elements = {};
        elements.$menu = this.$element.find(selectors.menu);
        elements.$anchorLink = this.$element.find(selectors.anchorLink);
        elements.$dropdownMenu = this.$element.find(selectors.dropdownMenu);
        elements.$dropdownMenuFinalItems = elements.$dropdownMenu.find('.menu-item:not(.menu-item-has-children) > a');
        elements.$menuToggle = this.$element.find(selectors.menuToggle)
        return elements;
    }
    bindEvents() {
        this.initMenu()
        if (!this.elements.$menu.length) {
            return;
        }

        this.elements.$menuToggle.on('click', this.toggleMenu.bind(this));

        if (this.getElementSettings('full_width')) {
            this.elements.$dropdownMenuFinalItems.on('click', this.toggleMenu.bind(this, false));
        }

        elementorFrontend.addListenerOnce(this.$element.data('model-cid'), 'resize', this.stretchMenu);
    }
    initStretchElement() {
        this.stretchElement = new elementorModules.frontend.tools.StretchElement({
            element: this.elements.$dropdownMenu
        });
    }
    toggleMenu(show) {
        var isDropdownVisible = this.elements.$menuToggle.hasClass('elementor-active');

        if ('boolean' !== typeof show) {
            show = !isDropdownVisible;
        }

        this.elements.$menuToggle.attr('aria-expanded', show);
        this.elements.$dropdownMenu.attr('aria-hidden', !show);
        this.elements.$menuToggle.toggleClass('elementor-active', show);

        if (show && this.getElementSettings('full_width')) {
            this.stretchElement.stretch();
        }
    }
    followMenuAnchors() {
        var self = this;
        self.elements.$anchorLink.each(function () {
            if (location.pathname === this.pathname && '' !== this.hash) {
                self.followMenuAnchor(jQuery(this));
            }
        });
    }

    followMenuAnchor($element) {
        var anchorSelector = $element[0].hash;
        var offset = -300,
            $anchor;

        try {
            $anchor = jQuery(decodeURIComponent(anchorSelector));
        } catch (e) {
            return;
        }

        if (!$anchor.length) {
            return;
        }

        if (!$anchor.hasClass('elementor-menu-anchor')) {
            var halfViewport = jQuery(window).height() / 2;
            offset = -$anchor.outerHeight() + halfViewport;
        }

        elementorFrontend.waypoint($anchor, function (direction) {
            if ('down' === direction) {
                $element.addClass('elementor-item-active');
            } else {
                $element.removeClass('elementor-item-active');
            }
        }, {
            offset: '50%',
            triggerOnce: false
        });
        elementorFrontend.waypoint($anchor, function (direction) {
            if ('down' === direction) {
                $element.removeClass('elementor-item-active');
            } else {
                $element.addClass('elementor-item-active');
            }
        }, {
            offset: offset,
            triggerOnce: false
        });
    }
    stretchMenu() {
        if (!(this instanceof TSNavMenuHandler)) return
        if (!this.stretchElement) this.initStretchElement();
        if (this.getElementSettings('full_width')) {
            this.stretchElement.stretch();
            this.elements.$dropdownMenu.css('top', this.elements.$menuToggle.outerHeight());
        } else {
            if (this.stretchElement) {
                this.stretchElement.reset();
            }
        }
    }

    initMenu() {
        if (!this.elements.$menu.length) {
            return;
        }

        var elementSettings = this.getElementSettings(),
            subIndicatorsContent = "<i class=\"".concat(elementSettings.submenu_icon.value, "\"></i>");

        const animationDuration = 150
        this.elements.$menu.smartmenus({
            subIndicators: '' !== subIndicatorsContent,
            subIndicatorsText: subIndicatorsContent,
            subIndicatorsPos: 'append',
            subMenusMaxWidth: '1000px',

            hideFunction: function ($ul, complete) {
                $ul.removeClass('dropdown-active').delay(animationDuration).fadeOut(0, complete)
            },
            showFunction: function ($ul, complete) {
                $ul.fadeIn(0).addClass('dropdown-active').delay(animationDuration).fadeIn(0, complete)
            }
        });

        this.initStretchElement();
        this.stretchMenu();

        if (!elementorFrontend.isEditMode()) {
            this.followMenuAnchors();
        }
    }

    onElementChange(propertyName) {
        if ('full_width' === propertyName) {
            this.stretchMenu();
        }
    }
}

themesharkFrontend.addInitCallback(() => {
    jQuery.SmartMenus.prototype.isCSSOn = function () { return true }
    themesharkFrontend.addWidgetHandler('ts-nav-menu', TSNavMenuHandler)
})