/**
 * Created by tballmann on 17.02.14.
 */

var DAM = (function () {

    // Instance stores a reference to the Singleton
    var instance;

    function init() {

        // Singleton


        // Init
        var workspace = $('#main');         // main
        var selectedList = null;            // selected items


        // enable sidebar switch
        var sidebar = $('#sidebar');
        var toggleButton = $('#btn-toggle-sidebar');

        toggleButton.click(function () {
            sidebar.toggleClass('hide');
            workspace.toggleClass('main-sidebar');
            $(this).toggleClass('active');

            localStorage.setItem('sidebarActive', sidebar.hasClass('hide') ? '0' : '1');
        });

        var sidebarIsActive = localStorage.getItem('sidebarActive');
        if (sidebarIsActive == null) {
            sidebarIsActive = !sidebar.hasClass('hide');
            localStorage.setItem('sidebarActive', sidebar.hasClass('hide') ? '0' : '1');
        }

        if (sidebarIsActive == sidebar.hasClass('hide')) {
            toggleButton.click();
        }

        /**
         * handle grid item buttons
         */
        var itemActionHandler = function (e) {
            e.preventDefault();
            e.stopPropagation();

            var item = $(this).parents('.grid-item');
            var button = $(this);
            var icon = button.find('.glyphicon');
            var action = button.data('action');
            var batch = item.hasClass('selected');


            // get ids from selected items
            var ids = [];
            if (item.length) {
                ids.push(item.data().id);
            }
            $(selectedList).each(function () {
                ids.push($(this).data('id'));
            });


            if (action == 'bookmark') {
                $.ajax({
                    url: button.prop('href'),
                    beforeSend: function () {
                        item.addClass('saving');
                        button.addClass('active');
                    }
                }).done(function (json) {

                    item.removeClass('saving');
                    button.removeClass('active');

                    if (json.added) {
                        icon.addClass('glyphicon-star');
                        icon.removeClass('glyphicon-star-empty');
                    }
                    else {
                        icon.addClass('glyphicon-star-empty');
                        icon.removeClass('glyphicon-star');
                    }

                });
            }
            else if (action == 'collection' || action == 'share') {
                $.ajax({
                    url: button.prop('href'),
                    data: {
                        'selectedItems': ids.join(',')
                    },
                    beforeSend: function () {
                        item.addClass('saving');
                        button.addClass('active');
                    }
                }).done(function (html) {

                    item.removeClass('saving');
                    button.removeClass('active');

                    var dialog = $(html);

                    var clipboard = new Clipboard('.clipboard-btn');
                    clipboard.on('success', function (e) {
                        e.clearSelection();
                        var clipboardBtn = $(e.trigger);
                        $('.clipboard-message').remove();

                        var message = $('<span class="clipboard-message" style="margin-right:10px; display:none;">' + clipboardBtn.data('success-message') + '</span>');
                        message.prependTo(clipboardBtn).fadeIn().delay(1000).fadeOut();
                    });

                    // enable loading indicator
                    dialog.find('[type=submit]').click(function (e) {
                        var btn = $(this);
                        btn.addClass('disabled');
                    });

                    // send form via ajax request
                    dialog.find('form').submit(function (e) {
                        e.preventDefault();

                        var form = $(this);

                        $.ajax({
                            url: form.prop('action'),
                            type: form.prop('method'),
                            data: form.serialize() + '&' + form.find('[type=submit].disabled').prop('name') + "=1"
                        }).done(function (html) {

                            if ((action == 'collection') || button.data('reload')) {
                                window.location.reload();
                            }
                            else {
                                // show response as modal
                                if (!html) {
                                    dialog.modal('hide');
                                }
                                else {
                                    dialog.off();
                                    dialog.modal('hide');
                                    var dialogResponse = $(html);

                                    // show dialog
                                    dialogResponse.modal();

                                    // remove dialog on close
                                    dialogResponse.on('hidden.bs.modal', function () {
                                        $(this).data('bs.modal', null);
                                        $('body > .modal').remove();
                                    });
                                }
                            }

                        });
                    });

                    // show dialog
                    dialog.modal();

                    // remove dialog on close
                    dialog.on('hidden.bs.modal', function () {
                        $(this).data('bs.modal', null);
                        $('body > .modal').remove();
                    });

                });
            }
            else if (action == 'relocate' || action == 'download') {
                $.ajax({
                    url: button.prop('href'),
                    data: {
                        'selectedItems': batch ? ids.join(',') : ''
                    },
                    beforeSend: function () {
                        item.addClass('saving');
                        button.addClass('active');
                    }
                }).done(function (html) {

                    item.removeClass('saving');
                    button.removeClass('active');

                    var dialog = $(html);

                    // enable loading indicator
                    dialog.find('input[type=submit]').click(function () {
                        var btn = $(this);
                        btn.button('loading');
                    });

                    // show dialog
                    dialog.modal();

                    // remove dialog on close
                    dialog.on('hidden.bs.modal', function () {
                        $(this).data('bs.modal', null);
                        $('body > .modal').remove();
                    });


                });
            }
            else if (action == 'delete') {
                $.ajax({
                    url: button.prop('href'),
                    data: {
                        'selectedItems': batch ? ids.join(',') : ''
                    },
                    beforeSend: function () {
                        item.addClass('saving');
                        button.addClass('active');
                    }
                }).done(function (html) {

                    item.removeClass('saving');
                    button.removeClass('active');

                    var dialog = $(html);

                    // enable loading indicator
                    dialog.find('input[type=submit]').click(function (e) {
                        var btn = $(this);
                        btn.button('loading');
                    });

                    // show dialog
                    dialog.modal();

                });
            }
            else if (action == 'zip' || action == 'batch-edit') {
                var url = button.prop('href') + '?selectedItems=' + ids.join(',');
                window.open(url);
            }

        };


        /**
         * enable extended preview
         */
        var itemExtendedPreview = function () {

            var previewContainer = $(this).parents('.asset-preview');
            var thumbs = previewContainer.find('img');
            var preview = $(thumbs[0]);
            thumbs = thumbs.splice(1);

            preview.prop('src', $(thumbs[0]).prop('src'));

            var show = function (mousePos) {
                var iconWidth = preview.width();
                var offset = ((Math.floor((mousePos / iconWidth) * thumbs.length - 1) % thumbs.length) + 1) % thumbs.length;
                var oldOffset = $(this).data('offset');

                if (offset !== oldOffset) {
                    var thumb = $(thumbs[offset]);
                    preview.prop('src', thumb.prop('src'));
                    $(this).data('offset', offset);
                }
            };

            // desktop browser
            previewContainer.on('mousemove', function (e) {

                var mousePos = e.pageX - $(this).offset().left;
                show(mousePos);

            });

            // touch devices
            previewContainer.on('touchmove', function (e) {

                var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
                var mousePos = touch.pageX - $(this).offset().left;

                show(mousePos);
            });

        };


        /**
         * select items and drag it into a folder or make a batch operation
         */
        var itemSelectHandler = function () {

            var assetList = $(this).parents('.grid-asset');
            var assetItem = $(this);

            /**
             * toggle item selection
             */
            var toggleActions = function () {

                assetItem.toggleClass('selected');
                selectedList = assetList.find('.selected')

            };


            // shift | ctrl click to mark items
            assetItem.on('click', function (e) {
                if (e.shiftKey || e.ctrlKey) {
                    e.preventDefault();
                    toggleActions();
                }

            });

            // two finger touch
            assetItem.on('touchstart', function (e) {

                if (e.originalEvent.changedTouches.length >= 1 && e.originalEvent.touches.length == 2) {
                    e.preventDefault();

                    toggleActions();
                }
            });
        };


        /**
         * enable sticky folder path while scrooling
         * @todo
         */
        var enableStickyFolderPath = function (list) {

            //            list.stacks({
            //                body: '.section',
            //                title: '.sticky',
            //                margin: 50,
            //                offset: 0
            //            });

        };


        /**
         * load a url in a dialog box
         * @param url
         */
        var ajaxDialog = function (url, element) {

            var
                ajaxForm = element.data('ajax-form'),
                ajaxData = element.data('ajax-param');

            // pass ajax-params if they exist and are
            // well formed json
            try {
                ajaxData = $.parseJSON(ajaxData);
            } catch (e) {
            }

            $.ajax({
                url: url,
                data: ajaxData
            }).done(function (html) {
                var dialog = $(html);

                // show dialog
                dialog.modal();


                // send form via ajax request
                if (ajaxForm) {
                    dialog.find('form').submit(function (e) {
                        e.preventDefault();

                        var form = $(this);

                        $.ajax({
                            url: form.prop('action'),
                            type: form.prop('method'),
                            data: form.serialize() + '&' + form.find('[type=submit].disabled').prop('name') + "=1"
                        }).done(function () {

                            if (ajaxForm == 'reload') {
                                window.location.reload();
                            }
                            else if (ajaxForm == 'close') {
                                dialog.modal('hide');
                            }

                        });
                    });
                }

            });
        };


        /**
         * calendar already loaded
         * @type {boolean}
         */
        var requireCalendar = false;
        var requireCalendarQueue = [];
        var requireCalendarLoading = false;

        var requireSelectize = false;
        var requireSelectizeQueue = [];
        var requireSelectizeLoading = false;

        var requireParsley = false;
        var requireParsleyQueue = [];
        var requireParsleyLoading = false;


        return {

            // Public methods and variables

            /**
             * global inits
             */
            init: function () {

                // enable ajax-popup
                $('a.ajax-dialog').click(function (e) {
                    e.preventDefault();

                    ajaxDialog($(this).prop('href'), $(this));
                });


                // nice tooltip
                if ((("ontouchstart" in window || window.DocumentTouch && document instanceof DocumentTouch) == true) === false) {
                    $('body').tooltip({ selector: '[title]', container: 'body' });
                }


                // hook item actions
                $('#main [data-action]').bind('click touchend', itemActionHandler);

            },


            /**
             * init asset grid list
             */
            initGrid: function (assetList) {

                // extended item preview
                assetList.find('.asset-preview .asset-preview-alternates').each(itemExtendedPreview);

                var gridItemSelector = '.grid-item';

                var gridItems = assetList.find(gridItemSelector);

                $('#main').on('click', function (e) {
                    if (!$(e.target).closest(gridItemSelector).length && !$(e.target).is(gridItemSelector)) {
                        assetList.find('.selected').removeClass('selected');
                    }
                });

                // enable select and drag actions
                gridItems.each(itemSelectHandler);


                //
                enableStickyFolderPath(assetList);


            },


            /**
             * enable upload functionality
             */
            enableUpload: function () {

                enableUpload();

            },


            /**
             * enable asset preview with hover effect
             * @param selector
             */
            enableExtendedAssetPreview: function (selector) {

                selector.each(itemExtendedPreview);

            },


            /**
             * load required libs and execute callback when finish
             * @param callback
             */
            requireCalendar: function (callback) {

                if (requireCalendar) {
                    callback();
                    return;
                }

                // add callback to require queue
                requireCalendarQueue.push(callback);

                if (!requireCalendarLoading) {
                    requireCalendarLoading = true;
                    var lang = websiteConfig && websiteConfig.language ? (websiteConfig.language == 'en' ? 'en-gb' : websiteConfig.language.replace("_", "-").toLowerCase()) : websiteConfig.language;

                    $.getScript("/bundles/pimcoredam/vendor/bootstrap-datetimepicker/moment.js")
                        .done(function () {
                            $.getScript("/bundles/pimcoredam/vendor/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js")
                                .done(function () {
                                    $.getScript("/bundles/pimcoredam/vendor/bootstrap-datetimepicker/locale/" + lang + ".js")
                                        .done(function () {

                                            $('head').append($('<link rel="stylesheet" type="text/css" />').attr('href', '/bundles/pimcoredam/vendor/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css'));
                                            requireCalendar = true;

                                            for (var i = 0; i < requireCalendarQueue.length; i++) {
                                                requireCalendarQueue[i]();
                                            }

                                            requireCalendarQueue = [];
                                        });
                                });
                        });
                }
            },



            /**
             * load required libs and execute callback when finish
             * @param callback
             */
            requireSelectize: function (callback) {

                if (requireSelectize) {
                    callback();
                    return;
                }

                // add callback to require queue
                requireSelectizeQueue.push(callback);


                if (!requireSelectizeLoading) {
                    requireSelectizeLoading = true;
                    var lang = websiteConfig.language == 'en' ? 'en-gb' : websiteConfig.language;

                    $.getScript("/bundles/pimcoredam/vendor/selectize-js/selectize.min.js")
                        .done(function () {

                            $('head').append($('<link rel="stylesheet" type="text/css" />').attr('href', '/bundles/pimcoredam/vendor/selectize-js/selectize.css'));
                            requireSelectize = true;

                            /**
                             * eigenes plugin hinzufügen
                             * workaround damit der x button mit bootstrap zuammen passt und die selbe farbe hat wie das label
                             */
                            Selectize.define('remove_button_colored', function (options) {
                                // options: plugin-specific options
                                // this: selectize instance

                                if (this.settings.mode === 'single') return;

                                options = $.extend({
                                    label: '&times;',
                                    title: 'Remove',
                                    className: 'remove',
                                    append: true
                                }, options);

                                var self = this;
                                var html = '<a href="javascript:void(0)" class="' + options.className + '" tabindex="-1" title="' + options.title + '" style="background-color: ;">' + options.label + '</a>';

                                /**
                                 * Appends an element as a child (with raw HTML).
                                 *
                                 * @param {string} html_container
                                 * @param {string} html_element
                                 * @return {string}
                                 */
                                var append = function (html_container, html_element) {
                                    var pos = html_container.search(/(<\/[^>]+>\s*)$/);
                                    return html_container.substring(0, pos) + html_element + html_container.substring(pos);
                                };

                                this.setup = (function () {
                                    var original = self.setup;
                                    return function () {
                                        // override the item rendering method to add the button to each
                                        if (options.append) {
                                            var render_item = self.settings.render.item;
                                            self.settings.render.item = function (data) {
                                                var x = html.replace('background-color: ;', 'background-color: ' + arguments[0].color + ';');
                                                return append(render_item.apply(this, arguments), x);
                                            };
                                        }

                                        original.apply(this, arguments);

                                        // add event listener
                                        this.$control.on('click', '.' + options.className, function (e) {
                                            e.preventDefault();
                                            if (self.isLocked) return;

                                            var $item = $(e.currentTarget).parent();
                                            self.setActiveItem($item);
                                            if (self.deleteSelection()) {
                                                self.setCaret(self.items.length);
                                            }
                                        });

                                    };
                                })();
                            });


                            /**
                             * eigenes plugin hinzufügen
                             * workaround das werte nicht angezeigt werden solange sie nicht als option verfügbar ist
                             */
                            Selectize.define('add_value_to_options', function () {

                                this.addOptionGroup('_values_', { name: '' });
                                for (var opt in this.settings.items) {
                                    this.addOption({ name: this.settings.items[opt], group: '_values_' });
                                }

                            });


                            /**
                             * eigenes plugin hinzufügen
                             * auswahl über ein dialog overlay
                             */
                            Selectize.define('dialog_overlay', function (options) {
                                var self = this;


                                options = $.extend({
                                    overlay: $(''
                                        + '<div class="modal dialog_overlay" role="dialog">'
                                        + '<div class="modal-dialog modal-lg">'
                                        + '<div class="modal-content">'
                                        + '<div class="modal-body">'
                                        //+ '<h4>eigene</h4>'
                                        + '<input type="text" name="custom" class="custom" placeholder="Add">'
                                        + '<ul class="list-group options"></ul>'
                                        + '</div>'

                                        + '<div class="modal-footer">'
                                        + '<button type="button" class="btn btn-primary" data-save>OK</button>'
                                        + '</div>'
                                        + '</div>'
                                        + '</div>'
                                        + '</div>'
                                    )
                                }, options);


                                var customOptions;


                                self.setup = (function () {
                                    var original = self.setup;
                                    return function () {

                                        self.settings.hideSelected = false;


                                        /**
                                         * render optgroup header
                                         * @param group
                                         * @param escape
                                         * @returns {string}
                                         */
                                        self.settings.render.optgroup_header = function (group, escape) {
                                            if (escape(group.name) != '') {
                                                return ''
                                                    + '<h4>'
                                                    + escape(group.name)
                                                    + '</h4>'
                                                    ;
                                            }
                                        };

                                        /**
                                         * render optgroup
                                         * @param optgroup
                                         * @returns {string}
                                         */
                                        self.settings.render.optgroup = function (optgroup) {
                                            if (optgroup.name != '_values_') {
                                                return ''
                                                    + '<li class="list-group-item">'
                                                    + optgroup.html
                                                    + '</li>'
                                                    ;
                                            }
                                            else {
                                                return '';
                                            }
                                        };

                                        /**
                                         * render options
                                         * @param item
                                         * @param escape
                                         * @returns {string}
                                         */
                                        self.settings.render.option = function (item, escape) {
                                            return ''
                                                + '<label class="checkbox-inline">'
                                                + '<input type="checkbox" value="' + item.name + '">'
                                                + escape(item.name)
                                                + '</label>'
                                                ;
                                        };


                                        original.apply(this, arguments);


                                        // commit
                                        options.overlay.find('[data-save]').click(function () {

                                            // clear
                                            self.clear();

                                            // add defined
                                            options.overlay.find('input:checked').each(function () {

                                                self.addItem($(this).val());

                                            });

                                            // add custom
                                            customOptions[0].selectize.items.forEach(function (item) {

                                                self.addOption({ name: item });
                                                self.addItem(item);

                                            });


                                            // close
                                            options.overlay.modal('hide');

                                        });
                                    };
                                })();


                                /**
                                 * show available options
                                 */
                                self.open = (function () {

                                    return function () {

                                        // init
                                        var overlay = options.overlay;
                                        var htmlOptions = $(self.$dropdown_content.html());


                                        // active current selection
                                        $(htmlOptions).find('input[type=checkbox]').filter(function () {
                                            return self.items.indexOf($(this).val()) != -1;
                                        }).prop('checked', true);


                                        // custom
                                        customOptions = $(overlay).find('input.custom').selectize({
                                            create: true,
                                            valueField: 'name',
                                            labelField: 'name',
                                            searchField: ['name']
                                        });

                                        for (var key in self.options) {
                                            var item = self.options[key];
                                            if (item.group == '_values_') {
                                                customOptions[0].selectize.addOption({ name: item.name });
                                                customOptions[0].selectize.addItem(item.name);
                                            }
                                        }


                                        // show overlay
                                        overlay.find('.modal-content .modal-body .options').html(htmlOptions);
                                        overlay.modal('show');
                                    };

                                })();
                            });


                            // execute callbacks
                            for (var i = 0; i < requireSelectizeQueue.length; i++) {
                                requireSelectizeQueue[i]();
                            }

                            requireSelectizeQueue = [];

                        })
                        .fail(function (jqxhr, settings, exception) {
                            console.log("load fail");
                        });
                }
            },


            /**
             * load required libs and execute callback when finish
             * @param callback
             */
            requireParsley: function (callback) {

                if (requireParsley) {
                    callback();
                    return;
                }

                // add callback to require queue
                requireParsleyQueue.push(callback);

                if (!requireParsleyLoading) {
                    requireParsleyLoading = true;
                    var lang = websiteConfig && websiteConfig.language ? (websiteConfig.language == 'de_AT' ? 'de' : websiteConfig.language.split("_")[0].toLowerCase()) : websiteConfig.language;

                    window.ParsleyConfig = {
                        validators: {
                            conditionalRequired: {
                                fn: function (value, requirements) {
                                    var containerId = requirements[0];
                                    var fieldname = requirements[1];
                                    var expectedValue = requirements[2];

                                    if (!value) {
                                        var targetField = $(containerId).find('[name="' + fieldname + '"]');

                                        var currentValue = targetField.val();
                                        if (targetField.attr('type') == 'checkbox' && !targetField.is(':checked')) {
                                            currentValue = null;
                                        }

                                        if (expectedValue == currentValue) {
                                            return false;
                                        }
                                    }

                                    return true;
                                },
                                priority: 256
                            }
                        }
                    };

                    $.getScript("/bundles/pimcoredam/vendor/parsley.min.js")
                        .done(function () {
                            $.getScript("/bundles/pimcoredam/vendor/parsley-i18n/" + lang + ".js")
                                .done(function () {

                                    window.ParsleyValidator.setLocale(lang);

                                    requireParsley = true;

                                    for (var i = 0; i < requireParsleyQueue.length; i++) {
                                        requireParsleyQueue[i]();
                                    }

                                    requireParsleyQueue = [];
                                });
                        });
                }
            },


            /**
             * pop with folder tree
             * @param callback
             */
            requireFolderTree: function (callback) {
                // load tree
                $.ajax({
                    url: '/dam/asset/folderTree'
                }).done(function (html) {

                    var dialog = $(html);
                    dialog.modal();

                    /** TODO:   change to dialog as event selector to avoid event conflicts
                     *          f.e. dialog.on(...)
                     */
                    $('body').on('hidden.bs.modal', '.modal', function () {
                        $(this).remove();
                    });

                    callback(dialog);
                });
            },

            /**
             * Initialize functionality for folder tree actions (i.e. relocate modal).
             *
             * @param tpl - the template to use for an element of the tree
             */
            initFolderTree: function (tpl) {

                var clickSelector = '.folderTree-folder.childs > .open-folder';

                $('.folder-tree-modal').on('hidden.bs.modal', function (e) {
                    $('body').off('click', clickSelector);
                    $(this).remove();
                });

                var animationSpeed = 200;

                // enable open / close
                $('body').on('click', clickSelector,
                    function () {

                        var list = $('<ul class="folderTree">'),
                            container = $(this).next(),
                            id = container.attr('data-id'),
                            self = this;

                        var parent = $(self).parent('.folderTree-folder');

                        var subtree = parent.find('.folderTree');
                        if (subtree.length) {
                            subtree.slideToggle(animationSpeed, function () {
                                parent.toggleClass('open');
                            });
                        } else {
                            parent.toggleClass('open');
                        }

                        $(self).toggleClass('glyphicon-plus-sign');
                        $(self).toggleClass('glyphicon-minus-sign');

                        if (parent.find('ul').length == 0 && !parent.data('loaded')
                            && $(self).parent('.folderTree-folder').hasClass('open')) {

                            $.ajax({
                                url: '/dam/asset/getFolderList',
                                data: {
                                    target: id
                                }
                            })
                                .done(function (json) {

                                    // we have no childs, disable open / close
                                    if (json.length == 0) {
                                        container.parent()
                                            .removeClass('childs')
                                            .find('> .open-folder')
                                            .removeClass('glyphicon-plus-sign')
                                            .addClass('glyphicon-folder-close');
                                        return;
                                    }


                                    // add
                                    $(json).each(function () {

                                        var item = tpl;
                                        item = item.replace(/\{id\}/g, this.id);
                                        item = item.replace('{name}', this.name);
                                        item = item.replace('{path}', this.path);

                                        list.append(item);
                                    });

                                    // hide list to wait for animation
                                    list.hide();

                                    // append html
                                    container.after(list);
                                    container.parent().find(list).slideDown(animationSpeed);

                                });

                        }
                    });
            },

            /**
             * Init download options functionality (aspect ratio, unit change, ...)
             *
             * @param dimension
             */
            initDownload: function (dimension) {

                var scopeSelector = '.download-container';
                var $scope = $(scopeSelector);
                var $body = $('body');

                $scope.find('select[name=preset]').change(function () {
                    $scope.find('[data-convert-custome]').toggleClass('hide', $(this).val() != '');
                });


                // convert values px|cm
                $scope.find('select[name=unit]').change(function () {

                    var unit = $(this).val();
                    var width = $scope.find('[data-convert-custome] input[name=width]');
                    var height = $scope.find('[data-convert-custome] input[name=height]');
                    var dpi = $scope.find('[data-convert-custome] input[name=dpi]');

                    var w = width.val();
                    var h = height.val();

                    // ausgabe
                    if (unit == 'cm') {
                        // to cm
                        w = w / dpi.val() * 2.54;
                        h = h / dpi.val() * 2.54;

                        width.val(Math.round(w * 100) / 100);
                        height.val(Math.round(h * 100) / 100);
                    }
                    else if (unit == 'px') {
                        // to px
                        w = w / 2.54 * dpi.val();
                        h = h / 2.54 * dpi.val();

                        width.val(Math.round(w));
                        height.val(Math.round(h));
                    }

                });

                // show colorspace only on jpeg
                $scope.find('select[name=format]').change(function () {

                    $scope.find('[data-convert-custome] select[name=colorspace]').toggleClass('hide', $(this).val() != 'JPEG');

                });

                // show dpi only on cm unit
                $scope.find('select[name=unit]').change(function () {
                    $scope.find('[data-convert-custome] input[name=dpi]').parent().toggleClass('hide', $(this).val() != 'cm');
                });


                if (dimension) {
                    // convert
                    var maxWidth = dimension.width;
                    var maxHeight = dimension.height;

                    var aspectRatio = maxWidth / maxHeight;

                    $body.on('click', scopeSelector + ' input[name=imageratio]', function () {
                        $scope.find('select[name=unit]').closest('.form-group').toggle();
                        $scope.find('input[name=height]').closest('.form-group').toggle();
                        $scope.find('input[name=aspectratio]').closest('.form-group').toggle();
                    });


                    $body.on('click', scopeSelector + ' input[name=aspectratio]', function () {
                        if ($(this).is(':checked')) {
                            $body.on('change', scopeSelector + ' input[name=width]', handleWidth);
                            $body.on('change', scopeSelector + ' input[name=height]', handleHeight);
                        } else {
                            $body.off('change', scopeSelector + ' input[name=width]');
                            $body.off('change', scopeSelector + ' input[name=height]');
                        }
                    });

                    var handleWidth = function () {
                        var val = parseInt($(this).val() < maxWidth ? $(this).val() : maxWidth);
                        $(this).val(val);
                        $scope.find('input[name=height]').val(Math.round(val / aspectRatio));
                    };

                    var handleHeight = function () {
                        var val = parseInt($(this).val() < maxHeight ? $(this).val() : maxHeight);
                        $(this).val(val);
                        $scope.find('input[name=width]').val(Math.round(val / aspectRatio));
                    };

                    $body.on('change', scopeSelector + ' input[name=width]', handleWidth);
                    $body.on('change', scopeSelector + ' input[name=height]', handleHeight);
                }

            }
        };


    };

    return {

        // Get the Singleton instance if one exists
        // or create one if it doesn't
        getInstance: function () {

            if (!instance) {
                instance = init();
            }

            return instance;
        }

    };

})();


// Usage:
// var dam = DAM.getInstance();
// dam.getRandomNumber();
