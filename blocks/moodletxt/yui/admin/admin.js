YUI.add('moodle-block_moodletxt-admin', function(Y) {
    M.block_moodletxt = {};
    M.block_moodletxt.admin = {
        init: function() {
            var el      = $('#fitem_id_accountUrl');
            var select  = $('#id_accountCtxtInstance');

            if(select.val() !== 'URL') {
                el.hide();
            }

            select.change(function(){
                if($(this).val() === 'URL') {
                    el.show();
                } else {
                    el.hide();
                }
            });
        }
    };
}, '@VERSION@', {
    requires: ['console', 'jquery']
});
