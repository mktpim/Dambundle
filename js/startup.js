pimcore.registerNS("pimcore.plugin.dam");

pimcore.plugin.dam = Class.create(pimcore.plugin.admin, {
    getClassName: function() {
        return "pimcore.plugin.dam";
    },

    initialize: function() {
        pimcore.plugin.broker.registerPlugin(this);
    },
 
    pimcoreReady: function (params,broker){


        if(pimcore.globalmanager.get("user").isAllowed("plugin_dam_settings")) {

            if ( pimcore.globalmanager.get("layout_toolbar").settingsMenu) {
                console.log('hax-extras');
                pimcore.globalmanager.get("layout_toolbar").settingsMenu.add({
                    text: t("plugin_dam_settings"),
                    iconCls: "x-menu-item-icon pimcore_icon_metadata",
                    handler: function () {

                        try {
                            pimcore.globalmanager.get("dam-settings").activate();
                        }
                        catch (e) {
                            pimcore.globalmanager.add("dam-settings",
                                new pimcore.tool.genericiframewindow(
                                    "pimcore_plugin_dam_settings",
                                    '/dam/admin/terms',
                                    "pimcore_icon_metadata",
                                    t("plugin_dam_settings")
                                )
                            );
                        }
                    }
                });
            }
        }
    },

    postOpenAsset : function(object, type)
    {
        if(pimcore.globalmanager.get("user").isAllowed("plugin_dam"))
        {
            var url = type == 'folder'
                ? '/dam/asset/list?pid=' + object.id
                : '/dam/asset/detail?id=' + object.id;

            var button = new Ext.Button({
                text: t('plugin_dam.open'),
                iconCls: "plugin_dam_open_icon",
                scale: "medium",
                handler: function () {
                    window.open( url );
                }
            });

            object.tab.items.items[0].add(button);

            pimcore.layout.refresh();
        }
    }

});

var damPlugin = new pimcore.plugin.dam();

