(function ($) {
    "use strict"


    function getAddSectionViews() {
        const previewChildViews = Object.values(elementor.getPreviewView().children?._views || {})
        const addSectionViews = previewChildViews.filter(view => {
            return 'object' === typeof view && view.addSectionView !== null
        }).map(view => view.addSectionView)
        return addSectionViews
    }

    // TEMPLATE SORTERS
    //-----------------------------------------------
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

    function sortTemplatesByKit(templatePreviews) {
        const getPriority = (category) => {
            const pageOrder = ['Helix Fitness', 'Buildi Builders']
            const index = pageOrder.indexOf(category)
            const priority = index >= 0 ? index : pageOrder.length
            return priority
        }
        return templatePreviews.sort((a, b) => {
            let categoryA = a.template_set,
                categoryB = b.template_set
            let priorityA = getPriority(categoryA),
                priorityB = getPriority(categoryB)

            if (priorityA === priorityB) return categoryA < categoryB ? -1 : 1
            return priorityA < priorityB ? -1 : 1
        })
    }

    function sortTemplatesByCategory(templatePreviews) {
        const getPriority = (category) => {
            const pageOrder = ['Homepage', 'About', 'Product / Service']
            const index = pageOrder.indexOf(category)
            const priority = index >= 0 ? index : pageOrder.length
            return priority
        }
        return templatePreviews.sort((a, b) => {
            let categoryA = a.ts_categories[0],
                categoryB = b.ts_categories[0]
            let priorityA = getPriority(categoryA),
                priorityB = getPriority(categoryB)

            if (priorityA === priorityB) return categoryA < categoryB ? -1 : 1
            return priorityA < priorityB ? -1 : 1
        })
    }


    $("document").ready(function () {

        let templateAddSection = $("#tmpl-elementor-add-section")
        if (0 < templateAddSection.length) {
            var oldTemplateButton = templateAddSection.html()
            oldTemplateButton = oldTemplateButton.replace(
                '<div class="elementor-add-section-drag-title',
                '<div class="elementor-add-section-area-button elementor-add-themeshark-template-button" title="ThemeShark"><i class="eicon-folder"></i></div><div class="elementor-add-section-drag-title'
            )
            templateAddSection.html(oldTemplateButton)
        }



        function createLoadingOverlay(output = null) {
            const loadingOverlay = new TSLoadingOverlay({
                loading: { text: 'Downloading...' },
                success: { text: 'Template Added!' },
                failure: { text: 'Error Downloading' },
                onIconAnimationEnd: (state, overlay) => {
                    overlay.fadeDestroy(800, 1000)
                    const $widgetContent = window.tsModal.getElements('widgetContent')
                    $widgetContent.css({ opacity: 0 })
                },
                onDestroy: (state, overlay) => {
                    window.tsModal.destroy()
                }
            }, output)
            return loadingOverlay
        }


        // TEMPLATE INSERT STEPS
        //-----------------------------------------------

        // 1) BUTTON CLICK - determine if owned and 
        function onImportButtonClick(e, template) {
            //add loader overlay
            window.tsLoadingOverlay = createLoadingOverlay(jQuery('#themeshark-template-popup')[0])
            window.tsLoadingOverlay.setState('loading')

            if (template.isOwned) {
                // if owned, skip to step 4 - import from library
                getLibData(template.libraryID)
            } else {
                // step 2 start
                startDownload(template.ID)
            }
        }

        // 2) START DOWNLOAD
        function startDownload(templateId) {
            const { textLoading, textFailure } = window.tsLoadingOverlay.elements
            textLoading.innerText = 'Downloading...'

            themesharkTemplatesManager.downloadTSJSON(templateId, {
                //DOQNLOAD SUCCESS - start import
                done: function (templateData) {
                    //step 3 start
                    startImport(templateId, templateData)
                },

                //DOWNLOAD FAIL
                fail: function (err) {
                    textFailure.innerText = 'Error Downloading'
                    window.tsLoadingOverlay.setState('failure')
                    console.error(err)
                }
            })
        }

        // 3) IMPORT TEMPLATE
        function startImport(templateId, templateData) {
            const { textLoading, textFailure } = window.tsLoadingOverlay.elements
            textLoading.innerText = 'Importing...'

            themesharkTemplatesManager.import(templateId, templateData, {
                //IMPORT SUCCESS - get data
                done: function (templatePostId) {
                    //step 4 start
                    getLibData(templatePostId)
                },

                //IMPORT FAIL
                fail: function (err) {
                    textFailure.innerText = 'Error Importing'
                    window.tsLoadingOverlay.setState('failure')
                    console.error(err)
                }
            })
        }


        // 4) GET TEMPLATE CONTENT FROM LIBRARY
        function getLibData(templatePostId) {
            const { textLoading, textFailure } = window.tsLoadingOverlay.elements
            textLoading.innerText = 'Getting Content...'

            themesharkTemplatesManager.getTemplateData(templatePostId, {
                done: function (templateData) {
                    //step 5 start
                    insertTemplate(templateData)
                },
                fail: function (err) {
                    textFailure.innerText = 'Error Getting Template Data'
                    window.tsLoadingOverlay.setState('failure')
                    console.error(err)
                }
            })
        }

        // 5) INSERT TEMPLATE CONTENT
        function insertTemplate(templateData) {
            const { textLoading, textFailure } = window.tsLoadingOverlay.elements
            textLoading.innerText = 'Inserting Template...'

            themesharkTemplatesManager.insertFromContent(templateData.content, {
                //INSERT SUCCESS - close popup and loader
                onInsertSuccess: function () {
                    window.tsLoadingOverlay.setState('success')
                    getAddSectionViews().forEach(v => v.fadeToDeath()) //remove all add section boxes
                },

                //INSERT FAIL
                onInsertFail: function (err) {
                    textFailure.innerText = 'Error Inserting'
                    window.tsLoadingOverlay.setState('failure')
                    console.error(err)
                },

                //ALWAYS
                onFinish: function () {
                    // fade out loader and popup
                    window.tsLoadingOverlay.fadeDestroy(800, 1500)
                    window.tsModal.getElements('widgetContent').animate({
                        opacity: 0
                    }, 1000)
                }
            })
        }

        // TEMPLATE PREVIEW
        //-----------------------------------------------
        /**
         * 
         * @param {*} templatesJson 
         * @param {*} filterBy categories, templateSet
         * @returns 
         */
        function createTemplatePreviews(templatesJson) {
            const templatesMeta = templatesJson
            const $widget = window.tsModal.getElements('widget')
            $widget.addClass('tsdry')

            const templatePreviews = templatesMeta.reduce((templates, meta) => {

                const templatePreview = new TSTemplatePreview(meta, {
                    elementClasses: {
                        '.template-item': ['rounded-4'],
                        '.ts-template-image-wrap': ['relative', 'over-hidden', 'rounded-4'],
                        '.ts-template-image': ['full-width', 'block'],
                        '.ts-template-image-overlay': ['absolute', 'pin-cover', 'bg-black-trans', 'hover-show'],
                        '.ts-template-title': ['hide'],
                        '.ts-template-links': ['bg-white', 'absolute', 'full-width', 'pin-bottom-right', 'p-2', 'font-sm'],
                        '.ts-template-preview-btn-link': ['hide'],
                        '.ts-template-preview-link': ['absolute', 'pin-center', 'white', 'font-md', 'mb-5'],
                        '.ts-template-icon-text': ['hide', 'invisible'],
                        '.ts-template-save-link': ['btn-success', 'block', 'rounded-3', 'p-1', 'pointer']
                    },
                    onStatusChange: function (status) {
                        if (['downloading', 'importing'].includes(status)) {
                            const text = status === 'downloading' ? 'Downloading' : 'Importing'
                            window.tsLoadingOverlay.setState('loading')
                            window.tsLoadingOverlay.elements.textLoading.innerText = text
                        }
                    },
                    useCustomButtonEvent: true,
                    disableOwned: false,
                    onButtonClick: onImportButtonClick,
                    statusText: {
                        standard: 'Insert',
                        downloaded: 'Insert From Library'
                    }
                })

                let previewBtn = templatePreview.element.querySelector('.ts-template-preview-btn-link')
                previewBtn.innerHTML = '<i class="ts-template-icon eicon-preview-medium"></i>'

                templates.push(templatePreview)
                return templates
            }, [])
            return templatePreviews
        }


        function activateTabFilter(filter) {
            if (!window.tsTemplatePreviews) return void console.error('window.tsTemplatePreviews must be defined')
            if (window.tsMasonryGrid) window.tsMasonryGrid.destroy()

            const templates = window.tsTemplatePreviews.slice(0) //clone templates
            const navTabs = document.querySelectorAll('[data-template-filter]')
            const targetNavTab = document.querySelector(`[data-template-filter='${filter}']`)

            navTabs.forEach(tab => tab.classList.remove('active'))
            targetNavTab.classList.add('active')

            let type, filterBy, templatesSorted

            switch (filter) {
                case 'full-website':
                    type = 'page'
                    filterBy = 'templateSet'
                    templatesSorted = sortTemplatesByKit(templates)
                    break
                case 'page':
                    type = 'page'
                    filterBy = 'categories'
                    templatesSorted = sortTemplatesByCategory(templates)
                    break
                case 'block':
                    type = 'block'
                    filterBy = 'categories'
                    templatesSorted = sortTemplatesByEvenDistribution(templates)
                    break
                default:
                    return void console.error(`${filter} is not a valid filter`)
            }

            window.tsMasonryGrid = createMasonryGrid(window.tsModal, templatesSorted, filterBy, type)
            window.tsMasonryGrid.shuffle.resetItems()
        }



        function createModalHeader(tsModal) {

            const $header = tsModal.getElements('header')

            class ModalHeader extends themeshark.RenderHTML {
                constructor(output) {
                    super(output)
                    output.classList.add('tsdry')

                    this.closePopup = () => window.tsModal.destroy()
                    this.init()
                }
                onBeforeOutput() {
                    this.tabs.forEach(tab => tab.addEventListener('click', e => {
                        if (e.target.classList.contains('active')) return
                        activateTabFilter(e.target.dataset.templateFilter)
                    }))
                }
                get tabs() {
                    return this.elements.tabsBar.querySelectorAll('[data-template-filter]')
                }
                render() {
                    const { $, e } = this
                    $('div', { class: 'themeshark-popup-header flex just-between px-4 py-0 full-width bg-white' })._(
                        $('div', { class: 'themeshark-popup-header__logo' })._(
                            $('img', {
                                class: 'themeshark-popup-logo',
                                src: `${themesharkLocalizedData.assetsDir}/images/themeshark-logo.png`
                            })
                        ),

                        $('div', { _key: 'tabsBar', class: 'themeshark-popup-header__menu' })._(
                            $('a', { _key: 'tabBlocks', 'data-template-filter': 'block', class: 'menu-item inline-block p-5 font-sm' })._('Blocks'),
                            $('a', { _key: 'tabPages', 'data-template-filter': 'page', class: 'menu-item inline-block p-5 font-sm' })._('Pages'),
                            $('a', { _key: 'tabKits', 'data-template-filter': 'full-website', class: 'menu-item inline-block p-5 font-sm' })._('Kits'),
                        ),
                        $('div', { class: 'themeshark-popup-header__actions' })._(
                            $('a', { _key: 'actionClose', class: 'popup-action' }, e('click', this.closePopup))._(
                                $('i', { class: 'eicon-close', 'aria-hidden': true, title: 'close' })
                            )
                        )
                    )
                }
            }

            return new ModalHeader($header[0])
        }

        /**
         * 
         * @param {*} tsModal 
         * @param {*} templatePreviews 
         * @param {*} filterBy categories, templateSet
         * @param {*} type block, page, all
         */
        function createMasonryGrid(tsModal, templatePreviews, filterBy = 'categories', type = 'block') {

            templatePreviews = templatePreviews.filter(tp => {
                if (tp.type === type || 'all' === type) {
                    tp.filter = filterBy
                    return tp
                }
            }).map(tp => tp.element)

            // const $popupHeader = tsModal.getElements('header')
            const $popupBody = tsModal.getElements('content-themeshark-templates')
            const masonryGrid = new TSShuffleGrid(templatePreviews, {
                columns: 3,
                gap: 30
            }, $popupBody[0])

            masonryGrid.gridContainer.classList.add('tsdry')
            $popupBody.append(masonryGrid.filterBar)
            $popupBody.append(masonryGrid.gridContainer)
            masonryGrid.initShuffle()

            return masonryGrid
        }




        elementor.on("preview:loaded", function () {
            $(elementor.$previewContents[0].body).on("click", ".elementor-add-themeshark-template-button", function (event) {

                //set where the template will be inserted on the page
                const insertPosition = themesharkTemplatesManager.getInsertPosition(event.target)
                themesharkTemplatesManager.currentInsertPosition = insertPosition

                window.tsModal = elementorCommon.dialogsManager.createWidget(
                    "lightbox",
                    {
                        id: "themeshark-template-popup",
                        headerMessage: false,
                        message: "",
                        hide: {
                            auto: false,
                            onClick: false,
                            onOutsideClick: false,
                            onOutsideContextMenu: false,
                            onBackgroundClick: true,
                        },

                        position: {
                            my: "center",
                            at: "center",
                        },
                        onShow: function () {
                            createModalHeader(this)

                            if (!window.tsTemplatePreviews) {

                                if (themesharkTemplatesManager.isValidStoredTemplatesMeta) {
                                    const meta = themesharkTemplatesManager.getStoredTemplatesMeta()
                                    const templatePreviews = createTemplatePreviews(meta)
                                    window.tsTemplatePreviews = templatePreviews
                                    Object.freeze(window.tsTemplatePreviews)
                                    activateTabFilter('page')
                                }
                                else {
                                    themesharkTemplatesManager.downloadTSMeta({
                                        done: (response) => {
                                            const templatePreviews = createTemplatePreviews(response)
                                            window.tsTemplatePreviews = templatePreviews
                                            Object.freeze(window.tsTemplatePreviews)
                                            activateTabFilter('page')
                                        },
                                        fail: err => {
                                            console.log(err)
                                            window.tsModal.destroy()
                                        }
                                    })
                                }

                            } else {
                                activateTabFilter('page')
                            }
                        },
                    }
                )
                window.tsModal.getElements("message").append(
                    window.tsModal.addElement("content-themeshark-templates")
                )
                window.tsModal.show()
            })
        })
    })
})(jQuery)