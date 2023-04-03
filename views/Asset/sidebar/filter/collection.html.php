<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */
/**
 * @var \Pimcore\Bundle\DamBundle\Dam\Filter\AbstractFilter $filter
 */
$filter = $this->filter;
$collectionList = \Pimcore\Bundle\DamBundle\Dam\Facade::getCollectionList();
// echo ('<pre>');
// print_r($collectionList);
if ($collectionList->count() > 0) : ?>
    <div id="filter-config-<?= $filter->getId() ?>" class="panel panel-default panel-filter <?= $filter->isActive() ? 'active' : 'hide' ?>" data-filter="collection">
        <div class="panel-heading">
            <h3 class="panel-title"><span class="glyphicon glyphicon-paperclip"></span> <?= $this->translate('dam.filter.collection.label') ?></h3>
            <button type="button" class="btn btn-default btn-xs remove-filter"><span class="glyphicon glyphicon-remove"></span></button>
        </div>
        <div class="panel-body">
            <input id="filterCollection" value="<?= $filter->getParam('filter_collection') ?>" name="filter_collection" />
        </div>
    </div>

    <script>
        <?php $this->headScript()->captureStart(); ?>

        $(function() {

            var dam = DAM.getInstance();
            dam.requireSelectize(function() {

                // enable collection assign
                $('#filterCollection').selectize({
                    plugins: ['remove_button_colored'],
                    persist: false,
                    maxItems: null,
                    valueField: 'id',
                    labelField: 'name',
                    searchField: ['name'],
                    options: [
                        <?php foreach (\Pimcore\Bundle\DamBundle\Dam\Facade::getCollectionList() as $collection) : ?> {
                                id: <?= $collection->getId() ?>,
                                name: '<?= $collection->getName() ?>',
                                color: '<?= $collection->hasColor() ? $collection->getColor() : '#999' ?>'
                            },
                        <?php endforeach; ?>
                    ],
                    render: {
                        item: function(item, escape) {
                            return '<span class="collection_ item">' +
                                '<span class="label name" style="background-color: ' + item.color + ';">' + escape(item.name) + '</span>' +
                                '</span>';
                        },
                        option: function(item, escape) {
                            return '<div><span class="collection_ item">' +
                                '<span class="label name" style="background-color: ' + item.color + ';">' + escape(item.name) + '</span>' +
                                '</span></div>';
                        }
                    }
                });
            });

        });

        <?php $this->headScript()->captureEnd(); ?>
    </script>
<?php endif; ?>