/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */

/**
 * @fileOverview The "placeholder" plugin.
 *
 */

'use strict';

(function () {
    CKEDITOR.plugins.add('placeholder', {
        requires: 'widget,dialog',
        lang: 'af,ar,az,bg,ca,cs,cy,da,de,de-ch,el,en,en-au,en-gb,eo,es,es-mx,et,eu,fa,fi,fr,fr-ca,gl,he,hr,hu,id,it,ja,km,ko,ku,lv,nb,nl,no,oc,pl,pt,pt-br,ro,ru,si,sk,sl,sq,sv,th,tr,tt,ug,uk,vi,zh,zh-cn', // %REMOVE_LINE_CORE%
        icons: 'placeholder', // %REMOVE_LINE_CORE%
        hidpi: true, // %REMOVE_LINE_CORE%

        onLoad: function () {
            // Register styles for placeholder widget frame.
            CKEDITOR.addCss('.cke_placeholder{background-color:#ff0}');
        },

        init: function (editor) {

            var lang = editor.lang.placeholder;

            // Register dialog.
            CKEDITOR.dialog.add('placeholder', this.path + 'dialogs/placeholder.js');

            // Put ur init code here.
            editor.widgets.add('placeholder', {
                // Widget code.
                dialog: 'placeholder',
                pathName: lang.pathName,
                // We need to have wrapping element, otherwise there are issues in
                // add dialog.
                template: '<span class="cke_placeholder">[[]]</span>',

                downcast: function () {
                    return new CKEDITOR.htmlParser.text(`[[${this.data.id}|${this.data.name}]]`);
                },

                init: function () {
                    this.setData('id', this.element.$.getAttribute('data-id'));
                    this.setData('name', this.element.$.getAttribute('data-name'));
                },

                data: function () {
                    let name = this.data.name || this.data.id;
                    this.element.setText(`[[${name}]]`);
                },

                getLabel: function () {
                    return this.editor.lang.widget.label.replace(/%1/, this.data.name + ' ' + this.pathName);
                }
            });

            editor.ui.addButton && editor.ui.addButton('CreatePlaceholder', {
                label: lang.toolbar,
                command: 'placeholder',
                toolbar: 'insert,5',
                icon: 'placeholder'
            });
        },

        afterInit: function (editor) {
            var placeholderReplaceRegex = /\[\[(\d+)\]\]|\[\[(\d+)\|(.*?)\]\]/mg;

            editor.dataProcessor.dataFilter.addRules({
                text: function (text, node) {
                    var dtd = node.parent && CKEDITOR.dtd[node.parent.name];

                    // Skip the case when placeholder is in elements like <title> or <textarea>
                    // but upcast placeholder in custom elements (no DTD).
                    if (dtd && !dtd.span)
                        return;

                    return text.replace(placeholderReplaceRegex, function (match) {
                        // Creating widget code.

                        var matches = placeholderReplaceRegex.exec(text);
                        var widgetWrapper = null,
                            innerElement = new CKEDITOR.htmlParser.element('span', {
                                'class': 'cke_placeholder',
                                'data-id': matches[1] || matches[2],
                                'data-name': matches[3]
                            });

                        // Adds placeholder identifier as innertext.
                        innerElement.add(new CKEDITOR.htmlParser.text(match));
                        widgetWrapper = editor.widgets.wrapElement(innerElement, 'placeholder');

                        // Return outerhtml of widget wrapper so it will be placed
                        // as replacement.
                        return widgetWrapper.getOuterHtml();
                    });
                }
            });
        }
    });
})();
