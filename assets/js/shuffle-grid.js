


class TSShuffleGrid extends themeshark.RenderHTML {

    /**
     * Used for masonry grid
     * @param {Array} gridElements elements must have 'data-groups' att.
     * @param {Object} options 
     * @param {HTMLElement} output 
     */
    constructor(gridElements, options = {}, output = null) {

        if (typeof Shuffle === 'undefined') return void console.error('Shuffle library is required')

        super(output)

        const _defaultOptions = {
            attsFilterMenu: {},
            attsGridContainer: {},
            includeFilterAll: true,
            shuffleSettings: {},
            useColumnStyles: true,
            columns: 4,
            gap: 15,
            startingFilter: '_ALL',
            filterLabels: {
                '_ALL': 'All'
            }
        }


        // ____PROPERTIES____ //
        this.shuffle = null
        this.gridElements = gridElements
        this.options = Object.entries(_defaultOptions).reduce((opts, [key, val]) => {
            opts[key] = key in options ? options[key] : val
            return opts
        }, {})

        gridElements.forEach(gridEl => { // confirm elements have filter class
            if (!gridEl.hasAttribute('data-groups')) {
                return void console.error(`could not find category attribute data-grouops in el:`, gridEl)
            }
        })


        // ____METHODS____ //

        this.initShuffle = () => {
            const shuffleDefaultSettings = { itemSelector: '.grid-item' }
            const shuffleSettings = { ...shuffleDefaultSettings, ...this.options.shuffleSettings }

            this.shuffle = new Shuffle(this.gridContainer, shuffleSettings)
        }

        this.activateFilter = (filterName) => {
            if (!this.shuffle) this.initShuffle()
            const filters = this.filterBar.querySelectorAll('[data-filter]')
            const targFilter = Array.from(filters).filter(filter => filter.dataset.filter === filterName)[0]

            filters.forEach(filter => filter.classList.remove('active'))
            targFilter.classList.add('active')

            this.shuffle.filter(filterName === '_ALL' ? this.shuffle.ALL_ITEMS : filterName)
        }

        this.onFilterClick = (e) => {
            const filterName = e.target.dataset.filter
            this.activateFilter(filterName)
        }

        this._addObjects = (object1, object2) => { // vals must be strings
            for (let [prop, val] of Object.entries(object2)) {
                if (typeof val !== 'string') return void console.error('only strings can be added')
                if (prop in object1) object1[prop] += ' ' + val
                else object1[prop] = val
            }
            return object1
        }

        this.renderFilterMenu = () => {
            const { $, f, e } = this
            const filters = this.filters
            const attsDefault = { class: 'ts-filter-menu' }
            const attsAdditional = this.options.attsFilterMenu
            const attsFilterMenu = this._addObjects(attsDefault, attsAdditional)

            if (this.options.includeFilterAll === true) filters.unshift('_ALL')

            $('div', attsFilterMenu)._(f(() => {
                filters.forEach(filter => {
                    const customLabels = this.options.filterLabels
                    const label = filter in customLabels ? customLabels[filter] : filter

                    $('a', { class: 'filter-item', 'data-filter': filter },
                        e('click', this.onFilterClick))._(label)
                })
            }))
        }

        this.destroy = () => {
            if (this.filterBar.parentNode) this.filterBar.parentNode.removeChild(this.filterBar)
            if (this.gridContainer.parentNode) this.gridContainer.parentNode.removeChild(this.gridContainer)
        }

        this.renderGrid = () => {
            const $ = this.$
            const attsDefault = { class: 'ts-grid-container', 'data-curfilter': '' }
            const attsAdditional = this.options.attsGridContainer
            const attsGridContainer = this._addObjects(attsDefault, attsAdditional)

            $('div', attsGridContainer)._(
                $('style', { _key: 'styleEl' })
            )
        }

        this.setCSSRules = (rulesObj) => {
            const styleEl = this.elements.styleEl
            const createCSSString = (selectorText, props) => {
                const propsString = Object.entries(props).reduce((propsString, [prop, val]) => {
                    propsString += `${prop}: ${val}; `
                    return propsString
                }, '')
                return `${selectorText} { ${propsString}} `
            }

            styleEl.innerHTML = ''
            for (let [selector, props] of Object.entries(rulesObj)) {
                let cssString = createCSSString(selector, props)
                styleEl.innerHTML += cssString
            }
            return true
        }


        this._updateCols = () => {
            this.setCSSRules({
                '.ts-grid-container > .grid-item': {
                    'width': `calc(100% / ${this.columns} - ${this.gap}px)`,
                    'margin': `${this.gap / 2}px`
                }
            })
            this.initShuffle()
            return true
        }

        this.init()
    }

    onBeforeOutput() {
        // After grid container is created, insert each grid element

        this.gridElements.forEach(gridEl => {
            gridEl.classList.add('grid-item')
            this.gridContainer.appendChild(gridEl)
        })
        const { columns, gap, startingFilter } = this.options

        this.columns = columns
        this.gap = gap
        this.activateFilter(startingFilter)
    }

    get filters() {

        return this.gridElements.reduce((filters, gridEl) => {

            const categoryString = gridEl.getAttribute('data-groups')
                .replaceAll(/\[|\]|\"|\'/g, '') //remove brackets for array strings

            const categories = categoryString.split(',')
            categories.forEach(category => {
                if (!filters.includes(category)) filters.push(category)
            })
            return filters
        }, [])
    }

    get columns() {
        return this.options.columns
    }

    set columns(cols) {
        if (typeof cols !== 'number') return void console.error('cols must be a number')
        this.options.columns = cols
        if (this.options.useColumnStyles === true) this._updateCols()
    }

    get gap() {
        return this.options.gap
    }

    set gap(gap) {
        if (typeof gap !== 'number') return void console.error('gap must be a number')
        this.options.gap = gap
        if (this.options.useColumnStyles === true) this._updateCols()
    }

    get filterBar() {
        return this.topLevelEls.filter(el => el.classList.contains('ts-filter-menu'))[0]
    }

    get gridContainer() {
        return this.topLevelEls.filter(el => el.classList.contains('ts-grid-container'))[0]
    }

    render() {
        this.renderFilterMenu()
        this.renderGrid()
    }
}

