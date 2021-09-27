class TSSVGText extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                svg: '.themeshark-svg-text',
            },
            dataAttributes: {
                duration: 'duration',
            },
        }
    }
    getDefaultElements() {
        const selectors = this.getSettings('selectors')
        return {
            $svg: this.$element.find(selectors.svg),
        }
    }

    getElementSettings() {
        const { toggle, duration } = this.getSettings('dataAttributes')
        const { $svg } = this.elements

        return {
            toggle: $svg.data(toggle) ? true : false,
            duration: $svg.data(duration)
        }
    }

    bindEvents() {
        this.setSVGDuration()
        this.createSVGText()
        if (!this.isEdit) {
            this.elements.$svg[0].removeAttribute('data-charpaths')
        }
    }

    setSVGDuration() {
        const { $svg } = this.elements
        const { duration } = this.getElementSettings()

        $svg.attr('style', `animation-duration: ${duration}s`)
    }



    createSVGText(svgEl = null) {

        function clearSVGText(svg) {
            let paths = svg.querySelectorAll("path")
            paths.forEach(p => p.parentNode.removeChild(p))
            svg.setAttribute("viewBox", "0 0 0 100")
            svg.setAttribute("width", "0")
            svg.setAttribute("height", svg.dataset.fontsize)
        }

        function addPathToSVG(path, svg, posX = 0, posY = 0) {
            const d = path.getAttribute("d")
            const dNew = movePathStartPoint(d, posX, posY)
            path.setAttribute("d", dNew)
            svg.appendChild(path)
        }

        function movePathStartPoint(pathString, offsetX, offsetY) {
            const pathData = parsePathString(pathString)
            pathData[0][1] += offsetX
            pathData[0][2] += offsetY
            return pathData.toString()
        }

        function getFontObject(svg, fontFamily) {
            if (themeshark.isEditMode) {
                return themeshark.themesharkLocalizedData.SVG_TEXT_FONTS[fontFamily];
            }
            else return JSON.parse(svg.dataset.charpaths)
        }

        function createSVGChars(string, svg) {
            const characters = Array.from(string)
            const fontFamily = svg.dataset.fontfamily
            const fontPathData = getFontObject(svg, fontFamily)
            const fontSize = parseFloat(svg.dataset.fontsize)
            const lineHeight = parseFloat(svg.dataset.lineheight) > 0 ?
                parseFloat(svg.dataset.lineheight) * 100 / fontSize : 1

            clearSVGText(svg)

            let lineWidths = [],
                curLine = 0

            const adjustToFontRatio = num => num * fontSize / 100
            const adjustToLineRatio = num => num * lineHeight / 100
            const adjustToHeightRatio = num => adjustToLineRatio(num) * fontSize / 100
            const isLongestLine = num => num === Math.max(...lineWidths)
            const isPastWidth = num => num > parseFloat(svg.getAttribute("width"))

            const setVbWidth = width => {
                const height = svg.viewBox.baseVal.height
                svg.setAttribute("viewBox", `0 0 ${width} ${height}`)
            }
            const setVbHeight = height => {
                const width = svg.viewBox.baseVal.width
                svg.setAttribute("viewBox", `0 0 ${width} ${height}`)
            }

            const addSpace = size => {
                lineWidths[curLine] += size
            }

            const addLineBreak = e => {
                curLine++
                const newLineHeight = curLine * lineHeight
                setVbHeight(adjustToLineRatio(newLineHeight))
                svg.setAttribute("height", adjustToHeightRatio(newLineHeight))
            }

            const addCharacter = char => {
                let { d, spaceLeft, spaceRight } = fontPathData[char]

                let path = createSVGEl("path", { "data-char": char, d: d })

                let lineWidth = lineWidths[curLine] || 0,
                    charPosX = lineWidth + spaceLeft,
                    charPosY = lineHeight * curLine

                addPathToSVG(path, svg, charPosX, charPosY)

                let charWidth = spaceLeft + path.getBBox().width + spaceRight,
                    newLineWidth = lineWidths[curLine] = lineWidth + charWidth

                setVbWidth(newLineWidth)
                svg.setAttribute("width", adjustToFontRatio(newLineWidth))
            }

            characters.forEach(c => {
                if (c === " ") addSpace(27)
                else if (c === "\n") addLineBreak()
                else addCharacter(c)
            })

            let title = svg.querySelector("title")
            if (title) title.innerHTML = string
        }


        function parsePathString(pathString) {
            let paramCounts = {
                a: 7, c: 6, o: 2, h: 1, l: 2, m: 2, r: 4, q: 4, s: 4, t: 2, v: 1, u: 3, z: 0
            }, data = [];

            let pathCommand = /([a-z])[\s,]*((-?\d*\.?\d*(?:e[\-+]?\d+)?[\s]*,?[\s]*)+)/ig,
                pathValues = /(-?\d*\.?\d*(?:e[\-+]?\d+)?)[\s]*,?[\s]*/ig

            String(pathString).replace(pathCommand, function (a, b, c) {
                let params = [],
                    name = b.toLowerCase();

                c.replace(pathValues, function (a, b) {
                    b && params.push(+b);
                });

                if (name == "m" && params.length > 2) {
                    data.push([b].concat(params.splice(0, 2)));
                    name = "l";
                    b = b == "m" ? "l" : "L";
                }
                if (name == "o" && params.length == 1) {
                    data.push([b, params[0]]);
                }
                if (name == "r") {
                    data.push([b].concat(params));
                }
                else while (params.length >= paramCounts[name]) {
                    data.push([b].concat(params.splice(0, paramCounts[name])));
                    if (!paramCounts[name]) {
                        break;
                    }
                }
            });
            data.toString = function () {
                const p2s = /,?([a-z]),?/gi
                return this.join(",").replace(p2s, "$1");
            }
            return data;
        };


        function addPathAnimationAttrs(svg, animationDuration) {
            let paths = svg.querySelectorAll('path');
            let lengthValues = [];

            //get min and max path lengths
            for (let i = 0; i < paths.length; i++) {
                lengthValues.push(paths[i].getTotalLength());
            }
            let maxLength = Math.max(...lengthValues);
            let minLength = Math.min(...lengthValues);
            for (let y = 0; y < paths.length; y++) {

                let path = paths[y]
                let length = path.getTotalLength();

                let timingAdjustment = (.7 - (minLength / maxLength)) * ((minLength / maxLength) / (length / maxLength));

                path.style.strokeDasharray = length;
                path.style.strokeDashoffset = length;
                path.style.animationDelay = (y / paths.length) + "s";
                path.style.animationDuration = ((length / maxLength) + timingAdjustment) * (animationDuration * .7) + "s";
            }
        }

        function createSVGEl(tagName, attributes = {}) {
            const el = document.createElementNS("http://www.w3.org/2000/svg", tagName)
            for (let [key, val] of Object.entries(attributes)) {
                el.setAttribute(key, val)
            }
            return el
        }

        const $svg = svgEl !== null ? jQuery(svgEl) : this.elements.$svg
        const duration = parseFloat($svg.attr('data-duration'))
        const text = $svg.attr('data-text')

        createSVGChars(text, $svg[0])
        addPathAnimationAttrs($svg[0], duration)
    }
}


themesharkFrontend.addInitCallback(() => {
    if (themeshark.isEditMode) {
        themeshark.themesharkControlsHandler.widgetHandlerEditorFunctions.addSVGTextEditorFunctions(TSSVGText)
    }
    themesharkFrontend.addWidgetHandler('ts-svg-text', TSSVGText)
})