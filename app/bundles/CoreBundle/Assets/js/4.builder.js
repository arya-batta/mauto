/**
 * Parses the query string and returns a parameter value
 * @param name
 * @returns {string}
 */
Le.getUrlParameter = function (name) {
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    var results = regex.exec(location.search);
    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
};

/**
 * Launch builder
 *
 * @param formName
 * @param actionName
 */
Le.launchBuilder = function (formName, actionName) {
    var builder = mQuery('.builder');
   // alert(builder);
    Le.codeMode = builder.hasClass('code-mode');
    Le.showChangeThemeWarning = true;

    mQuery('body').css('overflow-y', 'hidden');

    // Activate the builder
    builder.addClass('builder-active').removeClass('hide');

    if (typeof actionName == 'undefined') {
        actionName = formName;
    }

    var builderCss = {
        margin: "0",
        padding: "0",
        border: "none",
        width: "100%",
        height: "100%"
    };

    // Load the theme from the custom HTML textarea
    var themeHtml = mQuery('textarea.builder-html').val();
    if (Le.codeMode) {
        var rawTokens = mQuery.map(Le.builderTokens, function (element, index) {
            return index
        }).sort();
        Le.builderCodeMirror = CodeMirror(document.getElementById('customHtmlContainer'), {
            value: themeHtml,
            lineNumbers: true,
            mode: 'htmlmixed',
            extraKeys: {"Ctrl-Space": "autocomplete"},
            lineWrapping: true,
            hintOptions: {
                hint: function (editor) {
                    var cursor = editor.getCursor();
                    var currentLine = editor.getLine(cursor.line);
                    var start = cursor.ch;
                    var end = start;
                    while (end < currentLine.length && /[\w|}$]+/.test(currentLine.charAt(end))) ++end;
                    while (start && /[\w|{$]+/.test(currentLine.charAt(start - 1))) --start;
                    var curWord = start != end && currentLine.slice(start, end);
                    var regex = new RegExp('^' + curWord, 'i');
                    var result = {
                        list: (!curWord ? rawTokens : mQuery(rawTokens).filter(function(idx) {
                            return (rawTokens[idx].indexOf(curWord) !== -1);
                        })),
                        from: CodeMirror.Pos(cursor.line, start),
                        to: CodeMirror.Pos(cursor.line, end)
                    };

                    return result;
                }
            }
        });

        Le.keepPreviewAlive('builder-template-content');
    } else {
        // hide preference center slots
        var isPrefCenterEnabled = eval(parent.mQuery('input[name="page[isPreferenceCenter]"]:checked').val());
        var slots = [
            'segmentlist',
            'categorylist',
            'preferredchannel',
            'channelfrequency',
            'saveprefsbutton'
        ];
        mQuery.each(slots, function(i, s){
            if (isPrefCenterEnabled) {
                mQuery('[data-slot-type=' + s + ']').show();
            } else {
                mQuery('[data-slot-type=' + s + ']').hide();
            }
        });
    }

    var builderPanel = mQuery('.builder-panel');
    var builderContent = mQuery('.builder-content');
    var btnCloseBuilder = mQuery('.btn-close-builder');
    var applyBtn = mQuery('.btn-apply-builder');
    var panelHeight = (builderContent.css('right') == '0px') ? builderPanel.height() : 0;
    var panelWidth = (builderContent.css('right') == '0px') ? 0 : builderPanel.width();
    var spinnerLeft = (mQuery(window).width() - panelWidth - 60) / 2;
    var spinnerTop = (mQuery(window).height() - panelHeight - 60) / 2;
    var form = mQuery('form[name='+formName+']');

    applyBtn.off('click').on('click', function(e) {
       // alert("apply button clicked");
        Le.activateButtonLoadingIndicator(applyBtn);
        Le.sendBuilderContentToTextarea(function() {
            // Trigger slot:destroy event
            document.getElementById('builder-template-content').contentWindow.Le.destroySlots();
            // Clear the customize forms
            mQuery('#slot-form-container, #section-form-container').html('');
            Le.inBuilderSubmissionOn(form);
            var bgApplyBtn = mQuery('.btn-apply');
            if (0 === bgApplyBtn.length && ("1" === Le.getUrlParameter('contentOnly') || Le.isInBuilder)) {
                var frm = mQuery('.btn-save').closest('form');
                Le.inBuilderSubmissionOn(frm);
                frm.submit();
                Le.inBuilderSubmissionOff();
            } else {
                bgApplyBtn.trigger('click');
            }
            Le.inBuilderSubmissionOff();
        }, true);
    });

    // Blur and focus the focussed inputs to fix the browser autocomplete bug on scroll
    builderPanel.on('scroll', function(e) {
        // If Froala popup window open
        if(mQuery.find('.fr-popup:visible').length){
            if(!Le.isInViewport(builderPanel.find('.fr-view:visible'))) {
                builderPanel.find('.fr-view:visible').blur();
                builderPanel.find('input:focus').blur();
            }
        }else{
            builderPanel.find('input:focus').blur();

        }
    });

    var overlay = mQuery('<div id="builder-overlay" class="modal-backdrop fade in"><div style="position: absolute; top:' + spinnerTop + 'px; left:' + spinnerLeft + 'px" class="builder-spinner"><i class="fa fa-spinner fa-spin fa-5x"></i></div></div>').css(builderCss).appendTo('.builder-content');

    // Disable the close button until everything is loaded
    btnCloseBuilder.prop('disabled', true);
    applyBtn.prop('disabled', true);

    // Insert the Mautic assets to the header
    var assets = Le.htmlspecialchars_decode(mQuery('[data-builder-assets]').html());
    themeHtml = themeHtml.replace('</head>', assets+'</head>');

    Le.initBuilderIframe(themeHtml, btnCloseBuilder, applyBtn);
};

Le.isInViewport = function(el) {
    var elementTop = mQuery(el).offset().top;
    var elementBottom = elementTop + mQuery(el).outerHeight();

    var viewportTop = mQuery(window).scrollTop();
    var viewportBottom = viewportTop + mQuery(window).height();

    return elementBottom > viewportTop && elementTop < viewportBottom;
};

/**
 * Adds a hidded field which adds inBuilder=1 param to the request and will be returned in the response
 *
 * @param jQuery object of form
 */
Le.inBuilderSubmissionOn = function(form) {
    var inBuilder = mQuery('<input type="hidden" name="inBuilder" value="1" />');
    form.append(inBuilder);
}

/**
 * Removes the hidded field which adds inBuilder=1 param to the request
 *
 * @param jQuery object of form
 */
Le.inBuilderSubmissionOff = function(form) {
    Le.isInBuilder = false;
    mQuery('input[name="inBuilder"]').remove();
}

/**
 * Processes the Apply's button response
 *
 * @param  object response
 */
Le.processBuilderErrors = function(response) {
    if (response.validationError) {
        mQuery('.btn-apply-builder').attr('disabled', true);
        Le.closeCampaignBuilder();
        //mQuery('#builder-errors').show('fast').text(response.validationError);
    }
};

/**
 * Frmats code style in the CodeMirror editor
 */
Le.formatCode = function() {
    Le.builderCodeMirror.autoFormatRange({line: 0, ch: 0}, {line: Le.builderCodeMirror.lineCount()});
}

/**
 * Opens Filemanager window
 */
Le.openMediaManager = function() {
    Le.openServerBrowser(
        leBasePath + '/' + leAssetPrefix + 'app/bundles/CoreBundle/Assets/js/libraries/ckeditor/filemanager/index.html?type=Images',
        screen.width * 0.7,
        screen.height * 0.7
    );
}

/**
 * Receives a file URL from Filemanager when selected
 */
Le.setFileUrl = function(url, width, height, alt) {
    Le.insertTextAtCMCursor(url);
}

/**
 * Inserts the text to the cursor position or replace selected range
 */
Le.insertTextAtCMCursor = function(text) {
    var doc = Le.builderCodeMirror.getDoc();
    var cursor = doc.getCursor();
    doc.replaceRange(text, cursor);
}

/**
 * Opens new window on the URL
 */
Le.openServerBrowser = function(url, width, height) {
    var iLeft = (screen.width - width) / 2 ;
    var iTop = (screen.height - height) / 2 ;
    var sOptions = "toolbar=no,status=no,resizable=yes,dependent=yes" ;
    sOptions += ",width=" + width ;
    sOptions += ",height=" + height ;
    sOptions += ",left=" + iLeft ;
    sOptions += ",top=" + iTop ;
    var oWindow = window.open( url, "BrowseWindow", sOptions ) ;
}

/**
 * Creates an iframe and keeps its content live from CodeMirror changes
 *
 * @param iframeId
 * @param slot
 */
Le.keepPreviewAlive = function(iframeId, slot) {
    var codeChanged = false;
    // Watch for code changes
    Le.builderCodeMirror.on('change', function(cm, change) {
        codeChanged = true;
    });

    window.setInterval(function() {
        if (codeChanged) {
            var value = (Le.builderCodeMirror)?Le.builderCodeMirror.getValue():'';
            Le.livePreviewInterval = Le.updateIframeContent(iframeId, value, slot);
            codeChanged = false;
        }
    }, 2000);
};

Le.killLivePreview = function() {
    window.clearInterval(Le.livePreviewInterval);
};

Le.destroyCodeMirror = function() {
    delete Le.builderCodeMirror;
    mQuery('#customHtmlContainer').empty();
};

/**
 * @param themeHtml
 * @param id
 * @param onLoadCallback
 */
Le.buildBuilderIframe = function(themeHtml, id, onLoadCallback) {
    if (mQuery('iframe#'+id).length) {
        var builder = mQuery('iframe#'+id);
    } else {
        var builder = mQuery("<iframe />", {
            css: {
                margin: "0",
                padding: "0",
                border: "none",
                width: "100%",
                height: "100%"
            },
            id: id
        }).appendTo('.builder-content');
    }
    builder.off('load').on('load', function() {
        if (typeof onLoadCallback === 'function') {
            onLoadCallback();
        }
    });

    Le.updateIframeContent(id, themeHtml);
};

/**
 * @param encodedHtml
 * @returns {*}
 */
Le.htmlspecialchars_decode = function(encodedHtml) {
    encodedHtml = encodedHtml.replace(/&quot;/g, '"');
    encodedHtml = encodedHtml.replace(/&#039;/g, "'");
    encodedHtml = encodedHtml.replace(/&amp;/g, '&');
    encodedHtml = encodedHtml.replace(/&lt;/g, '<');
    encodedHtml = encodedHtml.replace(/&gt;/g, '>');
    return encodedHtml;
};

/**
 * Initialize theme selection
 *
 * @param themeField
 */
Le.initSelectTheme = function(themeField) {
    var customHtml = mQuery('textarea.builder-html');
    var isNew = Le.isNewEntity('#page_sessionId, #emailform_sessionId');
    Le.showChangeThemeWarning = true;
    Le.builderTheme = themeField.val();

    if (isNew) {
        Le.showChangeThemeWarning = false;

        // Populate default content
        if (!customHtml.length || !customHtml.val().length) {
            Le.setThemeHtml(Le.builderTheme);
        }
    }

    if (customHtml.length) {
        mQuery('[data-theme]').click(function(e) {

          //  alert("data-theme");
            e.preventDefault();
            var currentLink = mQuery(this);
            var theme = currentLink.attr('data-theme');
            var isCodeMode = (theme === 'mautic_code_mode');
            Le.builderTheme = theme;

            if (Le.showChangeThemeWarning && customHtml.val().length) {
                if (!isCodeMode) {
                    if (confirm(Le.translate('le.core.builder.theme_change_warning'))) {
                        customHtml.val('');
                        Le.showChangeThemeWarning = false;
                    } else {
                        return;
                    }
                } else {
                    if (confirm(Le.translate('Le.core.builder.code_mode_warning'))) {
                    } else {
                        return;
                    }
                }
            }

            // Set the theme field value
            themeField.val(theme);
            // Code Mode
            if (isCodeMode) {
                mQuery('.builder').addClass('code-mode');
                mQuery('.builder .code-editor').removeClass('hide');
                mQuery('.builder .code-mode-toolbar').removeClass('hide');
                mQuery('.builder .builder-toolbar').addClass('hide');
            } else {
                mQuery('.builder').removeClass('code-mode');
                mQuery('.builder .code-editor').addClass('hide');
                mQuery('.builder .code-mode-toolbar').addClass('hide');
                mQuery('.builder .builder-toolbar').removeClass('hide');

                // Load the theme HTML to the source textarea
                Le.setThemeHtml(theme);
            }

            // Manipulate classes to achieve the theme selection illusion
            mQuery('.theme-list .panel').removeClass('theme-selected');
            currentLink.closest('.panel').addClass('theme-selected');
            mQuery('.theme-list .select-theme-selected').addClass('hide');
            mQuery('.theme-list .select-theme-link').removeClass('hide');
            currentLink.closest('.panel').find('.select-theme-selected').removeClass('hide');
            currentLink.addClass('hide');
        });
    }
};

/**
 * Initialize theme selection
 *
 * @param themeField
 */
Le.initSelectBeeTemplate = function(themeField,formname) {
    var templateJSON = mQuery('textarea.bee-editor-json');
    var isNew = Le.isNewEntity('#page_sessionId, #emailform_sessionId');
    Le.showChangeThemeWarning = true;
    Le.beeTemplate = themeField.val();
    var templateJSON = mQuery('textarea.bee-editor-json');
    var templateHTML = mQuery('textarea.builder-html');
    // Populate default content
    if (!templateJSON.length || !templateJSON.val().length) {
        if(!templateHTML.length || !templateHTML.val().length) {
            Le.setBeeTemplateJSON(Le.beeTemplate);
        }
    }
    if (isNew) {
        Le.showChangeThemeWarning = false;
        if(!mQuery('.sidebar-content').is(':visible') && formname=='email') {
            Le.selectEmailEditor("basic");
        }
    }else{
        if(formname=='email'){
            if (!templateJSON.length || !templateJSON.val().length) {
                Le.selectEmailEditor("basic");
            }else{
                Le.selectEmailEditor("advance");
            }
        }
    }
    if(formname=='page'){
        var templateJSON = mQuery('textarea.bee-editor-json');
        // Populate default content
        if (!templateJSON.length || !templateJSON.val().length) {
            Le.setBeeTemplateJSON(Le.beeTemplate);
        }
    }

    if (templateJSON.length) {
         mQuery('[data-beetemplate]').click(function(e) {
            e.preventDefault();
            var currentLink = mQuery(this);
            var theme = currentLink.attr('data-beetemplate');
            Le.beeTemplate = theme;

            if (Le.showChangeThemeWarning && templateJSON.val().length) {
                    if (confirm(Le.translate('le.core.builder.theme_change_warning'))) {
                        templateJSON.val('');
                        Le.showChangeThemeWarning = false;
                    } else {
                        return;
                    }
            }
            mQuery('#builder_url_text').focus();
            // Set the theme field value
            themeField.val(theme);
            // Load the template JSON to the source textarea
            Le.setBeeTemplateJSON(theme);

            // Manipulate classes to achieve the theme selection illusion
            mQuery('.theme-list .panel').removeClass('theme-selected');
            currentLink.closest('.panel').addClass('theme-selected');
            mQuery('.theme-list .select-theme-selected').addClass('hide');
            mQuery('.theme-list .select-theme-link').removeClass('hide');
            currentLink.closest('.panel').find('.select-theme-selected').removeClass('hide');
            currentLink.addClass('hide');
            if(location.href.match(/(emails)/i)) {
                Le.launchBeeEditor('emailform', 'email');
            } else if (location.href.match(/(pages)/i)){
                Le.launchBeeEditor('pageform', 'page');
            } else if (location.href.match(/(dripemail)/i)){
                Le.launchBeeEditor('dripemail', 'email');
            }
        });
    }
};

/**
 * Updates content of an iframe
 *
 * @param iframeId ID
 * @param content HTML content
 * @param slot
 */
Le.updateIframeContent = function(iframeId, content, slot) {
    // remove empty lines
    content = content.replace(/^\s*[\r\n]/gm, '');
    if (iframeId) {
        var iframe = document.getElementById(iframeId);
        var doc = iframe.contentDocument || iframe.contentWindow.document;
        doc.open();
        doc.write(content);
        doc.close();
        // remove html classes because they are duplicated with each save
        if ('HTML' === doc.all[0].tagName) {
            mQuery(doc.all[0]).removeClass();
        }
    } else if (slot) {
        slot.html(content);
    }
};

/**
 * Set theme's HTML
 *
 * @param theme
 */
Le.setThemeHtml = function(theme) {
    mQuery.get(mQuery('#builder_url').val()+'?template=' + theme, function(themeHtml) {
        var textarea = mQuery('textarea.builder-html');
        textarea.val(themeHtml);
    });
};

/**
 * Set Bee Template's JSON
 *
 * @param template
 */

Le.setBeeTemplateJSON = function(template) {
    Le.setBeeTemplateHTML(template);
    mQuery.get(mQuery('#builder_url').val()+'?beetemplate=' + template, function(templatejson) {
        var textarea = mQuery('textarea.bee-editor-json');
        textarea.val(templatejson);
    });
};

/**
 * Set Bee Template's JSON
 *
 * @param template
 */

Le.setBeeTemplateHTML = function(template) {
    mQuery.get(mQuery('#builder_url').val()+'?beehtmltemplate=' + template, function(templatehtml) {
        var textarea = mQuery('textarea.builder-html');
        textarea.val(templatehtml);
    });
};

/**
 * Close the builder
 *
 * @param model
 */
Le.closeBuilder = function(model) {
    var panelHeight = (mQuery('.builder-content').css('right') == '0px') ? mQuery('.builder-panel').height() : 0,
        panelWidth = (mQuery('.builder-content').css('right') == '0px') ? 0 : mQuery('.builder-panel').width(),
        spinnerLeft = (mQuery(window).width() - panelWidth - 60) / 2,
        spinnerTop = (mQuery(window).height() - panelHeight - 60) / 2,
        closeBtn = mQuery('.btn-close-builder'),
        overlay = mQuery('#builder-overlay'),
        builder = mQuery('.builder');

    mQuery('.builder-spinner').css({
        left: spinnerLeft,
        top: spinnerTop
    });
    overlay.removeClass('hide');
    closeBtn.prop('disabled', true);

    mQuery('#builder-errors').hide('fast').text('');

    try {
        Le.sendBuilderContentToTextarea(function() {
            if (Le.codeMode) {
                Le.killLivePreview();
                Le.destroyCodeMirror();
                delete Le.codeMode;
            } else {
                // Trigger slot:destroy event
                document.getElementById('builder-template-content').contentWindow.Le.destroySlots();

                // Clear the customize forms
                mQuery('#slot-form-container, #section-form-container').html('');
            }

            // Kill the overlay
            overlay.remove();

            // Hide builder
            builder.removeClass('builder-active').addClass('hide');
            closeBtn.prop('disabled', false);
            mQuery('body').css('overflow-y', '');
            builder.addClass('hide');
            Le.stopIconSpinPostEvent();
            mQuery('#builder-template-content').remove();
        }, false);

    } catch (error) {
        // prevent from being able to close builder
        console.error(error);
    }
};

/**
 * Copies the HTML from the builder to the textarea and sanitizes it along the way.
 *
 * @param Function callback
 * @param bool keepBuilderContent
 */
Le.sendBuilderContentToTextarea = function(callback, keepBuilderContent) {
    var customHtml;
    try {
        if (Le.codeMode) {
            customHtml = Le.builderCodeMirror.getValue();

            // Convert dynamic slot definitions into tokens
            customHtml = Le.convertDynamicContentSlotsToTokens(customHtml);

            // Store the HTML content to the HTML textarea
            mQuery('.builder-html').val(customHtml);
            callback();
        } else {
            var builderHtml = mQuery('iframe#builder-template-content').contents();

            if (keepBuilderContent) {
                // The content has to be cloned so the sanitization won't affect the HTML in the builder
                Le.cloneHtmlContent(builderHtml, function(themeHtml) {
                    Le.sanitizeHtmlAndStoreToTextarea(themeHtml);
                    callback();
                });
            } else {
                Le.sanitizeHtmlAndStoreToTextarea(builderHtml);
                callback();
            }
        }
    } catch (error) {
        // prevent from being able to close builder
        console.error(error);
    }
}

Le.sanitizeHtmlAndStoreToTextarea = function(html) {
    var cleanHtml = Le.sanitizeHtmlBeforeSave(html);

    // Store the HTML content to the HTML textarea
    mQuery('.builder-html').val(Le.domToString(cleanHtml));
};

/**
 * Serializes DOM (full HTML document) to string
 *
 * @param  object dom
 * @return string
 */
Le.domToString = function(dom) {
    if (typeof dom === 'string') {
        return dom;
    }
    var xs = new XMLSerializer();
    return xs.serializeToString(dom.get(0));
};

/**
 * Removes stuff the Builder needs for it's magic but cannot be in the HTML result
 *
 * @param  object htmlContent
 */
Le.sanitizeHtmlBeforeSave = function(htmlContent) {
    // Remove Mautic's assets
    htmlContent.find('[data-source="le"]').remove();
    htmlContent.find('.atwho-container').remove();
    htmlContent.find('.fr-image-overlay, .fr-quick-insert, .fr-tooltip, .fr-toolbar, .fr-popup, .fr-image-resizer').remove();

    // Remove the slot focus highlight
    htmlContent.find('[data-slot-focus], [data-section-focus]').remove();

    var customHtml = Le.domToString(htmlContent);

    // Convert dynamic slot definitions into tokens
    return Le.convertDynamicContentSlotsToTokens(customHtml);
};

/**
 * Clones full HTML document by creating a virtual iframe, putting the HTML into it and
 * reading it back. This is async process.
 *
 * @param  object   content
 * @param  Function callback(clonedContent)
 */
Le.cloneHtmlContent = function(content, callback) {
    var id = 'iframe-helper';
    var iframeHelper = mQuery('<iframe id="'+id+'" />');
    Le.buildBuilderIframe(Le.domToString(content), id, function() {
        callback(mQuery('iframe#'+id).contents());
        iframeHelper.remove();
    });
}

Le.destroySlots = function() {
    // Trigger destroy slots event
    if (typeof Le.builderSlots !== 'undefined' && Le.builderSlots.length) {
        mQuery.each(Le.builderSlots, function(i, slotParams) {
            mQuery(slotParams.slot).trigger('slot:destroy', slotParams);
        });
        delete Le.builderSlots;
    }

    // Destroy sortable
    Le.builderContents.find('[data-slot-container]').sortable().sortable('destroy');

    // Remove empty class="" attr
    Le.builderContents.find('*[class=""]').removeAttr('class');

    // Remove border highlighted by Froala
    Le.builderContents = Le.clearFroalaStyles(Le.builderContents);

    // Remove style="z-index: 2501;" which Froala forgets there
    Le.builderContents.find('*[style="z-index: 2501;"]').removeAttr('style');

    // Make sure that the Froala editor is gone
    Le.builderContents.find('.fr-toolbar, .fr-line-breaker').remove();

    // Remove the class attr vrom HTML tag used by Modernizer
    var htmlTags = document.getElementsByTagName('html');
    htmlTags[0].removeAttribute('class');
};

Le.clearFroalaStyles = function(content) {
    mQuery.each(content.find('td, th, table, [fr-original-class], [fr-original-style]'), function() {
        var el = mQuery(this);
        if (el.attr('fr-original-class')) {
            el.attr('class', el.attr('fr-original-class'));
            el.removeAttr('fr-original-class');
        }
        if (el.attr('fr-original-style')) {
            el.attr('style', el.attr('fr-original-style'));
            el.removeAttr('fr-original-style');
        }
        if (el.css('border') === '1px solid rgb(221, 221, 221)') {
            el.css('border', '');
        }
    });
    content.find('link[href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css"]').remove();

    // fix Mautc's tokens in the strong tag
    content.find('strong[contenteditable="false"]').removeAttr('style');

    // data-atwho-at-query causes not working tokens
    content.find('[data-atwho-at-query]').removeAttr('data-atwho-at-query');
    return content;
}

Le.toggleBuilderButton = function (hide) {
    if (mQuery('.toolbar-form-buttons .toolbar-standard .btn-builder')) {
        if (hide) {
            // Move the builder button out of the group and hide it
            mQuery('.toolbar-form-buttons .toolbar-standard .btn-builder')
                .addClass('hide btn-standard-toolbar')
                .appendTo('.toolbar-form-buttons')

            mQuery('.toolbar-form-buttons .toolbar-dropdown i.fa-cube').parent().addClass('hide');
        } else {
            if (!mQuery('.btn-standard-toolbar.btn-builder').length) {
                mQuery('.toolbar-form-buttons .toolbar-standard .btn-builder').addClass('btn-standard-toolbar')
            } else {
                // Move the builder button out of the group and hide it
                mQuery('.toolbar-form-buttons .btn-standard-toolbar.btn-builder')
                    .prependTo('.toolbar-form-buttons .toolbar-standard')
                    .removeClass('hide');

                mQuery('.toolbar-form-buttons .toolbar-dropdown i.fa-cube').parent().removeClass('hide');
            }
        }
    }
};

Le.initSectionListeners = function() {
    Le.activateGlobalFroalaOptions();
    Le.selectedSlot = null;

    Le.builderContents.on('section:init', function(event, section, isNew) {
        section = mQuery(section);

        if (isNew) {
            Le.initSlots(section.find('[data-slot-container]'));
        }

        section.on('click', function(e) {
            var clickedSection = mQuery(this);
            var previouslyFocused = Le.builderContents.find('[data-section-focus]');
            var sectionWrapper = mQuery(this);
            var section = sectionWrapper.find('[data-section]');
            var focusParts = {
                'top': {},
                'right': {},
                'bottom': {},
                'left': {},
                'clone': {
                    classes: 'fa fa-copy',
                    onClick: function() {
                        var cloneBtn = mQuery(this);
                        var clonedElem = cloneBtn.closest('[data-section-wrapper]');
                        clonedElem.clone().insertAfter(clonedElem);
                        Le.initSlotListeners();
                        Le.initSections();
                        Le.initSlots();
                    }
                },
                'handle': {
                    classes: 'fa fa-arrows-v'
                },
                'delete': {
                    classes: 'fa fa-remove',
                    onClick: function() {
                        if (confirm(parent.Le.translate('Le.core.builder.section_delete_warning'))) {
                            var deleteBtn = mQuery(this);
                            var focusSeciton = deleteBtn.closest('[data-section-wrapper]').remove();
                        }
                    }
                }
            };
            var sectionForm = mQuery(parent.mQuery('script[data-section-form]').html());
            var sectionFormContainer = parent.mQuery('#section-form-container');

            if (previouslyFocused.length) {

                // Unfocus other section
                previouslyFocused.remove();

                // Destroy minicolors
                sectionFormContainer.find('input[data-toggle="color"]').each(function() {
                    mQuery(this).minicolors('destroy');
                });
            }

            Le.builderContents.find('[data-slot-focus]').each(function() {
                if (!mQuery(e.target).attr('data-slot-focus') && !mQuery(e.target).closest('data-slot').length && !mQuery(e.target).closest('[data-slot-container]').length) {
                    mQuery(this).remove();
                }
            });

            // Highlight the section
            mQuery.each(focusParts, function (key, config) {
                var focusPart = mQuery('<div/>').attr('data-section-focus', key).addClass(config.classes);

                if (config.onClick) {
                    focusPart.on('click', config.onClick);
                }

                sectionWrapper.append(focusPart);
            });

            // Open the section customize form
            sectionFormContainer.html(sectionForm);

            // Prefill the sectionform with section color
            if (section.length && section.css('background-color') !== 'rgba(0, 0, 0, 0)') {
                sectionForm.find('#builder_section_content-background-color').val(Le.rgb2hex(section.css('backgroundColor')));
            }

            // Prefill the sectionform with section wrapper color
            if (sectionWrapper.css('background-color') !== 'rgba(0, 0, 0, 0)') {
                sectionForm.find('#builder_section_wrapper-background-color').val(Le.rgb2hex(sectionWrapper.css('backgroundColor')));
            }

            // Initialize the color picker
            sectionFormContainer.find('input[data-toggle="color"]').each(function() {
                parent.Le.activateColorPicker(this);
            });

            // Handle color change events
            sectionForm.on('keyup paste change touchmove', function(e) {
                var field = mQuery(e.target);
                if (section.length && field.attr('id') === 'builder_section_content-background-color') {
                    Le.sectionBackgroundChanged(section, field.val());
                } else if (field.attr('id') === 'builder_section_wrapper-background-color') {
                    Le.sectionBackgroundChanged(sectionWrapper, field.val());
                }
            });

            parent.mQuery('#section-form-container').on('change.minicolors', function(e, hex) {
                var field = mQuery(e.target);
                var focusedSectionWrapper = mQuery('[data-section-focus]').parent();
                var focusedSection = focusedSectionWrapper.find('[data-section]');
                if (focusedSection.length && field.attr('id') === 'builder_section_content-background-color') {
                    Le.sectionBackgroundChanged(focusedSection, field.val());
                } else if (field.attr('id') === 'builder_section_wrapper-background-color') {
                    Le.sectionBackgroundChanged(focusedSectionWrapper, field.val());
                }
            });
        });
    });
}

Le.initSections = function() {
    Le.initSectionListeners();
    var sectionWrappers = Le.builderContents.find('[data-section-wrapper]');

    // Make slots sortable
    var bodyOverflow = {};
    Le.sortActive = false;

    mQuery('body').sortable({
        helper: function(e, ui) {
            // Fix body overflow that messes sortable up
            bodyOverflow.overflowX = mQuery('body').css('overflow-x');
            bodyOverflow.overflowY = mQuery('body').css('overflow-y');
            mQuery('body').css({
                overflowX: 'visible',
                overflowY: 'visible'
            });

            return ui;
        },
        axis: 'y',
        items: '[data-section-wrapper]',
        handle: '[data-section-focus="handle"]',
        placeholder: 'slot-placeholder',
        connectWith: 'body',
        start: function(event, ui) {
            Le.sortActive = true;
            ui.placeholder.height(ui.helper.outerHeight());
        },
        stop: function(event, ui) {
            if (ui.item.hasClass('section-type-handle')) {
                // Restore original overflow
                mQuery('body', parent.document).css(bodyOverflow);

                var newSection = mQuery('<div/>')
                    .attr('data-section-wrapper', ui.item.attr('data-section-type'))
                    .html(ui.item.find('script').html());
                ui.item.replaceWith(newSection);

                Le.builderContents.trigger('section:init', [newSection, true]);
            } else {
                // Restore original overflow
                mQuery('body').css(bodyOverflow);
            }

            Le.sortActive = false;
        },
    });

    // Allow to drag&drop new sections from the section type menu
    var iframe = mQuery('#builder-template-content', parent.document).contents();
    mQuery('#section-type-container .section-type-handle', parent.document).draggable({
        iframeFix: true,
        connectToSortable: 'body',
        revert: 'invalid',
        iframeOffset: iframe.offset(),
        helper: function(e, ui) {
            // Fix body overflow that messes sortable up
            bodyOverflow.overflowX = mQuery('body', parent.document).css('overflow-x');
            bodyOverflow.overflowY = mQuery('body', parent.document).css('overflow-y');
            mQuery('body', parent.document).css({
                overflowX: 'hidden',
                overflowY: 'hidden'
            });

            var helper = mQuery(this).clone()
                .css('height', mQuery(this).height())
                .css('width', mQuery(this).width());

            return helper;
        },
        zIndex: 8000,
        cursorAt: {top: 15, left: 15},
        start: function(event, ui) {
            mQuery('#builder-template-content', parent.document).css('overflow', 'hidden');
            mQuery('#builder-template-content', parent.document).attr('scrolling', 'no');
        },
        stop: function(event, ui) {
            // Restore original overflow
            mQuery('body', parent.document).css(bodyOverflow);

            mQuery('#builder-template-content', parent.document).css('overflow', 'visible');
            mQuery('#builder-template-content', parent.document).attr('scrolling', 'yes');
        }
    }).disableSelection();

    // Initialize the slots
    sectionWrappers.each(function() {
        mQuery(this).trigger('section:init', this);
    });
};

Le.sectionBackgroundChanged = function(element, color) {
    if (color.length) {
        color = '#'+color;
    } else {
        color = 'transparent';
    }
    element.css('background-color', color).attr('bgcolor', color);


    // Change the color of the editor for selected slots
    mQuery(element).find('[data-slot-focus]').each(function() {
        var focusedSlot = mQuery(this).closest('[data-slot]');
        if (focusedSlot.attr('data-slot') == 'text') {
            Le.setTextSlotEditorStyle(parent.mQuery('#slot_text_content'), focusedSlot);
        }
    });
};

Le.rgb2hex = function(orig) {
    var rgb = orig.replace(/\s/g,'').match(/^rgba?\((\d+),(\d+),(\d+)/i);
    return (rgb && rgb.length === 4) ? "#" +
        ("0" + parseInt(rgb[1],10).toString(16)).slice(-2) +
        ("0" + parseInt(rgb[2],10).toString(16)).slice(-2) +
        ("0" + parseInt(rgb[3],10).toString(16)).slice(-2) : orig;
};

Le.initSlots = function(slotContainers) {
    if (!slotContainers) {
        slotContainers = Le.builderContents.find('[data-slot-container]');
    }

    Le.builderContents.find('a').on('click', function(e) {
        e.preventDefault();
    });

    // Make slots sortable
    var bodyOverflow = {};
    Le.sortActive = false;
    Le.parentDocument = parent.document;

    slotContainers.sortable({
        helper: function(e, ui) {
            // Fix body overflow that messes sortable up
            bodyOverflow.overflowX = mQuery('body').css('overflow-x');
            bodyOverflow.overflowY = mQuery('body').css('overflow-y');
            mQuery('body').css({
                overflowX: 'visible',
                overflowY: 'visible'
            });

            return ui;
        },
        items: '[data-slot]',
        handle: '[data-slot-toolbar]',
        placeholder: 'slot-placeholder',
        connectWith: '[data-slot-container]',
        start: function(event, ui) {
            Le.sortActive = true;
            ui.placeholder.height(ui.helper.outerHeight());

            Le.builderContents.find('[data-slot-focus]').each( function() {
                var focusedSlot = mQuery(this).closest('[data-slot]');
                if (focusedSlot.attr('data-slot') === 'image') {
                    // Deactivate froala toolbar
                    focusedSlot.find('img').each( function() {
                        mQuery(this).froalaEditor('popups.hideAll');
                    });
                    Le.builderContents.find('.fr-image-resizer.fr-active').removeClass('fr-active');
                }
            });

            Le.builderContents.find('[data-slot-focus]').remove();
        },
        stop: function(event, ui) {
            if (ui.item.hasClass('slot-type-handle')) {
                // Restore original overflow
                mQuery('body', parent.document).css(bodyOverflow);

                var newSlot = mQuery('<div/>')
                    .attr('data-slot', ui.item.attr('data-slot-type'))
                    .html(ui.item.find('script').html())
                ui.item.replaceWith(newSlot);

                Le.builderContents.trigger('slot:init', newSlot);
            } else {
                // Restore original overflow
                mQuery('body').css(bodyOverflow);
            }

            Le.sortActive = false;
        }
    });

    // Allow to drag&drop new slots from the slot type menu
    var iframe = mQuery('#builder-template-content', parent.document).contents();
    mQuery('#slot-type-container .slot-type-handle', parent.document).draggable({
        iframeFix: true,
        connectToSortable: '[data-slot-container]',
        revert: 'invalid',
        iframeOffset: iframe.offset(),
        helper: function(e, ui) {
            // fix for Uncaught TypeError: Cannot read property 'document' of null
            // Fix body overflow that messes sortable up
            bodyOverflow.overflowX = mQuery('body', Le.parentDocument).css('overflow-x');
            bodyOverflow.overflowY = mQuery('body', Le.parentDocument).css('overflow-y');
            mQuery('body', Le.parentDocument).css({
                overflowX: 'hidden',
                overflowY: 'hidden'
            });

            return mQuery(this).clone()
                .css('height', mQuery(this).height())
                .css('width', mQuery(this).width());
        },
        zIndex: 8000,
        cursorAt: {top: 15, left: 15},
        start: function(event, ui) {
            mQuery('#builder-template-content', Le.parentDocument).css('overflow', 'hidden');
            mQuery('#builder-template-content', Le.parentDocument).attr('scrolling', 'no');
            // check if it is initialized first to prevent error
            if (slotContainers.data('sortable')) slotContainers.sortable('option', 'scroll', false);
        },
        stop: function(event, ui) {
            // Restore original overflow
            mQuery('body', Le.parentDocument).css(bodyOverflow);

            mQuery('#builder-template-content', Le.parentDocument).css('overflow', 'visible');
            mQuery('#builder-template-content', Le.parentDocument).attr('scrolling', 'yes');
            // check if it is initialized first to prevent error
            if (slotContainers.data('sortable')) slotContainers.sortable('option', 'scroll', true);
            // this fixes an issue where after reopening the builder and trying to drag a slot, it leaves a clone behind
            parent.mQuery('.ui-draggable-dragging').remove();
        }
    }).disableSelection();

    iframe.on('scroll', function() {
        mQuery('#slot-type-container .slot-type-handle', Le.parentDocument).draggable("option", "cursorAt", { top: -1 * iframe.scrollTop() + 15 });
    });

    // Initialize the slots
    slotContainers.find('[data-slot]').each(function() {
        mQuery(this).trigger('slot:init', this);
    });
};

Le.getSlotToolbar = function(type) {
    Le.builderContents.find('[data-slot-toolbar]').remove();

    var slotToolbar = mQuery('<div/>').attr('data-slot-toolbar', true);
    var deleteLink  = Le.getSlotDeleteLink();
    var cloneLink = Le.getSlotCloneLink();
    if (typeof type !== 'undefined') {
        mQuery('<span style="color:#fff;margin-left:10px;font-family:sans-serif;font-size:smaller">' + type.toUpperCase() + '</span>').appendTo(slotToolbar);
    }
    deleteLink.appendTo(slotToolbar);
    cloneLink.appendTo(slotToolbar);

    return slotToolbar;
};

Le.getSlotDeleteLink = function() {
    if (typeof Le.deleteLink == 'undefined') {
        Le.deleteLink = mQuery('<a><i class="fa fa-lg fa-times"></i></a>')
            .attr('data-slot-action', 'delete')
            .attr('alt', 'delete')
            .addClass('btn btn-delete btn-default');
    }

    return Le.deleteLink;
};

Le.getSlotCloneLink = function() {
    if (typeof Le.cloneLink == 'undefined') {
        Le.cloneLink = mQuery('<a><i class="fa fa-lg fa-copy"></i></a>')
            .attr('data-slot-action', 'clone')
            .attr('alt', 'clone')
            .addClass('btn btn-clone btn-clone');
    }
    return Le.cloneLink;
};

Le.getSlotFocus = function() {
    Le.builderContents.find('[data-slot-focus]').remove();

    return mQuery('<div/>').attr('data-slot-focus', true);
};

Le.cloneFocusForm = function(decId, removeFroala) {
    Le.reattachDEC();

    var focusForm = parent.mQuery('#emailform_dynamicContent_' + decId);
    Le.activeDECParent = focusForm.parent();
    // show if hidden
    focusForm.removeClass('fade');
    // remove delete default button
    focusForm.find('.tab-pane:first').find('.remove-item').hide();
    // hide add variant button
    focusForm.find('.addNewDynamicContentFilter').hide();
    var element =focusForm.detach();
    Le.activeDEC = element;
    return element;
};

Le.initEmailDynamicContentSlotEdit = function (clickedSlot) {
    var decId = clickedSlot.attr('data-param-dec-id');

    var focusForm;

    if (decId || decId === 0) {
        focusForm = Le.cloneFocusForm(decId);
    }

    var focusFormHeader = parent.mQuery('#customize-slot-panel').find('.panel-heading h4');
    var newDynConButton = mQuery('<button/>')
        .css('float', 'right')
        .addClass('btn btn-success btn-xs');

    newDynConButton.text('Add Variant');
    newDynConButton.on('click', function(e) {
        e.stopPropagation();
        Le.createNewDynamicContentFilter('#dynamicContentFilterTabs_'+decId, parent.mQuery);
        var focusForm = Le.cloneFocusForm(decId, false);
        focusForm.insertAfter(parent.mQuery('#slot_dynamiccontent > div.has-error'));
    });

    focusFormHeader.append(newDynConButton);

    return focusForm;
};

Le.removeAddVariantButton = function() {
    // Remove the Add Variant button for dynamicContent slots
    parent.mQuery('#customize-slot-panel').find('.panel-heading button').remove();
    Le.reattachDEC();
};

Le.reattachDEC = function() {
    if (typeof Le.activeDEC !== 'undefined') {
        var element = Le.activeDEC.detach();
        Le.activeDECParent.append(element);
    }
};

Le.isSlotInitiated = function(slot) {
    if (typeof Le.builderSlots === 'undefined' || Le.builderSlots.length === 0) return false;
    return typeof Le.builderSlots.find(function(params) {
        return slot.is(params.slot);
    }) !== 'undefined';
};

Le.initSlotListeners = function() {
    Le.activateGlobalFroalaOptions();
    Le.builderSlots = [];
    Le.selectedSlot = null;

    Le.builderContents.on('slot:selected', function(event, slot) {
        slot = mQuery(slot);
        Le.builderContents.find('[data-slot-focus]').remove();
        mQuery(slot).append(Le.getSlotFocus());
    });

    Le.builderContents.on('slot:init', function(event, slot) {
        slot = mQuery(slot);
        var type = slot.attr('data-slot');

        // Avoid initialising one slot several times
        if (Le.isSlotInitiated(slot)) return;

        // initialize the drag handle
        var slotToolbar = Le.getSlotToolbar(type);
        var deleteLink  = Le.getSlotDeleteLink();
        var cloneLink   = Le.getSlotCloneLink();
        var focus       = Le.getSlotFocus();

        slot.hover(function(e) {
            e.stopPropagation();

            // Get new copies of the focus, toolbar
            slotToolbar = Le.getSlotToolbar(type);
            focus       = Le.getSlotFocus();

            if (Le.sortActive) {
                // don't activate while sorting

                return;
            }

            slot.append(focus);
            deleteLink.click(function(e) {
                // if slot is DEC, delete it from the outside form
                if (type == 'dynamicContent') {
                    var dynConId = slot.attr('data-param-dec-id');
                    dynConId = '#emailform_dynamicContent_' + dynConId;
                    var dynConTarget = parent.mQuery(dynConId);
                    // clear name, so the slot:destroy event deletes it
                    dynConTarget.find(dynConId + '_tokenName').val('');
                }
                slot.trigger('slot:destroy', {slot: slot, type: type});
                mQuery.each(Le.builderSlots, function(i, slotParams) {
                    if (slotParams.slot.is(slot)) {
                        Le.builderSlots.splice(i, 1);
                        return false; // break the loop
                    }
                });
                slot.remove();
                focus.remove();
            });
            cloneLink.click(function(e) {
                slot.clone().insertAfter(slot);
                Le.initSlots(slot.closest('[data-slot-container="1"]'));
            });

            if (slot.offset().top < 25) {
                // If at the top of the page, move the toolbar to be visible
                slotToolbar.css('top', '0');
            } else {
                slotToolbar.css('top', '-24px');
            }

            slot.append(slotToolbar);
        }, function() {
            if (Le.sortActive) {
                // don't activate while sorting

                return;
            }

            slotToolbar.remove();
            focus.remove();
        });

        slot.on('click', function(e) {
            e.stopPropagation();

            Le.deleteCodeModeSlot();
            Le.removeAddVariantButton();

            var clickedSlot = mQuery(this);

            // Trigger the slot:change event
            clickedSlot.trigger('slot:selected', clickedSlot);

            // Destroy previously initiated minicolors
            var minicolors = parent.mQuery('#slot-form-container .minicolors');
            if (minicolors.length) {
                parent.mQuery('#slot-form-container input[data-toggle="color"]').each(function() {
                    mQuery(this).minicolors('destroy');
                });
                parent.mQuery('#slot-form-container').off('change.minicolors');
            }

            if (parent.mQuery('#slot-form-container').find('textarea.editor')) {
                // Deactivate all popups
                parent.mQuery('#slot-form-container').find('textarea.editor').each( function() {
                    parent.mQuery(this).froalaEditor('popups.hideAll');
                });
            }

            // Update form in the Customize tab to the form of the focused slot type
            var focusType = clickedSlot.attr('data-slot');
            var focusForm = mQuery(parent.mQuery('script[data-slot-type-form="'+focusType+'"]').html());
            var slotFormContainer = parent.mQuery('#slot-form-container');

            if (focusType == 'dynamicContent') {
                var nff = Le.initEmailDynamicContentSlotEdit(clickedSlot);
                // replace focusForm
                nff.insertAfter(focusForm.find('#slot_dynamiccontent > div.has-error'));
            }

            slotFormContainer.html(focusForm);

            // Prefill the form field values with the values from slot attributes if any
            parent.mQuery.each(clickedSlot.get(0).attributes, function(i, attr) {
                var regex = /data-param-(.*)/;
                var match = regex.exec(attr.name);

                if (match !== null) {

                    focusForm.find('input[type="text"][data-slot-param="'+match[1]+'"]').val(attr.value);

                    var selectField = focusForm.find('select[data-slot-param="'+match[1]+'"]');

                    if (selectField.length) {
                        selectField.val(attr.value)
                    }

                    // URL fields
                    var urlField = focusForm.find('input[type="url"][data-slot-param="'+match[1]+'"]');

                    if (urlField.length) {
                        urlField.val(attr.value);
                    }

                    // Number fields
                    var numberField = focusForm.find('input[type="number"][data-slot-param="'+match[1]+'"]');

                    if (numberField.length) {
                        numberField.val(attr.value);
                    }

                    var radioField = focusForm.find('input[type="radio"][data-slot-param="'+match[1]+'"][value="'+attr.value+'"]');

                    if (radioField.length) {
                        radioField.parent('.btn').addClass('active');
                        radioField.attr('checked', true);
                    }
                }
            });

            focusForm.on('keyup change', function(e) {
                var field = mQuery(e.target);

                // Store the slot settings as attributes
                if (field.attr('data-slot-param')) {
                    clickedSlot.attr('data-param-'+field.attr('data-slot-param'), field.val());
                }

                // Trigger the slot:change event
                clickedSlot.trigger('slot:change', {slot: clickedSlot, field: field, type: focusType});
            });

            focusForm.find('.btn').on('click', function(e) {
                var field = mQuery(this).find('input:radio');

                if (field.length) {
                    // Store the slot settings as attributes
                    clickedSlot.attr('data-param-'+field.attr('data-slot-param'), field.val());

                    // Trigger the slot:change event
                    clickedSlot.trigger('slot:change', {slot: clickedSlot, field: field, type: focusType});
                }
            });

            // Initialize the color picker
            focusForm.find('input[data-toggle="color"]').each(function() {
                parent.Le.activateColorPicker(this, {
                    change: function() {
                        var field = mQuery(this);

                        // Store the slot settings as attributes
                        clickedSlot.attr('data-param-'+field.attr('data-slot-param'), field.val());

                        clickedSlot.trigger('slot:change', {slot: clickedSlot, field: field, type: focusType});
                    }
                });
            });

            // initialize code mode slots
            $codeModeSlotTypes = ['codemode'];
            for (var i = 0; i < $codeModeSlotTypes.length; i++) {
                if ($codeModeSlotTypes[i] === type) {
                    Le.codeMode = true;
                    var element = focusForm.find('#slot_'+$codeModeSlotTypes[i]+'_content')[0];
                    if (element) {
                        Le.builderCodeMirror = CodeMirror.fromTextArea(element, {
                            lineNumbers: true,
                            mode: 'htmlmixed',
                            extraKeys: {"Ctrl-Space": "autocomplete"},
                            lineWrapping: true,
                        });
                        Le.builderCodeMirror.getDoc().setValue(slot.find('#codemodeHtmlContainer,.codemodeHtmlContainer').html());
                        Le.keepPreviewAlive(null, slot.find('#codemodeHtmlContainer,.codemodeHtmlContainer'));
                    }
                    break;
                }
            }

            focusForm.find('textarea.editor').each(function () {
                var theEditor = this;
                var slotHtml = parent.mQuery('<div/>').html(clickedSlot.html());
                slotHtml.find('[data-slot-focus]').remove();
                slotHtml.find('[data-slot-toolbar]').remove();

                var buttons = ['undo', 'redo', '|', 'bold', 'italic', 'underline', 'paragraphFormat', 'fontFamily', 'fontSize', 'color', 'align', 'formatOL', 'formatUL', 'quote', 'clearFormatting', 'token', 'insertLink', 'insertImage', 'insertGatedVideo', 'insertTable', 'html', 'fullscreen'];

                var builderEl = parent.mQuery('.builder');

                if (builderEl.length && builderEl.hasClass('email-builder')) {
                    buttons = parent.mQuery.grep(buttons, function (value) {
                        return value != 'insertGatedVideo';
                    });
                }

                var froalaOptions = {
                    toolbarButtons: buttons,
                    toolbarButtonsMD: buttons,
                    toolbarButtonsSM: buttons,
                    toolbarButtonsXS: buttons,
                    toolbarSticky: false,
                    linkList: [], // TODO push here the list of tokens from Le.getPredefinedLinks
                    imageEditButtons: ['imageReplace', 'imageAlign', 'imageRemove', 'imageAlt', 'imageSize', '|', 'imageLink', 'linkOpen', 'linkEdit', 'linkRemove']
                };

                // prevent overriding variant content in editor
                if (focusType !== 'dynamicContent') {
                    // init AtWho in a froala editor
                    parent.mQuery(this).on('froalaEditor.initialized', function (e, editor) {
                        parent.Le.initAtWho(editor.$el, parent.Le.getBuilderTokensMethod(), editor);
                        Le.setTextSlotEditorStyle(editor.$el, clickedSlot);
                    });
                }

                parent.mQuery(this).on('froalaEditor.contentChanged', function (e, editor) {
                    var slotHtml = mQuery('<div/>').append(editor.html.get());
                    // replace DEC with content from the first editor
                    if (!(focusType == 'dynamicContent' && mQuery(this).attr('id').match(/filters/))) {
                        clickedSlot.html(slotHtml.html());
                    }
                });

                // replace only the first editor content for DEC
                if (!(focusType == 'dynamicContent' && mQuery(this).attr('id').match(/filters/))) {
                    parent.mQuery(this).val(slotHtml.html());
                }

                parent.mQuery(this).froalaEditor(parent.mQuery.extend({}, Le.basicFroalaOptions, froalaOptions));
            });

        });

        // Initialize different slot types
        if (type === 'image' || type === 'imagecaption' || type === 'imagecard') {
            var image = slot.find('img');
            // fix of badly destroyed image slot
            image.removeAttr('data-froala.editor');

            image.on('froalaEditor.click', function (e, editor) {
                slot.closest('[data-slot]').trigger('click');
            });

            // Init Froala editor
            var froalaOptions = mQuery.extend({}, Le.basicFroalaOptions, {
                    linkList: [], // TODO push here the list of tokens from Le.getPredefinedLinks
                    imageEditButtons: ['imageReplace', 'imageAlign', 'imageAlt', 'imageSize', '|', 'imageLink', 'linkOpen', 'linkEdit', 'linkRemove'],
                    useClasses: false
                }
            );
            image.froalaEditor(froalaOptions);
        } else if (type === 'button') {
            slot.find('a').click(function(e) {
                e.preventDefault();
            });
        } else if (type === 'dynamicContent') {
            if (slot.html().match(/__dynamicContent__/)) {
                var decs = mQuery('[data-slot="dynamicContent"]');
                var ids = mQuery.map(decs, function(e){return mQuery(e).attr('data-param-dec-id');})
                var maxId = Math.max.apply(Math, ids);
                if (isNaN(maxId) || Number.NEGATIVE_INFINITY == maxId) maxId = 0;
                slot.attr('data-param-dec-id', maxId + 1);
                slot.html('Dynamic Content');
                Le.createNewDynamicContentItem(parent.mQuery);
            }
        }

        // Store the slot to a global var
        Le.builderSlots.push({slot: slot, type: type});
    });

    Le.getPredefinedLinks = function(callback) {
        var linkList = [];
        Le.getTokens(Le.getBuilderTokensMethod(), function(tokens) {
            if (tokens.length) {
                mQuery.each(tokens, function(token, label) {
                    if (token.startsWith('{pagelink=') ||
                        token.startsWith('{assetlink=') ||
                        token.startsWith('{webview_url') ||
                        token.startsWith('{unsubscribe_url')) {

                        linkList.push({
                            text: label,
                            href: token
                        });
                    }
                });
            }
            return callback(linkList);
        });
    };

    Le.builderContents.on('slot:change', function(event, params) {
        // Change some slot styles when the values are changed in the slot edit form
        var fieldParam = params.field.attr('data-slot-param');
        var type = params.type;

        if (type !== "dynamicContent") {
            Le.removeAddVariantButton();
        }
        Le.clearSlotFormError(fieldParam);
        if (fieldParam === 'width' || fieldParam === 'padding-left' || fieldParam === 'padding-right' || fieldParam === 'padding-top' || fieldParam === 'padding-bottom') {
            params.slot.css(fieldParam, params.field.val() + 'px');
        } else if ('label-text' === fieldParam) {
            params.slot.find('label.control-label').text(params.field.val());
        } else if ('label-text1' === fieldParam) {
            params.slot.find('label.label1').text(params.field.val());
        } else if ('label-text2' === fieldParam) {
            params.slot.find('label.label2').text(params.field.val());
        } else if ('label-text3' === fieldParam) {
            params.slot.find('label.label3').text(params.field.val());
        } else if ('label-text4' === fieldParam) {
            params.slot.find('label.label4').text(params.field.val());
        } else if ('glink' === fieldParam || 'flink' === fieldParam || 'tlink' === fieldParam) {
            params.slot.find('#'+fieldParam).attr('href', params.field.val());
        } else if (fieldParam === 'href') {
            params.slot.find('a').eq(0).attr('href', params.field.val());
        } else if (fieldParam === 'link-text') {
            params.slot.find('a').eq(0).text(params.field.val());
        } else if (fieldParam === 'float') {
            var values = ['left', 'center', 'right'];
            params.slot.find('a').parent().attr('align', values[params.field.val()]);
        } else if (fieldParam === 'caption') {
            params.slot.find('figcaption').text(params.field.val());
        } else if (fieldParam === 'cardcaption') {
            params.slot.find('td.imagecard-caption').text(params.field.val());
        } else if (fieldParam === 'text-align') {
            var values = ['left', 'center', 'right'];
            if (type === 'imagecard') {
                params.slot.find('.imagecard-caption').css(fieldParam, values[params.field.val()]);
            } else if (type === 'imagecaption') {
                params.slot.find('figcaption').css(fieldParam, values[params.field.val()]);
            }
        } else if (fieldParam === 'align') {
            Le.builderContents.find('[data-slot-focus]').each( function() {
                var focusedSlot = mQuery(this).closest('[data-slot]');
                if (focusedSlot.attr('data-slot') == 'image') {
                    // Deactivate froala toolbar
                    focusedSlot.find('img').each( function() {
                        mQuery(this).froalaEditor('popups.hideAll');
                    });
                    Le.builderContents.find('.fr-image-resizer.fr-active').removeClass('fr-active');
                }
            });

            var values = ['left', 'center', 'right'];
            if ('socialfollow' === type) {
                params.slot.find('div.socialfollow').css('text-align', values[params.field.val()]);
            } else if ('imagecaption' === type) {
                params.slot.find('figure').css('text-align', values[params.field.val()]);
            } else if ('imagecard' === type) {
                params.slot.find('td.imagecard-image').css('text-align', values[params.field.val()]);
            } else {
                params.slot.find('img').closest('div').css('text-align', values[params.field.val()]);
            }
        } else if (fieldParam === 'border-radius') {
            params.slot.find('a.button').css(fieldParam, params.field.val() + 'px');
        } else if (fieldParam === 'button-size') {
            var bg_clr = params.slot.attr('data-param-background-color');
            var values = [
                {borderWidth: '10px 20px', padding: '0', fontSize: '14px', borderColor : bg_clr, borderStyle: 'solid'},
                {borderWidth: '20px 23px', padding: '0', fontSize: '20px', borderColor : bg_clr, borderStyle: 'solid'},
                {borderWidth: '25px 40px', padding: '0', fontSize: '30px', borderColor : bg_clr, borderStyle: 'solid'}
            ];
            params.slot.find('a.button').css(values[params.field.val()]);
        } else if (fieldParam === 'caption-color') {
            params.slot.find('.imagecard-caption').css('background-color', '#' + params.field.val());
        } else if (fieldParam === 'background-color' || fieldParam === 'color') {
            var matches = params.field.val().match(/^#?([0-9a-f]{6}|[0-9a-f]{3})$/);

            if (matches !== null) {
                var color = matches[1];

                if (fieldParam === 'background-color') {
                    if ('imagecard' === type) {
                        params.slot.find('.imagecard').css(fieldParam, '#' + color);
                    } else {
                        params.slot.find('a.button').css(fieldParam, '#' + color);
                        params.slot.find('a.button').attr('background', '#' + color);
                        params.slot.find('a.button').css('border-color', '#' + color);
                    }
                } else if (fieldParam === 'color') {
                    if ('imagecard' === type) {
                        params.slot.find('.imagecard-caption').css(fieldParam, '#' + color);
                    } else if ('imagecaption' === type) {
                        params.slot.find('figcaption').css(fieldParam, '#' + color);
                    } else {
                        params.slot.find('a.button').css(fieldParam, '#' + color);
                    }
                }
            }
        } else if (/gatedvideo/.test(fieldParam)) {
            // Handle gatedVideo replacements
            var toInsert = fieldParam.split('-')[1];
            var insertVal = params.field.val();

            if (toInsert === 'url') {
                var videoProvider = Le.getVideoProvider(insertVal);

                if (videoProvider == null) {
                    Le.slotFormError(fieldParam, 'Please enter a valid YouTube, Vimeo, or MP4 url.');
                } else {
                    params.slot.find('source')
                        .attr('src', insertVal)
                        .attr('type', videoProvider);
                }
            } else if (toInsert === 'gatetime') {
                params.slot.find('video').attr('data-gate-time', insertVal);
            } else if (toInsert === 'formid') {
                params.slot.find('video').attr('data-form-id', insertVal);
            } else if (toInsert === 'height') {
                params.slot.find('video').attr('height', insertVal);
            } else if (toInsert === 'width') {
                params.slot.find('video').attr('width', insertVal);
            }
        } else if (fieldParam === 'separator-color') {
            params.slot.find('hr').css('border-color', '#' + params.field.val());
        } else if (fieldParam === 'separator-thickness') {
            var sep_color = params.slot.attr('data-param-separator-color');
            params.slot.find('hr').css('border', params.field.val() + 'px solid #'+ sep_color);
        }

        if (params.type == 'text') {
            // Le.setTextSlotEditorStyle(parent.mQuery('#slot_text_content'), params.slot);
        }
    });

    Le.builderContents.on('slot:destroy', function(event, params) {
        Le.reattachDEC();

        if (params.type === 'text') {
            if (parent.mQuery('#slot_text_content').length) {
                parent.mQuery('#slot_text_content').froalaEditor('destroy');
                parent.mQuery('#slot_text_content').find('.atwho-inserted').atwho('destroy');
            }
        } else if (params.type === 'image') {
            Le.deleteCodeModeSlot();

            var image = params.slot.find('img');
            if (typeof image !== 'undefined' && image.hasClass('fr-view')) {
                image.froalaEditor('destroy');
                image.removeAttr('data-froala.editor');
                image.removeClass('fr-view');
            }
        } else if (params.type === 'dynamicContent') {
            Le.removeAddVariantButton();
            // remove new DEC if name is empty
            var dynConId = params.slot.attr('data-param-dec-id');
            dynConId = '#emailform_dynamicContent_'+dynConId;
            if (Le.activeDEC && Le.activeDEC.attr('id') === dynConId.substr(1)) {
                delete Le.activeDEC;
                delete Le.activeDECParent;
            }
            var dynConTarget = parent.mQuery(dynConId);
            var dynConName   = dynConTarget.find(dynConId+'_tokenName').val();
            if (dynConName === '') {
                dynConTarget.find('a.remove-item:first').click();
                // remove vertical tab in outside form
                parent.mQuery('.dynamicContentFilterContainer').find('a[href=' + dynConId + ']').parent().remove();
                params.slot.remove();
            }
        }

        // Remove Symfony toolbar
        Le.builderContents.find('.sf-toolbar').remove();
    });
};

Le.deleteCodeModeSlot = function() {
    Le.killLivePreview();
    Le.destroyCodeMirror();
    delete Le.codeMode;
};

Le.clearSlotFormError = function(field) {
    var customizeSlotField = parent.mQuery('#customize-form-container').find('[data-slot-param="'+field+'"]');

    if (customizeSlotField.length) {
        customizeSlotField.attr('style', '');
        customizeSlotField.next('[data-error]').remove();
    }
};

Le.slotFormError = function (field, message) {
    var customizeSlotField = parent.mQuery('#customize-form-container').find('[data-slot-param="'+field+'"]');

    if (customizeSlotField.length) {
        customizeSlotField.css('border-color', 'red');

        if (message.length) {
            var messageContainer = mQuery('<p/>')
                .text(message)
                .attr('data-error', 'true')
                .css({
                    color: 'red',
                    padding: '5px 0'
                });

            messageContainer.insertAfter(customizeSlotField);
        }
    }
};

Le.getVideoProvider = function(url) {
    var providers = [
        {
            test_regex: /^.*((youtu.be)|(youtube.com))\/((v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))?\??v?=?([^#\&\?]*).*/,
            provider: 'video/youtube'
        },
        {
            test_regex: /^.*(vimeo\.com\/)((channels\/[A-z]+\/)|(groups\/[A-z]+\/videos\/))?([0-9]+)/,
            provider: 'video/vimeo'
        },
        {
            test_regex: /mp4/,
            provider: 'video/mp4'
        }
    ];

    for (var i = 0; i < providers.length; i++) {
        var vp = providers[i];
        if (vp.test_regex.test(url)) {
            return vp.provider;
        }
    }

    return null;
};

Le.setTextSlotEditorStyle = function(editorEl, slot)
{
    // Set the editor CSS to that of the slot
    var wrapper = parent.mQuery(editorEl).closest('.form-group').find('.fr-wrapper .fr-element').first();

    if (typeof wrapper == 'undefined') {
        return;
    }

    if (typeof slot.attr('style') !== 'undefined') {
        wrapper.attr('style', slot.attr('style'));
    }

    mQuery.each(['background-color', 'color', 'font-family', 'font-size', 'line-height', 'text-align'], function(key, style) {
        var overrideStyle = Le.getSlotStyle(slot, style, false);
        if (overrideStyle) {
            wrapper.css(style, overrideStyle);
        }
    });
};

Le.getSlotStyle = function(slot, styleName, fallback) {
    if ('background-color' == styleName) {
        // Get this browser's take on no fill
        // Must be appended else Chrome etc return 'initial'
        var temp = mQuery('<div style="background:none;display:none;"/>').appendTo('body');
        var transparent = temp.css(styleName);
        temp.remove();
    }

    var findStyle = function (slot) {
        function test(elem) {
            if ('background-color' == styleName) {
                if (typeof elem.attr('bgcolor') !== 'undefined') {
                    // Email tables
                    return elem.attr('bgcolor');
                }

                if (elem.css(styleName) == transparent) {
                    return !elem.is('body') ? test(elem.parent()) : fallback || transparent;
                } else {
                    return elem.css(styleName);
                }
            } else if (typeof elem.css(styleName) !== 'undefined') {
                return elem.css(styleName);
            } else {
                return !elem.is('body') ? test(elem.parent()) : fallback;
            }
        }

        return test(slot);
    };

    return findStyle(slot);
};

/**
 * @returns {string}
 */
Le.getBuilderTokensMethod = function() {
    var method = 'page:getBuilderTokens';
    if (parent.mQuery('.builder').hasClass('email-builder')) {
        method = 'email:getBuilderTokens';
    }
    return method;
};

Le.prepareBuilderIframe = function(themeHtml, btnCloseBuilder, applyBtn) {
    // find DEC tokens and inject them to builderTokens
    var decTokenRegex  = /(?:{)dynamiccontent="(.*?)(?:")}/g;
    var match = decTokenRegex.exec(themeHtml);
    while (match !== null) {
        var dynConToken = match[0];
        var dynConName = match[1];
        // Add the dynamic content tokens
        if (!Le.builderTokens.hasOwnProperty(dynConToken)) {
            Le.builderTokens[dynConToken] = dynConName;
        }

        // fetch next token
        match = decTokenRegex.exec(themeHtml);
    }

    // Turn Dynamic Content Tokens into builder slots
    themeHtml = Le.prepareDynamicContentBlocksForBuilder(themeHtml);

    // hide preference center slots
    var isPrefCenterEnabled = eval(parent.mQuery('input[name="page[isPreferenceCenter]"]:checked').val());
    if (!isPrefCenterEnabled) {
        var slots = [
            'segmentlist',
            'categorylist',
            'preferredchannel',
            'channelfrequency',
            'saveprefsbutton'
        ];
        mQuery.each(slots, function (i, s) {
            // delete existing tokens
            themeHtml = themeHtml.replace('{' + s + '}', '');
        });
        var parser = new DOMParser();
        var el = parser.parseFromString(themeHtml, "text/html");
        var $b = mQuery(el);
        mQuery.each(slots, function (i, s) {
            // delete existing slots
            $b.find('[data-slot=' + s + ']').remove();
        });
        themeHtml = Le.domToString($b);
    }

    Le.buildBuilderIframe(themeHtml, 'builder-template-content', function() {
        mQuery('#builder-overlay').addClass('hide');
        btnCloseBuilder.prop('disabled', false);
        applyBtn.prop('disabled', false);
    });
};

Le.initBuilderIframe = function(themeHtml, btnCloseBuilder, applyBtn) {
    // Avoid to request the tokens if not necessary
    if (Le.builderTokensRequestInProgress) {
        // Wait till previous request finish
        var intervalID = setInterval(function(){
            if (!Le.builderTokensRequestInProgress) {
                clearInterval(intervalID);
                Le.prepareBuilderIframe(themeHtml, btnCloseBuilder, applyBtn);
            }
        }, 500);
    } else {
        Le.prepareBuilderIframe(themeHtml, btnCloseBuilder, applyBtn);
    }
};

Le.prepareDynamicContentBlocksForBuilder = function(builderHtml) {
    for (var token in Le.builderTokens) {
        // If this is a dynamic content token
        if (Le.builderTokens.hasOwnProperty(token) && /\{dynamic/.test(token)) {
            var defaultContent = Le.convertDynamicContentTokenToSlot(token);

            builderHtml = builderHtml.replace(token, defaultContent);
        }
    }

    return builderHtml;
};

Le.convertDynamicContentTokenToSlot = function(token) {
    var dynConData = Le.getDynamicContentDataForToken(token);

    if (dynConData) {
        return '<div data-slot="dynamicContent" contenteditable="false" data-param-dec-id="'+dynConData.id+'">'+dynConData.content+'</div>';
    }

    return token;
};

Le.getDynamicContentDataForToken = function(token) {
    var dynConName      = /\{dynamiccontent="(.*)"\}/.exec(token)[1];
    var dynConTabs      = parent.mQuery('#dynamicContentTabs');
    var dynConTarget    = dynConTabs.find('a:contains("'+dynConName+'")').attr('href');
    var dynConContainer = parent.mQuery(dynConTarget);

    if (dynConContainer.html()) {
        var dynConContent = dynConContainer.find(dynConTarget+'_content');

        if (dynConContent.hasClass('editor')) {
            dynConContent = dynConContent.froalaEditor('html.get');
        } else {
            dynConContent = dynConContent.html();
        }

        return {
            id: parseInt(dynConTarget.replace(/[^0-9]/g, '')),
            content: dynConContent
        };
    }

    return null;
};

Le.convertDynamicContentSlotsToTokens = function (builderHtml) {
    var dynConSlots = mQuery(builderHtml).find('[data-slot="dynamicContent"]');

    if (dynConSlots.length) {
        dynConSlots.each(function(i) {
            var $this     = mQuery(this);
            var dynConNum = $this.attr('data-param-dec-id');
            var dynConId  = '#emailform_dynamicContent_' + dynConNum;

            var dynConTarget = mQuery(dynConId);
            var dynConName   = dynConTarget.find(dynConId + '_tokenName').val();
            var dynConToken  = '{dynamiccontent="' + dynConName+'"}';

            // Add the dynamic content tokens
            if (!Le.builderTokens.hasOwnProperty(dynConToken)) {
                Le.builderTokens[dynConToken] = dynConName;
            }

            // hack to convert builder HTML to jQuery and replace slot with token
            var parser = new DOMParser();
            var el = parser.parseFromString(builderHtml, "text/html");
            var $b = mQuery(el);
            $b.find('div[data-param-dec-id=' + dynConNum + ']').replaceWith(dynConToken);
            builderHtml = Le.domToString($b);

            // If it's still wrapped in an atwho, remove that
            if ($this.parent().hasClass('atwho-inserted')) {
                var toReplace = $this.parent('.atwho-inserted').get(0).outerHTML;

                builderHtml   = builderHtml.replace(toReplace, dynConToken);
            }
        });
    }

    return builderHtml;
};

Le.getPredefinedLinks = function(callback) {
    var linkList = [];
    Le.getTokens(Le.getBuilderTokensMethod(), function(tokens) {
        if (tokens.length) {
            mQuery.each(tokens, function(token, label) {
                if (token.startsWith('{pagelink=') ||
                    token.startsWith('{assetlink=') ||
                    token.startsWith('{webview_url') ||
                    token.startsWith('{unsubscribe_url')) {

                    linkList.push({
                        text: label,
                        href: token
                    });
                }
            });
        }
        return callback(linkList);
    });
};

// Init inside the builder's iframe
mQuery(function() {
    if (parent && parent.mQuery && parent.mQuery('#builder-template-content').length) {
        Le.builderContents = mQuery('body');
        if (!parent.Le.codeMode) {
            Le.initSlotListeners();
            Le.initSections();
            Le.initSlots();
        }
    }
});