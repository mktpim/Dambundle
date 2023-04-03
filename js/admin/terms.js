var terms = {
    init: function() {
        this.initCKEditor();
    },

    initCKEditor: function() {
        var toolbar = [
            { name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
            { name: 'basicstyles', items: [ 'Bold', 'Italic' ] },
            { name: 'list', items: [ 'NumberedList', 'BulletedList'] },
            { name: 'tools', items: [  'ShowBlocks'] }

        ];


        $('.ckeditor-textarea').each(function(i,el){
            this.introductionCkEditor = CKEDITOR.replace($(el).attr('id'), {
                toolbar: toolbar,
                height: 500
            });
        }.bind(this));


    }
};

$(function(){
    terms.init();
});