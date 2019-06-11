var leVars  = {};
var mQuery      = jQuery.noConflict(true);
window.jQuery   = mQuery;
window.$   = mQuery;
// Polyfil for ES6 startsWith method
if (!String.prototype.startsWith) {
    String.prototype.startsWith = function(searchString, position){
        position = position || 0;
        return this.substr(position, searchString.length) === searchString;
    };
}

//set default ajax options
leVars.activeRequests = 0;
/*mQuery(function() {
    mQuery('#flashes').delay(10000).fadeIn('normal', function() {
        mQuery(this).delay(50000).fadeOut();
    });
});*/
mQuery.ajaxSetup({
    beforeSend: function (request, settings) {
        if (settings.showLoadingBar) {
            mQuery('.loading-bar').addClass('active');
            leVars.activeRequests++;
        }

        if (typeof IdleTimer != 'undefined') {
            //append last active time
            var userLastActive = IdleTimer.getLastActive();
            var queryGlue = (settings.url.indexOf("?") == -1) ? '?' : '&';

            settings.url = settings.url + queryGlue + 'leUserLastActive=' + userLastActive;
        }

        if (mQuery('#leLastNotificationId').length) {
            //append last notifications
            var queryGlue = (settings.url.indexOf("?") == -1) ? '?' : '&';

            settings.url = settings.url + queryGlue + 'leLastNotificationId=' + mQuery('#leLastNotificationId').val();
        }

        // Set CSRF token to each AJAX POST request
        if (settings.type == 'POST') {
            request.setRequestHeader('X-CSRF-Token', leAjaxCsrf);
        }

        return true;
    },

    cache: false
});

mQuery( document ).ajaxComplete(function(event, xhr, settings) {
    xhr.always(function(response) {
        if (response.flashes) {
            var alerthtml=response.flashes;
            var alerthtmlel = mQuery(alerthtml);
            var alertmessage=mQuery(alerthtmlel).find('span').html();
            var alertType = alerthtmlel.attr('data-alert-type');
            if(typeof alertmessage != 'undefined'){
                alertmessage=alertmessage.trim();
            }else{
                alertmessage='';
            }
            if(alertmessage != ''){
                if(!alertmessage.includes("delete") && !alertmessage.includes("do not have access")){
                    if(alertType == "sweetalert"){
                        Le.successModel('Good Job!',alertmessage,'success');
                    } else {
                        Le.setFlashes(response.flashes);
                    }
                }
            }
        }
    });
});

// Force stop the page loading bar when no more requests are being in progress
mQuery( document ).ajaxStop(function(event) {
    // Seems to be stuck
    leVars.activeRequests = 0;
    Le.stopPageLoadingBar();
});

mQuery( document ).ready(function() {
    if (typeof leContent !== 'undefined') {
        mQuery("html").Core({
            console: false
        });
    }

    if (typeof IdleTimer != 'undefined') {
        IdleTimer.init({
            idleTimeout: 60000, //1 min
            awayTimeout: 900000, //15 min
            statusChangeUrl: leAjaxUrl + '?action=updateUserStatus'
        });
    }

    // Prevent backspace from activating browser back
    mQuery(document).on('keydown', function (e) {
        if (e.which === 8 && !mQuery(e.target).is("input:not([readonly]):not([type=radio]):not([type=checkbox]), textarea, [contentEditable], [contentEditable=true]")) {
            e.preventDefault();
        }
    });
});

//Fix for back/forward buttons not loading ajax content with History.pushState()
leVars.manualStateChange = true;

if (typeof History != 'undefined') {
    History.Adapter.bind(window, 'statechange', function () {
        if (leVars.manualStateChange == true) {
            //back/forward button pressed
            window.location.reload();
        }
        leVars.manualStateChange = true;
    });
}

//used for spinning icons to show something is in progress)
leVars.iconClasses          = {};

//prevent multiple ajax calls from multiple clicks
leVars.routeInProgress       = '';

//prevent interval ajax requests from overlapping
leVars.moderatedIntervals    = {};
leVars.intervalsInProgress   = {};

var Le = {
    loadedContent: {},

    keyboardShortcutHtml: {},

    /**
     *
     * @param sequence
     * @param description
     * @param func
     * @param section
     */
    addKeyboardShortcut: function (sequence, description, func, section) {
        Mousetrap.bind(sequence, func);
        var sectionName = section || 'global';

        if (!Le.keyboardShortcutHtml.hasOwnProperty(sectionName)) {
            Le.keyboardShortcutHtml[sectionName] = {};
        }

        Le.keyboardShortcutHtml[sectionName][sequence] = '<div class="col-xs-6"><mark>' + sequence + '</mark>: ' + description + '</div>';
    },

    /**
     * Binds global keyboard shortcuts
     */
    bindGlobalKeyboardShortcuts: function () {
        Le.addKeyboardShortcut('shift+d', 'Load the Dashboard', function (e) {
            mQuery('#le_dashboard_index').click();
        });

        Le.addKeyboardShortcut('shift+c', 'Load Contacts', function (e) {
            mQuery('#le_contact_index').click();
        });

        Le.addKeyboardShortcut('shift+right', 'Activate Right Menu', function (e) {
            mQuery(".navbar-right a[data-toggle='sidebar']").click();
        });

        Le.addKeyboardShortcut('shift+n', 'Show Notifications', function (e) {
            mQuery('.dropdown-notification').click();
        });

        Le.addKeyboardShortcut('shift+s', 'Global Search', function (e) {
            mQuery('#globalSearchContainer .search-button').click();
        });

        Le.addKeyboardShortcut('mod+z', 'Undo change', function (e) {
            if (mQuery('.btn-undo').length) {
                mQuery('.btn-undo').click();
            }
        });

        Le.addKeyboardShortcut('mod+shift+z', 'Redo change', function (e) {
            if (mQuery('.btn-redo').length) {
                mQuery('.btn-redo').click();
            }
        });

        Mousetrap.bind('?', function (e) {
            var modalWindow = mQuery('#leSharedModal');

            modalWindow.find('.modal-title').html('Keyboard Shortcuts');
            modalWindow.find('.modal-body').html(function () {
                var modalHtml = '';
                var sections = Object.keys(Le.keyboardShortcutHtml);
                sections.forEach(function (section) {
                    var sectionTitle = (section + '').replace(/^([a-z\u00E0-\u00FC])|\s+([a-z\u00E0-\u00FC])/g, function ($1) {
                        return $1.toUpperCase();
                    });
                    modalHtml += '<h4>' + sectionTitle + '</h4><br />';
                    modalHtml += '<div class="row">';
                    var sequences = Object.keys(Le.keyboardShortcutHtml[section]);
                    sequences.forEach(function (sequence) {
                        modalHtml += Le.keyboardShortcutHtml[section][sequence];
                    });
                    modalHtml += '</div><hr />';
                });

                return modalHtml;
            });
            modalWindow.find('.modal-footer').html('<p>Press <mark>shift+?</mark> at any time to view this help modal.');
            modalWindow.modal();
        });
    },

    /**
     * Translations
     *
     * @param id     string
     * @param params object
     */
    translate: function (id, params) {
        if (!leLang.hasOwnProperty(id)) {
            return id;
        }

        var translated = leLang[id];

        if (params) {
            for (var key in params) {
                if (!params.hasOwnProperty(key)) continue;

                var regEx = new RegExp('%' + key + '%', 'g');
                translated = translated.replace(regEx, params[key])
            }
        }

        return translated;
    },

    /**
     * Setups browser notifications
     */
    setupBrowserNotifier: function () {
        //request notification support
        notify.requestPermission();
        notify.config({
            autoClose: 10000
        });

        Le.browserNotifier = {
            isSupported: notify.isSupported,
            permissionLevel: notify.permissionLevel()
        };

        Le.browserNotifier.isSupported = notify.isSupported;
        Le.browserNotifier.permissionLevel = notify.permissionLevel();
        Le.browserNotifier.createNotification = function (title, options) {
            return notify.createNotification(title, options);
        }
    },

    /**
     * Stops the ajax page loading indicator
     */
    stopPageLoadingBar: function () {
        if (leVars.activeRequests < 1) {
            leVars.activeRequests = 0;
        } else {
            leVars.activeRequests--;
        }

        if (leVars.loadingBarTimeout) {
            clearTimeout(leVars.loadingBarTimeout);
        }

        if (leVars.activeRequests == 0) {
            mQuery('.loading-bar').removeClass('active');
        }
    },

    /**
     * Activate page loading bar
     */
    startPageLoadingBar: function () {
        mQuery('.loading-bar').addClass('active');
        leVars.activeRequests++;
    },

    /**
     * Starts the ajax loading indicator for the right canvas
     */
    startCanvasLoadingBar: function () {
        mQuery('.canvas-loading-bar').addClass('active');
    },

    /**
     * Starts the ajax loading indicator for modals
     *
     * @param modalTarget
     */
    startModalLoadingBar: function (modalTarget) {
        mQuery(modalTarget + ' .modal-loading-bar').addClass('active');
    },

    /**
     * Stops the ajax loading indicator for the right canvas
     */
    stopCanvasLoadingBar: function () {
        mQuery('.canvas-loading-bar').removeClass('active');
    },

    /**
     * Stops the ajax loading indicator for modals
     */
    stopModalLoadingBar: function (modalTarget) {
        mQuery(modalTarget + ' .modal-loading-bar').removeClass('active');
    },

    /**
     * Activate label loading spinner
     *
     * @param button (jQuery element)
     */
    activateButtonLoadingIndicator: function (button) {
        button.prop('disabled', true);
        if (!button.find('.fa-spinner.fa-spin').length) {
            button.append(mQuery('<i class="fa fa-fw fa-spinner fa-spin"></i>'));
        }
    },

    /**
     * Remove the spinner from label
     *
     * @param button (jQuery element)
     */
    removeButtonLoadingIndicator: function (button) {
        button.prop('disabled', false);
        button.find('.fa-spinner').remove();
    },

    /**
     * Activate label loading spinner
     *
     * @param el
     */
    activateLabelLoadingIndicator: function (el) {
        var labelSpinner = mQuery("label[for='" + el + "']");
        Le.labelSpinner = mQuery('<i class="fa fa-fw fa-spinner fa-spin"></i>');
        labelSpinner.append(Le.labelSpinner);
    },

    /**
     * Remove the spinner from label
     */
    removeLabelLoadingIndicator: function () {
        mQuery(Le.labelSpinner).remove();
    },

    /**
     * Open a popup
     * @param options
     */
    loadNewWindow: function (options) {
        if (options.windowUrl) {
            Le.startModalLoadingBar();

            setTimeout(function () {
                var opener = window.open(options.windowUrl, 'mauticpopup', 'height=600,width=1100');

                if (!opener || opener.closed || typeof opener.closed == 'undefined') {
                    alert(leLang.popupBlockerMessage);
                } else {
                    opener.onload = function () {
                        Le.stopModalLoadingBar();
                        Le.stopIconSpinPostEvent();
                    };
                }
            }, 100);
        }
    },

    /**
     * Inserts a new javascript file request into the document head
     *
     * @param url
     * @param onLoadCallback
     * @param alreadyLoadedCallback
     */
    loadScript: function (url, onLoadCallback, alreadyLoadedCallback) {
        // check if the asset has been loaded
        if (typeof Le.headLoadedAssets == 'undefined') {
            Le.headLoadedAssets = {};
        } else if (typeof Le.headLoadedAssets[url] != 'undefined') {
            // URL has already been appended to head

            if (alreadyLoadedCallback && typeof Le[alreadyLoadedCallback] == 'function') {
                Le[alreadyLoadedCallback]();
            }

            return;
        }

        // Note that asset has been appended
        Le.headLoadedAssets[url] = 1;

        mQuery.getScript(url, function (data, textStatus, jqxhr) {
            if (textStatus == 'success') {
                if (onLoadCallback && typeof Le[onLoadCallback] == 'function') {
                    Le[onLoadCallback]();
                } else if (typeof Le[leContent + "OnLoad"] == 'function') {
                    // Likely a page refresh; execute onLoad content
                    if (typeof Le.loadedContent[leContent] == 'undefined') {
                        Le.loadedContent[leContent] = true;
                        Le[leContent + "OnLoad"]('#app-content', {});
                    }
                }
            }
        });
    },

    /**
     * Inserts a new stylesheet into the document head
     *
     * @param url
     */
    loadStylesheet: function (url) {
        // check if the asset has been loaded
        if (typeof Le.headLoadedAssets == 'undefined') {
            Le.headLoadedAssets = {};
        } else if (typeof Le.headLoadedAssets[url] != 'undefined') {
            // URL has already been appended to head
            return;
        }

        // Note that asset has been appended
        Le.headLoadedAssets[url] = 1;

        var link = document.createElement("link");
        link.type = "text/css";
        link.rel = "stylesheet";
        link.href = url;
        mQuery('head').append(link);
    },

    /**
     * Just a little visual that an action is taking place
     *
     * @param event|string
     */
    startIconSpinOnEvent: function (target) {
        if (leVars.ignoreIconSpin) {
            leVars.ignoreIconSpin = false;
            return;
        }

        if (typeof target == 'object' && typeof(target.target) !== 'undefined') {
            target = target.target;
        }

        if (mQuery(target).length) {
            var hasBtn = mQuery(target).hasClass('btn');
            var hasIcon = mQuery(target).hasClass('fa');
            var dontspin = mQuery(target).hasClass('btn-nospin');

            var i = (hasBtn && mQuery(target).find('i.fa').length) ? mQuery(target).find('i.fa') : target;

            if (!dontspin && ((hasBtn && mQuery(target).find('i.fa').length) || hasIcon)) {
                var el = (hasIcon) ? target : mQuery(target).find('i.fa').first();
                var identifierClass = (new Date).getTime();
                leVars.iconClasses[identifierClass] = mQuery(el).attr('class');

                var specialClasses = ['fa-fw', 'fa-lg', 'fa-2x', 'fa-3x', 'fa-4x', 'fa-5x', 'fa-li', 'text-white', 'text-muted'];
                var appendClasses = "";

                //check for special classes to add to spinner
                for (var i = 0; i < specialClasses.length; i++) {
                    if (mQuery(el).hasClass(specialClasses[i])) {
                        appendClasses += " " + specialClasses[i];
                    }
                }
                mQuery(el).removeClass();
                mQuery(el).addClass('fa fa-spinner fa-spin ' + identifierClass + appendClasses);
            }
        }
    },

    /**
     * Stops the icon spinning after an event is complete
     */
    stopIconSpinPostEvent: function (specificId) {
        if (typeof specificId != 'undefined' && specificId in leVars.iconClasses) {
            mQuery('.' + specificId).removeClass('fa fa-spinner fa-spin ' + specificId).addClass(leVars.iconClasses[specificId]);
            delete leVars.iconClasses[specificId];
        } else {
            mQuery.each(leVars.iconClasses, function (index, value) {
                mQuery('.' + index).removeClass('fa fa-spinner fa-spin ' + index).addClass(value);
            });

            leVars.iconClasses = {};
        }
    },

    /**
     * Displays backdrop with wait message then redirects
     *
     * @param url
     */
    redirectWithBackdrop: function (url) {
        Le.activateBackdrop();
        setTimeout(function () {
            window.location = url;
        }, 50);
    },

    /**
     * Acivates a backdrop
     */
    activateBackdrop: function (hideWait) {
        if (!mQuery('#mautic-backdrop').length) {
            var container = mQuery('<div />', {
                id: 'mautic-backdrop'
            });

            mQuery('<div />', {
                'class': 'modal-backdrop fade in'
            }).appendTo(container);

            if (typeof hideWait == 'undefined') {
                mQuery('<div />', {
                    "class": 'mautic-pleasewait'
                }).html(leLang.pleaseWait)
                    .appendTo(container);
            }

            container.appendTo('body');
        }
    },

    /**
     * Deactivates backdrop
     */
    deactivateBackgroup: function () {
        if (mQuery('#mautic-backdrop').length) {
            mQuery('#mautic-backdrop').remove();
        }
        if (mQuery('.modal-backdrop').length) {
            mQuery('.modal-backdrop').remove();
        }
    },

    /**
     * Executes an object action
     *
     * @param action
     */
    executeAction: function (action, callback) {
        if (typeof Le.activeActions == 'undefined') {
            Le.activeActions = {};
        } else if (typeof Le.activeActions[action] != 'undefined') {
            // Action is currently being executed
            return;
        }

        Le.activeActions[action] = true;

        //dismiss modal if activated
        Le.dismissConfirmation();

        if (action.indexOf('batchExport') >= 0) {
            Le.initiateFileDownload(action);
            return;
        }

        mQuery.ajax({
            showLoadingBar: true,
            url: action,
            type: "POST",
            dataType: "json",
            success: function (response) {
                Le.processPageContent(response);

                if (typeof callback == 'function') {
                    callback(response);
                }
            },
            error: function (request, textStatus, errorThrown) {
                Le.processAjaxError(request, textStatus, errorThrown);
            },
            complete: function () {
                delete Le.activeActions[action]
            }
        });
    },

    /**
     * Processes ajax errors
     *
     *
     * @param request
     * @param textStatus
     * @param errorThrown
     */
    processAjaxError: function (request, textStatus, errorThrown, mainContent) {
        if (textStatus == 'abort') {
            Le.stopPageLoadingBar();
            Le.stopCanvasLoadingBar();
            Le.stopIconSpinPostEvent();
            return;
        }

        var inDevMode = typeof leEnv !== 'undefined' && leEnv == 'dev';

        if (inDevMode) {
            console.log(request);
        }

        if (typeof request.responseJSON !== 'undefined') {
            response = request.responseJSON;
        } else {
            //Symfony may have added some excess buffer if an exception was hit during a sub rendering and because
            //it uses ob_start, PHP dumps the buffer upon hitting the exception.  So let's filter that out.
            var errorStart = request.responseText.indexOf('{"newContent');
            var jsonString = request.responseText.slice(errorStart);

            if (jsonString) {
                try {
                    var response = mQuery.parseJSON(jsonString);
                    if (inDevMode) {
                        console.log(response);
                    }
                } catch (err) {
                    if (inDevMode) {
                        console.log(err);
                    }
                }
            } else {
                response = {};
            }
        }

        if (response) {
            if (response.newContent && mainContent) {
                //an error page was returned
                mQuery('#app-content .content-body').html(response.newContent);
                if (response.route && response.route.indexOf("ajax") == -1) {
                    //update URL in address bar
                    leVars.manualStateChange = false;
                    if(response.route.indexOf("validate") == -1) {
                        History.pushState(null, productBrandName, response.route);
                    }
                }
            } else if (response.newContent && mQuery('.modal.in').length) {
                //assume a modal was the recipient of the information
                mQuery('.modal.in .modal-body-content').html(response.newContent);
                mQuery('.modal.in .modal-body-content').removeClass('hide');
                if (mQuery('.modal.in  .loading-placeholder').length) {
                    mQuery('.modal.in  .loading-placeholder').addClass('hide');
                }
            } else if (inDevMode) {
                console.log(response);

                if (response.errors && response.errors[0] && response.errors[0].message) {
                    alert(response.errors[0].message);
                }
            }
        }

        Le.stopPageLoadingBar();
        Le.stopCanvasLoadingBar();
        Le.stopIconSpinPostEvent();
    },

    /**
     * Moderates intervals to prevent ajax overlaps
     *
     * @param key
     * @param callback
     * @param timeout
     */
    setModeratedInterval: function (key, callback, timeout, params) {
        if (typeof leVars.intervalsInProgress[key] != 'undefined') {
            //action is still pending so clear and reschedule
            clearTimeout(leVars.moderatedIntervals[key]);
        } else {
            leVars.intervalsInProgress[key] = true;

            //perform callback
            if (typeof params == 'undefined') {
                params = [];
            }

            if (typeof callback == 'function') {
                callback(params);
            } else {
                window["Le"][callback].apply('window', params);
            }
        }

        //schedule new timeout
        leVars.moderatedIntervals[key] = setTimeout(function () {
            Le.setModeratedInterval(key, callback, timeout, params)
        }, timeout);
    },

    /**
     * Call at the end of the moderated interval callback function to let setModeratedInterval know
     * the action is done and it's safe to execute again
     *
     * @param key
     */
    moderatedIntervalCallbackIsComplete: function (key) {
        delete leVars.intervalsInProgress[key];
    },

    /**
     * Clears a moderated interval
     *
     * @param key
     */
    clearModeratedInterval: function (key) {
        Le.moderatedIntervalCallbackIsComplete(key);
        clearTimeout(leVars.moderatedIntervals[key]);
        delete leVars.moderatedIntervals[key];
    },

    /**
     * Sets flashes
     * @param flashes
     */
    setFlashes: function (flashes) {
        mQuery('#flashes').append(flashes);

        mQuery('#flashes .alert-new').each(function () {
            var me = this;
            window.setTimeout(function () {
                mQuery(me).fadeTo(500, 0).slideUp(500, function () {
                    mQuery(this).remove();
                });
            }, 6000);

            mQuery(this).removeClass('alert-new');
        });
    },

    /**
     * Set browser notifications
     *
     * @param notifications
     */
    setBrowserNotifications: function (notifications) {
        mQuery.each(notifications, function (key, notification) {
            Le.browserNotifier.createNotification(
                notification.title,
                {
                    body: notification.message,
                    icon: notification.icon
                }
            );
        });
    },

    /**
     *
     * @param notifications
     */
    setNotifications: function (notifications) {
        if (notifications.lastId) {
            mQuery('#leLastNotificationId').val(notifications.lastId);
        }

        if (mQuery('#notifications .le-update')) {
            mQuery('#notifications .le-update').remove();
        }

        if (notifications.hasNewNotifications) {
            if (mQuery('#newNotificationIndicator').hasClass('hide')) {
                mQuery('#newNotificationIndicator').removeClass('hide');
            }
        }

        if (notifications.content) {
            mQuery('#notifications').prepend(notifications.content);

            if (!mQuery('#notificationLEbot').hasClass('hide')) {
                mQuery('#notificationLEbot').addClass('hide');
            }
        }

        if (notifications.sound) {
            mQuery('.playSound').remove();

            mQuery.playSound(notifications.sound);
        }
    },

    /**
     * Marks notifications as read and clears unread indicators
     */
    showNotifications: function () {
        mQuery("#notificationsDropdown").unbind('hide.bs.dropdown');
        mQuery('#notificationsDropdown').on('hidden.bs.dropdown', function () {
            if (!mQuery('#newNotificationIndicator').hasClass('hide')) {
                mQuery('#notifications .is-unread').remove();
                mQuery('#newNotificationIndicator').addClass('hide');
            }
        });
    },

    /**
     * Clear notification(s)
     * @param id
     */
    clearNotification: function (id) {
        if (id) {
            mQuery("#notification" + id).fadeTo("fast", 0.01).slideUp("fast", function () {
                mQuery(this).find("*[data-toggle='tooltip']").tooltip('destroy');
                mQuery(this).remove();

                if (!mQuery('#notifications .notification').length) {
                    if (mQuery('#notificationLEbot').hasClass('hide')) {
                        mQuery('#notificationLEbot').removeClass('hide');
                    }
                }
            });
        } else {
            mQuery("#notifications .notification").fadeOut(300, function () {
                mQuery(this).remove();

                if (mQuery('#notificationLEbot').hasClass('hide')) {
                    mQuery('#notificationLEbot').removeClass('hide');
                }
            });
        }

        mQuery.ajax({
            url: leAjaxUrl,
            type: "GET",
            data: "action=clearNotification&id=" + id
        });
    },

    /**
     * Execute an action to AjaxController
     *
     * @param action
     * @param data
     * @param successClosure
     * @param showLoadingBar
     * @param failureClosure
     */
    ajaxActionRequest: function (action, data, successClosure, showLoadingBar, queue) {
        if (typeof Le.ajaxActionXhrQueue == 'undefined') {
            Le.ajaxActionXhrQueue = {};
        }
        if (typeof Le.ajaxActionXhr == 'undefined') {
            Le.ajaxActionXhr = {};
        } else if (typeof Le.ajaxActionXhr[action] != 'undefined') {
            if (queue) {
                if (typeof Le.ajaxActionXhrQueue[action] == 'undefined') {
                    Le.ajaxActionXhrQueue[action] = [];
                }

                Le.ajaxActionXhrQueue[action].push({action: action, data: data, successClosure: successClosure, showLoadingBar: showLoadingBar});

                return;
            } else {
                Le.removeLabelLoadingIndicator();
                Le.ajaxActionXhr[action].abort();
            }
        }

        if (typeof showLoadingBar == 'undefined') {
            showLoadingBar = false;
        }

        Le.ajaxActionXhr[action] = mQuery.ajax({
            url: leAjaxUrl + '?action=' + action,
            type: 'POST',
            data: data,
            showLoadingBar: showLoadingBar,
            success: function (response) {
                if (typeof successClosure == 'function') {
                    successClosure(response);
                }
            },
            error: function (request, textStatus, errorThrown) {
                Le.processAjaxError(request, textStatus, errorThrown, true);
            },
            complete: function () {
                delete Le.ajaxActionXhr[action];

                if (typeof Le.ajaxActionXhrQueue[action] !== 'undefined' && Le.ajaxActionXhrQueue[action].length) {
                    var next = Le.ajaxActionXhrQueue[action].shift();

                    Le.ajaxActionRequest(next.action, next.data, next.successClosure, next.showLoadingBar, false);
                }
            }
        });
    },

    /**
     * Check if the browser supports local storage
     *
     * @returns {boolean}
     */
    isLocalStorageSupported: function() {
        try {
            // Check if localStorage is supported
            localStorage.setItem('Le.test', 'mautic');
            localStorage.removeItem('Le.test');

            return true;
        } catch (e) {
            return false;
        }
    },

    /**
     * Removes Action Buttons
     */
    removeActionButtons: function() {
        mQuery('html').click(function() {
            mQuery('.md-fab-animated').removeClass('md-fab-animated md-fab-active');
            mQuery('.md-fab-primary').css("width", '28px');
            mQuery('.md-fab-toolbar-actions').css("display","none");
        });
        mQuery('.md-fab-primary').click(function(event){
            event.stopPropagation();
        });
    },

    /**
     * License Notification Closes Button
     */
    closeLicenseButton: function() {
        var x = document.getElementById("licenseclosediv");
        if (x.style.display === "none") {
            x.style.display = "block";
        } else {
            x.style.display = "none";
            mQuery('#licenseclosediv').addClass('hide');
        }
    },
    /**
     * SMS Notification Closes Button
     */
    closeSMSNotification: function() {
        var x = document.getElementById("licenseclosediv");

        if (x.style.display === "none") {
            x.style.display = "block";
        } else {
            mQuery('.sms-notifiation').addClass('hide');
        }
    }

};
