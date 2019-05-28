Le.slackOnLoad = function (){

    Le.getTokens('email:getBuilderTokens', function(tokens) {
         mQuery.each(tokens, function(k,v){
             if (!k.match(/leadfield=/i)){
                delete tokens[k];
             }
             if (k.match(/leadfield=created_source/i) || k.match(/leadfield=status/i)){
                 delete tokens[k];
             }
        });
        var k, keys = [];
        for (k in tokens) {
            if (tokens.hasOwnProperty(k)) {
                keys.push(k);
            }
        }
        //keys.sort();
        //var tborder= "<table border='1' class='email-subject-table' ><tbody style='background-color:whitesmoke;'><tr>";
        var tborder= "<div border='1' class='email-subject-table' ><tbody style='background-color:whitesmoke;'><tr>";
        for (var i = 0; i < keys.length; i++) {
            var val = keys[i];
            var title = tokens[val];
            if(i % 3 == 0 && i  !=0 ){
                tborder+= "</tr><tr>";
            }
            var value= '<li class="email-subject-table-border"><a class="email-subject-token" id="insert-value" data-cmd="inserttoken" data-email-token="' + val + '" title="' + title + '">' + title +'</a></li>';
            tborder+= value;
        }
        tborder+= "</div>";


        mQuery('.insert-tokens').html(tborder);
        mQuery('[data-email-token]').click(function(e) {
            e.preventDefault();
            var currentLink = mQuery(this);
            var value = currentLink.attr('data-email-token');
            var subValue= mQuery('#slack_message').val();
            if(subValue == ''){
                mQuery("#slack_message").val(value);
            } else {
                if(subValue.includes(value)){

                } else {
                    //subValue+=value;
                    //mQuery("#sms_message").val(subValue);
                    var cursorPos = mQuery('#slack_message').prop('selectionStart');
                    var v = subValue;
                    var textBefore = v.substring(0,  cursorPos);
                    var textAfter  = v.substring(cursorPos, v.length);

                    mQuery('#slack_message').val(textBefore + value + textAfter);
                }

            }
        });
    });
};