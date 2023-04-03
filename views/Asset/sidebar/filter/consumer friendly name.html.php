<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */
/** @var \Pimcore\Bundle\DamBundle\Dam\Filter\AbstractFilter $filter
 */
$filter = $this->filter;
$consumerFriendlyNameList = \Pimcore\Bundle\DamBundle\Dam\Filter\ConsumerFriendlyName::getConsumerFriendlyNameOptions();
if (count($consumerFriendlyNameList) > 0) : ?>
    <div id="filter-config-<?= $filter->getId() ?>" class="panel panel-default panel-filter <?= $filter->isActive() ? 'active' : 'hide' ?>" data-filter="consumerFriendlyName">
        <div class="panel-heading">
            <h3 class="panel-title"><span class="glyphicon glyphicon-bookmark"></span> <?= ('Consumer Friendly Name') ?></h3>

            <button type="button" class="btn btn-default btn-xs remove-filter"><span class="glyphicon glyphicon-remove"></span></button>
        </div>

        <div class="panel-body row">
            <input id="filterConsumerFriendlyName" value="<?= $filter->getParam('filter_consumer_friendly_name') ?>" name="filter_consumer_friendly_name" />
        </div>
    </div>

    <script>
        <?php $this->headScript()->captureStart(); ?>

        $(function() {

            var dam = DAM.getInstance();
            dam.requireSelectize(function() {

                // enable consumer friendly name assign
                $('#filterConsumerFriendlyName').selectize({
                    plugins: ['remove_button_colored'],
                    persist: false,
                    maxItems: null,
                    valueField: 'name',
                    labelField: 'name',
                    searchField: ['name'],
                    options: [
                        <?php foreach (\Pimcore\Bundle\DamBundle\Dam\Filter\ConsumerFriendlyName::getConsumerFriendlyNameOptions() as $key => $consumerFriendlyName) : ?> {
                                id: <?= $key ?>,
                                name: `<?= $consumerFriendlyName ?>`
                            },
                        <?php endforeach; ?>
                    ],
                    render: {
                        item: function(item, escape) {
                            return '<span class="consumer_friendly_name_item">' +
                                '<span class="label name" style="color:black;">' + escape(item.name) + '</span>' +
                                '</span>';
                        },
                        option: function(item, escape) {
                            return '<div><span class="consumer_frienldy_name_item">' +
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