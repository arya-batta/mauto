//LeadBundle
Le.leadOnLoad = function (container, response) {
    alert("Testing");
    Le.removeActionButtons();
    Le.addKeyboardShortcut('a', 'Quick add a New Lead', function(e) {
        if(mQuery('a.quickadd').length) {
            mQuery('a.quickadd').click();
        } else if (mQuery('a.btn-leadnote-add').length) {
            mQuery('a.btn-leadnote-add').click();
        }
    }, 'contact pages');

    Le.addKeyboardShortcut('t', 'Activate Table View', function(e) {
        mQuery('#table-view').click();
    }, 'contact pages');

    Le.addKeyboardShortcut('c', 'Activate Card View', function(e) {
        mQuery('#card-view').click();
    }, 'contact pages');

    //Prevent single combo keys from initiating within lead note
    Mousetrap.stopCallback = function(e, element, combo) {
        if (element.id == 'leadnote_text' && combo != 'mod+enter') {
            return true;
        }

        // if the element has the class "mousetrap" then no need to stop
        if ((' ' + element.className + ' ').indexOf(' mousetrap ') > -1) {
            return false;
        }

        // stop for input, select, and textarea
        return element.tagName == 'INPUT' || element.tagName == 'SELECT' || element.tagName == 'TEXTAREA' || (element.contentEditable && element.contentEditable == 'true');
    };

    // Timeline filters
    var timelineForm = mQuery(container + ' #timeline-filters');
    if (timelineForm.length) {
        timelineForm.on('change', function() {
            timelineForm.submit();
        }).on('keyup', function() {
            timelineForm.delay(200).submit();
        }).on('submit', function(e) {
            e.preventDefault();
            Le.refreshLeadTimeline(timelineForm);
        });

        var toggleTimelineDetails = function (el) {
            var activateDetailsState = mQuery(el).hasClass('active');

            if (activateDetailsState) {
                mQuery('#timeline-details-'+detailsId).addClass('hide');
                mQuery(el).removeClass('active');
            } else {
                mQuery('#timeline-details-'+detailsId).removeClass('hide');
                mQuery(el).addClass('active');
            }
        };

        Le.leadTimelineOnLoad(container, response);
        Le.leadAuditlogOnLoad(container, response);
    }

    // Auditlog filters
    var auditlogForm = mQuery(container + ' #auditlog-filters');
    if (auditlogForm.length) {
        auditlogForm.on('change', function() {
            auditlogForm.submit();
        }).on('keyup', function() {
            auditlogForm.delay(200).submit();
        }).on('submit', function(e) {
            e.preventDefault();
            Le.refreshLeadAuditLog(auditlogForm);
        });
    }

    //Note type filters
    var noteForm = mQuery(container + ' #note-filters');
    if (noteForm.length) {
        noteForm.on('change', function() {
            noteForm.submit();
        }).on('keyup', function() {
            noteForm.delay(200).submit();
        }).on('submit', function(e) {
            e.preventDefault();
            Le.refreshLeadNotes(noteForm);
        });
    }
   if (mQuery(container + ' #list-search').length) {
        Le.activateSearchAutocomplete('list-search', 'lead.lead');
    }

    if (mQuery(container + ' #notes-container').length) {
        Le.activateSearchAutocomplete('NoteFilter', 'lead.note');
    }

    if (mQuery('#lead_preferred_profile_image').length) {
        mQuery('#lead_preferred_profile_image').on('change', function() {
            if (mQuery(this).val() == 'custom') {
                mQuery('#customAvatarContainer').slideDown('fast');
            } else {
                mQuery('#customAvatarContainer').slideUp('fast');
            }
        })
    }

    if (mQuery('.lead-avatar-panel').length) {
        mQuery('.lead-avatar-panel .avatar-collapser a.arrow').on('click', function() {
            setTimeout(function() {
                var status = (mQuery('#lead-avatar-block').hasClass('in') ? 'expanded' : 'collapsed');
                Cookies.set('mautic_lead_avatar_panel', status, {expires: 30});
            }, 500);
        });
    }

    if (mQuery('#anonymousLeadButton').length) {
        var searchValue = mQuery('#list-search').typeahead('val').toLowerCase();
        var string      = mQuery('#anonymousLeadButton').data('anonymous').toLowerCase();

        if (searchValue.indexOf(string) >= 0 && searchValue.indexOf('!' + string) == -1) {
            mQuery('#anonymousLeadButton').addClass('btn-primary');
        } else {
            mQuery('#anonymousLeadButton').removeClass('btn-primary');
        }
    }

    var leadMap = [];

    mQuery(document).on('shown.bs.tab', 'a#load-lead-map', function (e) {
        leadMap = Le.renderMap(mQuery('#place-container .vector-map'));
    });

    mQuery('a[data-toggle="tab"]').not('a#load-lead-map').on('shown.bs.tab', function (e) {
        if (leadMap.length) {
            Le.destroyMap(leadMap);
            leadMap = [];
        }
    });

    Le.initUniqueIdentifierFields();

    if (mQuery(container + ' .panel-companies').length) {
        mQuery(container + ' .panel-companies .fa-check').tooltip({html: true});
    }
    /*mQuery(function() {
        mQuery('#flashes').delay(2000).fadeIn('normal', function() {
            mQuery(this).delay(1500).fadeOut();
            mQuery('#flashes').addClass('hide');
        });
    });*/

};
Le.segment_filter = function(id){
    if(mQuery('#segment-filter-block_'+id).css("display") != 'none')
    {
        mQuery('#segment-filter-block_'+id).css("display","none");
        mQuery('#'+id).setAttribute('aria-expanded','false');
    }
    else
    {
        mQuery('.filter_option').css("display","none");
        mQuery('#segment-filter-block_'+id).css("display","");
        mQuery('#'+id).setAttribute('aria-expanded','true');
    }
}
Le.leadTimelineOnLoad = function (container, response) {
    mQuery("#contact-timeline a[data-activate-details='all']").on('click', function() {
        if (mQuery(this).find('span').first().hasClass('fa-level-down')) {
            mQuery("#contact-timeline a[data-activate-details!='all']").each(function () {
                var detailsId = mQuery(this).data('activate-details');
                if (detailsId && mQuery('#timeline-details-'+detailsId).length) {
                    mQuery('#timeline-details-' + detailsId).removeClass('hide');
                    mQuery(this).addClass('active');
                }
            });
            mQuery(this).find('span').first().removeClass('fa-level-down').addClass('fa-level-up');
        } else {
            mQuery("#contact-timeline a[data-activate-details!='all']").each(function () {
                var detailsId = mQuery(this).data('activate-details');
                if (detailsId && mQuery('#timeline-details-'+detailsId).length) {
                    mQuery('#timeline-details-' + detailsId).addClass('hide');
                    mQuery(this).removeClass('active');
                }
            });
            mQuery(this).find('span').first().removeClass('fa-level-up').addClass('fa-level-down');
        }
    });
    mQuery("#contact-timeline a[data-activate-details!='all']").on('click', function() {
        var detailsId = mQuery(this).data('activate-details');
        if (detailsId && mQuery('#timeline-details-'+detailsId).length) {
            var activateDetailsState = mQuery(this).hasClass('active');

            if (activateDetailsState) {
                mQuery('#timeline-details-'+detailsId).addClass('hide');
                mQuery(this).removeClass('active');
            } else {
                mQuery('#timeline-details-'+detailsId).removeClass('hide');
                mQuery(this).addClass('active');
            }
        }
    });

    if (response && typeof response.timelineCount != 'undefined') {
        mQuery('#TimelineCount').html(response.timelineCount);
    }
};

Le.leadAuditlogOnLoad = function (container, response) {
    mQuery("#contact-auditlog a[data-activate-details='all']").on('click', function() {
        if (mQuery(this).find('span').first().hasClass('fa-level-down')) {
            mQuery("#contact-auditlog a[data-activate-details!='all']").each(function () {
                var detailsId = mQuery(this).data('activate-details');
                if (detailsId && mQuery('#auditlog-details-'+detailsId).length) {
                    mQuery('#auditlog-details-' + detailsId).removeClass('hide');
                    mQuery(this).addClass('active');
                }
            });
            mQuery(this).find('span').first().removeClass('fa-level-down').addClass('fa-level-up');
        } else {
            mQuery("#contact-auditlog a[data-activate-details!='all']").each(function () {
                var detailsId = mQuery(this).data('activate-details');
                if (detailsId && mQuery('#auditlog-details-'+detailsId).length) {
                    mQuery('#auditlog-details-' + detailsId).addClass('hide');
                    mQuery(this).removeClass('active');
                }
            });
            mQuery(this).find('span').first().removeClass('fa-level-up').addClass('fa-level-down');
        }
    });
    mQuery("#contact-auditlog a[data-activate-details!='all']").on('click', function() {
        var detailsId = mQuery(this).data('activate-details');
        if (detailsId && mQuery('#auditlog-details-'+detailsId).length) {
            var activateDetailsState = mQuery(this).hasClass('active');

            if (activateDetailsState) {
                mQuery('#auditlog-details-'+detailsId).addClass('hide');
                mQuery(this).removeClass('active');
            } else {
                mQuery('#auditlog-details-'+detailsId).removeClass('hide');
                mQuery(this).addClass('active');
            }
        }
    });
};

Le.leadOnUnload = function(id) {
    if (typeof MauticVars.moderatedIntervals['leadListLiveUpdate'] != 'undefined') {
        Le.clearModeratedInterval('leadListLiveUpdate');
    }

    if (typeof Le.mapObjects !== 'undefined') {
        delete Le.mapObjects;
    }
};

Le.getLeadId = function() {
    return mQuery('input#leadId').val();
}

Le.leadEmailOnLoad = function(container, response) {
    mQuery('[data-verified-email]').click(function(e) {
        e.preventDefault();
        var currentLink = mQuery(this);
        var value = currentLink.attr('data-verified-email');
        mQuery("#lead_quickemail_from").val(value);
    });
    // Some hacky editations made on every form submit because of Froala (more at: https://github.com/froala/wysiwyg-editor/issues/1372)
    mQuery('[name="lead_quickemail"]').on('submit.ajaxform', function() {
        var emailHtml = mQuery('.fr-iframe').contents();
        var textarea = mQuery(this).find('#lead_quickemail_body');
        mQuery.each(emailHtml.find('td, th, table'), function() {
            var td = mQuery(this);

            // Bring back element's class names.
            if (td.attr('fr-original-class')) {
                td.attr('class', td.attr('fr-original-class'));
                td.removeAttr('fr-original-class');
            }

            // Bring back element's class inline styles.
            if (td.attr('fr-original-style')) {
                td.attr('style', td.attr('fr-original-style'));
                td.removeAttr('fr-original-style');
            }

            // Remove Froala's border.
            if (td.css('border') === '1px solid rgb(221, 221, 221)') {
                td.css('border', '');
            }
        });

        // Prevents contenteditable in sent e-mail.
        emailHtml.find('body').removeAttr('contenteditable');
        // Prevents unscrollable sent e-mail.
        emailHtml.find('body').css('overflow', 'initial');

        // Prevents unscrollable e-mail also in style tag.
        var styleElement = emailHtml.find('style[data-fr-style]'); // We hope, that there's no other style with this attribute...
        var style = styleElement.text();
        style = style.replace(/overflow:\s*hidden\s*;\s*/, ''); // ...and we hope, that no other element will have `overflow: hidden` before `body`. This replaces only first occurence.
        styleElement.get(0).innerHTML = style;

        // Rewrites value of the body textarea.
        textarea.val(emailHtml.find('html').get(0).outerHTML);
    });
}
 Le.addHide= function(){
    mQuery('#segment_filters').addClass('hide');
     //mQuery('#segment-filter-block_lead').removeClass('collapse in');
     //mQuery('#segment-filter-block_lead').addClass('collapse');
}
Le.removeHide= function(){
    mQuery('#segment_filters').removeClass('hide');
    mQuery('#segment-filter-block_list_leadlist').css("display","");
    mQuery('#segment-filter-block_list_leadlist').css("height","auto");
}
Le.closeOnOpen= function(headerid){
    if (headerid != "") {
        if (mQuery(headerid).hasClass('collapse in'))
        {
            mQuery(headerid).addClass('collapse');
        }else {
            mQuery('.close_all').removeClass('collapse in ');
            mQuery('.close_all').addClass('collapse ');
            mQuery(headerid).removeClass('collapse');
        }
    }
};
Le.addSegementFilter= function(filterId){
    if (filterId.id != "") {
        Le.addLeadListFilter(filterId.id);
        mQuery(this).val('');
        mQuery(this).trigger('chosen:updated');

        mQuery('#filters').addClass('active in');
        mQuery('#details').removeClass('active in');
        mQuery('#filterstab').addClass('active');
        mQuery('#detailstab').removeClass('active');
    }
};
Le.leadlistOnLoad = function(container) {
    if (mQuery(container + ' #list-search').length) {
        Le.activateSearchAutocomplete('list-search', 'lead.list');
    }

    var prefix = 'leadlist';
    var parent = mQuery('.dynamic-content-filter, .dwc-filter');
    if (parent.length) {
        prefix = parent.attr('id');
    }

    if (mQuery('#' + prefix + '_filters').length) {
        mQuery('#available_filters').on('change', function() {
            if (mQuery(this).val()) {
                Le.addLeadListFilter(mQuery(this).val());
                mQuery(this).val('');
                mQuery(this).trigger('chosen:updated');
            }
        });

        mQuery('#' + prefix + '_filters .remove-selected').each( function (index, el) {
            mQuery(el).on('click', function () {
                mQuery(this).closest('.panel').animate(
                    {'opacity': 0},
                    'fast',
                    function () {
                        mQuery(this).remove();
                    }
                );

                if (!mQuery('#' + prefix + '_filters li:not(.placeholder)').length) {
                    mQuery('#' + prefix + '_filters li.placeholder').removeClass('hide');
                } else {
                    mQuery('#' + prefix + '_filters li.placeholder').addClass('hide');
                }
            });
        });

        var bodyOverflow = {};
        mQuery('#' + prefix + '_filters').sortable({
            items: '.panel',
            helper: function(e, ui) {
                ui.children().each(function() {
                    if (mQuery(this).is(":visible")) {
                        mQuery(this).width(mQuery(this).width());
                    }
                });

                // Fix body overflow that messes sortable up
                bodyOverflow.overflowX = mQuery('body').css('overflow-x');
                bodyOverflow.overflowY = mQuery('body').css('overflow-y');
                mQuery('body').css({
                    overflowX: 'visible',
                    overflowY: 'visible'
                });

                return ui;
            },
            scroll: true,
            axis: 'y',
            stop: function(e, ui) {
                // Restore original overflow
                mQuery('body').css(bodyOverflow);

                // First in the list should be an "and"
                ui.item.find('select.glue-select').first().val('and');

                Le.reorderSegmentFilters();
            }
        });

    }

    // segment contact filters
    var segmentContactForm = mQuery('#segment-contact-filters');

    if (segmentContactForm.length) {
        segmentContactForm.on('change', function() {
            segmentContactForm.submit();
        }).on('keyup', function() {
            segmentContactForm.delay(200).submit();
        }).on('submit', function(e) {
            e.preventDefault();
            Le.refreshSegmentContacts(segmentContactForm);
        });
    }
    Le.removeActionButtons();
};

Le.reorderSegmentFilters = function() {
    // Update the filter numbers sot that they are ordered correctly when processed and grouped server side
    var counter = 0;

    var prefix = 'leadlist';
    var parent = mQuery('.dynamic-content-filter, .dwc-filter');
    if (parent.length) {
        prefix = parent.attr('id');
    }

    mQuery('#' + prefix + '_filters .panel').each(function() {
        Le.updateFilterPositioning(mQuery(this).find('select.glue-select').first());
        mQuery(this).find('[id^="' + prefix + '_filters_"]').each(function() {
            var id = mQuery(this).attr('id');
            if (prefix + '_filters___name___filter' === id) {
                return true;
            }

            var suffix = id.split(/[_]+/).pop();

            mQuery(this).attr('id', prefix + '_filters_'+counter+'_'+suffix);
            mQuery(this).attr('name', prefix + '[filters]['+counter+']['+suffix+']');
        });

        ++counter;
    });

    mQuery('#' + prefix + '_filters .panel-heading').removeClass('hide');
    mQuery('#' + prefix + '_filters .panel-heading').first().addClass('hide');
    mQuery('#' + prefix + '_filters .panel').first().removeClass('in-group');
};

Le.convertLeadFilterInput = function(el) {
     var iscampaignmodel=false;
    if (mQuery('#CampaignEventModal').length) {
        iscampaignmodel=true;
    }

    var prefix = 'leadlist';
if(iscampaignmodel){
    prefix = 'campaignevent_properties';
}
    var parent = mQuery(el).parents('.dynamic-content-filter, .dwc-filter');
    if (parent.length) {
        prefix = parent.attr('id');
    }

    var operator = mQuery(el).val();

    // Extract the filter number
    var regExp    = /_filters_(\d+)_operator/;
    var matches   = regExp.exec(mQuery(el).attr('id'));
    var filterNum = matches[1];
    var filterId  = '#' + prefix + '_filters_' + filterNum + '_filter';
    var typeId  = '#' + prefix + '_filters_' + filterNum + '_type';
    var fieldtype = mQuery(typeId).val();
    var isSpecial = (mQuery.inArray(fieldtype, ['leadlist', 'device_type',  'device_brand', 'device_os','owner_id','lead_email_received', 'lead_email_sent', 'tags', 'multiselect', 'boolean', 'select', 'country', 'timezone', 'region', 'stage', 'locale', 'globalcategory','landingpage_list','formsubmit_list','asset_downloads_list']) != -1);
    // Reset has-error
    if (mQuery(filterId).parent().hasClass('has-error')) {
        mQuery(filterId).parent().find('div.help-block').hide();
        mQuery(filterId).parent().removeClass('has-error');
    }

    var disabled = (operator == 'empty' || operator == '!empty');
    mQuery(filterId+', #' + prefix + '_filters_' + filterNum + '_display').prop('disabled', disabled);

    if (disabled) {
        mQuery(filterId).val('');
    }

    var customFilter = (operator == 'today' || operator == 'tomorrow' || operator == 'yesterday' || operator == 'week_last' || operator == 'week_next' || operator == 'week_this' || operator == 'month_last' || operator == 'month_next' || operator == 'month_this' || operator == 'year_last' || operator == 'year_next' || operator == 'year_this');

    if (customFilter) {
        mQuery(filterId+', #' + prefix + '_filters_' + filterNum + '_display').attr('tabindex', '-1');
        mQuery(filterId+', #' + prefix + '_filters_' + filterNum + '_display').attr('style', 'pointer-events: none;background-color: #ebedf0;opacity: 1;');
        mQuery(filterId).val(operator);
    } else {
        mQuery(filterId+', #' + prefix + '_filters_' + filterNum + '_display').removeAttr('tabindex', '-1');
        mQuery(filterId+', #' + prefix + '_filters_' + filterNum + '_display').removeAttr('style', 'pointer-events: none;background-color: #ebedf0;opacity: 1;');
        mQuery(filterId).val("");
    }

    var newName = '';
    var lastPos;

    if (mQuery(filterId).is('select')) {
        var isMultiple  = mQuery(filterId).attr('multiple');
        var multiple    = (operator == 'in' || operator == '!in' || operator == 'empty' || operator == '!empty');
        var placeholder = mQuery(filterId).attr('data-placeholder');

        if (multiple && !isMultiple) {
            mQuery(filterId).attr('multiple', 'multiple');

            // Update the name
            newName =  mQuery(filterId).attr('name') + '[]';
            mQuery(filterId).attr('name', newName);

            placeholder = mauticLang['chosenChooseMore'];
        } else if (!multiple && isMultiple) {
            mQuery(filterId).removeAttr('multiple');

            // Update the name
            newName = filterEl.attr('name');
            lastPos = newName.lastIndexOf('[]');
            newName = newName.substring(0, lastPos);

            mQuery(filterId).attr('name', newName);

            placeholder = mauticLang['chosenChooseOne'];
        }

        if (multiple) {
            // Remove empty option
            mQuery(filterId).find('option[value=""]').remove();

            // Make sure none are selected
            mQuery(filterId + ' option:selected').removeAttr('selected');
        } else {
            // Add empty option
            mQuery(filterId).prepend("<option value='' selected></option>");
        }

        // Destroy the chosen and recreate
        if (mQuery(filterId + '_chosen').length) {
            mQuery(filterId).chosen('destroy');
        }

        mQuery(filterId).attr('data-placeholder', placeholder);

        Le.activateChosenSelect(mQuery(filterId));
    }
};

Le.updateLookupListFilter = function(field, datum) {
    if (datum && datum.id) {
        var filterField = '#'+field.replace('_display', '_filter');
        mQuery(filterField).val(datum.id);
    }
};

Le.activateSegmentFilterTypeahead = function(displayId, filterId, fieldOptions, mQueryObject) {

    if(typeof mQueryObject == 'function'){
        mQuery = mQueryObject;
    }

    mQuery('#' + displayId).attr('data-lookup-callback', 'updateLookupListFilter');

    Le.activateFieldTypeahead(displayId, filterId, [], 'lead:fieldList')
};

Le.addLeadListFilter = function (elId) {
    var filterId = '#available_' + elId;
    var label    = mQuery(filterId).text();

    //create a new filter

    var filterNum = parseInt(mQuery('.available-filters').data('index'));
    mQuery('.available-filters').data('index', filterNum + 1);

    var prototype = mQuery('.available-filters').data('prototype');
    var fieldType = mQuery(filterId).data('field-type');
    var fieldObject = mQuery(filterId).data('field-object');
    var customObject = mQuery(filterId).data('field-customobject');
    var isSpecial = (mQuery.inArray(fieldType, ['leadlist', 'device_type',  'device_brand', 'device_os','owner_id','lead_email_received', 'lead_email_sent', 'tags', 'multiselect', 'boolean', 'select', 'country', 'timezone', 'region', 'stage', 'locale', 'globalcategory','landingpage_list','formsubmit_list','asset_downloads_list']) != -1);

    prototype = prototype.replace(/__name__/g, filterNum);
    prototype = prototype.replace(/__label__/g, label);

    // Convert to DOM
    prototype = mQuery(prototype);

    var prefix = 'leadlist';
    var parent = mQuery(filterId).parents('.dynamic-content-filter, .dwc-filter');
    if (parent.length) {
        prefix = parent.attr('id');
    }
    var iscampaignmodel=false;
    if (mQuery('#CampaignEventModal').length) {
        iscampaignmodel=true;
    }
    var filterBase  = prefix+ "[filters][" + filterNum + "]";
    var filterIdBase = prefix + "_filters_" + filterNum + "_";
if(iscampaignmodel){
    filterBase  = "campaignevent[properties][filters][" + filterNum + "]";
    filterIdBase = "campaignevent_properties_filters_" + filterNum + "_";
}
    var operator = mQuery('#' + filterIdBase + 'operator').val();
    if (isSpecial && (operator != "empty" && operator != "!empty")) {
        var templateField = fieldType;
        if (fieldType == 'boolean' || fieldType == 'multiselect') {
            templateField = 'select';
        }

        var template = mQuery('#templates .' + templateField + '-template').clone();
        mQuery(template).attr('name', mQuery(template).attr('name').replace(/__name__/g, filterNum));
        mQuery(template).attr('id', mQuery(template).attr('id').replace(/__name__/g, filterNum));
        mQuery(prototype).find('input[name="' + filterBase + '[filter]"]').replaceWith(template);
    }

    if (mQuery('#' + prefix + '_filters div.panel').length == 0) {
        // First filter so hide the glue footer
        mQuery(prototype).find(".panel-heading").addClass('hide');
    }

    if (fieldObject == 'company') {
        mQuery(prototype).find(".object-icon").removeClass('fa-user').addClass('fa-building');
    } else {
        mQuery(prototype).find(".object-icon").removeClass('fa-building').addClass('fa-user');
    }
    mQuery(prototype).find(".inline-spacer").append(fieldObject);

    mQuery(prototype).find("a.remove-selected").on('click', function() {
        mQuery(this).closest('.panel').animate(
            {'opacity': 0},
            'fast',
            function () {
                mQuery(this).remove();
            }
        );
    });

    mQuery(prototype).find("input[name='" + filterBase + "[field]']").val(elId);
    mQuery(prototype).find("input[name='" + filterBase + "[type]']").val(fieldType);
    mQuery(prototype).find("input[name='" + filterBase + "[object]']").val(fieldObject);
    mQuery(prototype).find("input[name='" + filterBase + "[customObject]']").val(customObject);
    mQuery(prototype).find("input[name='" + filterBase + "[fieldlabel]']").val(label);

    var filterEl = (isSpecial) ? "select[name='" + filterBase + "[filter]']" : "input[name='" + filterBase + "[filter]']";

    mQuery(prototype).appendTo('#' + prefix + '_filters');
    if(elId == "lead_email_activity"){
        mQuery(prototype).find('.email-activity-label').removeClass('hide');
        mQuery(prototype).find('.filter-field-segment').removeClass('col-sm-5').addClass('col-sm-3 lead_filter_padding_right');
    } else {
        mQuery(prototype).find('.email-activity-label').removeClass('hide lead_filter_padding_right').addClass('hide');
    }

    var filter = '#' + filterIdBase + 'filter';

    //activate fields
    if (isSpecial) {
        if (fieldType == 'select' || fieldType == 'multiselect' || fieldType == 'boolean') {
            // Generate the options
            var fieldOptions = mQuery(filterId).data("field-list");
            mQuery.each(fieldOptions, function(index, val) {
                if (mQuery.isPlainObject(val)) {
                    var optGroup = index;
                    mQuery.each(val, function(index, value) {
                        mQuery('<option class="' + optGroup + '">').val(index).text(value).appendTo(filterEl);
                    });
                    mQuery('.' + index).wrapAll("<optgroup label='"+index+"' />");
                } else {
                    mQuery('<option>').val(index).text(val).appendTo(filterEl);
                }
            });
        }
    } else if (fieldType == 'lookup') {
        var fieldCallback = mQuery(filterId).data("field-callback");
        if (fieldCallback && typeof Le[fieldCallback] == 'function') {
            var fieldOptions = mQuery(filterId).data("field-list");
            Le[fieldCallback](filterIdBase + 'filter', elId, fieldOptions);
        }
    } else if (fieldType == 'datetime') {
        mQuery(filter).datetimepicker({
            format: 'Y-m-d H:i',
            lazyInit: true,
            validateOnBlur: false,
            allowBlank: true,
            scrollInput: false
        });
    } else if (fieldType == 'date') {
        mQuery(filter).datetimepicker({
            timepicker: false,
            format: 'Y-m-d',
            lazyInit: true,
            validateOnBlur: false,
            allowBlank: true,
            scrollInput: false,
            closeOnDateSelect: true
        });
    } else if (fieldType == 'time') {
        mQuery(filter).datetimepicker({
            datepicker: false,
            format: 'H:i',
            lazyInit: true,
            validateOnBlur: false,
            allowBlank: true,
            scrollInput: false
        });
    } else if (fieldType == 'lookup_id') {
        //switch the filter and display elements
        var oldFilter = mQuery(filterEl);
        var newDisplay = mQuery(oldFilter).clone();
        mQuery(newDisplay).attr('name', filterBase + '[display]')
            .attr('id', filterIdBase + 'display');

        var oldDisplay = mQuery(prototype).find("input[name='" + filterBase + "[display]']");
        var newFilter = mQuery(oldDisplay).clone();
        mQuery(newFilter).attr('name', filterBase + '[filter]')
            .attr('id', filterIdBase + 'filter');

        mQuery(oldFilter).replaceWith(newFilter);
        mQuery(oldDisplay).replaceWith(newDisplay);

        var fieldCallback = mQuery(filterId).data("field-callback");
        if (fieldCallback && typeof Le[fieldCallback] == 'function') {
            var fieldOptions = mQuery(filterId).data("field-list");
            Le[fieldCallback](filterIdBase + 'display', elId, fieldOptions);
        }
    } else {
        mQuery(filter).attr('type', fieldType);
    }
    if(fieldObject == "pages" && fieldType == "number"){
        mQuery(filter).attr('min', 0);
    }
    if(fieldObject == "emails" && fieldType == "number"){
        mQuery(filter).attr('min', 0);
    }

    var operators = mQuery(filterId).data('field-operators');
    mQuery('#' + filterIdBase + 'operator').html('');
    mQuery.each(operators, function (value, label) {
        var newOption = mQuery('<option/>').val(value).text(label);
        newOption.appendTo(mQuery('#' + filterIdBase + 'operator'));
    });

    // Convert based on first option in list
    Le.convertLeadFilterInput('#' + filterIdBase + 'operator');

    // Reposition if applicable
    Le.updateFilterPositioning(mQuery('#' + filterIdBase + 'glue'));
};

Le.leadfieldOnLoad = function (container) {
    if (mQuery(container + ' .leadfield-list').length) {
        var bodyOverflow = {};
        mQuery(container + ' .leadfield-list tbody').sortable({
            handle: '.fa-ellipsis-v',
            helper: function(e, ui) {
                ui.children().each(function() {
                    mQuery(this).width(mQuery(this).width());
                });

                // Fix body overflow that messes sortable up
                bodyOverflow.overflowX = mQuery('body').css('overflow-x');
                bodyOverflow.overflowY = mQuery('body').css('overflow-y');
                mQuery('body').css({
                    overflowX: 'visible',
                    overflowY: 'visible'
                });

                return ui;
            },
            scroll: false,
            axis: 'y',
            containment: container + ' .leadfield-list',
            stop: function(e, ui) {
                // Restore original overflow
                mQuery('body').css(bodyOverflow);

                // Get the page and limit
                mQuery.ajax({
                    type: "POST",
                    url: mauticAjaxUrl + "?action=lead:reorder&limit=" + mQuery('.pagination-limit').val() + '&page=' + mQuery('.pagination li.active a span').first().text(),
                    data: mQuery(container + ' .leadfield-list tbody').sortable("serialize")});
            }
        });
    }

    if (mQuery(container + ' form[name="leadfield"]').length) {
        Le.updateLeadFieldProperties(mQuery('#leadfield_type').val(), true);
    }

};

Le.updateLeadFieldProperties = function(selectedVal, onload) {
    if (selectedVal == 'multiselect') {
        // Use select
        selectedVal = 'select';
    }

    if (mQuery('#field-templates .' + selectedVal).length) {
        mQuery('#leadfield_properties').html(
            mQuery('#field-templates .' + selectedVal).html()
                .replace(/leadfield_properties_template/g, 'leadfield_properties')
        );
        mQuery("#leadfield_properties *[data-toggle='sortablelist']").each(function (index) {
            var sortableList = mQuery(this);
            Le.activateSortable(this);
            // Using an interval so removing, adding, updating and reordering are accounted for
            var contactFieldListOptions = mQuery('#leadfield_properties').find('input').map(function() {
                return mQuery(this).val();
            }).get().join();
            var updateDefaultValuesetInterval = setInterval(function() {
                var evalListOptions = mQuery('#leadfield_properties').find('input').map(function() {
                    return mQuery(this).val();
                }).get().join();
                if (mQuery('#leadfield_properties_itemcount').length) {
                    if (contactFieldListOptions != evalListOptions) {
                        contactFieldListOptions = evalListOptions;
                        var selected = mQuery('#leadfield_defaultValue').val();
                        mQuery('#leadfield_defaultValue').html('<option value=""></option>');
                        var labels = mQuery('#leadfield_properties').find('input.sortable-label');
                        if (labels.length) {
                            labels.each(function () {
                                // label/value pairs
                                var label = mQuery(this).val();
                                var val = mQuery(this).closest('.row').find('input.sortable-value').first().val();
                                mQuery('<option value="' + val + '">' + label + '</option>').appendTo(mQuery('#leadfield_defaultValue'));
                            });
                        } else {
                            mQuery('#leadfield_properties .list-sortable').find('input').each(function () {
                                var val = mQuery(this).val();
                                mQuery('<option value="' + val + '">' + val + '</option>').appendTo(mQuery('#leadfield_defaultValue'));
                            });
                        }

                        mQuery('#leadfield_defaultValue').val(selected);
                        mQuery('#leadfield_defaultValue').trigger('chosen:updated');
                    }
                } else {
                    clearInterval(updateDefaultValuesetInterval);
                    delete contactFieldListOptions;
                }
            }, 500);
        });
    } else if (!mQuery('#leadfield_properties .' + selectedVal).length) {
        mQuery('#leadfield_properties').html('');
    }

    if (selectedVal == 'time') {
        mQuery('#leadfield_isListable').closest('.row').addClass('hide');
    } else {
        mQuery('#leadfield_isListable').closest('.row').removeClass('hide');
    }

    // Switch default field if applicable
    var defaultValueField = mQuery('#leadfield_defaultValue');
    if (defaultValueField.hasClass('calendar-activated')) {
        defaultValueField.datetimepicker('destroy').removeClass('calendar-activated');
    } else if (mQuery('#leadfield_defaultValue_chosen').length) {
        mQuery('#leadfield_defaultValue').chosen('destroy');
    }

    var defaultFieldType = mQuery('input[name="leadfield[defaultValue]"]').attr('type');
    var tempType = selectedVal;
    var html = '';
    var isSelect = false;
    var defaultVal = defaultValueField.val();
    switch (selectedVal) {
        case 'boolean':
            if (defaultFieldType != 'radio') {
                // Convert to a boolean type
                html = '<div id="leadfield_default_template_boolean">' + mQuery('#field-templates .default_template_boolean').html() + '</div>';
            }
            break;
        case 'country':
        case 'region':
        case 'locale':
        case 'timezone':
            html = mQuery('#field-templates .default_template_' + selectedVal).html();
            isSelect = true;
            break;
        case 'select':
        case 'multiselect':
        case 'lookup':
            html = mQuery('#field-templates .default_template_select').html();
            tempType = 'select';
            isSelect = true;
            break;
        case 'textarea':
            html = mQuery('#field-templates .default_template_textarea').html();
            break;
        default:
            html = mQuery('#field-templates .default_template_text').html();
            tempType = 'text';

            if (selectedVal == 'number' || selectedVal == 'tel' || selectedVal == 'url' || selectedVal == 'email') {
                var replace = 'type="text"';
                var regex = new RegExp(replace, "g");
                html = html.replace(regex, 'type="' + selectedVal + '"');
            }

            break;
    }

    if (html && !onload) {
        var replace = 'default_template_' + tempType;
        var regex = new RegExp(replace, "g");
        html = html.replace(regex, 'defaultValue')
        defaultValueField.replaceWith(mQuery(html));
        mQuery('#leadfield_defaultValue').val(defaultVal);
    }
    mQuery('#leadfield_defaultValue').addClass('le-input');
    if (selectedVal === 'datetime' || selectedVal === 'date' || selectedVal === 'time') {
        Le.activateDateTimeInputs('#leadfield_defaultValue', selectedVal);
    } else if (isSelect) {
       Le.activateChosenSelect('#leadfield_defaultValue');
    }
};

Le.updateLeadFieldBooleanLabels = function(el, label) {
    mQuery('#leadfield_defaultValue_' + label).parent().find('span').text(
        mQuery(el).val()
    );
};

Le.refreshLeadSocialProfile = function(network, leadId, event) {
    var query = "action=lead:updateSocialProfile&network=" + network + "&lead=" + leadId;
    mQuery.ajax({
        showLoadingBar: true,
        url: mauticAjaxUrl,
        type: "POST",
        data: query,
        dataType: "json",
        success: function (response) {
            if (response.success) {
                if (response.completeProfile) {
                    mQuery('#social-container').html(response.completeProfile);
                    mQuery('#SocialCount').html(response.socialCount);
                } else {
                    //loop through each network
                    mQuery.each(response.profiles, function (index, value) {
                        if (mQuery('#' + index + 'CompleteProfile').length) {
                            mQuery('#' + index + 'CompleteProfile').html(value.newContent);
                        }
                    });
                }
            }
            Le.stopPageLoadingBar();
            Le.stopIconSpinPostEvent();
        },
        error: function (request, textStatus, errorThrown) {
            Le.processAjaxError(request, textStatus, errorThrown);
        }
    });
};

Le.clearLeadSocialProfile = function(network, leadId, event) {
    Le.startIconSpinOnEvent(event);
    var query = "action=lead:clearSocialProfile&network=" + network + "&lead=" + leadId;
    mQuery.ajax({
        url: mauticAjaxUrl,
        type: "POST",
        data: query,
        dataType: "json",
        success: function (response) {
            if (response.success) {
                //activate the click to remove the panel
                mQuery('.' + network + '-panelremove').click();
                if (response.completeProfile) {
                    mQuery('#social-container').html(response.completeProfile);
                }
                mQuery('#SocialCount').html(response.socialCount);
            }

            Le.stopIconSpinPostEvent();
        },
        error: function (request, textStatus, errorThrown) {
            Le.processAjaxError(request, textStatus, errorThrown);
            Le.stopIconSpinPostEvent();
        }
    });
};

Le.refreshLeadAuditLog = function(form) {
    Le.postForm(mQuery(form), function (response) {
        response.target = '#auditlog-table';
        mQuery('#AuditLogCount').html(response.auditLogCount);
        Le.processPageContent(response);
    });
};

Le.refreshLeadTimeline = function(form) {
    Le.postForm(mQuery(form), function (response) {
        response.target = '#timeline-table';
        mQuery('#TimelineCount').html(response.timelineCount);
        Le.processPageContent(response);
    });
};

Le.refreshLeadNotes = function(form) {
    Le.postForm(mQuery(form), function (response) {
        response.target = '#NoteList';
        mQuery('#NoteCount').html(response.noteCount);
        Le.processPageContent(response);
    });
};

Le.refreshSegmentContacts = function(form) {
    Le.postForm(mQuery(form), function (response) {
        response.target = '#contacts-container';
        Le.processPageContent(response);
    });
};

Le.toggleLeadList = function(toggleId, leadId, listId) {
    var action = mQuery('#' + toggleId).hasClass('fa-toggle-on') ? 'remove' : 'add';
    var query = "action=lead:toggleLeadList&leadId=" + leadId + "&listId=" + listId + "&listAction=" + action;

    Le.toggleLeadSwitch(toggleId, query, action);
};

Le.togglePreferredChannel = function(channel) {
    if (channel == 'all') {
        var status = mQuery('#lead_contact_frequency_rules_subscribed_channels_0')[0].checked;  //"select all" change

       // "select all" checked status
        mQuery('#channels input:checkbox').each(function(){ //iterate all listed checkbox items
            if (this.checked != status) {
                this.checked = status;
                Le.setPreferredChannel(this.value);
            }
        });
    } else {
        Le.setPreferredChannel(channel);
    }
};

Le.setPreferredChannel = function(channel) {
    mQuery( '#frequency_' + channel ).slideToggle();
    mQuery( '#frequency_' + channel ).removeClass('hide');
    if (mQuery('#' + channel)[0].checked) {
        mQuery('#is-contactable-' + channel).removeClass('text-muted');
        mQuery('#lead_contact_frequency_rules_frequency_number_' + channel).prop("disabled" , false).trigger("chosen:updated");
        mQuery('#preferred_' + channel).prop("disabled" , false);
        mQuery('#lead_contact_frequency_rules_frequency_time_' + channel).prop("disabled" , false).trigger("chosen:updated");
        mQuery('#lead_contact_frequency_rules_contact_pause_start_date_' + channel).prop("disabled" , false);
        mQuery('#lead_contact_frequency_rules_contact_pause_end_date_' + channel).prop("disabled" , false);
    } else {
        mQuery('#is-contactable-' + channel).addClass('text-muted');
        mQuery('#lead_contact_frequency_rules_frequency_number_' + channel).prop("disabled" , true).trigger("chosen:updated");
        mQuery('#preferred_' + channel).prop("disabled" , true);
        mQuery('#lead_contact_frequency_rules_frequency_time_' + channel).prop("disabled" , true).trigger("chosen:updated");
        mQuery('#lead_contact_frequency_rules_contact_pause_start_date_' + channel).prop("disabled" , true);
        mQuery('#lead_contact_frequency_rules_contact_pause_end_date_' + channel).prop("disabled" , true);
    }
};

Le.toggleCompanyLead = function(toggleId, leadId, companyId) {
    var action = mQuery('#' + toggleId).hasClass('fa-toggle-on') ? 'remove' : 'add';
    var query = "action=lead:toggleCompanyLead&leadId=" + leadId + "&companyId=" + companyId + "&companyAction=" + action;
    Le.toggleLeadSwitch(toggleId, query, action);
};

Le.toggleLeadCampaign = function(toggleId, leadId, campaignId) {
    var action = mQuery('#' + toggleId).hasClass('fa-toggle-on') ? 'remove' : 'add';
    var query  = "action=lead:toggleLeadCampaign&leadId=" + leadId + "&campaignId=" + campaignId + "&campaignAction=" + action;

    Le.toggleLeadSwitch(toggleId, query, action);
};

Le.toggleLeadSwitch = function(toggleId, query, action) {
    var toggleOn  = 'fa-toggle-on text-success';
    var toggleOff = 'fa-toggle-off text-danger';
    var spinClass = 'fa-spin fa-spinner ';

    if (action == 'remove') {
        //switch it on
        mQuery('#' + toggleId).removeClass(toggleOn).addClass(spinClass + 'text-danger');
    } else {
        mQuery('#' + toggleId).removeClass(toggleOff).addClass(spinClass + 'text-success');
    }

    mQuery.ajax({
        url: mauticAjaxUrl,
        type: "POST",
        data: query,
        dataType: "json",
        success: function (response) {
            mQuery('#' + toggleId).removeClass(spinClass);
            if (!response.success) {
                //return the icon back
                if (action == 'remove') {
                    //switch it on
                    mQuery('#' + toggleId).removeClass(toggleOff).addClass(toggleOn);
                } else {
                    mQuery('#' + toggleId).removeClass(toggleOn).addClass(toggleOff);
                }
            } else {
                if (action == 'remove') {
                    //switch it on
                    mQuery('#' + toggleId).removeClass(toggleOn).addClass(toggleOff);
                } else {
                    mQuery('#' + toggleId).removeClass(toggleOff).addClass(toggleOn);
                }
            }
        },
        error: function (request, textStatus, errorThrown) {
            //return the icon back
            mQuery('#' + toggleId).removeClass(spinClass);

            if (action == 'remove') {
                //switch it on
                mQuery('#' + toggleId).removeClass(toggleOff).addClass(toggleOn);
            } else {
                mQuery('#' + toggleId).removeClass(toggleOn).addClass(toggleOff);
            }
        }
    });
};

Le.leadNoteOnLoad = function (container, response) {
    if (response.noteHtml) {
        var el = '#LeadNote' + response.noteId;
        if (mQuery(el).length) {
            mQuery(el).replaceWith(response.noteHtml);
        } else {
            mQuery('#LeadNotes').prepend(response.noteHtml);
        }

        Le.makeModalsAlive(mQuery(el + " *[data-toggle='ajaxmodal']"));
        Le.makeConfirmationsAlive(mQuery(el+' a[data-toggle="confirmation"]'));
        Le.makeLinksAlive(mQuery(el + " a[data-toggle='ajax']"));
    } else if (response.deleteId && mQuery('#LeadNote' + response.deleteId).length) {
        mQuery('#LeadNote' + response.deleteId).remove();
    }

    if (response.upNoteCount || response.noteCount || response.downNoteCount) {
        var noteCountWrapper = mQuery('#NoteCount');
        var count = parseInt(noteCountWrapper.text().trim());

        if (response.upNoteCount) {
            count++;
        } else if (response.downNoteCount) {
            count--;
        } else {
            count = parseInt(response.noteCount);
        }

        noteCountWrapper.text(count);
    }
};

Le.showSocialMediaImageModal = function(imgSrc) {
    mQuery('#socialImageModal img').attr('src', imgSrc);
    mQuery('#socialImageModal').modal('show');
};

Le.leadImportOnLoad = function (container, response) {
    if (!mQuery('#leadImportProgress').length) {
        Le.clearModeratedInterval('leadImportProgress');
    } else {
        Le.setModeratedInterval('leadImportProgress', 'reloadLeadImportProgress', 3000);
    }
};

Le.reloadLeadImportProgress = function() {
    if (!mQuery('#leadImportProgress').length) {
        Le.clearModeratedInterval('leadImportProgress');
    } else {
        // Get progress separate so there's no delay while the import batches
        Le.ajaxActionRequest('lead:getImportProgress', {}, function(response) {
            if (response.progress) {
                if (response.progress[0] > 0) {
                    mQuery('.imported-count').html(response.progress[0]);
                    mQuery('.progress-bar-import').attr('aria-valuenow', response.progress[0]).css('width', response.percent + '%');
                    mQuery('.progress-bar-import span.sr-only').html(response.percent + '%');
                }
            }
        });

        // Initiate import
        mQuery.ajax({
            showLoadingBar: false,
            url: window.location + '?importbatch=1',
            success: function(response) {
                Le.moderatedIntervalCallbackIsComplete('leadImportProgress');

                if (response.newContent) {
                    // It's done so pass to process page
                    Le.processPageContent(response);
                }
            }
        });
    }
};

Le.removeBounceStatus = function (el, dncId) {
    mQuery(el).removeClass('fa-times').addClass('fa-spinner fa-spin');

    Le.ajaxActionRequest('lead:removeBounceStatus', 'id=' + dncId, function() {
        mQuery('#bounceLabel' + dncId).tooltip('destroy');
        mQuery('#bounceLabel' + dncId).fadeOut(300, function() { mQuery(this).remove(); });
    });
};

Le.toggleLiveLeadListUpdate = function () {
    if (typeof MauticVars.moderatedIntervals['leadListLiveUpdate'] == 'undefined') {
        Le.setModeratedInterval('leadListLiveUpdate', 'updateLeadList', 5000);
        mQuery('#liveModeButton').addClass('btn-primary');
    } else {
        Le.clearModeratedInterval('leadListLiveUpdate');
        mQuery('#liveModeButton').removeClass('btn-primary');
    }
};

Le.updateLeadList = function () {
    var maxLeadId = mQuery('#liveModeButton').data('max-id');
    mQuery.ajax({
        url: mauticAjaxUrl,
        type: "get",
        data: "action=lead:getNewLeads&maxId=" + maxLeadId,
        dataType: "json",
        success: function (response) {
            if (response.leads) {
                if (response.indexMode == 'list') {
                    mQuery('#leadTable tbody').prepend(response.leads);
                } else {
                    var items = mQuery(response.leads);
                    mQuery('.shuffle-grid').prepend(items);
                    mQuery('.shuffle-grid').shuffle('appended', items);
                    mQuery('.shuffle-grid').shuffle('update');

                    mQuery('#liveModeButton').data('max-id', response.maxId);
                }
            }

            if (typeof IdleTimer != 'undefined' && !IdleTimer.isIdle()) {
                // Remove highlighted classes
                if (response.indexMode == 'list') {
                    mQuery('#leadTable tr.warning').each(function() {
                        var that = this;
                        setTimeout(function() {
                            mQuery(that).removeClass('warning', 1000)
                        }, 5000);
                    });
                } else {
                    mQuery('.shuffle-grid .highlight').each(function() {
                        var that = this;
                        setTimeout(function() {
                            mQuery(that).removeClass('highlight', 1000, function() {
                                mQuery(that).css('border-top-color', mQuery(that).data('color'));
                            })
                        }, 5000);
                    });
                }
            }

            if (response.maxId) {
                mQuery('#liveModeButton').data('max-id', response.maxId);
            }

            Le.moderatedIntervalCallbackIsComplete('leadListLiveUpdate');
        },
        error: function (request, textStatus, errorThrown) {
            Le.processAjaxError(request, textStatus, errorThrown);

            Le.moderatedIntervalCallbackIsComplete('leadListLiveUpdate');
        }
    });
};

Le.toggleAnonymousLeads = function() {
    var searchValue = mQuery('#list-search').typeahead('val');
    var string      = mQuery('#anonymousLeadButton').data('anonymous').toLowerCase();

    if (searchValue.toLowerCase().indexOf('!' + string) == 0) {
        searchValue = searchValue.replace('!' + string, string);
        mQuery('#anonymousLeadButton').addClass('btn-primary');
    } else if (searchValue.toLowerCase().indexOf(string) == -1) {
        if (searchValue) {
            searchValue = searchValue + ' ' + string;
        } else {
            searchValue = string;
        }
        searchValue = string;
        mQuery('#anonymousLeadButton').addClass('btn-primary');
    } else {
        searchValue = mQuery.trim(searchValue.replace(string, ''));
        mQuery('#anonymousLeadButton').removeClass('btn-primary');
    }
    searchValue = searchValue.replace("  ", " ");
    Le.setSearchFilter(null, 'list-search', searchValue);
};

Le.getLeadEmailContent = function (el) {
    var id = (mQuery.type( el ) === "string") ? el : mQuery(el).attr('id');
    Le.activateLabelLoadingIndicator(id);

    var inModal = mQuery('#'+id).closest('modal').length;
    if (inModal) {
        mQuery('#MauticSharedModal .btn-primary').prop('disabled', true);
    }

    Le.ajaxActionRequest('lead:getEmailTemplate', {'template': mQuery(el).val()}, function(response) {
        if (inModal) {
            mQuery('#MauticSharedModal .btn-primary').prop('disabled', false);
        }
        var idPrefix = id.replace('templates', '');
        var bodyEl = (mQuery('#'+idPrefix+'message').length) ? '#'+idPrefix+'message' : '#'+idPrefix+'body';
        mQuery(bodyEl).froalaEditor('html.set', response.body);
        mQuery(bodyEl).val(response.body);
        mQuery('#'+idPrefix+'subject').val(response.subject);

        Le.removeLabelLoadingIndicator();
    });
};

Le.updateLeadTags = function () {
    Le.activateLabelLoadingIndicator('lead_tags_tags');
    var formData = mQuery('form[name="lead_tags"]').serialize();
    Le.ajaxActionRequest('lead:updateLeadTags', formData, function(response) {
        if (response.tags) {
            mQuery('#lead_tags_tags').html(response.tags);
            mQuery('#lead_tags_tags').trigger('chosen:updated');
        }
        Le.removeLabelLoadingIndicator();
    });
};

Le.createLeadTag = function (el) {
    var newFound = false;
    mQuery('#' + mQuery(el).attr('id') + ' :selected').each(function(i, selected) {
        if (!mQuery.isNumeric(mQuery(selected).val())) {
            newFound = true;
        }
    });

    var tags = JSON.stringify(mQuery(el).val());

    if (!newFound) {
        Le.activateLabelLoadingIndicator(mQuery(el).attr('id'));
        Le.ajaxActionRequest('lead:addNumericLeadTags', {tags: tags}, function(response) {
            if (response.tags) {
                mQuery('#' + mQuery(el).attr('id')).html(response.tags);
                mQuery('#' + mQuery(el).attr('id')).trigger('chosen:updated');
            }

            Le.removeLabelLoadingIndicator();
        });

        return
    }

        Le.activateLabelLoadingIndicator(mQuery(el).attr('id'));
        Le.ajaxActionRequest('lead:addLeadTags', {tags: tags}, function(response) {
            if (response.tags) {
                mQuery('#' + mQuery(el).attr('id')).html(response.tags);
                mQuery('#' + mQuery(el).attr('id')).trigger('chosen:updated');
            }

            Le.removeLabelLoadingIndicator();
        });
};

Le.createLeadUtmTag = function (el) {
    var newFound = false;
    mQuery('#' + mQuery(el).attr('id') + ' :selected').each(function(i, selected) {
        if (!mQuery.isNumeric(mQuery(selected).val())) {
            newFound = true;
        }
    });

    if (!newFound) {
        return;
    }

    Le.activateLabelLoadingIndicator(mQuery(el).attr('id'));

    var utmtags = JSON.stringify(mQuery(el).val());

    Le.ajaxActionRequest('lead:addLeadUtmTags', {utmtags: utmtags}, function(response) {
        if (response.tags) {
            mQuery('#' + mQuery(el).attr('id')).html(response.utmtags);
            mQuery('#' + mQuery(el).attr('id')).trigger('chosen:updated');
        }

        Le.removeLabelLoadingIndicator();
    });
};

Le.leadBatchSubmit = function() {
    if (Le.batchActionPrecheck()) {
        if (mQuery('#lead_batch_remove').val() || mQuery('#lead_batch_add').val() || mQuery('#lead_batch_dnc_reason').length || mQuery('#lead_batch_stage_addstage').length || mQuery('#lead_batch_owner_addowner').length) {
            var ids = Le.getCheckedListIds(false, true);

            if (mQuery('#lead_batch_ids').length) {
                mQuery('#lead_batch_ids').val(ids);
            } else if (mQuery('#lead_batch_dnc_reason').length) {
                mQuery('#lead_batch_dnc_ids').val(ids);
            } else if (mQuery('#lead_batch_stage_addstage').length) {
                mQuery('#lead_batch_stage_ids').val(ids);
            }else if (mQuery('#lead_batch_owner_addowner').length) {
                mQuery('#lead_batch_owner_ids').val(ids);
            }

            return true;
        }

    }

    mQuery('#MauticSharedModal').modal('hide');

    return false;
};

Le.updateLeadFieldValues = function (field) {
    mQuery('.condition-custom-date-row').hide();
    Le.updateFieldOperatorValue(field, 'lead:updateLeadFieldValues', Le.updateLeadFieldValueOptions, [true]);
};

Le.updateLeadFieldValueOptions = function (field, updating) {
    var fieldId = mQuery(field).attr('id');
    var fieldPrefix = fieldId.slice(0, -5);

    if ('date' === mQuery('#'+fieldPrefix + 'operator').val()) {
        var customOption = mQuery(field).find('option[data-custom=1]');
        var value        = mQuery(field).val();

        var customSelected = mQuery(customOption).prop('selected');
        if (customSelected) {
            if (!updating) {
                // -/+ P/PT number unit
                var regex = /(\+|-)(PT?)([0-9]*)([DMHY])$/g;
                var match = regex.exec(value);
                if (match) {
                    var interval = ('-' === match[1]) ? match[1] + match[3] : match[3];
                    var unit = ('PT' === match[2] && 'M' === match[4]) ? 'i' : match[4];

                    mQuery('#lead-field-custom-date-interval').val(interval);
                    mQuery('#lead-field-custom-date-unit').val(unit.toLowerCase());
                }
            } else {
                var interval = mQuery('#lead-field-custom-date-interval').val();
                var unit = mQuery('#lead-field-custom-date-unit').val();

                // Convert interval/unit into PHP a DateInterval format
                var prefix = ("i" == unit || "h" == unit) ? "PT" : "P";
                // DateInterval uses M for minutes instead of i
                if ("i" === unit) {
                    unit = "m";
                }

                unit = unit.toUpperCase();

                var operator = "+";
                if (parseInt(interval) < 0) {
                    operator = "-";
                    interval = -1 * parseInt(interval);
                }
                var newValue = operator + prefix + interval + unit;
                customOption.attr('value', newValue);
            }
            mQuery('.condition-custom-date-row').show();
        } else {
            mQuery('.condition-custom-date-row').hide();
        }
    } else {
        mQuery('.condition-custom-date-row').hide();
    }
};

Le.toggleTimelineMoreVisiblity = function (el) {
    if (mQuery(el).is(':visible')) {
        mQuery(el).slideUp('fast');
        mQuery(el).next().text(mauticLang['showMore']);
    } else {
        mQuery(el).slideDown('fast');
        mQuery(el).next().text(mauticLang['hideMore']);
    }
};

Le.displayUniqueIdentifierWarning = function (el) {
    if (mQuery(el).val() === "0") {
        mQuery('.unique-identifier-warning').fadeOut('fast');
    } else {
        mQuery('.unique-identifier-warning').fadeIn('fast');
    }
};

Le.initUniqueIdentifierFields = function() {
    var uniqueFields = mQuery('[data-unique-identifier]');
    if (uniqueFields.length) {
        uniqueFields.on('change', function() {
            var input = mQuery(this);
            var request = {
                field: input.data('unique-identifier'),
                value: input.val(),
                ignore: mQuery('#lead_unlockId').val()
            };
            Le.ajaxActionRequest('lead:getLeadIdsByFieldValue', request, function(response) {
                if (response.items !== 'undefined' && response.items.length) {
                    var warning = mQuery('<div class="exists-warning" />').text(response.existsMessage);
                    mQuery.each(response.items, function(i, item) {
                        if (i > 0) {
                            warning.append(mQuery('<span>, </span>'));
                        }

                        var link = mQuery('<a/>')
                            .attr('href', item.link)
                            .attr('target', '_blank')
                            .text(item.name+' ('+item.id+')');
                        warning.append(link);
                    });
                    warning.appendTo(input.parent());
                } else {
                    input.parent().find('div.exists-warning').remove();
                }
            });
        });
    }
};

Le.updateFilterPositioning = function (el) {
    var $el = mQuery(el);
    var $parentEl = $el.closest('.panel');

    if ($el.val() == 'and') {
        $parentEl.addClass('in-group');
    } else {
        $parentEl.removeClass('in-group');
    }
};

Le.setAsPrimaryCompany = function (companyId,leadId){
    Le.ajaxActionRequest('lead:setAsPrimaryCompany', {'companyId': companyId, 'leadId': leadId}, function(response) {
        if (response.success) {
            if (response.oldPrimary == response.newPrimary && mQuery('#company-' + response.oldPrimary).hasClass('primary')) {
                mQuery('#company-' + response.oldPrimary).removeClass('primary');
            } else {
                mQuery('#company-' + response.oldPrimary).removeClass('primary');
                mQuery('#company-' + response.newPrimary).addClass('primary');
            }

        }
    });
}

Le.showOthersField = function (selectedval){
    if(selectedval == "Other"){
        mQuery('#kycinfo_others_label').addClass('required');
        mQuery('#kycinfo_others_label').css("display", "block");
        mQuery('#kycinfo_others').css("display", "block");
        mQuery('#kycinfo_others').prop('required',true);
    } else {
        mQuery('#kycinfo_others_label').removeClass('required');
        mQuery('#kycinfo_others_label').css("display", "none");
        mQuery('#kycinfo_others').css("display", "none");
        mQuery('#kycinfo_others').prop('required',false);
    }
}

Le.showMarketingOthersField = function (selectedval){
    if(selectedval == "Other"){
        mQuery('#marketing_others_label').addClass('required');
        mQuery('#marketing_others_label').css("display", "block");
        mQuery('#kycinfo_emailcontent').css("display", "block");
        mQuery('#kycinfo_emailcontent').prop('required',true);
    } else {
        mQuery('#marketing_others_label').removeClass('required');
        mQuery('#marketing_others_label').css("display", "none");
        mQuery('#kycinfo_emailcontent').css("display", "none");
        mQuery('#kycinfo_emailcontent').prop('required',false);
    }
}

Le.showGSTNumber = function (selectedval){
    //if(selectedval == "India"){
        //mQuery('.accountTimezone').val('Asia/Kolkata');
        //mQuery('#gstnumber_info').addClass('required');
       // mQuery('#gstnumber_info').css("display", "block");
       // mQuery('#billinginfo_gstnumber').css("display", "block");
        //mQuery('#billinginfo_gstnumber').prop('required',true);
   // } else {
        //mQuery('.accountTimezone').val('');
        //mQuery('#gstnumber_info').removeClass('required');
        mQuery('#gstnumber_info').css("display", "block");
        mQuery('#billinginfo_gstnumber').css("display", "block");
        //mQuery('#billinginfo_gstnumber').prop('required',false);
    //}
}
Le.exportLeads = function(){
    Le.ajaxActionRequest('lead:exportLead',{},function(response){});
}
