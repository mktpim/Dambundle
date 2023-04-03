<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */
/** @var \Pimcore\Bundle\DamBundle\Dam\Filter\AbstractFilter $filter
 */
$filter = $this->filter;
$marketSKUList = \Pimcore\Bundle\DamBundle\Dam\Filter\MarketSku::getMarketSKUOptions();
if (count($marketSKUList) > 0) : ?>
    <div id="filter-config-<?= $filter->getId() ?>" class="panel panel-default panel-filter <?= $filter->isActive() ? 'active' : 'hide' ?>" data-filter="marketSku">
        <div class="panel-heading">
            <h3 class="panel-title"><span class="glyphicon glyphicon-barcode"></span> <?= ('Market SKU') ?></h3>

            <button type="button" class="btn btn-default btn-xs remove-filter"><span class="glyphicon glyphicon-remove"></span></button>
        </div>

        <div class="panel-body row">
            <input id="filterMarketSKU" value="<?= $filter->getParam('filter_market_sku') ?>" name="filter_market_sku" />
        </div>
    </div>

    <script>
        <?php $this->headScript()->captureStart(); ?>

        $(function() {

            var dam = DAM.getInstance();
            dam.requireSelectize(function() {

                // enable market SKUs assign
                $('#filterMarketSKU').selectize({
                    plugins: ['remove_button_colored'],
                    persist: false,
                    maxItems: null,
                    valueField: 'name',
                    labelField: 'name',
                    searchField: ['name'],
                    options: [
                        <?php foreach (\Pimcore\Bundle\DamBundle\Dam\Filter\MarketSku::getMarketSKUOptions() as $key => $marketSKU) : ?> {
                                id: <?= $key ?>,
                                name: '<?= $marketSKU ?>'
                            },
                        <?php endforeach; ?>
                    ],
                    render: {
                        item: function(item, escape) {
                            return '<span class="market_sku_item">' +
                                '<span class="label name" style="color:black;">' + escape(item.name) + '</span>' +
                                '</span>';
                        },
                        option: function(item, escape) {
                            return '<div><span class="market_sku_item">' +
                                '<span class="label name" style="color:black;">' + escape(item.name) + '</span>' +
                                '</span></div>';
                        }
                    }
                });
            });

        });

        <?php $this->headScript()->captureEnd(); ?>
    </script>
<?php endif; ?>