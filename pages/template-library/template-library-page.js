window.addEventListener("load", function () {

    class Fail_Message extends themeshark.RenderHTML {
        constructor(output) {
            super(output)
            this.init()
        }
        render() {
            const $ = this.$
            $("span")._("Could not connect to template library. For help, please contact ")._(
                $("a", {
                    href: "mailto: aevers@themeshark.com"
                })._("aevers@themeshark.com")
            )
        }
    }

    const navTabs = document.querySelectorAll('[data-template-filter]')
    const templateGridWrap = document.getElementById("themeshark-template-grid-wrap")
    const messageContainer = document.getElementById("message-container")
    const filterContainer = document.getElementById("filter-container")

    function showLoader() {
        const loaderTemplate = document.getElementById('ts-template-loader-template')
        const loaderCopy = loaderTemplate.content.cloneNode(true)
        messageContainer.appendChild(loaderCopy)
    }

    function hideLoader() {
        const loader = document.querySelector('.ts-template-loader-large-wrap')
        if (loader) loader.parentNode.removeChild(loader)
    }

    function clearAllContainers() {
        templateGridWrap.innerHTML = ''
        filterContainer.innerHTML = ''
        messageContainer.innerHTML = ''
    }


    /**
     * @param type 'all', 'block', 'page'
     * @param filter 'categories', 'templateSet'
     */
    function filterTemplates(type = 'all', filter = 'categories') {

        clearAllContainers()
        showLoader()

        if (themesharkTemplatesManager.isValidStoredTemplatesMeta) {
            const meta = themesharkTemplatesManager.getStoredTemplatesMeta()
            createPreviewsMasonry(meta, type, filter)
            hideLoader()
        } else {

            themesharkTemplatesManager.downloadTSMeta({
                done: (meta) => {
                    createPreviewsMasonry(meta, type, filter)
                },
                fail: (err) => { new Fail_Message(messageContainer) },
                always: () => hideLoader()
            }, true)
        }

    }


    function sortTemplatesByEvenDistribution(templatePreviews) {
        return Object.values(templatePreviews.reduce((sorted, template) => {
            let set = template.template_set
            if (!sorted[set]) sorted[set] = []
            sorted[set].push(template)
            return sorted
        }, {}))
            .reduce((templatesPositioned, templatesGroup) => {
                let freq = templatePreviews.length / templatesGroup.length
                for (var i = 0; i < templatesGroup.length; i++) {
                    templatesPositioned.push({ template: templatesGroup[i], pos: i * freq + freq / 2 })
                }
                return templatesPositioned
            }, [])
            .sort((a, b) => a.pos < b.pos ? -1 : 1)
            .map(obj => obj.template)
    }



    function createPreviewsMasonry(templatesMeta, type, filter) {

        if (filter !== 'templateSet') templatesMeta = sortTemplatesByEvenDistribution(templatesMeta)

        let templatePreviews = templatesMeta.reduce((templates, meta) => {
            const templatePreview = new TSTemplatePreview(meta, {
                filter: filter
            })

            if (meta.type === type || type === 'all') templates.push(templatePreview.element)
            return templates
        }, [])

        // if (filter !== 'templateSet') templatePreviews = sortTemplatesByEvenDistribution(templatePreviews)

        const masonryGrid = new TSShuffleGrid(templatePreviews, {
            useColumnStyles: false,
            attsGridContainer: {
                id: 'themeshark-template-grid'
            }
        })

        templateGridWrap.appendChild(masonryGrid.gridContainer)
        filterContainer.appendChild(masonryGrid.filterBar)
        masonryGrid.initShuffle()
        document.getElementById('themeshark-template-grid').dataset.curfiltertype = type
    }

    function activateFilter(filter) {
        const targetNavTab = document.querySelector(`[data-template-filter='${filter}']`)

        navTabs.forEach(tab => tab.classList.remove('active'))
        targetNavTab.classList.add('active')

        let type, filterBy

        switch (filter) {
            case 'full-website':
                type = 'page'
                filterBy = 'templateSet'
                break
            case 'page':
                type = 'page'
                filterBy = 'categories'
                break
            case 'block':
                type = 'block'
                filterBy = 'categories'
                break
        }

        filterTemplates(type, filterBy)
    }

    navTabs.forEach(tab => tab.addEventListener('click', e => {
        let filter = e.target.dataset.templateFilter
        activateFilter(filter)
    }))

    activateFilter('page')
})