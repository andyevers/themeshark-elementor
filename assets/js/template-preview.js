class TSTemplatePreview extends themeshark.RenderHTML {
    constructor(meta, options = {}, output = null) {


        if (typeof themeshark?.themesharkLocalizedData === 'undefined') {
            return void console.error('themesharkLocalizedData required for template preview')
        }

        if (!window._themesharkOwnedTemplates) {
            //creates global var to keep track of owned templates
            window._themesharkOwnedTemplates = Object.assign({}, themesharkLocalizedData.ownedTemplates)
        }

        super(output)

        const { ID, image_url, title, preview_link, pro_only, template_set, ts_categories, type } = meta
        const { importUrl, downloadUrl, savedTemplates, goProLink, ownedTemplates } = themeshark.themesharkLocalizedData
        const _defaultOptions = {
            filter: 'categories',
            elementClasses: {},
            downloadAction: 'import', //import, insert, null
            lockOwned: true,
            insertOptions: {},
            importOptions: {},
            downloadOptions: {},
            disableOwned: true,
            onStatusChange: null,
            useCustomButtonEvent: false,
            onButtonClick: null,

            statusText: { //button will display this text during state
                'standard': 'Save',
                'importing': 'Importing',
                'inserting': 'Inserting Template',
                'downloading': 'Downloading',
                'downloaded': 'Saved to Library',
                'download-error': 'Error Downloading',
                'import-error': 'Error Importing'
            }
        }


        const updateObject = (origObject, updateObject) => {
            if (!updateObject) updateObject = {}
            return Object.entries(origObject).reduce((newObj, [key, val]) => {
                newObj[key] = key in updateObject ? updateObject[key] : val
                return newObj
            }, {})
        }

        this.options = updateObject(_defaultOptions, options)
        this.options.statusText = updateObject(_defaultOptions.statusText, options.statusText)



        // this.options = Object.entries(_defaultOptions).reduce((opts, [key, val]) => {
        //     opts[key] = key in options ? options[key] : val
        //     return opts
        // }, {})

        this.ID = ID
        this.image_url = image_url
        this.title = title
        this.preview_link = preview_link
        this.template_set = template_set
        this.ts_categories = ts_categories
        this.type = type

        this.importUrl = importUrl
        this.downloadUrl = downloadUrl
        this.savedTemplates = savedTemplates
        this.goProLink = goProLink

        this.hiddenClass = "template-hidden"
        // this.isOwned = ownedTemplates.includes(ID)
        this._isActive = true

        this.imageLoaded = false
        this.filter = this.options.filter

        // DOWNLOAD FUNCTIONS____________________

        this.getFilters = (filter = this.filter) => {
            switch (filter) {
                case 'categories':
                    return this.ts_categories
                case 'templateSet':
                    return this.template_set
                default:
                    console.error(`${this.filter} is not a valid filter type. use 'categories' or 'templateSet'`)
            }
        }


        this.addActions = () => {

            themeshark.hooks.addAction(`templates/import/start/${this.ID}`, () => {
                this.setStatus('importing')
            })
            themeshark.hooks.addAction(`templates/import/done/${this.ID}`, (libraryID) => {
                this.libraryID = libraryID
                this.setStatus('downloaded')
            })


        }




        this.setStatus = (status) => {
            const setText = txt => this.elements.btnText.innerText = txt
            const setClass = className => this.element.classList.add(className)

            const statusClasses = ['template-error', 'downloading', 'downloaded']
            statusClasses.forEach(c => this.element.classList.remove(c))

            const statusText = this.options.statusText[status]

            switch (status) {
                case 'standard':
                    setText(this.saveText)
                    break
                case 'importing':
                    setClass('downloading')
                    setText(statusText)
                    break
                case 'inserting':
                    setClass('downloading')
                    setText(statusText)
                    break
                case 'downloading':
                    setClass('downloading')
                    setText(statusText)
                    break
                case 'downloaded':
                    setClass('downloaded')
                    setText(statusText)
                    if (this.options.disableOwned) this.elements.btnLink.removeEventListener('click', this.onButtonClick)
                    break
                case 'download-error':
                    setClass('template-error')
                    setText(statusText)
                    break

                case 'import-error':
                    setClass('template-error')
                    setText(statusText)
                    break
                default:
            }
            if (this.options.onStatusChange) this.options.onStatusChange(status, this)
        }

        this.downloadTemplate = e => {
            if (this.element.classList.contains("downloading")) return
            e.target.removeEventListener("click", this.downloadTemplate)

            const { done, fail, always } = this.options.downloadOptions
            this.setStatus('downloading')

            themesharkTemplatesManager.downloadTSJSON(this.ID, {
                done: (res) => {
                    try { this.handleDownloadResponse(res) }
                    catch (err) {
                        console.error(err)
                        this.setStatus('download-error')
                    }
                    if (done) done(res)
                },
                fail: (err) => {
                    console.error(err)
                    this.setStatus('download-error')
                    if (fail) fail(err)
                },
                always: () => {
                    if (always) always()
                }
            })
        }


        this.handleDownloadResponse = response => {
            switch (this.options.downloadAction) {
                case 'import':
                    this.importTemplate(response)
                    break
                case 'insert':
                    this.insertTemplate(response)
                    break
            }
        }


        // IMPORT FUNCTIONS____________________
        this.importTemplate = templateData => {
            this.setStatus('importing')
            const { done, fail, always } = this.options.importOptions
            themesharkTemplatesManager.import(this.ID, templateData, {
                done: (res) => {
                    this.setStatus('downloaded')
                    if (done) done(res)
                },
                fail: (err) => {
                    console.error(err)
                    this.setStatus('template-error')
                    if (fail) fail(res)
                },
                always: () => {
                    if (always) always()
                }
            })
        }

        this.insertTemplate = templateData => {
            this.setStatus('inserting')
            const templateContent = JSON.parse(templateData).content
            const insertPosition = themesharkTemplatesManager.insertPosition

            const { onInsertSuccess, onInsertFail, onBeforeAdd, onAfterAdd, onFinish } = this.options.insertOptions

            // themesharkTemplatesManager.impo
            themesharkTemplatesManager.insertFromContent(templateContent, {
                onInsertSuccess: (model) => { if (onInsertSuccess) onInsertSuccess(model) },
                onInsertFail: (model) => { if (onInsertFail) onInsertFail(model) },
                onBeforeAdd: (model) => { if (onBeforeAdd) onBeforeAdd(model) },
                onAfterAdd: (model) => { if (onAfterAdd) onAfterAdd(model) },
                onFinish: () => { if (onFinish) onFinish() },
            })
        }

        this.onButtonClick = e => {

            if (this.options.onButtonClick) this.options.onButtonClick(e, this)
            if (this.options.useCustomButtonEvent === true) return

            const cl = this.element.classList
            if (cl.contains("downloading") || cl.contains("downloaded")) return
            if (this.isProLocked) return
            this.downloadTemplate(e)
        }

        this.onImageLoad = e => {
            const target = this.element
            this.imageLoaded = true
            target.classList.remove("loading")
            window.dispatchEvent(new Event('resize'));
        }




        this.addActions()
        this.init()
    }


    get isOwned() {
        return Object.keys(window._themesharkOwnedTemplates).includes(this.ID)
    }

    get libraryID() {
        return window._themesharkOwnedTemplates[this.ID]
    }

    set libraryID(libID) {
        window._themesharkOwnedTemplates[this.ID] = parseInt(libID)
    }

    get templateItemClass() {
        let itemClass = `template-item loading template-item-type-${this.type} `

        if (this.isOwned) itemClass += "is-owned "
        if (this.pro_only) itemClass += "is-pro "
        if (this.isProLocked) itemClass += "is-pro-locked"
        if (!this.isActive) itemClass += `${this.hiddenClass} `
        return itemClass
    }

    get saveText() {
        if (this.isOwned) return this.options.statusText.downloaded
        else return this.options.statusText.standard
    }

    get buttonAttributes() {
        let atts = {
            _key: "btnLink",
            class: "ts-template-save-link"
        }
        if (this.isProLocked) {
            atts.href = this.goProLink
            atts.target = "_blank"
        }
        return atts
    }

    get isActive() {
        return this._isActive
    }

    set isActive(bool) {
        this._isActive = bool
        const cl = this.element.classList
        if (bool === true) {
            cl.add("loading")
            cl.remove(this.hiddenClass)
            setTimeout(() => {
                if (this.imageLoaded) cl.remove("loading")
            }, 50)
        }
        if (bool === false) cl.add(this.hiddenClass)

    }


    onBeforeOutput() {
        //add additional element classes
        for (let [selector, classes] of Object.entries(this.options.elementClasses)) {
            this.element.parentNode.querySelectorAll(selector).forEach(el => {
                classes.forEach(c => el.classList.add(c))
            })
        }

        //disabled owned template action
        const isDisabled = this.options.disableOwned && this.isOwned === true
        if (!isDisabled) {
            this.elements.btnLink.addEventListener('click', this.onButtonClick)
        }
    }

    get filter() {
        return this.options.filter
    }

    set filter(filter) {
        if (this.isInitialized) {
            let filters = this.getFilters(filter)
            this.element.dataset.groups = JSON.stringify(filters)
        }
        this.options.filter = filter
    }

    render() {
        const $ = this.$
        const e = this.e

        const filters = this.getFilters()

        $("div", { class: this.templateItemClass, 'data-groups': JSON.stringify(filters) })._(

            $("div", { class: "ts-template-image-wrap" })._(

                $("img", {
                    _key: "previewImage",
                    class: "ts-template-image",
                    src: this.image_url,
                    onError: 'templateImageNotFound(this)'
                }, e("load", this.onImageLoad)),

                $("div", { class: "ts-template-image-overlay" })._(

                    $("h3", { class: "ts-template-title" })._(this.title),

                    $("a", { href: this.preview_link, target: "_blank" })._(

                        $("span", { class: "ts-template-preview-link" })._(

                            $("i", { class: "ts-template-icon eicon-preview-medium" }),
                            $("div", { class: "ts-template-icon-text" })._("Preview")
                        )
                    ),
                    $("div", { class: "ts-template-links" })._(

                        $("a", { class: 'ts-template-preview-btn-link', href: this.preview_link, 'target': '_blank' })._(

                            $("span", { class: "ts-template-btn-text" })._('Preview'),
                        ),

                        $("a", this.buttonAttributes)._(

                            $("span", { _key: "btnText", class: "ts-template-btn-text" })._(this.saveText),
                            $("span", { class: "ts-template-loader" })
                        ),
                    )
                )
            )
        )
    }
}


//when template image not found, adds placeholder and shows title
function templateImageNotFound(image) {
    image.onerror = null;
    image.src = themesharkLocalizedData.fallbackImageUrl
    jQuery(image).closest('.grid-item').addClass('template-item-image-not-found')
}
