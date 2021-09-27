const themesharkTemplatesManager = {

    _currentInsertPosition: 0,

    get storedData() {
        return localStorage.getItem('ts_templates_data')
    },

    get currentInsertPosition() {
        return this._currentInsertPosition
    },

    set currentInsertPosition(position) {
        if (typeof position !== 'number') return void console.error('position must be a number')
        this._currentInsertPosition = position
    },

    getStoredTemplatesMeta(asJson = false) {
        let storedData = JSON.parse(this.storedData)
        if (storedData) return asJson ? JSON.stringify(storedData.meta) : storedData.meta
        else return null
    },


    /**
     * gets index of where a template will be inserted based on an element based on the position of the .elementor-add-section ancestory
     * @param {Element} element
     * 
     * @returns {Int} index of insert position
     */
    getInsertPosition: function (element) {

        if (typeof elementor === 'undefined') return console.error('This can only be used in editor')

        const $element = jQuery(element)
        const $sectionsWrap = $element.parents('.elementor-section-wrap')
        const $newSectionWrap = $element.parents('.elementor-add-section')

        const insertPosition = $sectionsWrap.length > 0 ? $newSectionWrap.index() : 0
        return insertPosition
    },

    /**
     * Makes template library data accessible in elementor.templates.getTemplatesCollection()
     * @param {Object} options forceUpdate, forceSync, onUpdate, onBeforeUpdate
     */
    requestLibraryData: function (options = {}) {
        const { done, fail, always } = options

        if (typeof elementor === 'undefined') return console.error('This can only be used in editor')

        // elementorCommon.ajax.addRequest('get_library_data', { data: { sync: true } })
        //     .done(res => { if (done) done(res) })
        //     .fail(err => { if (fail) fail(err) })
        //     .always(res => { if (always) always(res) })
        elementor.templates.requestLibraryData({
            forceSync: true,
            forceUpdate: true,
            onUpdate: onUpdate,
            onBeforeUpdate: onBeforeUpdate
        })
    },

    /**
     * Imports template into elementor library. responds with new elementor template ID if successful
     * @param {String} templateJson 
     * @param {Object} ajaxOptions done, fail, always
     */
    import: function (themeshark_id, templateJson, ajaxOptions = {}) {
        const { done, fail, always } = ajaxOptions

        // themeshark.hooks.doAction('templates/import_start', themeshark_id, templateJson)
        themeshark.hooks.doAction(`templates/import/start/${themeshark_id}`, templateJson)

        jQuery.post(
            themeshark.themesharkLocalizedData.importUrl, {
            action: 'import_elementor',
            template: templateJson,
            themeshark_id: themeshark_id,
            title: JSON.parse(templateJson).title

        }, response => {
            themeshark.hooks.doAction(`templates/import/done/${themeshark_id}`, response)
            if (done) done(response)
        })
            .fail((err) => {
                themeshark.hooks.doAction(`templates/import/fail/${themeshark_id}`, err)
                if (fail) fail(err)
            })
            .always((res) => {
                themeshark.hooks.doAction(`templates/import/always/${themeshark_id}`, res)
                if (always) always(res)
            })
    },





    /**
     * Import a template onto the page from template library. ajax done fires after 'document/elements/import' action
     * @param {String} templateId ex: 16772
     * @param {String} position null = current position
     * @param {Object} importOptions 
     * @param {Object} ajaxOptions done, fail, always
     */
    insert: function (templateId, importOptions = { withPageSettings: false }, ajaxOptions = {}, requestLibraryDataIfNotFound = true, insertPosition = null) {
        const { done, fail, always } = ajaxOptions
        let position = insertPosition === null ? this.currentInsertPosition : insertPosition

        if (typeof elementor === 'undefined') {
            return console.error('This can only be used in editor')
        }

        importOptions.at = position


        console.log(templateId)
        let templateModel = null
        let templatesCollection = elementor.templates.getTemplatesCollection()

        if (templatesCollection) {
            templateModel = templatesCollection.findWhere({ template_id: templateId })
        }

        function insertTemplate(templateId = templateId) {


            // if (templatesCollection === undefined) {
            //     return console.error('templates collection data must be requested before inserting template')
            // }
            const templateModel = elementor.templates.getTemplatesCollection()?.findWhere({ template_id: templateId })
            // const templateModel = templatesCollection.findWhere({ template_id: templateId })
            window.templateModel = templateModel
            if (templateModel === undefined) {
                console.log(templatesCollection)
                window.collection = templatesCollection
                console.log(templatesCollection.findWhere({ template_id: templateId }))
                return console.error(`Could not find template with id ${templateId}.`)
            }

            console.log(templateModel)

            window.myModel = templateModel

            elementor.templates.requestTemplateContent(templateModel.get('source'), templateId, {
                success: function (data) {
                    $e.run('document/elements/import', {
                        model: templateModel,
                        data: data,
                        options: importOptions
                    });
                    if (done) done(data)
                },
                error: (err) => { if (fail) fail(err) },
                complete: () => { if (always) always() }
            })
        }


        // elementorCommon.ajax.addRequest('get_library_data', { data: { sync: true } }).;

        if (!templateModel) {
            console.log('no model defined')
            if (requestLibraryDataIfNotFound === true) {
                console.log('requesting')
                try {
                    elementor.templates.requestLibraryData({
                        forceSync: true,
                        forceUpdate: true,
                        onUpdate: () => {
                            setTimeout(() => insertTemplate(templateId))
                        }
                    })
                } catch (err) {
                    console.error(err)
                }


            } else {
                console.error('cannot find template model')
            }
        }
        else {
            insertTemplate()
        }
    },


    /**
     * Downloads ThemeShark template JSON from themesharkLocalizedData.downloadUrl using the templateID
     * @param {String} themeshark_id ex: 'bb-home'
     * @param {Object} ajaxOptions done, fail, always
     * 
     * @returns JSON contents for template
     */
    downloadTSJSON: function (themeshark_id, ajaxOptions = {}) {
        const { done, fail, always } = ajaxOptions

        themeshark.hooks.doAction(`templates/download/start/${themeshark_id}`)

        const _ajaxOptions = {
            done: (res) => {
                themeshark.hooks.doAction(`templates/download/done/${themeshark_id}`, res)
                if (done) done(res)
            },
            fail: (err) => {
                themeshark.hooks.doAction(`templates/download/fail/${themeshark_id}`, err)
                if (fail) fail(err)
            },
            always: (res) => {
                themeshark.hooks.doAction(`templates/download/always/${themeshark_id}`, res)
                if (always) always(res)
            }
        }

        themeshark.postRequest('get_template_json', {
            template_id: themeshark_id,
            email: themeshark.themesharkLocalizedData.sharedData.email
        }, _ajaxOptions)
    },



    get isValidStoredTemplatesMeta() {
        const storedData = localStorage.getItem('ts_templates_data')
        if (!themeshark.isJsonString(storedData)) return false

        const { meta, time } = JSON.parse(storedData)
        if (!Array.isArray(meta)) return false

        const isExpired = themeshark.secondsPassed(time) > this.SAVED_META_EXPIRATION
        if (isExpired) return false

        return true
    },

    SAVED_META_EXPIRATION: themeshark.DAY_IN_SECONDS,

    saveTSMeta: function (meta) {
        return localStorage.setItem('ts_templates_data', JSON.stringify({
            meta: meta,
            time: Date.now()
        }))
    },

    /**
     * Downloads all ThemeShark templates meta data from themesharkLocalizedData.downloadUrl
     * @param {String} templateId ex: 'bb-home'
     * @param {Object} ajaxOptions done, fail, always
     * @param {Boolean} forceUpdate false if data can be pulled from localStorage
     * 
     * @returns JSON contents for template
     */
    downloadTSMeta: function (ajaxOptions = {}, saveResponse = true) {
        const { done, fail, always } = ajaxOptions

        const request = themeshark.postRequest('get_preview_meta', {
            siteUrl: window.location.origin
        }, {
            done: (res) => {
                if (saveResponse) this.saveTSMeta(res)
                if (done) done(res)
            },
            fail: (res) => { if (fail) fail(res) },
            always: (res) => { if (always) always(res) }
        })
        return request

    },


    getTemplateData: function (templateId, ajaxOptions) {

        themeshark.hooks.doAction(`templates/get_template_data/start/${templateId}`)

        const { done, fail, always } = ajaxOptions
        elementorCommon.ajax.addRequest('get_template_data', {
            data: {
                source: 'local',
                template_id: templateId
            }
        })
            .done(res => {
                themeshark.hooks.doAction(`templates/get_template_data/done/${templateId}`, res)
                if (done) done(res)
            })
            .fail(res => {
                themeshark.hooks.doAction(`templates/get_template_data/fail/${templateId}`, res)
                if (fail) fail(res)
            })
            .always(res => {
                themeshark.hooks.doAction(`templates/get_template_data/always/${templateId}`, res)
                if (always) always(res)
            })
    },


    /**
     * takes template content and creates sections. ('content' key found in exported json file)
     * @param {Array} templateContent array of models for template
     * @param {Int} insertPosition where the template will be inserted. null = current insert position
     */
    insertFromContent: function (templateContentArr, insertOptions = {}, insertPosition = null) {
        const { onBeforeAdd, onAfterAdd, onInsertSuccess, onInsertFail, onFinish } = insertOptions

        if (elementor === undefined) return console.error('This can only be used in editor')

        let position = insertPosition === null ? this.currentInsertPosition : insertPosition

        // start history log
        let historyLogId = $e.internal("document/history/start-log", {
            type: "add",
            title: "Add ThemeShark Template Content"
        })

        for (let i = 0; i < templateContentArr.length; i++) {
            let model = templateContentArr[i]
            let isFinalModel = i === templateContentArr.length - 1

            // create element from model data
            try {
                $e.run("document/elements/create", {
                    container: elementor.getPreviewContainer(),
                    model: model, //the template model
                    options: {
                        at: position,
                        onBeforeAdd: (model) => {
                            if (onBeforeAdd) onBeforeAdd(model)
                        },
                        onAfterAdd: (model) => {
                            if (onAfterAdd) onAfterAdd(model)
                            if (isFinalModel && onInsertSuccess) onInsertSuccess(model)
                        }
                    }
                })
            } catch (err) {
                if (onInsertFail) onInsertFail(err)
            }
            position++
        }

        //end history log
        $e.internal("document/history/end-log", {
            id: historyLogId
        })

        if (onFinish) onFinish()
    }
}
