<?php
/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */
/**
 * @var \Pimcore\Bundle\DamBundle\Model\Collection $collection
 * @var \Pimcore\Model\User[] $userList
 */
$collection = $this->collection;
$userList = $this->userList;

$this->headScript()->appendFile('/bundles/pimcoredam/static/vendor/selectize-js/selectize.min.js');
$this->headLink()->appendStylesheet('/bundles/pimcoredam/static/vendor/selectize-js/selectize.css');

//style_="display: block; opacity: 1; top: 200px;"
?>
<div class="modal fade collection-share" data-backdrop="static" tabindex="-1" role="dialog"
     aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <?php
            $urlSave = $this->path('pimcore_dam_collection_share');
            ?>
            <form action="<?= $urlSave ?>" method="post">

                <input type="hidden" name="id" value="<?= $collection->getId() ?>"/>

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">
                        <span class="label name" style="background-color: <?= $collection->getColor() ?>;"> </span>&nbsp;
                        <?= $collection->getName() ?>
                        <small><?= $this->translate('dam.share.collection') ?></small>
                    </h4>
                </div>


                <div class="modal-body">

                    <h4><?= $this->t('dam.collection.permissions') ?></h4>
                    <table class="table-striped table-hover table" id="share-table">
                        <tr>
                            <th width="70%"><?= $this->t('dam.collection.user') ?></th>
                            <th align="center"><?= $this->t('dam.collection.permission.view') ?></th>
                            <th align="center"><?= $this->t('dam.collection.permission.edit') ?></th>
                        </tr>
                        <?php
                        $collectionUsers = $collection->getUsers(true);
                        $sharingUser = $collection->getShare(true);
                        $editingUser = $collection->getEdit(true);

                        if ($collectionUsers) {
                            $collectionUsersList = new \Pimcore\Model\User\Listing();
                            $collectionUsersList->setCondition('id in (' . implode(',', $collectionUsers) . ')');
                            $collectionUsersList = $collectionUsersList->load();

                            $collectionUserGroupsList = new \Pimcore\Model\User\Role\Listing();
                            $collectionUserGroupsList->setCondition('id in (' . implode(',', $collectionUsers) . ')');
                            $collectionUsersList = array_merge($collectionUsersList, $collectionUserGroupsList->load()); ?>

                            <?php foreach ($collectionUsersList as $user) {
                                $checked = $collection->isEditable($user) ? \Pimcore\Bundle\DamBundle\Dam\Collection\Permissions::EDIT : \Pimcore\Bundle\DamBundle\Dam\Collection\Permissions::VIEW; ?>
                                <tr id="user-<?= $user->getId() ?>" data-userId="<?= $user->getId() ?>">
                                    <td><?= $user->getName() ?></td>
                                    <td><input type="radio" name="permissions[<?= $user->getId() ?>]"
                                               value="<?= \Pimcore\Bundle\DamBundle\Dam\Collection\Permissions::VIEW ?>"
                                            <?= $checked == \Pimcore\Bundle\DamBundle\Dam\Collection\Permissions::VIEW ? 'checked' : '' ?>>
                                    </td>
                                    <td><input type="radio" name="permissions[<?= $user->getId() ?>]"
                                               value="<?= \Pimcore\Bundle\DamBundle\Dam\Collection\Permissions::EDIT ?>"
                                            <?= $checked == \Pimcore\Bundle\DamBundle\Dam\Collection\Permissions::EDIT ? 'checked' : '' ?>
                                        ></td>
                                </tr>
                            <?php
                            } ?>
                        <?php
                        } ?>

                    </table>

                    <h4><?= $this->t('dam.share.collection.add-user') ?></h4>
                    <textarea id="userList" name="userList"><?= implode(',', $collectionUsers) ?></textarea>

                </div>


                <div class="modal-footer">
                    <button type="button" class="btn btn-default"
                            data-dismiss="modal"><?= $this->translate('dam.close.dialog') ?></button>
                    <input type="submit" class="btn btn-primary" value="<?= $this->translate('dam.save.share') ?>"/>
                </div>


            </form>
        </div>
    </div>

    <script>
        $(function () {
            $('#userList').selectize({
                plugins: ['remove_button'],
                persist: false,
                maxItems: null,
                valueField: 'id',
                labelField: 'username',
                searchField: ['username', 'name'],
                options: [
                    <?php foreach ($userList as $user): ?>
                    {
                        id: <?= $user->getId() ?>,
                        username: '<?= $user->getName() ?>',
                        name: '<?= $user instanceof \Pimcore\Model\User ? $user->getFirstname() . ' ' . $user->getLastname() : '' ?>'
                    },
                    <?php endforeach; ?>
                ],
                render: {
                    item: function (item, escape) {
                        return '<div class="item-root" data-username="' + item.username + '"><span class="group">' +
                            (item.username ? '<span class="username">' + escape(item.username) + '</span>' : '') +
                            (item.name && item.name != ' ' ? '<span class="name">' + escape(item.name) + '</span>' : '') +
                            '</span></div>';
                    },
                    option: function (item, escape) {
                        var label = item.name || item.username;
                        var caption = item.name ? item.username : null;

                        return '<div>' +
                            (caption ? '<span class="caption">' + escape(caption) + '</span>' : '') +
                            '<span class="label">' + escape(label) + '</span>' +
                            '</div>';
                    }
                },
                onItemAdd: function (value, $item) {
                    var id = value, username = $item.attr('data-username');
                    $('#share-table').append('<tr id="user-' + id + '" data-userId="' + id + '">' +
                        '<td>' + username + '</td>' +
                        '<td><input type="radio" name="permissions[' + id + ']" value="<?= \Pimcore\Bundle\DamBundle\Dam\Collection\Permissions::VIEW ?>" checked></td>"' +
                        '<td><input type="radio" name="permissions[' + id + ']" value="<?= \Pimcore\Bundle\DamBundle\Dam\Collection\Permissions::EDIT ?>"></td>"' +
                        '"</tr>');
                },
                onItemRemove: function (value) {
                    $('#share-table').find('tr#user-' + value).remove();
                }
            });
        });
    </script>
</div>
