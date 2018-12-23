
Le.SVGNAMESPACEURI="http://www.w3.org/2000/svg";
Le.WF_TRIGGER_NODE_WIDTH_ADJUST=32;
Le.WF_TRIGGER_NODE_HEIGHT_ADJUST=49;
Le.WF_FORK_NODE_HEIGHT_ADJUST=40;
Le.WF_STEP_NODE_HEIGHT_ADJUST=20;
Le.WF_FORK_NODE_PATH_HEIGHT_ADJUST=150;
Le.WF_DECISION_NODE_PATH_HEIGHT_ADJUST=159;
Le.WF_FORK_DECISION_NODE_PATH_HEIGHT_CONSTANT=90;
Le.WF_TRIGGER_NODE_PATH_HEIGHT_CONSTANT=70;
Le.WF_TRIGGER_NODE_GAP_WIDTH_CONSTANT=16;
Le.WF_COUNT_NODE_HEIGHT_ADJUST=27;
Le.WF_NODE_PATH_HEIGHT_ADJUST=49;
Le.SVG_NODE_HEIGHT_ADJUST=200;
Le.WF_SHOW_STATSTICS=true;
Le.getActionNodeJSON=function(){
var json={
    "id":Le.randomString(32),
    "type":"action",
    "category":"action",
    "subcategory":"campaign.defaultaction",
    "view":{
        "label":"Define your action...",
        "incomplete":true
    }
};
return json;
};
Le.getDelayNodeJSON=function(){
    var json={
        "id":Le.randomString(32),
        "type":"delay",
        "category":"action",
        "subcategory":"campaign.defaultdelay",
        "view":{
            "label":"Define your delay...",
            "incomplete":true
        },
    };
    return json;
};
Le.getExitNodeJSON=function(){
    var json={
        "id":Le.randomString(32),
        "type":"exit",
        "category":"action",
        "subcategory":"campaign.defaultexit",
    };
    return json;
};
Le.getForkNodeJSON=function(){
    var json={
        "id":Le.randomString(32),
        "type":"fork",
        "paths":[
            {
                "id":Le.randomString(32),
                "type":"path",
                "triggers":[
                ],
                "steps":[
                ]
            },
            {
                "id":Le.randomString(32),
                "type":"path",
                "triggers":[
                ],
                "steps":[
                ]
            }
        ]
    };
    return json;
};
Le.getDecisionNodeJSON=function(){
    var json={
        "id":Le.randomString(32),
        "type":"decision",
        "view":{
            "label":"Define your condition...",
            "incomplete":true
        },
        "category":"condition",
        "subcategory":"lead.campaign_list_filter",
        "true_path":{
            "id":Le.randomString(32),
            "type":"path",
            "triggers":[

            ],
            "steps":[

            ]
        },
        "false_path":{
            "id":Le.randomString(32),
            "type":"path",
            "triggers":[

            ],
            "steps":[

            ]
        },
    };
    return json;
};
Le.getInterruptNodeJSON=function(){
    var json={
        "id":Le.randomString(32),
        "type":"interrupt",
        "triggers":[
            {
                "id":Le.randomString(32),
                "type":"trigger",
                "category":"source",
                "subcategory":"campaign.defaultsource",
                "entry_point":false,
                "view":{
                    "label":"Define your trigger...",
                    "incomplete":true
                }
            }
        ]
    };
    return json;
};
Le.getNewWorkFlowJSON=function(){
  var json={
      "id":Le.randomString(32),
      "type":"path",
      "triggers":[
          {
              "id":Le.randomString(32),
              "type":"trigger",
              "category":"source",
              "subcategory":"campaign.defaultsource",
              "entry_point":true,
              "view":{
                  "label":"Define your trigger...",
                  "incomplete":true
              }
          }
      ],
      "steps":[
          {
              "id":Le.randomString(32),
              "type":"exit",
              "category":"action",
              "subcategory":"campaign.defaultexit",
          }
      ]
  };
  return json;
};
Le.getNewPathJSON=function(){
var json= {
    "id":Le.randomString(32),
    "type":"path",
    "triggers":[

    ],
    "steps":[

    ]
};
return json;
};
Le.getNewTriggerNodeJSON=function(){
    var json=  {
        "id":Le.randomString(32),
        "type":"trigger",
        "category":"source",
        "subcategory":"campaign.defaultsource",
        "entry_point":true,
         "view":{
            "label":"Define your trigger...",
            "incomplete":true
        }
    };
    return json;
};
Le.getJSONByEventType=function(type){
if(type == 'action'){
 return Le.getActionNodeJSON();
}else if(type == 'decision'){
    return Le.getDecisionNodeJSON();
}else if(type == 'fork'){
    return Le.getForkNodeJSON();
}else if(type == 'goal'){
    return Le.getInterruptNodeJSON();
}else if(type == 'delay'){
    return Le.getDelayNodeJSON();
}else if(type == 'exit'){
    return Le.getExitNodeJSON();
}else {
    return {};
}
};
Le.campaignupdatedjson = {};
Le.lastclickedinsertpoint = {
    "id":'',
    "insertat":-1,
};
Le.lastclickedwfnode = {
    "id":'',
    "type":'',
    "eventType":'',
};
Le.campaignOnLoad = function (container, response) {
    if (mQuery(container + ' #list-search').length) {
        Le.activateSearchAutocomplete('list-search', 'campaign');
    }
    mQuery('#ui-tab-stat-header1').click(function(){
        mQuery('#ui-tab-stat-header1').addClass('btn-default ui-tabs-selected');
        mQuery('#ui-tab-stat-header2').removeClass('btn-default ui-tabs-selected');
        mQuery('#fragment-stat-1').removeClass('hide');
        mQuery('#fragment-stat-2').addClass('hide');

    });
    mQuery('#ui-tab-stat-header2').click(function(){
        mQuery('#ui-tab-stat-header2').addClass('btn-default ui-tabs-selected');
        mQuery('#ui-tab-stat-header1').removeClass('btn-default ui-tabs-selected');
        mQuery('#fragment-stat-2').removeClass('hide');
        mQuery('#fragment-stat-1').addClass('hide');

    });
    if (mQuery('.workflow-canvas').length) {
        Le.launchCampaignEditor();
    }
    mQuery('#campaign_buttons').addClass('hide');
    mQuery('.chosen-single').css("background","#fff");
    Le.removeActionButtons();
    if (mQuery('table.campaign-list').length) {
        mQuery('tr.wf-row-stats').each(function () {
            var id = mQuery(this).attr('data-stats');
            // Process the request one at a time or the xhr will cancel the previous
            Le.ajaxActionRequest(
                'campaign:getWorkFlowCountStats',
                {id: id},
                function (response) {
                    if (response.success && mQuery('#wf-progress-' + id + ' div').length) {
                        mQuery('#wf-progress-' + id + ' > a').html(response.progress);
                        mQuery('#wf-completed-' + id + ' > a').html(response.completed);
                        mQuery('#wf-goal-' + id + ' > a').html(response.goals);
                    }
                },
                false,
                true
            );
        });
    }
};

Le.getNodeElement = function(rootelement,type,id,width,label,incomplete,lastnode){
    mQuery('.le-modal-box-align').attr("style","margin-left:18%");
var gelement = document.createElementNS(Le.SVGNAMESPACEURI,"g");
if(incomplete){
    label='Define your '+type+'...';
    gelement.setAttributeNS(null, "class", 'wf-'+type+' incomplete');
}else{
    gelement.setAttributeNS(null, "class", 'wf-'+type);
}
gelement.setAttributeNS(null, "id", type+'-'+ id);
gelement.setAttributeNS(null, "width", width);
if(type != 'interrupt'){
    var gelement1 = document.createElementNS(Le.SVGNAMESPACEURI,"g");
    gelement1.setAttributeNS(null, "class", 'wf-node-labels');
    var gelement2 = document.createElementNS(Le.SVGNAMESPACEURI,"g");
    gelement2.setAttributeNS(null, "class", 'wf-'+type+'-primary-label wf-node-primary-label');
    gelement2.setAttributeNS(null, "transform", 'translate(0,0)');
    var gelement3 = document.createElementNS(Le.SVGNAMESPACEURI,"g");
    gelement3.setAttributeNS(null, "class", 'wf-label-wrap');
    if(type == 'fork'){
        gelement3.setAttributeNS(null, "transform", 'translate(10,10)');
    }else{
        gelement3.setAttributeNS(null, "transform", 'translate(16,16)');
    }
    var textelement = document.createElementNS(Le.SVGNAMESPACEURI,"text");
    textelement.setAttributeNS(null, "class", 'wf-label');
    textelement.setAttributeNS(null, "y", '12');
    textelement.setAttributeNS(null, "dy", '0');
    var rectwidth=40;
    var rectheight=Le.WF_TRIGGER_NODE_HEIGHT_ADJUST;
    if(type == 'fork'){
        rectheight=Le.WF_FORK_NODE_HEIGHT_ADJUST;
        gelement3.appendChild(textelement);
        var forkpathelement = document.createElementNS(Le.SVGNAMESPACEURI,"path");
        forkpathelement.setAttributeNS(null, "class", 'wf-fork-icon');
        forkpathelement.setAttributeNS(null, "d", 'M19.9,15.7L17.1,16C16.9,9.5,14.4,7.5,13,5.6C11.6,3.7,11.7,0,11.7,0h0H8.3h0c0,0,0.1,3.7-1.3,5.6C5.6,7.5,3.1,9.5,2.9,16 l-2.7-0.2C0,15.7,0,15.8,0,15.9l2.2,2l2.2,2c0.1,0.1,0.2,0.1,0.3,0l2.2-2l2.2-2c0.1-0.1,0-0.2-0.1-0.2L6.2,16 c0.2-5.9,2.4-7.2,3.8-8.9c1.4,1.6,3.6,3,3.8,8.9l-2.9-0.3c-0.1,0-0.2,0.1-0.1,0.2l2.2,2l2.2,2c0.1,0.1,0.2,0.1,0.3,0l2.2-2l2.2-2 C20,15.8,20,15.7,19.9,15.7z');
        forkpathelement.setAttributeNS(null, "fill-rule", 'evenodd');
        gelement3.appendChild(forkpathelement);
    }else{
        var tspanelement = document.createElementNS(Le.SVGNAMESPACEURI,"tspan");
        tspanelement.setAttributeNS(null, "x", '0');
        tspanelement.setAttributeNS(null, "y", '12');
        tspanelement.setAttributeNS(null, "dy", '0em');
        tspanelement.textContent=label;
        textelement.appendChild(tspanelement);
        gelement3.appendChild(textelement);
        rootelement.appendChild(gelement3);
        var bcr = gelement3.getBoundingClientRect();
        rectwidth=bcr.width+Le.WF_TRIGGER_NODE_WIDTH_ADJUST;
        gelement3.parentNode.removeChild(gelement3);
    }
    var rectelement = document.createElementNS(Le.SVGNAMESPACEURI,"rect");
    rectelement.setAttributeNS(null, "class", 'wf-enclosure');
    rectelement.setAttributeNS(null, "x", '0');
    rectelement.setAttributeNS(null, "y", '0');
    rectelement.setAttributeNS(null, "rx", '4');
    rectelement.setAttributeNS(null, "ry", '4');
    rectelement.setAttributeNS(null, "width", rectwidth);
    rectelement.setAttributeNS(null, "height", rectheight);
        gelement.setAttributeNS(null, "width", rectwidth);
        gelement1.setAttributeNS(null, "width", rectwidth);
    gelement2.appendChild(rectelement);
    gelement2.appendChild(gelement3);
   // gelement2.appendChild(gelement3);
    gelement1.appendChild(gelement2);
    if(type != 'fork' && type != 'decision' && Le.WF_SHOW_STATSTICS){
       gelement1.appendChild(Le.getCountNode(rootelement,rectwidth,type,id));
    }
    var removenodexposition=0;
    if(type != 'trigger'){
        rootelement.appendChild(gelement1);
        var bcr = gelement1.getBoundingClientRect();
        var xposition=(width-(rectwidth))/2;
        removenodexposition=(xposition + +rectwidth)-8;
        gelement1.setAttributeNS(null, "transform", 'translate('+xposition+',20)');
        gelement1.parentNode.removeChild(gelement1);
    }
    if(type != 'exit' && type != 'fork'){
        Le.registerClickListener(gelement1);
    }
    Le.registerMouseListener(gelement1);
    gelement.appendChild(gelement1);
    var needremove=false;
    if(type == 'exit'){
        var rootparentel=rootelement.parentNode.parentNode;
        var rootparenttag=rootparentel.tagName;
        if(rootparenttag == 'svg' && lastnode){
            needremove=true;
        }
    }
    if(type != 'exit' || (type == 'exit' && !needremove)){
        var removeelement = document.createElementNS(Le.SVGNAMESPACEURI,"g");
        removeelement.setAttributeNS(null, "class", 'wf-remove-button wf-remove-node-button');
        removeelement.setAttributeNS(null, "transform", 'translate('+(removenodexposition)+',12)');
        var circlelement = document.createElementNS(Le.SVGNAMESPACEURI,"circle");
        circlelement.setAttributeNS(null, "class", 'wf-remove-button-enclosure');
        circlelement.setAttributeNS(null, "r", '8');
        circlelement.setAttributeNS(null, "cx", '8');
        circlelement.setAttributeNS(null, "cy", '8');
        var lineelement1 = document.createElementNS(Le.SVGNAMESPACEURI,"line");
        lineelement1.setAttributeNS(null, "class", 'wf-x-bar');
        lineelement1.setAttributeNS(null, "x1", '5');
        lineelement1.setAttributeNS(null, "y1", '5');
        lineelement1.setAttributeNS(null, "x2", '11');
        lineelement1.setAttributeNS(null, "y2", '11');
        var lineelement2 = document.createElementNS(Le.SVGNAMESPACEURI,"line");
        lineelement2.setAttributeNS(null, "class", 'wf-x-bar');
        lineelement2.setAttributeNS(null, "x1", '11');
        lineelement2.setAttributeNS(null, "y1", '5');
        lineelement2.setAttributeNS(null, "x2", '5');
        lineelement2.setAttributeNS(null, "y2", '11');
        removeelement.appendChild(circlelement);
        removeelement.appendChild(lineelement1);
        removeelement.appendChild(lineelement2);
        Le.registerClickListener(removeelement);
        Le.registerMouseListener(removeelement);
        gelement.appendChild(removeelement);
    }
    if(type != 'fork'){
        gelement.appendChild(Le.getInfoNode(rootelement,type,xposition));
    }
    if(type == 'fork'){
        var finsertionpoint=Le.getTriggerInsertionPointNode(id,'fork');
        finsertionpoint.setAttributeNS(null,'transform','translate('+(removenodexposition + +18 )+',32)')
        gelement.appendChild(finsertionpoint);
    }
}
return gelement;
}
Le.getInfoNode=function(rootelement,type,xposition){
    var icontype=type;
    if(type == 'trigger'){
        var rootclass=rootelement.getAttributeNS(null,'class');
        if(rootclass == 'wf-interrupt'){
            icontype='goal';
        }
    }
    var inforectelement = document.createElementNS(Le.SVGNAMESPACEURI,"rect");
    inforectelement.setAttributeNS(null, "class", 'wf-enclosure');
    inforectelement.setAttributeNS(null, "x", '-2');
    inforectelement.setAttributeNS(null, "y", '-2');
    inforectelement.setAttributeNS(null, "rx", '0');
    inforectelement.setAttributeNS(null, "ry", '0');
    inforectelement.setAttributeNS(null, "width", '20');
    inforectelement.setAttributeNS(null, "height", '20');
    var infoelement = document.createElementNS(Le.SVGNAMESPACEURI,"g");
    infoelement.setAttributeNS(null, "class", 'wf-info-button wf-'+type+'-info-node-button');
    var infox=-10;
    var infoy=18;
    if(xposition > 0){
        infox=xposition-10;
        infoy=36;
    }
    infoelement.setAttributeNS(null, "transform", 'translate('+infox+','+infoy+')');
    var infopathelement=document.createElementNS(Le.SVGNAMESPACEURI,"path");
    infopathelement.setAttributeNS(null,'d',Le.getIconPathDimensionByType(icontype));
    infoelement.appendChild(inforectelement);
    infoelement.appendChild(infopathelement);
    return infoelement;
}

Le.getCountNode=function(rootelement,nodewidth,type,id){
    var label='0 done';
    if(type == 'trigger'){
    if(Le.campaignBuilderStatistics[id]){
         label=Le.campaignBuilderStatistics[id].total;
    }else{
        label='0';
    }
    var rootclass=rootelement.getAttributeNS(null,'class');
            if(rootclass == 'wf-interrupt'){
                label=label + ' achieved';
            }else{
                label=label + ' triggered';
            }
    }else{
        var current=0;
        var done=0;
        if(Le.campaignBuilderStatistics[id]){
            current=Le.campaignBuilderStatistics[id].current;
            done=Le.campaignBuilderStatistics[id].done;
        }
        if(type == 'delay'){
            label=current+' scheduled / '+done+' done';
        }else{
            label=done+' done';
        }
    }
    var textelement = document.createElementNS(Le.SVGNAMESPACEURI,"text");
    textelement.setAttributeNS(null, "class", 'wf-label');
    textelement.setAttributeNS(null, "y", '12');
    textelement.setAttributeNS(null, "dy", '0');
    var tspanelement = document.createElementNS(Le.SVGNAMESPACEURI,"tspan");
    tspanelement.setAttributeNS(null, "x", '0');
    tspanelement.setAttributeNS(null, "y", '12');
    tspanelement.setAttributeNS(null, "dy", '0em');
    tspanelement.textContent=label;
    textelement.appendChild(tspanelement);
    var gelement = document.createElementNS(Le.SVGNAMESPACEURI,"g");
    gelement.setAttributeNS(null, "class", 'wf-label-wrap');
    gelement.setAttributeNS(null, "transform", 'translate(6,6)');
    gelement.appendChild(textelement);
    rootelement.appendChild(gelement);
    var bcr = gelement.getBoundingClientRect();
    var rectwidth=bcr.width;
    gelement.parentNode.removeChild(gelement);
    var rectelement = document.createElementNS(Le.SVGNAMESPACEURI,"rect");
    rectelement.setAttributeNS(null, "class", 'wf-enclosure');
    rectelement.setAttributeNS(null, "x", '0');
    rectelement.setAttributeNS(null, "y", '0');
    rectelement.setAttributeNS(null, "rx", '4');
    rectelement.setAttributeNS(null, "ry", '4');
    rectelement.setAttributeNS(null, "width", rectwidth);
    rectelement.setAttributeNS(null, "height", Le.WF_COUNT_NODE_HEIGHT_ADJUST);
    var gelement1 = document.createElementNS(Le.SVGNAMESPACEURI,"g");
    gelement1.setAttributeNS(null, "class", 'wf-node-count-label');
    gelement1.appendChild(rectelement);
    gelement1.appendChild(gelement);
    var xposition=(nodewidth-rectwidth)/2;
    gelement1.setAttributeNS(null, "transform", 'translate('+xposition+',51)');
    return gelement1;
}
Le.getIconPathDimensionByType=function(type){
    if(type == 'trigger'){
     return 'M9.25 7c0 0.133-0.055 0.258-0.148 0.352l-4.25 4.25c-0.094 0.094-0.219 0.148-0.352 0.148-0.273 0-0.5-0.227-0.5-0.5v-2.25h-3.5c-0.273 0-0.5-0.227-0.5-0.5v-3c0-0.273 0.227-0.5 0.5-0.5h3.5v-2.25c0-0.273 0.227-0.5 0.5-0.5 0.133 0 0.258 0.055 0.352 0.148l4.25 4.25c0.094 0.094 0.148 0.219 0.148 0.352zM12 4.25v5.5c0 1.242-1.008 2.25-2.25 2.25h-2.5c-0.133 0-0.25-0.117-0.25-0.25 0-0.219-0.102-0.75 0.25-0.75h2.5c0.688 0 1.25-0.563 1.25-1.25v-5.5c0-0.688-0.563-1.25-1.25-1.25h-2.25c-0.195 0-0.5 0.039-0.5-0.25 0-0.219-0.102-0.75 0.25-0.75h2.5c1.242 0 2.25 1.008 2.25 2.25z';
    }else if(type == 'action'){
        return 'M10.914 4.422c0.086 0.094 0.109 0.227 0.055 0.344l-4.219 9.039c-0.062 0.117-0.187 0.195-0.328 0.195-0.031 0-0.070-0.008-0.109-0.016-0.172-0.055-0.273-0.219-0.234-0.383l1.539-6.312-3.172 0.789c-0.031 0.008-0.062 0.008-0.094 0.008-0.086 0-0.18-0.031-0.242-0.086-0.094-0.078-0.125-0.195-0.102-0.305l1.57-6.445c0.039-0.148 0.18-0.25 0.344-0.25h2.563c0.195 0 0.352 0.148 0.352 0.328 0 0.047-0.016 0.094-0.039 0.141l-1.336 3.617 3.094-0.766c0.031-0.008 0.062-0.016 0.094-0.016 0.102 0 0.195 0.047 0.266 0.117z';
    }else if(type == 'decision'){
        return 'M7 11h2v2h-2zM11 4c0.552 0 1 0.448 1 1v3l-3 2h-2v-1l3-2v-1h-5v-2h6zM8 1.5c-1.736 0-3.369 0.676-4.596 1.904s-1.904 2.86-1.904 4.596c0 1.736 0.676 3.369 1.904 4.596s2.86 1.904 4.596 1.904c1.736 0 3.369-0.676 4.596-1.904s1.904-2.86 1.904-4.596c0-1.736-0.676-3.369-1.904-4.596s-2.86-1.904-4.596-1.904zM8 0v0c4.418 0 8 3.582 8 8s-3.582 8-8 8c-4.418 0-8-3.582-8-8s3.582-8 8-8z';
    }else if(type == 'delay'){
        return 'M10.293 11.707l-3.293-3.293v-4.414h2v3.586l2.707 2.707zM8 0c-4.418 0-8 3.582-8 8s3.582 8 8 8 8-3.582 8-8-3.582-8-8-8zM8 14c-3.314 0-6-2.686-6-6s2.686-6 6-6c3.314 0 6 2.686 6 6s-2.686 6-6 6z';
    }else if(type == 'exit'){
        return 'M5 11.25c0 0.219 0.102 0.75-0.25 0.75h-2.5c-1.242 0-2.25-1.008-2.25-2.25v-5.5c0-1.242 1.008-2.25 2.25-2.25h2.5c0.133 0 0.25 0.117 0.25 0.25 0 0.219 0.102 0.75-0.25 0.75h-2.5c-0.688 0-1.25 0.563-1.25 1.25v5.5c0 0.688 0.563 1.25 1.25 1.25h2.25c0.195 0 0.5-0.039 0.5 0.25zM12.25 7c0 0.133-0.055 0.258-0.148 0.352l-4.25 4.25c-0.094 0.094-0.219 0.148-0.352 0.148-0.273 0-0.5-0.227-0.5-0.5v-2.25h-3.5c-0.273 0-0.5-0.227-0.5-0.5v-3c0-0.273 0.227-0.5 0.5-0.5h3.5v-2.25c0-0.273 0.227-0.5 0.5-0.5 0.133 0 0.258 0.055 0.352 0.148l4.25 4.25c0.094 0.094 0.148 0.219 0.148 0.352z';
    }else if(type == 'goal'){
        return 'M13 3v-2h-10v2h-3v2c0 1.657 1.343 3 3 3 0.314 0 0.616-0.048 0.9-0.138 0.721 1.031 1.822 1.778 3.1 2.037v3.1h-1c-1.105 0-2 0.895-2 2h8c0-1.105-0.895-2-2-2h-1v-3.1c1.278-0.259 2.378-1.006 3.1-2.037 0.284 0.089 0.587 0.138 0.9 0.138 1.657 0 3-1.343 3-3v-2h-3zM3 6.813c-0.999 0-1.813-0.813-1.813-1.813v-1h1.813v1c0 0.628 0.116 1.229 0.327 1.782-0.106 0.019-0.216 0.030-0.327 0.030zM14.813 5c0 0.999-0.813 1.813-1.813 1.813-0.112 0-0.221-0.011-0.327-0.030 0.211-0.554 0.327-1.154 0.327-1.782v-1h1.813v1z';
    }
}
Le.updateTriggerPath=function (rootelement) {
    var rootfixedwidth=rootelement.getAttributeNS(null,'width');
    var childrens=rootelement.children;
    var totalwidth=0;
    for(var ch=0;ch<childrens.length;ch++){
    var child=childrens[ch];
    var bcr=child.getBoundingClientRect();
    totalwidth+=bcr.width;
    }
    var posistionadjust=-4;
    if(childrens.length > 1){
        posistionadjust=posistionadjust+(Le.WF_TRIGGER_NODE_GAP_WIDTH_CONSTANT*(childrens.length-1));
    }
    totalwidth=totalwidth+ +posistionadjust;
    var startposition=(rootfixedwidth-totalwidth)/2;
    var nextposition=startposition;
    for(var ch=0;ch<childrens.length;ch++){
        var child=childrens[ch];
        var bcr=child.getBoundingClientRect();
        child.setAttributeNS(null, "transform", 'translate('+nextposition+',0)');
        var removenode=child.children[1];
        removenode.setAttributeNS(null, "transform", 'translate('+(bcr.width-20)+',-8)');
        nextposition=(+nextposition + +bcr.width);
        nextposition=nextposition + +Le.WF_TRIGGER_NODE_GAP_WIDTH_CONSTANT;
    }
    var insertnode=rootelement.parentNode.children[rootelement.parentNode.children.length-1];
    var rootnodematrix={};
    if(typeof(rootelement.transform.baseVal.length)  === "undefined")
    {
        rootnodematrix=rootelement.transform.baseVal.getItem(0).matrix;//for ios safari browswer
    }else{
        rootnodematrix=rootelement.transform.baseVal[0].matrix;
    }
    insertnode.setAttributeNS(null, "transform", 'translate('+nextposition+','+(rootnodematrix.f + +14)+')');
    var paths=[];
    for(var ch=0;ch<childrens.length;ch++){
        var child=childrens[ch];
        var matrix={};
        if(typeof(child.transform.baseVal.length)  === "undefined")
        {
            matrix=child.transform.baseVal.getItem(0).matrix;//for ios safari browswer
        }else{
            matrix=child.transform.baseVal[0].matrix;
        }
        var bcr=child.getBoundingClientRect();
        var xposition=matrix.e;
        //alert("X:"+xposition+",Width:"+(bcr.width));
        var mx=((+(bcr.width)/2) + +xposition);
        mx=mx-6;
        var x1=mx;
        var x2=rootfixedwidth/2;
        var x=x2;
        var my=Le.WF_NODE_PATH_HEIGHT_ADJUST;
        var y= +my + +Le.WF_TRIGGER_NODE_PATH_HEIGHT_CONSTANT;
        var y1=(+my + +y)/2;
        var y2=y1;
        var dimension="M"+mx+","+my+"C"+x1+","+y1+" "+x2+","+y2+" "+x+","+y;
        var pathelement = document.createElementNS(Le.SVGNAMESPACEURI,"path");
        pathelement.setAttributeNS(null, "class", 'wf-link');
        pathelement.setAttributeNS(null, "d", dimension);
        paths.push(pathelement);
    }
    for(var p=0;p<paths.length;p++){
        rootelement.appendChild(paths[p]);
    }
}
Le.removeAllByTags=function(element,tag){
    var tags=element.getElementsByTagName(tag);
    while(tags.length > 0){
        element.removeChild(tags[0]);
        tags=element.getElementsByTagName(tag);
    }
}
Le.randomString=function (length) {
    return Math.round((Math.pow(36, length + 1) - Math.random() * Math.pow(36, length))).toString(36).slice(1);
}

Le.iterateJSONOBJECT = function(data,width,parent,tracker,lastnode){
    var id=data.id;
    var type=data.type;
    if(type == 'path'){
        var triggers=data.triggers;
        var steps=data.steps;
        var pathnode=Le.getPathNode(id,width);
        var triggernode=Le.getTriggersNode(id,width);
        var stepsnode=Le.getStepsNode(id,width);
        parent.appendChild(pathnode);
        pathnode.appendChild(triggernode);
        pathnode.appendChild(stepsnode);
        if(triggers.length > 0){
            pathnode.appendChild(Le.getTriggerInsertionPointNode(id,'trigger'));
        }
        Le.setTransformAttr(triggernode,0,mQuery.isEmptyObject(triggers) ? 0: Le.WF_STEP_NODE_HEIGHT_ADJUST);
        if(!mQuery.isEmptyObject(triggers)){
            mQuery.each(triggers, function (index, trigger) {
                triggernode.appendChild(Le.getNodeElement(pathnode,trigger.type,trigger.id,width,trigger.view.label,trigger.view.incomplete,(index == triggers.length-1)));
            });
            Le.updateTriggerPath(triggernode);
        }
        var triggerbcr=triggernode.getBoundingClientRect();
        Le.setTransformAttr(stepsnode,0,mQuery.isEmptyObject(triggers) ? 0:(triggerbcr.height + +12));
        if(!mQuery.isEmptyObject(steps)){
            var stepsindex=0;
            mQuery.each(steps, function (index, event) {
                    stepsnode.appendChild(Le.getStepNode(stepsnode,Le.randomString(32),width,'insertion-point','',false,lastnode));
                    tracker=Le.iterateJSONOBJECT(event,width,stepsnode,tracker,(index == steps.length-1));
                    if(event.type != 'exit' && stepsindex == steps.length-1 ){
                        stepsnode.appendChild(Le.getStepNode(stepsnode,Le.randomString(32),width,'insertion-point','',false,lastnode));
                    }
                stepsindex++;
            });
            var yposition=Le.updateStepsPosition(stepsnode);
            if(parent.tagName == 'svg'){
                Le.adjustSizeforSVG(parent,stepsnode,yposition);
            }
        }else{
            tracker+="insert point alone"+"----->"+width+"\n";
            stepsnode.appendChild(Le.getStepNode(stepsnode,Le.randomString(32),width,'insertion-point','',false,lastnode));
        }
    }else if(type == 'action'){
        tracker+=data.view.label+"----->"+width+"\n";
        parent.appendChild(Le.getStepNode(parent,id,width,type,data.view.label,data.view.incomplete,lastnode));
    }else if(type == 'decision'){
        tracker+=data.description+"----->"+width+"\n";
        var stepnode=Le.getStepNode(parent,id,width,type,data.view.label,data.view.incomplete,lastnode);
        var decisionnode=stepnode.children[0];
        parent.appendChild(stepnode);
        var decisionnodewidth=width/2;
        var truepath=data.true_path;
        var falsepath=data.false_path;
        var truepathnode=Le.getWfPathNode(truepath.id,decisionnodewidth,'true');
        var falsepathnode=Le.getWfPathNode(falsepath.id,decisionnodewidth,'false');
        Le.setTransformAttr(truepathnode,0,Le.WF_DECISION_NODE_PATH_HEIGHT_ADJUST);
        Le.setTransformAttr(falsepathnode,decisionnodewidth,Le.WF_DECISION_NODE_PATH_HEIGHT_ADJUST);
        decisionnode.appendChild(truepathnode);
        decisionnode.appendChild(falsepathnode);
        tracker= Le.iterateJSONOBJECT(truepath,decisionnodewidth,truepathnode,tracker,lastnode);
        tracker=Le.iterateJSONOBJECT(falsepath,decisionnodewidth,falsepathnode,tracker,lastnode);
    }else if(type == 'delay'){
        tracker+=data.description+"----->"+width+"\n";
        parent.appendChild(Le.getStepNode(parent,id,width,type,data.view.label,data.view.incomplete,lastnode));
    }else if(type == 'exit'){
        tracker+="exit called"+"----->"+width+"\n";
        parent.appendChild(Le.getStepNode(parent,id,width,type,'Exit',false,lastnode));
    }else if(type == 'fork'){
        tracker+="fork called"+"----->"+width+"\n";
        var stepnode=Le.getStepNode(parent,id,width,type,'Fork',false,lastnode);
        var forknode=stepnode.children[0];
        parent.appendChild(stepnode);
        var paths=data.paths;
        var forkxposition=0;
        mQuery.each(paths, function (index, value) {
            var forknodewidth=width/paths.length;
            var forkpathnode=Le.getWfPathNode(value.id,forknodewidth,'fork');
            Le.setTransformAttr(forkpathnode,forkxposition,Le.WF_FORK_NODE_PATH_HEIGHT_ADJUST);
            forkxposition=forkxposition+forknodewidth;
            forknode.appendChild(forkpathnode);
            tracker=Le.iterateJSONOBJECT(value,forknodewidth,forkpathnode,tracker,lastnode);
        });
    }else if(type == 'interrupt'){
        tracker+="interrupt called"+"----->"+width+"\n";
        var stepnode=Le.getStepNode(parent,id,width,type,'',false,lastnode);
        parent.appendChild(stepnode);
        var interruptnode=stepnode.children[0];
        var triggernode=Le.getTriggersNode(id,width);
        Le.setTransformAttr(triggernode,0,Le.WF_STEP_NODE_HEIGHT_ADJUST + +20);
        interruptnode.appendChild(triggernode);
        var triggers=data.triggers;
        if(triggers.length > 0){
            interruptnode.appendChild(Le.getTriggerInsertionPointNode(id,'trigger'));
        }
        if(!mQuery.isEmptyObject(triggers)){
            mQuery.each(triggers, function (index, trigger) {
                triggernode.appendChild(Le.getNodeElement(interruptnode,trigger.type,trigger.id,width,trigger.view.label,trigger.view.incomplete,(index == triggers.length-1)));
            });
            Le.updateTriggerPath(triggernode);
        }
    }
   // if(id == '0496a4807ec10136a81512a5241a0474'){
  //alert(tracker);
    // }
    return tracker;
}
Le.getPathNode = function(id,width){
    var path = document.createElementNS(Le.SVGNAMESPACEURI,"g");
    path.setAttributeNS(null, "class", 'path');
    path.setAttributeNS(null, "id", 'path'+'-'+id);
    path.setAttributeNS(null, "width", width);
    return path;
}
Le.getWfPathNode = function(id,width,type){
    var path = document.createElementNS(Le.SVGNAMESPACEURI,"g");
    path.setAttributeNS(null, "class", 'wf-'+type+'-path');
    path.setAttributeNS(null, "id", type+'-path'+'-'+id);
    path.setAttributeNS(null, "width", width);
    return path;
}
Le.getStepsNode = function(id,width){
    var steps = document.createElementNS(Le.SVGNAMESPACEURI,"g");
    steps.setAttributeNS(null, "class", 'steps');
    steps.setAttributeNS(null, "id", 'steps'+'-'+id);
    steps.setAttributeNS(null, "width", width);
    return steps;
}
Le.getTriggersNode = function(id,width){
    var triggers = document.createElementNS(Le.SVGNAMESPACEURI,"g");
    triggers.setAttributeNS(null, "class", 'triggers');
    triggers.setAttributeNS(null, "id", 'triggers'+'-'+id);
    triggers.setAttributeNS(null, "width", width);
    return triggers;
}
Le.getStepNode = function(parent,id,width,type,label,incomplete,lastnode){
    var step = document.createElementNS(Le.SVGNAMESPACEURI,"g");
    step.setAttributeNS(null, "class", 'step');
    step.setAttributeNS(null, "id", 'step'+'-'+id);
    step.setAttributeNS(null, "width", width);
    var child=null;
    if(type == 'insertion-point'){
        child=Le.getInsertionPointNode(id,width,label);
    }else{
        child=Le.getNodeElement(parent,type,id,width,label,incomplete,lastnode);
    }
    step.appendChild(child);
    return step;
}
Le.getInsertionPointNode=function(id,width){
    var xposition=width/2;
    xposition=xposition - 8;
    var ip = document.createElementNS(Le.SVGNAMESPACEURI,"g");
    ip.setAttributeNS(null, "class", 'wf-insertion-point');
    ip.setAttributeNS(null, "id", 'insertion-point'+'-'+id);
    ip.setAttributeNS(null, "width", width);
    ip.setAttributeNS(null, "transform", 'translate('+xposition+',0)');
    var circlelement = document.createElementNS(Le.SVGNAMESPACEURI,"circle");
    circlelement.setAttributeNS(null, "class", 'wf-insert-button');
    circlelement.setAttributeNS(null, "r", '8');
    circlelement.setAttributeNS(null, "cx", '8');
    circlelement.setAttributeNS(null, "cy", '8');
    var lineelement1 = document.createElementNS(Le.SVGNAMESPACEURI,"line");
    lineelement1.setAttributeNS(null, "class", 'wf-plus-bar');
    lineelement1.setAttributeNS(null, "x1", '4');
    lineelement1.setAttributeNS(null, "y1", '8');
    lineelement1.setAttributeNS(null, "x2", '12');
    lineelement1.setAttributeNS(null, "y2", '8');
    var lineelement2 = document.createElementNS(Le.SVGNAMESPACEURI,"line");
    lineelement2.setAttributeNS(null, "class", 'wf-plus-bar');
    lineelement2.setAttributeNS(null, "x1", '8');
    lineelement2.setAttributeNS(null, "y1", '4');
    lineelement2.setAttributeNS(null, "x2", '8');
    lineelement2.setAttributeNS(null, "y2", '12');
    ip.appendChild(circlelement);
    ip.appendChild(lineelement1);
    ip.appendChild(lineelement2);
    Le.registerClickListener(ip);
    return ip;
}
Le.getTriggerInsertionPointNode=function(id,type){
    var ip = document.createElementNS(Le.SVGNAMESPACEURI,"g");
    ip.setAttributeNS(null, "class", type+'-insertion-point insertion-point');
    ip.setAttributeNS(null, "id", type+'-insertion-point'+'-'+id);
    ip.setAttributeNS(null, "transform", 'translate(0,0)');
    var circlelement = document.createElementNS(Le.SVGNAMESPACEURI,"circle");
    circlelement.setAttributeNS(null, "class", 'wf-insert-button');
    circlelement.setAttributeNS(null, "r", '8');
    circlelement.setAttributeNS(null, "cx", '8');
    circlelement.setAttributeNS(null, "cy", '8');
    var lineelement1 = document.createElementNS(Le.SVGNAMESPACEURI,"line");
    lineelement1.setAttributeNS(null, "class", 'wf-plus-bar');
    lineelement1.setAttributeNS(null, "x1", '4');
    lineelement1.setAttributeNS(null, "y1", '8');
    lineelement1.setAttributeNS(null, "x2", '12');
    lineelement1.setAttributeNS(null, "y2", '8');
    var lineelement2 = document.createElementNS(Le.SVGNAMESPACEURI,"line");
    lineelement2.setAttributeNS(null, "class", 'wf-plus-bar');
    lineelement2.setAttributeNS(null, "x1", '8');
    lineelement2.setAttributeNS(null, "y1", '4');
    lineelement2.setAttributeNS(null, "x2", '8');
    lineelement2.setAttributeNS(null, "y2", '12');
    ip.appendChild(circlelement);
    ip.appendChild(lineelement1);
    ip.appendChild(lineelement2);
    Le.registerClickListener(ip);
    return ip;
}

Le.setTransformAttr=function(node,x,y){
    node.setAttributeNS(null, "transform", 'translate('+x+','+y+')');
}

Le.updateStepsPosition=function(steps){
    var childrens=steps.children;
    var yposition=0;
    mQuery.each(childrens, function (index, child) {
        var wfnode=child.children[0];
        var classname=wfnode.getAttributeNS(null,'class');
        if(classname != 'wf-insertion-point'){
            var id=wfnode.getAttributeNS(null,'id');
            var response=id.split('-');
            var type=response[0];
            var width=child.getAttributeNS(null,'width');
            var lastchild=(index == childrens.length-1);
            var xposition=width/2;
            if(type != 'interrupt'){
                var pathsplit=Le.getPathConnectorNode('split','M'+xposition+',0C'+xposition+',10 '+xposition+',10 '+xposition+',20');
                child.appendChild(pathsplit);
                if((type != 'decision' && type != 'fork' && type != 'exit') || (type == 'exit' && !lastchild)){
                    var my=Le.WF_NODE_PATH_HEIGHT_ADJUST + +Le.WF_STEP_NODE_HEIGHT_ADJUST;
                    var y=Le.WF_NODE_PATH_HEIGHT_ADJUST + +(Le.WF_STEP_NODE_HEIGHT_ADJUST*2);
                    var y1=(my+ +y)/2;
                    var y2=y1;
                    var pathlink=Le.getPathConnectorNode('link','M'+xposition+','+my+'C'+xposition+','+y1+' '+xposition+','+y2+' '+xposition+','+y+'');
                    child.appendChild(pathlink);
                }else if(type == 'decision'){
                    Le.updateDecisionPath(child);
                }else if(type == 'fork'){
                    Le.updateForkPath(child);
                }
            }else{
                var wfnode=child.children[0];
            var horizontalbar=Le.getLineConnectorNode('break-bar',xposition-10,20,(xposition + +10),20);
            var verticalbar=Le.getLineConnectorNode('break-bar',xposition,0,xposition,20);
                wfnode.appendChild(verticalbar);
                wfnode.appendChild(horizontalbar);
            }

        }
        Le.setTransformAttr(child,0,yposition);
        var bcr=child.getBoundingClientRect();
        yposition=yposition+bcr.height;
    });
    return yposition;
}
Le.updateDecisionPath=function(stepnode){
    var wfnode=stepnode.children[0];
    var width=stepnode.getAttributeNS(null,'width');
    var avgwidth=width/4;
    var wfnodes=Le.filterWfNodeByClass(wfnode,'wf-true-path');
    var truepathnode=wfnodes[0];
    var wfnodes=Le.filterWfNodeByClass(wfnode,'wf-false-path');
    var falsepathnode=wfnodes[0];
    var tpbcr=truepathnode.getBoundingClientRect();
    var fpbcr=falsepathnode.getBoundingClientRect();
   var tpheight=tpbcr.height;
   var fpheight=fpbcr.height;
   var maxheight=Math.max(tpheight, fpheight);

    var tpmy=tpheight+ +Le.WF_DECISION_NODE_PATH_HEIGHT_ADJUST;
    var tpy=maxheight+ +(Le.WF_DECISION_NODE_PATH_HEIGHT_ADJUST + +Le.WF_FORK_DECISION_NODE_PATH_HEIGHT_CONSTANT);
    var tpy1=(tpmy + +tpy)/2;
    var tpy2=tpy1;

    var fpmy=fpheight+ +Le.WF_DECISION_NODE_PATH_HEIGHT_ADJUST;
    var fpy=tpy;
    var fpy1=(fpmy + +fpy)/2;
    var fpy2=fpy1;

    var tpmx=avgwidth;
    var tpx1=tpmx;
    var tpx2=avgwidth*2;
    var tpx=tpx2;

    var fpmx=avgwidth*3;
    var fpx1=fpmx;
    var fpx2=avgwidth*2;
    var fpx=fpx2;


if(Le.isLinkPathRequired(truepathnode)){
    var tpathlink=Le.getPathConnectorNode('link','M'+tpmx+','+tpmy+'C'+tpx1+','+tpy1+' '+tpx2+','+tpy2+' '+tpx+','+tpy+'');
    wfnode.appendChild(tpathlink);
}
    if(Le.isLinkPathRequired(falsepathnode)){
        var fpathlink=Le.getPathConnectorNode('link','M'+fpmx+','+fpmy+'C'+fpx1+','+fpy1+' '+fpx2+','+fpy2+' '+fpx+','+fpy+'');
        wfnode.appendChild(fpathlink);
    }
    var splitystart=Le.WF_TRIGGER_NODE_HEIGHT_ADJUST + +Le.WF_STEP_NODE_HEIGHT_ADJUST;
    var splityend=splitystart + +Le.WF_FORK_DECISION_NODE_PATH_HEIGHT_CONSTANT;
    var splitymid=(splitystart + +splityend)/2;
    var tpathsplit=Le.getPathConnectorNode('split','M'+tpx+','+splitystart+'C'+tpx2+','+splitymid+' '+tpx1+','+splitymid+' '+tpmx+','+splityend+'');
    wfnode.appendChild(tpathsplit);
    var fpathsplit=Le.getPathConnectorNode('split','M'+fpx+','+splitystart+'C'+fpx2+','+splitymid+' '+fpx1+','+splitymid+' '+fpmx+','+splityend+'');
    wfnode.appendChild(fpathsplit);
    var yesx=((tpmx + +tpx)/2)-30;
    var yesy=splitymid-10;
    wfnode.appendChild(Le.getDecisionIndicatorNode(wfnode,"Yes",yesx,yesy));

    var nox=(fpmx + +fpx)/2;
    var noy=splitymid-10;
    wfnode.appendChild(Le.getDecisionIndicatorNode(wfnode,"No",nox,noy));
}
Le.updateForkPath=function(stepnode){
    var wfnode=stepnode.children[0];
    var width=stepnode.getAttributeNS(null,'width');
    var wfnodes=Le.filterWfNodeByClass(wfnode,'wf-fork-path');
    var avgwidth=width/(wfnodes.length*2);
    var maxheight=0;
    for(var n=0;n<wfnodes.length;n++){
       var node= wfnodes[n];
       var nbcr=node.getBoundingClientRect();
       maxheight=Math.max(maxheight, nbcr.height);
    }
    var xposmultiply=1;
    for(var n=0;n<wfnodes.length;n++){
        var node= wfnodes[n];
        var nbcr=node.getBoundingClientRect();
        var mx=avgwidth*xposmultiply;
        var x1=mx;
        var x2=avgwidth* wfnodes.length;
        var x=x2;
        var my=nbcr.height + +Le.WF_FORK_NODE_PATH_HEIGHT_ADJUST;
        var y=maxheight + +(Le.WF_FORK_NODE_PATH_HEIGHT_ADJUST + +Le.WF_FORK_DECISION_NODE_PATH_HEIGHT_CONSTANT);
        var y1=(my+ +y)/2;
        var y2=y1;
        if(Le.isLinkPathRequired(node)){
            var fpathlink=Le.getPathConnectorNode('link','M'+mx+','+my+'C'+x1+','+y1+' '+x2+','+y2+' '+x+','+y+'');
            wfnode.appendChild(fpathlink);
        }
        var splitystart=Le.WF_FORK_NODE_HEIGHT_ADJUST+ +Le.WF_STEP_NODE_HEIGHT_ADJUST;
        var splityend=splitystart+Le.WF_FORK_DECISION_NODE_PATH_HEIGHT_CONSTANT;
        var splitymid=(splitystart + +splityend)/2;
        var fpathsplit=Le.getPathConnectorNode('split','M'+x+','+splitystart+'C'+x2+','+splitymid+' '+x1+','+splitymid+' '+mx+','+splityend+'');
        wfnode.appendChild(fpathsplit);
        xposmultiply=xposmultiply+2;
    }
}
Le.isLinkPathRequired=function(wfnode){
    var pathnode=wfnode.children[0];
    var steps=pathnode.children[1];
    if(steps.children.length % 2 == 0){
       return false;
    }else{
        return true;
    }
}
Le.filterWfNodeByClass=function(parent,classname){
    var childrens=parent.children;
    var wfnodes=[];
    for(var ch=0;ch<childrens.length;ch++){
var child=childrens[ch];
if(child.getAttributeNS(null,'class') == classname){
    wfnodes.push(child);
}
}
    return wfnodes;
}
Le.getPathConnectorNode=function(classname,dimension){
    var path = document.createElementNS(Le.SVGNAMESPACEURI,"path");
    path.setAttributeNS(null, "class", 'wf-'+classname);
    path.setAttributeNS(null, "d", dimension);
    return path;
}
Le.getDecisionIndicatorNode=function(parent,indicator,x,y){
    try{
        var gelement = document.createElementNS(Le.SVGNAMESPACEURI,"g");
        gelement.setAttributeNS(null,'class','wf-decision-path-label wf-'+indicator.toLowerCase()+'-label');
        var gelement1 = document.createElementNS(Le.SVGNAMESPACEURI,"g");
        gelement1.setAttributeNS(null,'class','wf-label-wrap');
        gelement1.setAttributeNS(null,'transform','translate(5,2)');
        var textelement = document.createElementNS(Le.SVGNAMESPACEURI,"text");
       // textelement.setAttributeNS(null, "class", 'wf-label');
        textelement.setAttributeNS(null, "y", '12');
        textelement.setAttributeNS(null, "dy", '0');
        textelement.textContent=indicator;
        gelement1.appendChild(textelement);
        parent.appendChild(gelement1);
        var bcr=gelement1.getBoundingClientRect();
        var labelwidth=bcr.width + +10;
        gelement1.parentNode.removeChild(gelement1);
        var rectelement = document.createElementNS(Le.SVGNAMESPACEURI,"rect");
        rectelement.setAttributeNS(null, "class", 'wf-enclosure');
        rectelement.setAttributeNS(null, "x", '0');
        rectelement.setAttributeNS(null, "y", '0');
        rectelement.setAttributeNS(null, "rx", '3');
        rectelement.setAttributeNS(null, "ry", '3');
        rectelement.setAttributeNS(null, "width", labelwidth);
        rectelement.setAttributeNS(null, "height", '20');
        gelement.appendChild(rectelement);
        gelement.appendChild(gelement1);
        Le.setTransformAttr(gelement,x,y);
    }catch(err){
    alert(err);
    }
    return gelement;
}
Le.getLineConnectorNode=function(classname,x1,y1,x2,y2){
    var line = document.createElementNS(Le.SVGNAMESPACEURI,"line");
    line.setAttributeNS(null, "class", 'wf-'+classname);
    line.setAttributeNS(null, "x1", x1);
    line.setAttributeNS(null, "y1", y1);
    line.setAttributeNS(null, "x2", x2);
    line.setAttributeNS(null, "y2", y2);
    return line;
}

Le.registerMouseListener = function(wfnode){
     wfnode.addEventListener("mouseover", function (event) {
        event.stopPropagation();
        try{
            var el=this;
            el.parentNode.classList.add("node-active");
        }catch(error){
            alert(error);
        }

    });
    wfnode.addEventListener("mouseout", function (event) {
        event.stopPropagation();
        try{
            var el=this;
            el.parentNode.classList.remove("node-active");
        }catch(error){
            alert(error);
        }

    });

}
Le.registerClickListener = function(element){
    element.addEventListener("click", function (event) {
        event.stopPropagation();
        try{
            var el=this;
            var classlist=el.classList;
            var classname=classlist[0];
            if(classname == 'wf-insertion-point'){
                Le.showCampaignTypeModel();
                var stepnode=el.parentNode;
                var stepsnode=stepnode.parentNode;
                var childindex=Array.from(stepsnode.children).indexOf(stepnode);
                var id=stepsnode.getAttributeNS(null,'id');
                var response=id.split('-');
                var findid=response[1];
                Le.lastclickedinsertpoint.id=findid;
                Le.lastclickedinsertpoint.insertat=childindex/2;
            }else if(classname == 'trigger-insertion-point'){
                var parentnode=el.parentNode;
                var id=parentnode.getAttributeNS(null,'id');
                var response=id.split('-');
                var findtype=response[0];
                var findid=response[1];
                var added=Le.findAndAddObjectIntoJson(Le.campaignupdatedjson,findtype,findid,-1,false);
                if(added){
                    // alert("added successfully");
                    Le.refreshWorkFlowCanvas();
                }
            }else if(classname == 'fork-insertion-point'){
                var forknode=el.parentNode;
                var id=forknode.getAttributeNS(null,'id');
                var response=id.split('-');
                var findid=response[1];
                var added=Le.findAndAddObjectIntoJson(Le.campaignupdatedjson,'fork',findid,-1,false);
                if(added){
                    // alert("added successfully");
                    Le.refreshWorkFlowCanvas();
                }
            }else if(classname == 'wf-remove-button'){
                Le.removeWfNode(el.parentNode);
            }else if(classname == 'wf-node-labels'){
                var id=el.parentNode.getAttributeNS(null,'id');
                var response=id.split('-');
                var findtype=response[0];
                var findid=response[1];
                var matchobj=Le.findAndGetObjectFromJSON(Le.campaignupdatedjson,findtype,findid,[]);
                // alert("Find Type:"+findtype+",Find ID:"+findid+",Length:"+matchobj.length);
                if(matchobj.length > 0){
                    Le.invokeCampaignEventEditAction(matchobj[0])
                }
            }
        }catch(error){
            alert(error);
        }
    });
};

Le.removeWfNode=function(wfnode){
    var id=wfnode.getAttributeNS(null,'id');
    var response=id.split('-');
    var findtype=response[0];
    var findid=response[1];
   // alert("Find Type-->"+findtype+"-->Find ID:"+findid);
    if(findtype == 'trigger'){
    var triggerschilds=wfnode.parentNode.children;
    if(triggerschilds.length == 2){
        var triggersparent=wfnode.parentNode.parentNode;
        var tpclassname=triggersparent.getAttributeNS(null,'class');
        var tpid=triggersparent.getAttributeNS(null,'id');
       if(tpclassname == 'wf-interrupt'){
            response=tpid.split('-');
            findtype=response[0];
            findid=response[1];
       }
    }
    }
    var deleted=Le.findAndRemoveObjectFromJson(Le.campaignupdatedjson,findtype,findid,false);
    if(deleted){
        Le.refreshWorkFlowCanvas();
//         Le.invokeCampaignEventDeleteAction(findid,function(err){
// if(!err){
//     Le.refreshWorkFlowCanvas();
// }
//         });

    }
}
var save = function(filename, content) {
    saveAs(
        new Blob([content], {type: 'text/plain;charset=utf-8'}),
        filename
    );
};
Le.findAndRemoveObjectFromJson = function(data,findtype,findid,deleted){
    var type=data.type;
    if(type == 'path' && !deleted){
        var triggers=data.triggers;
        var steps=data.steps;
        if(!mQuery.isEmptyObject(triggers)){
            mQuery.each(triggers, function (index,trigger) {
                if(trigger.type == findtype && trigger.id == findid){
                    if(triggers.length > 1){
                        triggers.splice(index,1);
                        deleted=true;
                        return false;
                    }
                }
            });
        }
        if(!mQuery.isEmptyObject(steps)){
            mQuery.each(steps, function (index, event) {
                if(event.type == findtype && event.id == findid){
            //    alert("Match Found:"+index+"-->id:"+event.id+"-->type:"+findtype);
                    steps.splice(index,1);
                    deleted=true;
                    return false;
                }else{
                    deleted=Le.findAndRemoveObjectFromJson(event,findtype,findid,deleted);
                }
            });
        }
    }if(type == 'decision' && !deleted){
        var truepath=data.true_path;
        var falsepath=data.false_path;
        deleted=Le.findAndRemoveObjectFromJson(truepath,findtype,findid,deleted);
        deleted=Le.findAndRemoveObjectFromJson(falsepath,findtype,findid,deleted);
    }else if(type == 'fork' && !deleted){
        var paths=data.paths;
        mQuery.each(paths, function (index, value) {
            deleted=Le.findAndRemoveObjectFromJson(value,findtype,findid,deleted);
        });
    }else if(type == 'interrupt'){
        var triggers=data.triggers;
        if(!mQuery.isEmptyObject(triggers)){
            mQuery.each(triggers, function (index, trigger) {
                if(trigger.type == findtype && trigger.id == findid){
                    if(triggers.length > 1){
                        triggers.splice(index,1);
                        deleted=true;
                        return false;
                    }
                }
            });
        }
    }
    return deleted;
}
Le.findAndAddObjectIntoJson = function(data,findtype,findid,insertat,added){
    var type=data.type;
    var id=data.id;
    if(type == 'path' && !added){
        var triggers=data.triggers;
        var steps=data.steps;
        if(insertat == -1 && id == findid){
            var newjson=Le.getNewTriggerNodeJSON();
            triggers.push(newjson);
            added=true;
            Le.invokeCampaignEventAddAction(newjson,function(response) {
                if (!response) {
                }
            }
            );
        }else if(insertat >= 0 && id == findid){
            var newjson=Le.getJSONByEventType(findtype);
            if(findtype == 'fork'){
                steps.splice(insertat, 0,newjson);
                added=true;
            }else{
                steps.splice(insertat, 0,newjson);
                added=true;
                Le.invokeCampaignEventAddAction(newjson,function(response) {
                        if (!response) {
                        }
                    }
                );
            }
        }else{
            if(!mQuery.isEmptyObject(steps)){
                mQuery.each(steps, function (index, event) {
                    added=Le.findAndAddObjectIntoJson(event,findtype,findid,insertat,added);
                });
            }
        }
    }if(type == 'decision' && !added){
        var truepath=data.true_path;
        var falsepath=data.false_path;
        added=Le.findAndAddObjectIntoJson(truepath,findtype,findid,insertat,added);
        added=Le.findAndAddObjectIntoJson(falsepath,findtype,findid,insertat,added);
    }else if(type == 'fork' && !added){
        var paths=data.paths;
        if(findtype == type && id == findid){
            paths.push(Le.getNewPathJSON());
            added=true;
        }else {
            mQuery.each(paths, function (index, value) {
                added=Le.findAndAddObjectIntoJson(value,findtype,findid,insertat,added);
            });
        }

    }else if(type == 'interrupt'){
        var triggers=data.triggers;
        if(findtype == type && id == findid){
            var newjson=Le.getNewTriggerNodeJSON();
            newjson.entry_point=false;
            triggers.push(newjson);
            added=true;
            Le.invokeCampaignEventAddAction(newjson,function(response) {
                    if (!response) {
                    }
                }
            );
        }
    }
    return added;
}
Le.findAndGetObjectFromJSON = function(data,findtype,findid,matchobj){
    var type=data.type;
    if(type == 'path' && matchobj.length == 0){
        var triggers=data.triggers;
        var steps=data.steps;

            if(!mQuery.isEmptyObject(triggers)){
                mQuery.each(triggers, function (index, trigger) {
                    if(trigger.id == findid){
                        matchobj.push(trigger);
                        return false;
                    }
                });
            }

            if(!mQuery.isEmptyObject(steps) && matchobj.length == 0){
                mQuery.each(steps, function (index, event) {
                    if(event.id == findid){
                        matchobj.push(event);
                        return false;
                    }else{
                        matchobj=Le.findAndGetObjectFromJSON(event,findtype,findid,matchobj);
                    }
                });
            }


    }if(type == 'decision' && matchobj.length == 0){
        var truepath=data.true_path;
        var falsepath=data.false_path;
        matchobj=Le.findAndGetObjectFromJSON(truepath,findtype,findid,matchobj);
        matchobj=Le.findAndGetObjectFromJSON(falsepath,findtype,findid,matchobj);
    }else if(type == 'fork' && matchobj.length == 0){
        var paths=data.paths;
            mQuery.each(paths, function (index, value) {
                matchobj=Le.findAndGetObjectFromJSON(value,findtype,findid,matchobj);
                if(matchobj.length > 0){
                   return false;
                }
            });
    }else if(type == 'interrupt' && matchobj.length == 0){
        var triggers=data.triggers;
        if(!mQuery.isEmptyObject(triggers)){
            mQuery.each(triggers, function (index, trigger) {
                if(trigger.id == findid){
                    matchobj.push(trigger);
                    return false;
                }
            });
        }
    }
    return matchobj;
}
Le.findAndUpdateObjectIntoJson = function(data,findtype,findid,info,updated){
    var type=data.type;
    var id=data.id;
    if(type == 'path' && !updated){
        var triggers=data.triggers;
        var steps=data.steps;
        if(findtype == 'source'){
            mQuery.each(triggers, function (index, value) {
                if(value.id == findid){
                    value.view.label=info.label;
                    value.view.incomplete=info.incomplete;
                    value.category=info.category;
                    value.subcategory=info.subcategory;
                    updated=true;
                    return false;
                }
            });
        }
            if(!mQuery.isEmptyObject(steps) && !updated){
                mQuery.each(steps, function (index, event) {
                        updated=Le.findAndUpdateObjectIntoJson(event,findtype,findid,info,updated);
                });
            }

    }else if(type == 'fork' && !updated){
        var paths=data.paths;
            mQuery.each(paths, function (index, value) {
                updated=Le.findAndUpdateObjectIntoJson(value,findtype,findid,info,updated);
            });
    }else if(type == 'interrupt' && !updated){
        var triggers=data.triggers;
        if(findtype == 'source'){
            mQuery.each(triggers, function (index, value) {
if(value.id == findid){
    value.view.label=info.label;
    value.view.incomplete=info.incomplete;
    value.category=info.category;
    value.subcategory=info.subcategory;
    updated=true;
    return false;
}
            });
        }
    }else if(!updated && (type == 'decision' || type == 'action' || type == 'delay')){
      if(id == findid){
          data.view.label=info.label;
          data.view.incomplete=info.incomplete;
          data.category=info.category;
          data.subcategory=info.subcategory;
          updated=true;
      }else if(type == 'decision'){
          var truepath=data.true_path;
          var falsepath=data.false_path;
          updated=Le.findAndUpdateObjectIntoJson(truepath,findtype,findid,info,updated);
          updated=Le.findAndUpdateObjectIntoJson(falsepath,findtype,findid,info,updated);
      }
    }
    return updated;
}
Le.refreshWorkFlowCanvas=function(){
    Le.WF_NODE_PATH_HEIGHT_ADJUST=Le.WF_TRIGGER_NODE_HEIGHT_ADJUST;
    if(Le.WF_SHOW_STATSTICS){
        Le.WF_NODE_PATH_HEIGHT_ADJUST=Le.WF_NODE_PATH_HEIGHT_ADJUST+ +Le.WF_COUNT_NODE_HEIGHT_ADJUST;
    }
    var svgelements=mQuery('.workflow-canvas').children();
    var oldsvg=svgelements[0];
    var newsvg=oldsvg.cloneNode(false);
    oldsvg.parentNode.replaceChild(newsvg, oldsvg);
    //alert(newsvg.getAttributeNS(null,'id'));
    var svgwidth=mQuery(window).width();
    if(svgwidth > 0){
        svgwidth=svgwidth-20;
    }
    if(Le.campaignupdatedjson.width > svgwidth){
        svgwidth=Le.campaignupdatedjson.width;
    }
    svgwidth=Math.ceil(svgwidth);
    Le.iterateJSONOBJECT(Le.campaignupdatedjson,svgwidth,newsvg,'',false);
    //save('inserted.txt',JSON.stringify(Le.campaignupdatedjson));
}

Le.registerKeyupCampaignName = function(){
    /*var campaignname = mQuery("#campaign_name").val();
    mQuery('#campaign_CustomName').val(campaignname);
    mQuery("#campaign_CustomName").keyup(function(){
        var campaignname = mQuery("#campaign_CustomName").val();
        mQuery('#campaign_name').val(campaignname);
    });*/
    if(mQuery('#campaign_isPublished_1').attr('checked') == "checked"){
        mQuery('#campaignPublishButton').removeClass('background-orange').addClass('background-pink');
        mQuery('#campaignPublishButton').html('Stop Workflow');
        mQuery('#campaignPublishButton').attr("value","unpublish");
        mQuery('#campaignPublishButton').attr("data-original-title","Stop this workflow.");
    } else {
        mQuery('#campaignPublishButton').removeClass('background-pink').addClass('background-orange');
        mQuery('#campaignPublishButton').html('Start Workflow');
        mQuery('#campaignPublishButton').attr("value","publish");
        mQuery('#campaignPublishButton').attr("data-original-title", " workflow will be in draft/ pause till you start. Tap this button to start this workflow.");
    }
};

Le.CloseStatisticsWidget = function(){
    var value = mQuery('#campaignStatistics').attr("value");
    if(value == "close") {
        mQuery('#campaignStatistics').attr("value","open");
        mQuery('.campaign-statistics').addClass('minimized');
        mQuery('.'+value+'Group').addClass('hide');
        mQuery('#campaginStatClass').addClass('fa fa-angle-double-right');
        mQuery('.status-body').addClass('hide');

    } else {
        mQuery('#campaignStatistics').attr("value","close");
        mQuery('.campaign-statistics').removeClass('minimized');
        mQuery('.campaign-event-list').removeClass('hide');
        mQuery('#campaginStatClass').removeClass('fa fa-angle-double-right');
        mQuery('#campaginStatClass').addClass('fa fa-angle-double-left');
        mQuery('.status-body').removeClass('hide');
    }
};
Le.publishCampaign = function(flag){
    var campaignname = mQuery('#campaign_name').val();
    var msg = "Workflow"+" "+ '"'+campaignname+'"' +" "+" successfully";
  if(flag){
      Le.toggleYesNoButtonClass('campaign_isPublished_1');
      mQuery('#campaign_isPublished_1').attr('checked',true);
      mQuery('#campaign_isPublished_0').attr('checked',false);
      var potherLabel = mQuery('#campaign_isPublished_1').parent().parent().find('.btn-yes');
      mQuery(potherLabel).addClass('active');
      var uotherLabel = mQuery('#campaign_isPublished_0').parent().parent().find('.btn-no');
      mQuery(uotherLabel).removeClass('active');
      mQuery('#flash').css('display','inline-block');
      mQuery('#flash').html(msg+' started.');
  }else{
      Le.toggleYesNoButtonClass('campaign_isPublished_0');
      mQuery('#campaign_isPublished_0').attr('checked',true);
      mQuery('#campaign_isPublished_1').attr('checked',false);
      var uotherLabel = mQuery('#campaign_isPublished_0').parent().parent().find('.btn-no');
      mQuery(uotherLabel).addClass('active');
      var potherLabel = mQuery('#campaign_isPublished_1').parent().parent().find('.btn-yes');
      mQuery(potherLabel).removeClass('active');
      mQuery('#flash').css('display','inline-block');
      mQuery('#flash').html(msg+' stopped.');
  }
    mQuery(function() {
        mQuery('#flash').delay(800).fadeIn('normal', function() {
            mQuery(this).delay(1500).fadeOut();
        });
    });
}
Le.publishCampaign_old = function(){
    var value = mQuery('#campaignPublishButton').attr("value");
    var campaignname = mQuery('#campaign_name').val();
    var msg = "Workflow"+" "+ '"'+campaignname+'"' +" "+" successfully";
    if(value == "publish"){
        Le.toggleYesNoButtonClass('campaign_isPublished_1');
        mQuery('#campaign_isPublished_1').attr('checked',true);
        mQuery('#campaign_isPublished_0').attr('checked',false);
        var potherLabel = mQuery('#campaign_isPublished_1').parent().parent().find('.btn-yes');
        mQuery(potherLabel).addClass('active');
        var uotherLabel = mQuery('#campaign_isPublished_0').parent().parent().find('.btn-no');
        mQuery(uotherLabel).removeClass('active');
        mQuery('#campaignPublishButton').attr("value","unpublish");
        mQuery('#campaignPublishButton').removeClass('background-orange').addClass('background-pink');
        mQuery('#campaignPublishButton').html('Stop Workflow');
        mQuery('#campaignPublishButton').attr("data-original-title","Stop this workflow.");
        mQuery('#flash').css('display','inline-block');
        mQuery('#flash').html(msg+' started.');
    } else {
        Le.toggleYesNoButtonClass('campaign_isPublished_0');
        mQuery('#campaign_isPublished_0').attr('checked',true);
        var uotherLabel = mQuery('#campaign_isPublished_0').parent().parent().find('.btn-no');
        mQuery(uotherLabel).addClass('active');
        var potherLabel = mQuery('#campaign_isPublished_1').parent().parent().find('.btn-yes');
        mQuery(potherLabel).removeClass('active');
        mQuery('#campaignPublishButton').attr("data-original-title", "Workflow will be in draft/ pause till you start. Tap this button to start this workflow.");
        mQuery('#campaignPublishButton').attr("value","publish");
        mQuery('#campaignPublishButton').removeClass('background-pink').addClass('background-orange');
        mQuery('#campaignPublishButton').html('Start Workflow');
        mQuery('#flash').css('display','inline-block');
        mQuery('#flash').html(msg+' stopped.');
    }
    mQuery(function() {
        mQuery('#flash').delay(800).fadeIn('normal', function() {
            mQuery(this).delay(1500).fadeOut();
        });
    });

};

Le.applyCampaignFromBuilder = function() {
    Le.activateButtonLoadingIndicator(mQuery('.btn-apply-builder'));
    Le.updateConnections(function(err) {
        if (!err) {
            var applyBtn = mQuery('.btn-apply');
            Le.inBuilderSubmissionOn(applyBtn.closest('form'));
            applyBtn.trigger('click');
            Le.inBuilderSubmissionOff();
        }
    });
};

Le.saveCampaignFromBuilder = function() {
    Le.activateButtonLoadingIndicator(mQuery('.btn-save-builder'));
    Le.updateConnections(function(err) {
        if (!err) {
            mQuery('body').css('overflow-y', '');
            var saveBtn = mQuery('.btn-save');
            Le.inBuilderSubmissionOn(saveBtn.closest('form'));
            saveBtn.trigger('click');
            Le.inBuilderSubmissionOff();
        }
    });
};
Le.updateConnections = function(callback) {
    var campaignId     = mQuery('#campaignId').val();
    var query          = "action=campaign:updateConnections&campaignId=" + campaignId;
    var canvasSettings = {canvasSettings: JSON.stringify(Le.campaignupdatedjson)};
    mQuery.ajax({
        url:leAjaxUrl + '?' + query,
        type: "POST",
        data: canvasSettings,
        dataType: "json",
        success: function (response) {
            if (typeof callback === 'function') callback(false, response);
        },
        error: function (response, textStatus, errorThrown) {
            Le.processAjaxError(response, textStatus, errorThrown);
            if (typeof callback === 'function') callback(true, response);
        }
    });
};
/**
 * Close campaign builder
 */
Le.closeCampaignBuilder = function() {
    var builderCss = {
        margin: "0",
        padding: "0",
        border: "none",
        width: "100%",
        height: "100%"
    };

    var panelHeight = (mQuery('.builder-content').css('right') == '0px') ? mQuery('.builder-panel').height() : 0,
        panelWidth = (mQuery('.builder-content').css('right') == '0px') ? 0 : mQuery('.builder-panel').width(),
        spinnerLeft = (mQuery(window).width() - panelWidth - 60) / 2,
        spinnerTop = (mQuery(window).height() - panelHeight - 60) / 2;

    var overlay = mQuery('<div id="builder-overlay" class="modal-backdrop fade in"><div style="position: absolute; top:' + spinnerTop + 'px; left:' + spinnerLeft + 'px" class=".builder-spinner"><i class="fa fa-spinner fa-spin fa-5x"></i></div></div>').css(builderCss).appendTo('.builder-content');
    mQuery('.btn-close-builder').prop('disabled', true);

    Le.removeButtonLoadingIndicator(mQuery('.btn-apply-builder'));
    mQuery('#builder-errors').hide('fast').text('');

    Le.updateConnections(function(err, response) {
        //mQuery('body').css('overflow-y', '');

        if (!err) {
            mQuery('#builder-overlay').remove();
            //mQuery('body').css('overflow-y', '');
            if (response.success) {
                //mQuery('.builder').addClass('hide').removeClass('builder-active');
            }
            mQuery('.btn-close-builder').prop('disabled', false);
            var cancelBtn = mQuery('.btn-cancel');
            Le.inBuilderSubmissionOn(cancelBtn.closest('form'));
            cancelBtn.trigger('click');
            Le.inBuilderSubmissionOff();
        }
    });
};

/**
 * Enable/Disable timeframe settings if the toggle for immediate trigger is changed
 */
Le.campaignToggleTimeframes = function() {
    if (mQuery('#campaignevent_triggerMode_2').length) {
        var immediateChecked = mQuery('#campaignevent_triggerMode_0').prop('checked');
        var intervalChecked = mQuery('#campaignevent_triggerMode_1').prop('checked');
        var dateChecked = mQuery('#campaignevent_triggerMode_2').prop('checked');
    } else {
        var immediateChecked = false;
        var intervalChecked = mQuery('#campaignevent_triggerMode_0').prop('checked');
        var dateChecked = mQuery('#campaignevent_triggerMode_1').prop('checked');
    }

    if (mQuery('#campaignevent_triggerInterval').length) {
        if (immediateChecked) {
            mQuery('#triggerInterval').addClass('hide');
            mQuery('#triggerDate').addClass('hide');
        } else if (intervalChecked) {
            mQuery('#triggerInterval').removeClass('hide');
            mQuery('#triggerDate').addClass('hide');
        } else if (dateChecked) {
            mQuery('#triggerInterval').addClass('hide');
            mQuery('#triggerDate').removeClass('hide');
        }
    }
};

/**
 * Close Name Model
 */
Le.CloseDataModelCampaign = function(){
    if(mQuery('#campaign_name').val() != ""){
        mQuery('#leSharedModal').modal('hide');
    } else {
        mQuery('#campaign_name .help-block').html("A name is required");
    }
};

/**
 * Launch campaign builder modal
 */
Le.launchCampaignEditor = function() {
    Le.stopIconSpinPostEvent();
    mQuery('body').css('overflow-y', 'hidden');
    mQuery('.builder').addClass('builder-active').removeClass('hide');
    try{
        //save('text.txt',JSON.stringify(Le.campaignBuilderCanvasSettings));
        Le.campaignupdatedjson=Le.campaignBuilderCanvasSettings;
        var svgwidth=mQuery(window).width();
        if(svgwidth > 0){
            svgwidth=svgwidth-20;
        }
        if(typeof Le.campaignupdatedjson.width != 'number'){
            Le.campaignupdatedjson['width']=svgwidth;
        }
        if(Le.campaignupdatedjson.width > svgwidth){
            svgwidth=Le.campaignupdatedjson.width;
        }
        svgwidth=Math.ceil(svgwidth);
        var svgelement = document.createElementNS(Le.SVGNAMESPACEURI,"svg");
        svgelement.setAttributeNS(null, "id", 'workflow'+'-'+ Le.randomString(8));
        svgelement.setAttributeNS(null, "width", svgwidth);
        mQuery('.workflow-canvas').append(svgelement);
        Le.WF_NODE_PATH_HEIGHT_ADJUST=Le.WF_TRIGGER_NODE_HEIGHT_ADJUST;
        if(Le.WF_SHOW_STATSTICS){
            Le.WF_NODE_PATH_HEIGHT_ADJUST=Le.WF_NODE_PATH_HEIGHT_ADJUST+ +Le.WF_COUNT_NODE_HEIGHT_ADJUST;
        }
       Le.iterateJSONOBJECT(Le.campaignupdatedjson,svgwidth,svgelement,'',false);
    }catch(error){
        alert(error);
    }
};

Le.selectCampaignType=function(eventType){
    Le.closeCampaignTypeModel();
    var added=Le.findAndAddObjectIntoJson(Le.campaignupdatedjson,eventType,Le.lastclickedinsertpoint.id,Le.lastclickedinsertpoint.insertat,false);
    if(added){
        Le.refreshWorkFlowCanvas();
    }
};
Le.closeCampaignTypeModel=function(){
    mQuery('.campaignevent-type-modal-backdrop').addClass('hide');
    mQuery('.campaignevent-type-modal').addClass('hide');
    mQuery('.campaignevent-type-modal').removeClass('in');
};

Le.showCampaignTypeModel=function(){
    mQuery('.campaignevent-type-modal').addClass('in');
    mQuery('.campaignevent-type-modal-backdrop').removeClass('hide');
    mQuery('.campaignevent-type-modal').removeClass('hide');

};
Le.invokeCampaignEventEditAction=function(lastclickednode){
    var campaignId = mQuery('#campaignId').val();
    var posturl = mQuery('#campaign-request-url').attr('data-href');
    posturl = posturl.replace("objectAction", 'edit');
    posturl = posturl+"/"+lastclickednode.id;
    var route = posturl + "?campaignId=" + campaignId+"&type="+lastclickednode.subcategory+"&eventType="+lastclickednode.category;
    var divelement = document.createElement("div");
    divelement.setAttribute('data-target', '#CampaignEventModal');
    divelement.setAttribute('data-href', route);
    //alert("Path:"+route);
    Le.ajaxifyModal(divelement);
};
Le.invokeCampaignEventAddAction=function(wfjson,callback){
    var wfnodetype=wfjson.type;
    if(wfjson.type == 'interrupt'){
      wfjson=wfjson.triggers[0];
    }
    if(wfjson.type == 'trigger' && !wfjson.entry_point){
        wfnodetype='interrupt';
    }
    var campaignId = mQuery('#campaignId').val();
    //  alert(mQuery('#campaign-new-request-url').attr('data-href'));
    var posturl = mQuery('#campaign-request-url').attr('data-href');
    posturl = posturl.replace("objectAction", 'new');
    var route = posturl + "?campaignId=" + campaignId;
    //alert(route);
   // alert(wfjson.subcategory);
   // callback(false, {});
    var data = {campaignId:campaignId,type:wfjson.subcategory,eventType:wfjson.category,keyId:wfjson.id,wfnodetype:wfnodetype};
    mQuery.ajax({
        url: route,
        type: "POST",
        data: data,
        dataType: "json",
        success: function (response) {
           if (typeof callback === 'function') callback(false, response);
        },
        error: function (response, textStatus, errorThrown) {
            Le.processAjaxError(response, textStatus, errorThrown);
            if (typeof callback === 'function') callback(true, response);
        }
    });
};
Le.invokeCampaignEventDeleteAction=function(id,callback){
    var campaignId = mQuery('#campaignId').val();
    var posturl = mQuery('#campaign-request-url').attr('data-href');
    posturl = posturl.replace("objectAction", 'delete');
    posturl = posturl+"/"+id;
    var route = posturl + "?campaignId=" + campaignId;
    mQuery.ajax({
        url: route,
        type: "POST",
        data: {},
        dataType: "json",
        success: function (response) {
            if (typeof callback === 'function') callback(false, response);
        },
        error: function (response, textStatus, errorThrown) {
            Le.processAjaxError(response, textStatus, errorThrown);
            if (typeof callback === 'function') callback(true, response);
        }
    });
};
/**
 * Setup the campaign event view
 *
 * @param container
 * @param response
 */

Le.campaignEventOnLoad = function (container, response) {
    var value = mQuery('#campaignevent_properties_campaigntype').val();
    var eventNames = mQuery('#campaignevent_type').val();

    if(eventNames == "openEmail" || eventNames == "clickEmail"){
        Le.getSelectedCampaignValue(value);
    }
   if(eventNames == "lead.campaign_list_filter"){
       Le.leadlistOnLoad(container);
   }

    var eventType=response.eventType;
    if(!response.closeModal && !response.deleted){
        var cegselctize = mQuery('#campaignevent_group').selectize({
            persist: true,
            maxItems: 1,
            valueField: 'label',
            labelField: 'label',
            searchField: ['label'],
            sortField: [
                {field: 'order', direction: 'asc'},
            ],
            options: Le.campaignBuilderGroupOptions,
            render: {
                item: function (item, escape) {
                    return '<div>' + escape(item.label) + '</div>';
                },
                option: function (item, escape) {
                    return '<div style="display: block;color:#47535f;font-weight: 600">' + escape(item.label) + '</div>';
                }
            },
        })[0].selectize;
        var cesgselectize = mQuery('#campaignevent_subgroup').selectize({
            persist: true,
            maxItems: 1,
            placeholder: eventType == 'source' ? 'Choose a trigger..' :'Choose a action..',
            valueField: 'category',
            labelField: 'label',
            searchField: ['label', 'desc'],
            sortField: [
                {field: 'order', direction: 'asc'},
            ],
            options: [],
            render: {
                item: function (item, escape) {
                    return '<div>' + escape(item.label) + '</div>';
                },
                option: function (item, escape) {

                    return '<div>' +
                        '<div style="display: block;color:#47535f;font-weight: 600";>' + escape(item.label) + '</div>' +
                        '<div style="display: block;color: #666;">' + escape(item.desc) + '</div>' +
                        '</div>';
                }
            },

        })[0].selectize;
        cegselctize.on('change', function (value) {
            var groupname = this.options[value].label;
            var suboptions=[];
            if(eventType == 'source'){
                suboptions = Le.getFilteredCampaignSourceSubgroupOptions(groupname);
            }else{
                suboptions = Le.getFilteredCampaignEventSubgroupOptions(groupname);
            }
            try{
                cesgselectize.clearOptions();
            }catch(err){
                //alert(err.message);
            }
            for (var index = 0; index < suboptions.length; index++) {
                cesgselectize.addOption(suboptions[index]);
                //cesgselectize.addItem(suboptions[index].category,true);
            }
            cesgselectize.refreshOptions(false);
            // cesgselectize.refreshItems();
            if (cesgselectize.isOpen) {
                cesgselectize.close();
            }


        });
        cesgselectize.on('change', function (value) {
            var category = this.options[value].category;
            var campaignId = mQuery('#campaignId').val();
            var posturl = mQuery('#campaigneventgroup').attr('data-href');
            var route = posturl + "?type=" + category + "&eventType="+eventType+"&campaignId=" + campaignId + "&keyId=" + Le.lastclickedwfnode.id;
            var divelement = document.createElement("div");
            divelement.setAttribute('data-target', '#CampaignEventModal');
            divelement.setAttribute('data-href', route);
            //divelement.setAttribute('data-prevent-dismiss',true);
            Le.updateAjaxModal('#CampaignEventModal', route, 'GET');
        });
        var campaignEventType = mQuery('#campaignevent_type').val();
        if(eventType == 'source') {
            cegselctize.setValue(Le.getCampaignSourceGroupName(campaignEventType), false);
        }
        else{
            cegselctize.setValue(Le.getCampaignEventGroupName(campaignEventType), false);
        }
        if(campaignEventType != 'campaign.defaultsource' && campaignEventType != 'campaign.defaultaction'){
            cesgselectize.setValue(campaignEventType, true);
        }
    }
    if (response.deleted) {
    } else if (response.success) {
        try{
            var eventId=response.eventId;
            var eventName=response.eventName;
            var type=response.type;
            var info={
                'label': eventName,
                'category': eventType,
                'subcategory':type,
                'incomplete': (type != 'campaign.defaultdelay' && type.includes("campaign.default")) ? true :false
            };
            //alert("Eventype:"+eventType+",Event ID:"+eventId+",Eventname:"+eventName+",Type:"+type);
            var updated = Le.findAndUpdateObjectIntoJson(Le.campaignupdatedjson,eventType,eventId,info,false);
            if(updated){
                Le.refreshWorkFlowCanvas();
            }
        }catch(err){
            alert(err);
        }
    }
       //mQuery('.le-modal-box-align').css("marginLeft","210px");
    var value = mQuery('#campaignevent_properties_useremail_email').val()
    if(value === '' || value === null ){
        mQuery('#campaignevent_properties_useremail_previewEmailButton').prop('disabled', true);
    }else{
        mQuery('#campaignevent_properties_useremail_previewEmailButton').prop('disabled', false);
    }

};
Le.getFilteredCampaignEventSubgroupOptions=function(groupname){
    var filteroptions = [];
    for(var index=0;index < Le.campaignBuilderEventOptions.length;index++){
        var options=Le.campaignBuilderEventOptions[index];
        if(options.group == groupname){
            filteroptions.push(options);
        }

    }
    return filteroptions;
};
Le.getFilteredCampaignSourceSubgroupOptions=function(groupname){
    var filteroptions = [];
    for(var index=0;index < Le.campaignBuilderSourceOptions.length;index++){
        var options=Le.campaignBuilderSourceOptions[index];
        if(options.group == groupname){
            filteroptions.push(options);
        }
    }
    return filteroptions;
};
Le.getCampaignEventGroupName=function(subgroup){
    // alert('Sub Group Matched-->'+subgroup);
    for(var index=0;index < Le.campaignBuilderEventOptions.length;index++){
        var options=Le.campaignBuilderEventOptions[index];
        if(options.category == subgroup){
            // alert('Group Matched-->'+options.group);
            return options.group;
            break;
        }
    }
    return Le.campaignBuilderGroupOptions[0].label;
};
Le.getCampaignSourceGroupName=function(subgroup){
    // alert('Sub Group Matched-->'+subgroup);
    for(var index=0;index < Le.campaignBuilderSourceOptions.length;index++){
        var options=Le.campaignBuilderSourceOptions[index];
        if(options.category == subgroup){
            //alert('Group Matched-->'+options.group);
            return options.group;
            break;
        }
    }
    return Le.campaignBuilderGroupOptions[0].label;
};


/**
 * Submit the campaign event form
 * @param e
 */
Le.submitCampaignEvent = function(e) {
    e.preventDefault();
    mQuery('#campaignevent_canvasSettings_droppedX').val(mQuery('#droppedX').val());
    mQuery('#campaignevent_canvasSettings_droppedY').val(mQuery('#droppedY').val());

    mQuery('form[name="campaignevent"]').submit();
};

Le.showStatistics=function(show){
 Le.WF_SHOW_STATSTICS=show;
 Le.refreshWorkFlowCanvas();
}
Le.adjustSizeforSVG=function(svgnode,stepsnode,yposition){
    var bcr=stepsnode.parentNode.getBoundingClientRect();
    var svgwidth=mQuery(window).width();
    if(svgwidth < bcr.width){
        svgwidth=bcr.width + +100;
    }else{
        svgwidth=svgwidth-20;
    }
    svgwidth=Math.ceil(svgwidth);
    Le.campaignupdatedjson.width=svgwidth;
    svgnode.setAttributeNS(null,"height",yposition + +Le.SVG_NODE_HEIGHT_ADJUST);
    svgnode.setAttributeNS(null,"width",svgwidth);
}