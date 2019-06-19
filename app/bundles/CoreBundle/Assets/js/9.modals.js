Le.modalContentXhr = {};
Le.activeModal = '';
Le.backgroundedModal = '';

/**
 * Load a modal with ajax content
 *
 * @param el
 * @param event
 *
 * @returns {boolean}
 */
Le.ajaxifyModal = function (el, event) {
    if (mQuery(el).hasClass('disabled')) {
        return false;
    }

    var target = mQuery(el).attr('data-target');
    var route = (mQuery(el).attr('data-href')) ? mQuery(el).attr('data-href') : mQuery(el).attr('href');
    if (route.indexOf('javascript') >= 0) {
        return false;
    }

    mQuery('body').addClass('noscroll');

    var method = mQuery(el).attr('data-method');
    if (!method) {
        method = 'GET'
    }

    var header = mQuery(el).attr('data-header');
    var footer = mQuery(el).attr('data-footer');

    var preventDismissal = mQuery(el).attr('data-prevent-dismiss');
    if (preventDismissal) {
        // Reset
        mQuery(el).removeAttr('data-prevent-dismiss');
    }
    Le.loadAjaxModal(target, route, method, header, footer, preventDismissal);
};

/**
 * Retrieve ajax content for modal
 * @param target
 * @param route
 * @param method
 * @param header
 * @param footer
 */
Le.loadAjaxModal = function (target, route, method, header, footer, preventDismissal) {
    if (mQuery(target + ' .loading-placeholder').length) {
        mQuery(target + ' .loading-placeholder').removeClass('hide');
        mQuery(target + ' .modal-body-content').addClass('hide');

        if (mQuery(target + ' .modal-loading-bar').length) {
            mQuery(target + ' .modal-loading-bar').addClass('active');
        }
    }

    if (footer == 'false') {
        mQuery(target + " .modal-footer").addClass('hide');
    }

    //move the modal to the body tag to get around positioned div issues
    mQuery(target).on('show.bs.modal', function () {
        if (header) {
            mQuery(target + " .modal-title").html(header);
        }

        if (footer && footer != 'false') {
            mQuery(target + " .modal-footer").html(header);
        }
    });

    //clean slate upon close
    mQuery(target).on('hidden.bs.modal', function () {
        if (typeof Le.modalContentXhr[target] != 'undefined') {
            Le.modalContentXhr[target].abort();
            delete Le.modalContentXhr[target];
        }

        mQuery('body').removeClass('noscroll');

        var response = {};
        if (Le.modalMauticContent) {
            response.leContent = Le.modalMauticContent;
            delete Le.modalMauticContent;
        }

        //unload
        Le.onPageUnload(target, response);

        Le.resetModal(target);
    });

    // Check if dismissal is allowed
    if (typeof mQuery(target).data('bs.modal') !== 'undefined' && typeof mQuery(target).data('bs.modal').options !== 'undefined') {
        if (preventDismissal) {
            mQuery(target).data('bs.modal').options.keyboard = false;
            mQuery(target).data('bs.modal').options.backdrop = 'static';
        } else {
            mQuery(target).data('bs.modal').options.keyboard = true;
            mQuery(target).data('bs.modal').options.backdrop = true;
        }
    } else {
        if (preventDismissal) {
            mQuery(target).modal({
                backdrop: 'static',
                keyboard: false
            });
        } else {
            mQuery(target).modal({
                backdrop: true,
                keyboard: true
            });
        }
    }

    Le.showModal(target);

    if (typeof Le.modalContentXhr == 'undefined') {
        Le.modalContentXhr = {};
    } else if (typeof Le.modalContentXhr[target] != 'undefined') {
        Le.modalContentXhr[target].abort();
    }
    Le.modalContentXhr[target] = mQuery.ajax({
        url: route,
        type: method,
        dataType: "json",
        success: function (response) {
            if (response) {
                Le.processModalContent(response, target);
            }
            Le.stopIconSpinPostEvent();
        },
        error: function (request, textStatus, errorThrown) {
            Le.processAjaxError(request, textStatus, errorThrown);
            Le.stopIconSpinPostEvent();
        },
        complete: function () {
            Le.stopModalLoadingBar(target);
            delete Le.modalContentXhr[target];
        }
    });
};

Le.updateAjaxModal = function (target, route, method) {
    if (mQuery(target + ' .loading-placeholder').length) {
        mQuery(target + ' .loading-placeholder').removeClass('hide');
        mQuery(target + ' .modal-body-content').addClass('hide');

        if (mQuery(target + ' .modal-loading-bar').length) {
            mQuery(target + ' .modal-loading-bar').addClass('active');
        }
    }
    Le.modalContentXhr[target] = mQuery.ajax({
        url: route,
        type: method,
        dataType: "json",
        success: function (response) {
            if (response.newContent) {
                if(!mQuery(target).hasClass('in')) {
                    mQuery(target).attr('style', 'display:block;margin-left: 18%;')
                    mQuery(target).addClass('in');
                    mQuery('modal-backdrop').addClass('in');
                }
                //load the content
                if (mQuery(target + ' .loading-placeholder').length) {
                    mQuery(target + ' .loading-placeholder').addClass('hide');
                    mQuery(target + ' .modal-body-content').html(response.newContent);
                    mQuery(target + ' .modal-body-content').removeClass('hide');
                } else {
                    mQuery(target + ' .modal-body').html(response.newContent);
                }
                //activate content specific stuff
                Le.onPageLoad(target, response, true);
            }
            Le.stopIconSpinPostEvent();
        },
        error: function (request, textStatus, errorThrown) {
            Le.processAjaxError(request, textStatus, errorThrown);
            Le.stopIconSpinPostEvent();
        },
        complete: function () {
            Le.stopModalLoadingBar(target);
            delete Le.modalContentXhr[target];
        }
    });
};
/**
 * Clears content from a shared modal
 * @param target
 */
Le.resetModal = function (target) {
    if (mQuery(target).hasClass('in')) {
        return;
    }
    mQuery(target + " .modal-title").html('');
    mQuery(target + " .modal-body-content").html('');

    if (mQuery(target + " loading-placeholder").length) {
        mQuery(target + " loading-placeholder").removeClass('hide');
    }
    if (mQuery(target + " .modal-footer").length) {
        var hasFooterButtons = mQuery(target + " .modal-footer .modal-form-buttons").length;
        mQuery(target + " .modal-footer").html('');
        if (hasFooterButtons) {
            //add footer buttons
            mQuery('<div class="modal-form-buttons" />').appendTo(target + " .modal-footer");
        }
        mQuery(target + " .modal-footer").removeClass('hide');
    }
};

/**
 * Handles modal content post ajax request
 * @param response
 * @param target
 */
Le.processModalContent = function (response, target) {
    Le.stopIconSpinPostEvent();
    if (response.error) {
        if (response.errors) {
            alert(response.errors[0].message);
        } else if (response.error.message) {
            alert(response.error.message);
        } else {
            alert(response.error);
        }

        return;
    }

    if (response.sessionExpired || (response.closeModal && response.newContent && !response.updateModalContent)) {
        mQuery(target).modal('hide');
        mQuery('body').removeClass('modal-open');
        mQuery('.modal-backdrop').remove();
        //assume the content is to refresh main app
        Le.processPageContent(response);
    } else {
        if (response.notifications) {
            Le.setNotifications(response.notifications);
        }

        if (response.browserNotifications) {
            Le.setBrowserNotifications(response.browserNotifications);
        }

        if (response.callback) {
            window["Le"][response.callback].apply('window', [response]);
            return;
        }

        if (response.target) {
            mQuery(response.target).html(response.newContent);

            //activate content specific stuff
            Le.onPageLoad(response.target, response, true);
        } else if (response.newContent) {
            //load the content
            if (mQuery(target + ' .loading-placeholder').length) {
                mQuery(target + ' .loading-placeholder').addClass('hide');
                 mQuery(target + ' .modal-body-content').html(response.newContent);
                mQuery(target + ' .modal-body-content').removeClass('hide');
            } else {
               mQuery(target + ' .modal-body').html(response.newContent);
            }
        }

        //activate content specific stuff
        Le.onPageLoad(target, response, true);
        Le.modalMauticContent = false;
        if (response.closeModal) {
            mQuery('body').removeClass('noscroll');
            mQuery(target).modal('hide');

            if (!response.updateModalContent) {
                Le.onPageUnload(target, response);
            }
        } else {
            // Note for the hidden event
            Le.modalMauticContent = response.leContent ? response.leContent : false;
        }
    }
};

/**
 * Display confirmation modal
 */
Le.showConfirmation = function (el) {
    var precheck = mQuery(el).data('precheck');

    if (precheck) {
        if (typeof precheck == 'function') {
            if (!precheck()) {
                return;
            }
        } else if (typeof Le[precheck] == 'function') {
            if (!Le[precheck]()) {
                return;
            }
        }
    }

    var message = mQuery(el).data('message');
    var confirmText = mQuery(el).data('confirm-text');
    var confirmAction = mQuery(el).attr('href');
    var confirmCallback = mQuery(el).data('confirm-callback');
    var cancelText = mQuery(el).data('cancel-text');
    var cancelCallback = mQuery(el).data('cancel-callback');
    var buttonText = 'Yes, delete it';
    if(typeof confirmText !== "undefined" && confirmText == "Activate"){
        buttonText = 'Yes, '+confirmText+' it';
    }
    var canceltxt = 'No, cancel';
    var type = 'warning';
    if (confirmAction.indexOf('/batchExport') != -1) {
        buttonText = 'Yes';
        canceltxt  = 'No';
        type = '';
    }
    swal({
        title: 'Are you sure?',
        text: message,
        type: type,
        showCancelButton: true,
        confirmButtonText: buttonText,
        cancelButtonText:  canceltxt,
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger m-l-10',
        buttonsStyling: false
    }).then(function (response) {
       // alert(JSON.stringify(response));
        if(response.value){
            if (typeof Le[confirmCallback] === "function") {
                if(confirmCallback == 'executeAction'){
                    window["Le"][confirmCallback].apply('window', [confirmAction,Le.responseCallBack]);
                }else{
                    window["Le"][confirmCallback].apply('window', [confirmAction, el, Le.responseCallBack]);
                }

            }
        }else  if (response.dismiss === 'cancel') {
            if (cancelCallback && typeof Le[cancelCallback] === "function") {
                window["Le"][cancelCallback].apply('window', []);
            }
            // swal(
            //     'Cancelled',
            //     'Your imaginary file is safe :)',
            //     'error'
            // )
        }
    }, function (dismiss) {
        // dismiss can be 'cancel', 'overlay',
        // // 'close', and 'timer'
        // if (dismiss === 'cancel') {
        //     swal(
        //         'Cancelled',
        //         'Your imaginary file is safe :)',
        //         'error'
        //     )
        // }
    })
   /* var confirmContainer = mQuery("<div />").attr({"class": "modal fade confirmation-modal"});
    var confirmDialogDiv = mQuery("<div />").attr({"class": "modal-dialog"});
    var confirmContentDiv = mQuery("<div />").attr({"class": "modal-content"});
    var confirmFooterDiv = mQuery("<div />").attr({"class": "modal-body text-center"});
    var confirmHeaderDiv = mQuery("<div />").attr({"class": "modal-header"});
    confirmHeaderDiv.append(mQuery('<h4 />').attr({"class": "modal-title"}).text(message));
    var confirmButton = mQuery('<button type="button" />')
        .addClass("btn btn-danger")
        .css("marginRight", "5px")
        .css("marginLeft", "5px")
        .click(function () {
            if (typeof Le[confirmCallback] === "function") {
                window["Le"][confirmCallback].apply('window', [confirmAction, el]);
            }
        })
        .html(confirmText);
    if (cancelText) {
        var cancelButton = mQuery('<button type="button" />')
            .addClass("btn btn-primary")
            .click(function () {
                if (cancelCallback && typeof Le[cancelCallback] === "function") {
                    window["Le"][cancelCallback].apply('window', []);
                } else {
                    Le.dismissConfirmation();
                }
            })
            .html(cancelText);
    }

    if (typeof cancelButton != 'undefined') {
        confirmFooterDiv.append(cancelButton);
    }

    confirmFooterDiv.append(confirmButton);

    confirmContentDiv.append(confirmHeaderDiv);
    confirmContentDiv.append(confirmFooterDiv);

    confirmContainer.append(confirmDialogDiv.append(confirmContentDiv));
    mQuery('body').append(confirmContainer);

    mQuery('.confirmation-modal').on('hidden.bs.modal', function () {
        mQuery(this).remove();
    });

    mQuery('.confirmation-modal').modal('show');*/
};

/**
 * Success
 */
Le.successModel = function(title,msg,type){
    swal(
        title,
        msg,
        type
    );
};

/**
 * Dismiss confirmation modal
 */
Le.dismissConfirmation = function () {
    if (mQuery('.confirmation-modal').length) {
        mQuery('.confirmation-modal').modal('hide');
    }
};
Le.responseCallBack = function (response) {
    try{
        if(response.flashes){
            var alerthtml=response.flashes;
            var alerthtmlel = mQuery(alerthtml);
            var alerttype=alerthtmlel.attr('data-alert-type');
            var alertmessage=mQuery(alerthtmlel).find('span').html();
            if(typeof alertmessage != 'undefined'){
                alertmessage=alertmessage.trim();
            }else{
                alertmessage='';
            }
            if(alertmessage != ''){
                if(alerttype != 'error'){
                    alerttype='success';
                    alerttitle='Deleted!';
                }else{
                    alerttitle='Sorry!';
                }
                swal(
                    alerttitle,
                    alertmessage,
                    alerttype
                );
            }
        }
    }catch(err){
       // alert(err);
    }

};
/**
 * Close the given modal and redirect to a URL
 *
 * @param el
 * @param url
 */
Le.closeModalAndRedirect = function(el, url) {
    Le.startModalLoadingBar(el);

    Le.loadContent(url);

    mQuery('body').removeClass('noscroll');
};

/**
 * Close the given modal and redirect to a URL
 *
 * @param url
 */
Le.RedirectToGivenURL = function(url) {

    window.location.href = url;

};

/**
 * Open modal route when a specific value is selected from a select list
 *
 * @param el
 * @param url
 * @param header
 */
Le.loadAjaxModalBySelectValue = function (el, value, route, header) {
    var selectVal = mQuery(el).val();
    var hasValue = (selectVal == value);
    if (!hasValue && mQuery.isArray(selectVal)) {
        hasValue = (mQuery.inArray(value, selectVal) !== -1);
    }
    if (hasValue) {
        // Remove it from the select
        route = route + (route.indexOf('?') > -1 ? '&' : '?') + 'modal=1&contentOnly=1&updateSelect=' + mQuery(el).attr('id');
        mQuery(el).find('option[value="' + value + '"]').prop('selected', false);
        mQuery(el).trigger("chosen:updated");
        Le.loadAjaxModal('#leSharedModal', route, 'get', header);
    }
};

/**
 * Push active modal to the background and bring it back once this one closes
 *
 * @param target
 */
Le.showModal = function(target) {
    if (mQuery('.modal.in').length) {
        // another modal is activated so let's stack

        // is this modal within another modal?
        if (mQuery(target).closest('.modal').length) {
            // the modal has to be moved outside of it's parent for this to work so take note where it needs to
            // moved back to
            mQuery('<div />').attr('data-modal-placeholder', target).insertAfter(mQuery(target));

            mQuery(target).attr('data-modal-moved', 1);

            mQuery(target).appendTo('body');
        }

        var activeModal = mQuery('.modal.in .modal-dialog:not(:has(.aside))').parents('.modal').last(),
            targetModal  = mQuery(target);

        if (activeModal.length && activeModal.attr('id') !== targetModal.attr('id')) {
            targetModal.attr('data-previous-modal', '#'+activeModal.attr('id'));
            activeModal.find('.modal-dialog').addClass('aside');
            var stackedDialogCount = mQuery('.modal.in .modal-dialog.aside').length;
            if (stackedDialogCount <= 5) {
                activeModal.find('.modal-dialog').addClass('aside-' + stackedDialogCount);
            }

            mQuery(target).on('hide.bs.modal', function () {
                var modal = mQuery(this);
                var previous = modal.attr('data-previous-modal');

                if (previous) {
                    mQuery(previous).find('.modal-dialog').removeClass('aside');
                    mQuery(modal).attr('data-previous-modal', undefined);
                }

                if (mQuery(modal).attr('data-modal-moved')) {
                    mQuery('[data-modal-placeholder').replaceWith(mQuery(modal));
                    mQuery(modal).attr('data-modal-moved', undefined);
                }
            });
        }
    }

    mQuery(target).modal('show');
};


