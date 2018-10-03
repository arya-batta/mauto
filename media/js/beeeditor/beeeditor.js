      var request = function(method, url, data, type, callback) {
      var req = new XMLHttpRequest();
      console.log(type);
      req.onreadystatechange = function() {
        if (req.readyState === 4 && req.status === 200) {
          var response = JSON.parse(req.responseText);
          callback(response);
        }
      };

      req.open(method, url, true);
      if (data && type) {
        if(type === 'multipart/form-data') {
          var formData = new FormData();
          for (var key in data) {
            formData.append(key, data[key]);
          }
          data = formData;
        }
        else {
          req.setRequestHeader('Content-type', type);
        }
      }

      req.send(data);
    };

    var save = function(filename, content) {
      saveAs(
        new Blob([content], {type: 'text/plain;charset=utf-8'}),
        filename
      );
    };

    var specialLinks = [];

    // {
      //     type: 'unsubscribe',
      //         label: 'SpecialLink.Unsubscribe',
      //     link: 'http://[unsubscribe]/'
      // }, {
      //     type: 'subscribe',
      //         label: 'SpecialLink.Subscribe',
      //         link: 'http://[subscribe]/'
      // }


    var mergeTags = [];
      // {
      //     name: 'tag 1',
      //         value: '[tag1]'
      // }, {
      //     name: 'tag 2',
      //         value: '[tag2]'
      // }

    var mergeContents = [];

      // {
      //     name: 'content 1',
      //         value: '[content1]'
      // }, {
      //     name: 'content 2',
      //         value: '[content1]'
      // }

    var beeConfig = {
      uid: leClientID,
      container: 'bee-plugin-viewpanel',
      autosave: 15,
      language: 'en-US',
      specialLinks: specialLinks,
      mergeTags: mergeTags,
      mergeContents: mergeContents,
      onSave: function(jsonFile, htmlFile) {
          mQuery('.builder-html').val(Mautic.domToString(htmlFile));
          jsonFile=jsonFile.replace(/&quot;/g, "'");
          mQuery('.bee-editor-json').val(jsonFile);
          Mautic.closeBeeEditor(function(){
              var bgApplyBtn = mQuery('.btn-apply');
              bgApplyBtn.trigger('click');
          });

       // save('newsletter.html', htmlFile);
        // save('newsletter.json', jsonFile);
      },
      onSaveAsTemplate: function(jsonFile) { // + thumbnail?
          save('template.json', jsonFile);
         // alert("Feature not supported.");
        //save('newsletter-template.json', jsonFile);
      },
      onAutoSave: function(jsonFile) {
          mQuery('.bee-editor-json').val(jsonFile);
          // + thumbnail?
       // console.log(new Date().toISOString() + ' autosaving...');
       // window.localStorage.setItem('newsletter.autosave', jsonFile);
      },
      onSend: function(htmlFile) {
          save('template.html', htmlFile);
         // alert("Feature not supported.");
        //write your send test function here
      },
      onError: function(errorMessage) {
          alert(JSON.stringify(errorMessage));
       // console.log('onError ', errorMessage);
      }
    };

    var bee = null;

    var loadTemplate = function(e) {
      var templateFile = e.target.files[0];
      var reader = new FileReader();

      reader.onload = function() {
        var templateString = reader.result;
        var template = JSON.parse(templateString);
        bee.load(template);
      };
      reader.readAsText(templateFile);
    };

   // document.getElementById('choose-template').addEventListener('change', loadTemplate, false);



      Mautic.launchBeeEditor = function (formName, actionName) {
          var height=620;
          if(mQuery('.sidebar-content').is(':visible')) {
              height=mQuery('.sidebar-left').height();
              if(height <= 0){
                  height=620;
              }
          }

          mQuery('body').css('overflow-y', 'hidden');
         // mQuery('#bee-plugin-viewpanel').css('height', height+"px");
          Mautic.getTokens(actionName+':getBuilderTokens', function(tokens) {
              mergeTags.length=0;
              mQuery.each(tokens, function(k,v){
                  if (k.match(/assetlink=/i) && v.match(/a:/)){
                      delete tokens[k];
                      var nv = v.replace('a:', '');
                      k = '<a title=\'Asset Link\' href=\'' + k + '\'>' + nv + '</a>';
                      tokens[k] = nv;
                  } else if (k.match(/pagelink=/i) && v.match(/a:/)){
                      delete tokens[k];
                      nv = v.replace('a:', '');
                      k = '<a title=\'Page Link\' href=\'' + k + '\'>' + nv + '</a>';
                      tokens[k] = nv;
                  } else if (k.match(/dwc=/i)){
                      var tn = k.substr(5, k.length - 6);
                      tokens[k] = v + ' (' + tn + ')';
                  } else if (k.match(/leadfield=company/i) && !v.match(/company/i)){
                      tokens[k] = 'Company ' + v;
                  }
              });
              var k, keys = [];
              for (k in tokens) {
                  if (tokens.hasOwnProperty(k)) {
                      keys.push(k);
                  }
              }
              //keys.sort();
              for (var i = 0; i < keys.length; i++) {
                  var val = keys[i];
                  var str = ' (_BADGE_)';
                  var badge = (val.match(/page link/i))?
                      str.replace(/_BADGE_/, 'page') :
                      (val.match(/asset link/i))?
                          str.replace(/_BADGE_/, 'asset') :
                          (val.match(/form=/i))?
                              str.replace(/_BADGE_/,'form') :
                              (val.match(/focus=/i))?
                                  str.replace(/_BADGE_/,'focus') :
                                  (val.match(/dynamiccontent=/i))?
                                      str.replace(/_BADGE_/,'dynamic') :
                                      (val.match(/dwc=/i))?
                                          str.replace(/_BADGE_/,'dwc') : '';
                  var title = tokens[val];
                  if (title.length>24) title = title.substr(0, 24) + '...';
                 mergeTags.push({name : title + badge,value : val});
              }
              var container = mQuery('#bee-plugin-container');
              var viewpanel = mQuery('#bee-plugin-viewpanel');
              // Activate the builder
              // builder.addClass('builder-active').removeClass('hide');
              container.removeClass('hide');
              viewpanel.addClass('builder-active');
              //viewpanel.addClass('builder-active');
              request(
                  'POST',
                  mauticBaseUrl+'beefree/getcredentials',
                  '',
                  'application/x-www-form-urlencoded',
                  function(token) {
                      BeePlugin.create(token, beeConfig, function(beePluginInstance) {
                          bee = beePluginInstance;
                          var themejson = mQuery('textarea.bee-editor-json').val();
                          if(themejson != null && themejson != ""){
                         //   save('template1.json', themejson);
                             // try {
                                  themejson=themejson.trim();
                                  bee.start(mQuery.parseJSON(themejson));
                                  bee.toggleStructure();
                                  Mautic.setCustomHeader(formName);
                            // } catch(e) {
                              //    alert(e); // error in the above string (in this case, yes)!
                             // }

                          }else{
                              request(
                                  'GET',
                                  mQuery('#builder_url').val()+'?beetemplate=blank',
                                  null,
                                  null,
                                  function(template) {
                                     // save('template2.json', JSON.stringify(template));
                                      bee.start(mQuery.parseJSON(template));
                                      bee.toggleStructure();
                                      Mautic.setCustomHeader(formName);
                                  });
                          }
                      });
                  });
          });
      }
      Mautic.setCustomHeader = function (formName) {
          var TitleValue = "";
          if(formName == "pageform"){
              TitleValue = mQuery('#page_title').val();
          } else if( formName == "emailform"){
              TitleValue = mQuery('#emailform_subject').val();
          }
          mQuery('#BeeEditor_Header').html(TitleValue);
      }
      Mautic.closeBeeEditor = function (callback) {
          mQuery('body').css('overflow-y', '');
          mQuery('#bee-plugin-viewpanel').css('height', '');
          var viewpanel = mQuery('#bee-plugin-viewpanel');
          var container = mQuery('#bee-plugin-container');
          viewpanel.removeClass('builder-active');
          container.addClass('hide');
          viewpanel.html("");
          callback();
      }
