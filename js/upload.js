/**
 * Created by tballmann on 07.08.14.
 */

// init system
var dam = DAM.getInstance();



var dropZoneHandler;
var configUploadPredefinedSelectbox = configUploadPredefinedSelectbox || {};
var configCollectionAssignOptions = configCollectionAssignOptions  || {};

var initUploadTemplate;

/**
 * enable upload
 */
var dropZone = $('#dropzone');
var enableUpload = function () {

    // ist der upload button deaktiviert dann gibt es keine upload möglichkeit
    if($('#btn-upload').hasClass('disabled'))
    {
        return;
    }


    // enable upload buttons
    dropZone.find('button[data-action]').click(function (e) {

        e.preventDefault();

        var button = $(this);
        var action = button.data('action');

        if(action == 'close')
        {
            window.onbeforeunload = null;
            window.location.reload();
        }
        else if(action == 'edit')
        {
            var ids = [];
            dropZone.find('[data-id]').each(function () {
                ids.push( $(this).attr('data-id') );
            });

            window.location = button.data('href') + '?selectedItems=' + ids.join(',');

        }
        else if(action == 'upload')
        {
            if($('#dropzone').parsley().validate('dovalidate'))
            {
                $(this).addClass('disabled');
                $('#uploadAssetCollection').selectize()[0].selectize.disable();

                // start upload
                dropZoneHandler.processQueue();
            }

        }

    });


    // DD drop zone
    var doc_leave_timer;
    $(document).on('dragenter', function (e) {
        // docEnter
        clearTimeout(doc_leave_timer);
        e.preventDefault();

        dropZone.removeClass('hide');

    }).on('dragover', function (e) {
        // docOver
        clearTimeout(doc_leave_timer);
        e.preventDefault();

    }).on('dragleave', function () {
        // docLeave
        doc_leave_timer = setTimeout(function () {
            dropZone.addClass('hide');
        }, 500);

    });


    /**
     * metafield lang switcher
     * @param       element   lang dropdown element
     * @param bool  global    sprache global umschalten
     */
    var metafieldSwitchLang = function (element, global) {

        // change label
        var lang = element.data('language');
        var label = element.parents('.dropdown-menu').prev('.dropdown-toggle');
        label.html( lang.toUpperCase() + ' <span class="caret"></span>');

        // swtich fields
        var container = global
            ? dropZone.find('.upload-progress, #metadata-batch-input')
            : element.parents('.form-group');
        container.find('.input-group-element[data-language]:not([data-language=""])').addClass('hide');
        container.find('.input-group-element[data-language=' + lang + ']').removeClass('hide');

        //change label of all field specific language switches
        if(global) {
            container.find('.dropdown-toggle').html(lang.toUpperCase() + ' <span class="caret"></span>');
        }

    };

    dropZone.find('#metadata-batch-language-switch .dropdown-menu a').click(function (e) {
        e.preventDefault();

        metafieldSwitchLang( $(this), true );
    });


    /**
     *
     * @param template
     */
    initUploadTemplate = function (template) {

        // add unique id to form block, exchange all ids in validator-data-attributes and add parsely-group
        var uniqueId = "id-" + Math.random().toString(36).substr(2, 16);
        $(template).attr("id", uniqueId);

        var replacer = new RegExp("#form-metadata","g");
        $(template).html($(template).html().replace(replacer, "#" + uniqueId));
        $(template).find('.js-form-control').attr('data-parsley-group','dovalidate');

        // enable switch lang
        template.find('.dropdown-menu a').click(function (e) {
            e.preventDefault();

            metafieldSwitchLang( $(this), false );
        });


        // enable selectbox
        for(var name in configUploadPredefinedSelectbox) {
            template.find(':input[name*=metadata\\$' + name + '\\$]')
                .removeClass('form-control')
                .selectize( configUploadPredefinedSelectbox[name] );
        }


        // enable date input type
        dam.requireCalendar(function () {
            template.find('.input-group.date > input').datetimepicker({
                language: websiteConfig['language'],
                pickTime: false
            });

        });


        // enable collection assign
        if(typeof configCollectionAssignOptions != 'undefined') {
            dam.requireSelectize(function () {
                template.find(":input[name*='metadata$collection$']").selectize({
                    plugins: ['remove_button_colored'],
                    persist: false,
                    maxItems: null,
                    valueField: 'id',
                    labelField: 'name',
                    searchField: ['name'],
                    options: configCollectionAssignOptions.options,
                    render: {
                        item: function (item, escape) {
                            return '<span class="collection item">' +
                                '<span class="label name" style="background-color: ' + item.color + ';">' + escape(item.name) + '</span>' +
                                '</span>';
                        },
                        option: function (item, escape) {
                            return '<div><span class="collection item">' +
                                '<span class="label name" style="background-color: ' + item.color + ';">' + escape(item.name) + '</span>' +
                                '</span></div>';
                        }
                    }
                });
            });
        }

    };


    //window.onbeforeunload = function () {
    //
    //};


    // enable dropzone js
    var uploadInfo = dropZone.find('.upload-info');
    var globalProgress = dropZone.find('.upload-info .progress-bar');

    dropZoneHandler = new Dropzone('#dropzone', {
        previewsContainer: dropZone.find('.upload-progress')[0],
        previewTemplate: dropZone.find('.upload-progress .upload-progress-template').html(),
        parallelUploads: 1,
        maxFilesize: 1024,
        autoProcessQueue: false,
        init: function() {

            this.on('dragover', function () {
                // bugfix, das dragover event wird auf dem document selbst nicht mehr ausgeführt !
                clearTimeout(doc_leave_timer);

                dropZoneHandler.options.autoProcessQueue = false;
            });

            this.on('addedfile', function (file) {
                dropZone.removeClass('hide');

                dropZone.addClass('active');
                dropZone.removeClass('complete');
                dropZone.addClass('processing');

                // total files
                var label = uploadInfo.find('#upload-progress-totalFiles');
                var count = label.data('count') == undefined ? 0 : label.data('count');
                count++;
                label.data('count',count).text( count )

                // total bytes
                label = uploadInfo.find('#upload-progress-totalBytes');
                var total = label.data('total') == undefined ? 0 : label.data('total');
                total += file.size;

                var sizes = ['Bytes', 'KB', 'MB', 'GB'];
                var i = 0, totalLabel = total;
                while(totalLabel >= 1024 && (totalLabel /= 1024))
                {
                    i++;
                }
                label.data('total', total).text( Math.round(totalLabel) + ' ' + sizes[i] );


                initUploadTemplate( $(file.previewTemplate) );


                // warn user from leaving an active upload
                window.onbeforeunload = function () {
                    return '';
                };
            });


            /**
             * update progress
             */
            this.on('totaluploadprogress', function (progress, totalBytes, totalBytesSent) {

                globalProgress.width( progress + '%' );
                globalProgress.text(  Math.round(progress) + '%' );

                uploadInfo.find('#upload-progress-totalBytesSent').text( totalBytesSent );

                if(progress == 100)
                {
                    dropZone.addClass('complete');
                    dropZone.removeClass('processing');
                }

            });


            /**
             * send form data along with the asset
             */
            this.on('sending', function (file, xhr, formData) {

                // init
                var container = $(file.previewElement);
                var input = container.find(':input');


                // add metadata to post data
                input.each(function () {
                    if($(this).attr('type') == 'checkbox') {
                        if($(this).is(':checked')) {
                            formData.append( $(this).prop('name'), $(this).val() );
                        } else {
                            formData.append( $(this).prop('name'), "" );
                        }
                    } else {
                        formData.append( $(this).prop('name'), $(this).val() );
                    }
                });


                // add opt. filename
                var rename = uploadInfo.find('input[name=rename]').val();
                if(rename)
                {
                    rename = rename + '-' + container.index() + file.name.substr(file.name.lastIndexOf('.'));
                    formData.append( 'filename', rename);
                }


                // disable all inputs
                input.prop('disabled', true);

            });


            /**
             * store server response
             */
            this.on('success', function (file, response) {

                // init
                var container = $(file.previewElement);
                var asset = response[ file.name ];

                if(asset)
                {
                    container.attr('data-id', asset.id);
                }
                else
                {
                    // processing asset failed
                    container
                        .removeClass("dz-success")
                        .removeClass("dz-progress")
                        .addClass("dz-error")
                    ;

                    container.find('[data-dz-errormessage]').html(response);
                }

            });


            /**
             * ...
             */
            this.on('complete', function () {

                // FIXME bugfix, https://github.com/enyo/dropzone/issues/531
                if(this.options.autoProcessQueue == false && this.getQueuedFiles().length > 0)
                {
                    this.processQueue();
                    return;
                }

                $(this).removeClass('disabled');
                $('#uploadAssetCollection').selectize()[0].selectize.enable();


                // disable warning
                window.onbeforeunload = null;
            });
        }
    });


    // enable change upload target folder
    dropZone.find('#upload-target').click(function () {

        // init
        var parent = dropZone.find('input[name=pid]');
        var viewPath = $(this).parent().next();


        // load tree
        $.ajax({
            url: '/dam/asset/folderTree'
        }).done(function(html) {

            var dialog = $(html);
            dialog.modal();

            dialog.find('.folderTree a').click(function (e) {
                e.preventDefault();

                parent.val( $(this).data('id') );
                viewPath.val( $(this).data('path') );

                dialog.modal('hide');
            });

        });

    });


    // enable upload feature panel
    var swapUploadTools = dropZone.find('#swapUploadTools');
    swapUploadTools.click(function (e) {
        e.preventDefault();

        // set icon
        var icon = $(this).find('.glyphicon');
        icon.toggleClass('glyphicon-chevron-down');
        icon.toggleClass('glyphicon-chevron-up');

        // toggle show / hide
        uploadInfo.toggleClass('collapsed');


        // switch to upload tab
        if(icon.hasClass('glyphicon-chevron-down'))
        {
            // close
            uploadInfo.find('.nav-tabs a[href=#upload]').click();
        }
        else
        {
            // open
        }

    });


    // Module Pattern
    return {

        initUploadPanel: initUploadTemplate

    };
}();


/**
 * default upload
 */
$('#btn-upload').on('click', function (e) {
    e.preventDefault();

    dropZone.click();
});


/**
 * upload archive and extract it
 */
$('#btn-upload-archive').on('click', function (e) {
    e.preventDefault();

    dropZone.find('input[name=archive]').val("1");
    dropZone.click();

});
