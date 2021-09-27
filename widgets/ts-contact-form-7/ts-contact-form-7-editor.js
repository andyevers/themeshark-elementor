themesharkControlsHandler.addHandler('contact_form_7_add_form_field_slides', function (controlsHandler, controlView) {
    //controlView = REPEATER control
    controlViewRepeater().on('show', () => {
        ensureFormControlChangeListener()
        ensureLabelControlsTextHandler()
        ensureFieldsRepeaterIdClass()
    })

    controlViewRepeater().on('childview:childview:settings:change', () => {
        setTimeout(() => setSavedFieldGroupValues())
    })

    let didRenderFields = controlsHandler.editedElementView.$el.data('did-rendered-fields')
    if (!didRenderFields) {
        renderForm()
        controlsHandler.editedElementView.on('render', ensureAllRequirements)
        controlsHandler.editedElementView.$el.data('did-rendered-fields', true)
    }

    ensureAllRequirements()

    function controlViewValueSaver() { return controlsHandler.getControlView('field_value_saver') }
    function controlViewFormSelect() { return controlsHandler.getControlView('contact_form') }
    function controlViewRepeater() {
        return controlView.isDestroyed ? controlsHandler.getControlView('contact_form_fields') : controlView
    }

    //index by field name. ex: {your-name: groupView, your-email: groupView}
    function childFieldGroupViews(indexed = true) {
        const groupViews = getChildViews(controlViewRepeater())
        return indexed === true
            ? groupViews.reduce((fieldGroupsObj, fieldGroupView) => {
                let fieldGroupName = fieldGroupView.model.attributes['field']
                fieldGroupsObj[fieldGroupName] = fieldGroupView
                return fieldGroupsObj
            }, {})
            : groupViews
    }


    function childFieldControlViews() {
        return Object.entries(childFieldGroupViews())
            .reduce((fieldControlsObj, [fieldGroupName, fieldGroupView]) => {
                let childViews = getChildViews(fieldGroupView)
                    .reduce((childViewsObj, childView) => {
                        let controlName = childView.model.attributes.name
                        childViewsObj[controlName] = childView
                        return childViewsObj
                    }, {})

                fieldControlsObj[fieldGroupName] = childViews
                return fieldControlsObj
            }, {})
    }

    function getChildViews(view) { return Object.values(view.children?._views || {}) }
    function getGroupId(groupView) { return groupView.options.container.id }
    function getCurrentFormId() { return controlViewFormSelect()?.getControlValue() }

    function getFieldGroupValues() {
        let fieldGroupValues = Object.entries(childFieldControlViews())
            .reduce((controlGroupsObj, [fieldGroupName, fieldViews]) => {
                let groupValues = Object.entries(fieldViews).reduce((fieldsObj, [fieldName, fieldView]) => {
                    if (fieldView.getControlValue) fieldsObj[fieldName] = fieldView.getControlValue()
                    return fieldsObj
                }, {})
                controlGroupsObj[fieldGroupName] = groupValues
                return controlGroupsObj
            }, {})
        return fieldGroupValues
    }

    function getSavedFieldGroupValues(formId, fieldGroupName) {
        let controlValue = controlViewValueSaver()?.getControlValue()
        if (!controlValue) return null
        const savedValues = controlValue === null || typeof controlValue !== 'object'
            ? {} : controlValue

        if (!formId) return savedValues
        const savedFormValues = savedValues[formId] || {}
        return fieldGroupName ? savedFormValues[fieldGroupName] || {} : savedFormValues
    }

    // ex: {contact_form_id: {field_group: {width: 100, width_mobile: 20}}, 'contact_form_id': {...}}
    function setSavedFieldGroupValues(formId = 'CURRENT', fieldGroupsValues = 'CURRENT') {
        lockRender()
        formId = formId === 'CURRENT' ? getCurrentFormId() : formId
        fieldGroupsValues = fieldGroupsValues === 'CURRENT' ? getFieldGroupValues() : fieldGroupsValues
        const savedValues = getSavedFieldGroupValues() || {}

        const newSavedFormValues = Object.entries(fieldGroupsValues)
            .reduce((fieldGroupsObj, [fieldGroupName, controlValues]) => {
                fieldGroupsObj[fieldGroupName] = controlValues
                return fieldGroupsObj
            }, savedValues[formId] || {})

        savedValues[formId] = newSavedFormValues
        controlViewValueSaver().setValue(savedValues)
        controlViewValueSaver().applySavedValue()
        unlockRender()
    }

    function renderForm() { controlsHandler.editedElementView.renderHTML() }
    function lockRender() { controlsHandler.editedElementView.allowRender = false }
    function unlockRender() { controlsHandler.editedElementView.allowRender = true }

    function getRequiredFormFields(formId) {
        const formFields = controlsHandler.getControlAttributes(controlViewRepeater()).contact_form_field_sets
        return formId ? formFields[formId] : formFields
    }

    function createNewModel() {
        let controlView = controlViewRepeater()
        var newModel = window.parent.$e.run('document/repeater/insert', {
            container: controlView.options.container,
            name: controlView.model.get('name'),
            model: controlView.getDefaults()
        });

        var newChild = controlView.children.findByModel(newModel);
        if (!newChild) return null

        controlView.editRow(newChild);
        controlView.toggleMinRowsClass();
        return newModel
    }

    function addChildFieldGroupView(groupName, matchSavedValues = true) {
        lockRender()
        const childFieldGroupModel = createNewModel()
        if (!childFieldGroupModel) {
            unlockRender()
            return
        }
        const childFieldGroupId = childFieldGroupModel.attributes._id
        const childControlFieldName = controlsHandler.getRepeaterControlView('field', childFieldGroupId)
        childControlFieldName.setValue(groupName)

        if (matchSavedValues) { // if existing saved value for the field key, sets the value of the new field
            const savedValues = getSavedFieldGroupValues(getCurrentFormId(), groupName)
            if (!savedValues) return
            const ignoredFieldKeys = ['_id', 'field']
            Object.entries(savedValues).forEach(([fieldKey, value]) => {
                if (!ignoredFieldKeys.includes(fieldKey)) {
                    let field = controlsHandler.getRepeaterControlView(fieldKey, childFieldGroupId)
                    if (field) {
                        field.setValue(value)
                        field.applySavedValue()
                    } else console.warn('cannot find repeater field field: ', fieldKey)
                }
            })
        }
        unlockRender()
        return childFieldGroupViews()[groupName]
    }

    function removeChildFieldGroupView(childFieldGroupView) {
        lockRender()
        const controlViewFieldsRepeater = controlViewRepeater()
        if (childFieldGroupView === controlViewFieldsRepeater.currentEditableChild) {
            delete controlViewFieldsRepeater.currentEditableChild;
        }
        window.parent.$e.run('document/repeater/remove', {
            container: controlViewFieldsRepeater.options.container,
            name: controlViewFieldsRepeater.model.get('name'),
            index: childFieldGroupView._index
        });
        controlViewFieldsRepeater.updateActiveRow();
        controlViewFieldsRepeater.updateChildIndexes();
        controlViewFieldsRepeater.toggleMinRowsClass();
        unlockRender()
    }

    function moveChildFieldGroupView(oldIndex, newIndex) {
        lockRender()
        let controlView = controlViewRepeater()
        window.parent.$e.run('document/repeater/move', {
            container: controlView.options.container,
            name: controlView.model.get('name'),
            sourceIndex: oldIndex,
            targetIndex: newIndex
        });
        unlockRender()
    }

    function isValidFieldSet(reportError = true) {
        const requiredFieldKeys = Object.keys(getRequiredFormFields(getCurrentFormId()))
        const hasDuplicates = array => (new Set(array)).size !== array.length
        const getDuplicates = array => array.reduce((duplicates, item) => {
            if (hasDuplicates(array) && !duplicates.includes(item)) duplicates.push(item)
            return duplicates
        })

        let isValid = true
        let errorMessage = 'invalid contact form fields. '

        if (hasDuplicates(requiredFieldKeys)) {
            isValid = false
            errorMessage += `found duplicate field keys: ` + getDuplicates(requiredFieldKeys).join(', ')
        }

        if (reportError && isValid === false) console.error(errorMessage)
        return isValid
    }

    function ensureFormControlChangeListener() {
        const contactFormView = controlViewFormSelect()
        const $contactFormSelect = contactFormView.$el
        const hasChangeListener = $contactFormSelect.data('has-change-listener') === true
        if (contactFormView && !hasChangeListener) {
            $contactFormSelect.data('has-change-listener', true)

            controlViewRepeater().listenTo(contactFormView, 'settings:change', () => {
                renderForm()
            })
        }
    }

    function ensureLabelControlsTextHandler() {

        Object.entries(childFieldGroupViews()).forEach(([groupName, groupView]) => {
            let groupId = getGroupId(groupView)
            let labelControlView = controlsHandler.getRepeaterControlView('field_label', groupId)
            let $labelControl = labelControlView.$el
            let hasLinkElementHandler = $labelControl.data('has-link-element-handler') === true

            if (!hasLinkElementHandler) controlsHandler.runControlHandlers(labelControlView)
            $labelControl.data('has-link-element-handler', true)

            let controlValue = labelControlView.getControlValue()
            if (controlValue) {
                let $field = controlsHandler.editedElementView.$el.find(`.elementor-field-group-${groupName}`)
                let $label = $field.find('.elementor-field-label')
                $label.text(controlValue)
            }
        })
    }

    function ensureFieldsRepeaterIdClass() {
        const $element = controlsHandler.editedElementView.$el
        let mustRenderUI = false
        for (let [fieldName, groupView] of Object.entries(childFieldGroupViews())) {
            let groupId = getGroupId(groupView)
            let classFieldName = `elementor-field-group-${fieldName}`
            let classRepeaterId = `elementor-repeater-item-${groupId}`
            let $field = $element.find(`.${classFieldName}`)

            if (!$field.hasClass(classRepeaterId)) {
                $field.addClass(classRepeaterId)
                mustRenderUI = true
            }
        }
        if (mustRenderUI) controlsHandler.editedElementView.renderUI()
    }


    function ensureNoDuplicateOrUnrequired() {
        const currentFormId = getCurrentFormId()
        const childFieldGroups = childFieldGroupViews(false)
        const requiredFieldKeys = Object.keys(getRequiredFormFields(currentFormId))

        let fieldGroupKeys = []
        for (let fieldGroupView of childFieldGroups) {
            let fieldGroupName = fieldGroupView.model.attributes['field'],
                isDuplicate = fieldGroupKeys.includes(fieldGroupName),
                isRequired = requiredFieldKeys.includes(fieldGroupName)

            if (isDuplicate || !isRequired) {
                removeChildFieldGroupView(fieldGroupView)
            }
            fieldGroupKeys.push(fieldGroupName)
        }
    }

    function ensureRequiredControls() {
        const currentFormId = getCurrentFormId()
        const requiredFieldKeys = Object.keys(getRequiredFormFields(currentFormId))
        const existingFieldKeys = Object.keys(childFieldGroupViews())

        for (let fieldKey of requiredFieldKeys) {
            let isExistingFieldGroup = existingFieldKeys.includes(fieldKey)
            if (!isExistingFieldGroup) {
                addChildFieldGroupView(fieldKey)
            }
        }
    }

    function ensureControlOrder() {
        const currentFormId = getCurrentFormId()
        const requiredFieldKeys = Object.keys(getRequiredFormFields(currentFormId))

        const arraymove = (arr, fromIndex, toIndex) => {
            let element = arr[fromIndex]
            arr.splice(fromIndex, 1)
            arr.splice(toIndex, 0, element)
        }

        let existingKeys = Object.keys(childFieldGroupViews())
        let maxExecutions = 100, executions = 0 //amount of moves allowed. used to avoid infinite loop if error sorting

        for (let i = 0; i < requiredFieldKeys.length; i++) {

            let fieldKey = requiredFieldKeys[i],
                currentPosition = existingKeys.indexOf(fieldKey),
                isCorrectPosition = currentPosition === i

            if (!isCorrectPosition) {
                moveChildFieldGroupView(currentPosition, i)
                arraymove(existingKeys, currentPosition, i) //sort field in array to match new position
                i--
                executions++
                if (executions >= maxExecutions) {
                    console.error(`Could not sort field, sorting calls reached (${maxExecutions})`)
                    break
                }
            }
        }
    }

    function ensureAllRequirements() {

        function _ensureAllRequirements() {
            if (!isValidFieldSet()) return
            if (!controlViewFormSelect() || !controlViewValueSaver()) return

            ensureNoDuplicateOrUnrequired()
            ensureRequiredControls()
            ensureControlOrder()

            setTimeout(() => {
                ensureFormControlChangeListener()
                ensureLabelControlsTextHandler()
                ensureFieldsRepeaterIdClass()
            })
        }
        setTimeout(() => _ensureAllRequirements())
    }
})
