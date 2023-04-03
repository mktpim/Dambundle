<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */
/**
 * @var \Pimcore\Templating\PhpEngine $this
 * @var \Pimcore\Templating\PhpEngine $view
 * @var \Pimcore\Templating\GlobalVariables $app
 */

$shareConfig = null;
if ($share = $this->share) {
    /* @var \Pimcore\Bundle\DamBundle\Dam\Share\AbstractShare $share */
    $shareConfig = $share->getConfig();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">

    <title>DAM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link rel="shortcut icon" href="/bundles/pimcoredam/cubes.ico" />

    <!-- Le styles -->
    <link href="/bundles/pimcoredam/vendor/bootstrap/css/bootstrap.css" rel="stylesheet">

    <link href="/bundles/pimcoredam/css/global.css" rel="stylesheet">

    <?php
    $files = \Pimcore\Bundle\DamBundle\PimcoreDamBundle::getConfig()['frontend']['customize']['css'];
    if (is_array($files)) {
        foreach ($files as $file) {
            if ($file) : ?>
                <link href="<?= $file ?>" rel="stylesheet" />
            <?php endif;
        }
    } else {
        if ($files) : ?>
            <link href="<?= $files ?>" rel="stylesheet" />
    <?php endif;
    }
    ?>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="/bundles/pimcoredam/vendor/html5shiv.js"></script>
    <script src="/bundles/pimcoredam/vendor/respond.min.js"></script>
    <![endif]-->

</head>

<body class="dam" style="padding-bottom: 70px; padding-top: 70px;">

    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container-fluid">
            <?php
            $link = $view->router()->path('pimcore_dam_share_tokenlist', [
                't' => $this->getParam('t'), 'lang' => $this->language
            ]);

            $linkDownloadZip = $view->router()->path('pimcore_dam_share_tokenlist', [
                't' => $this->getParam('t'), 'download' => 1
            ]);
            ?>
            <a class="navbar-brand" href="<?= $link ?>"><span class="glyphicon glyphicon-home"></span></a>

            <ul class="nav navbar-nav navbar-right">

                <?php if ($share && $shareConfig && $shareConfig['enable-download']) : ?>

                    <li>
                        <?php
                        if ($shareConfig['accept-termsconditions']) {
                            $linkDownload = '#';
                            $linkId = 'id="download-accept-terms"';
                        } else {
                            $linkDownload = $linkDownloadZip;
                            $linkId = '';
                        }
                        ?>

                        <a class="share-download-button" <?= $linkId ?> href="<?= $linkDownload ?>"><span class="glyphicon glyphicon-cloud-download"></span> <?= $this->translate('dam.download.archive.zip') ?></a>
                    </li>
                <?php endif; ?>

                <!-- removed for front view of DAM public share Language Dropdown -->
                <!-- <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?= strtoupper($this->language) ?> <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <?php
                        $currentRoute = $app->getRequest()->get('_route');
                        foreach (\Pimcore\Tool::getValidLanguages() as $lang) :
                            if ($lang != $this->language) :
                                $linkLang = $this->router()->path($currentRoute, array_merge($app->getRequest()->query->all(), [
                                    'lang' => $lang
                                ]));
                        ?>
                                <li><a href="<?= $linkLang ?>"><?= strtoupper($lang) ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </li> -->
            </ul>
        </div>


    </nav>
    <div class="sidebar" style="width:25%">

    </div>
    <!-- main -->
    <div id="main" class="main" style="margin-left: 23%;">

        <?php $this->slots()->output('_content') ?>
    </div>

    <?php
    $expire = $share ? $share->getExpireDate() : null;
    if ($expire) : ?>
        <nav class="navbar navbar-default navbar-fixed-bottom" role="navigation">
            <div class="container">
                <?php if ($this->terms) {
                ?>
                    <p class="navbar-text pull-right">
                        <a href="#" class="show-terms"><?= $this->translate('dam.terms') ?></a>
                    </p>
                <?php
                } ?>
                <p class="navbar-text">
                    <?= sprintf($this->translate('dam.expire.token.label'), $expire->toFormattedDateString()); ?>
                </p>
            </div>
        </nav>
    <?php endif; ?>



    <div class="modal fade" id="accept-terms-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?= $this->translate('dam.share.terms.question') ?></h4>
                </div>
                <div class="modal-body" style="max-height: 300px; overflow-y: auto;">
                    <?php if ($share && $share->getTermsComment()) {
                    ?>
                        <strong><?= $this->translate('dam.share.terms.comment') ?></strong>
                        <p><?= $share->getTermsComment() ?></p>
                        <br />
                    <?php
                    } ?>
                    <?= $this->terms ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= $this->translate('dam.share.terms.no') ?></button>
                    <a href="<?= $linkDownloadZip ?>" class="btn btn-success" id="accept-terms-button"><?= $this->translate('dam.share.terms.yes') ?></a>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <?php if ($this->terms) {
    ?>
        <div class="modal fade" id="show-terms-modal">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                        <?php if ($share && $share->getTermsComment()) {
                        ?>
                            <strong><?= $this->translate('dam.share.terms.comment') ?></strong>
                            <p><?= $share->getTermsComment() ?></p>
                            <br />
                        <?php
                        } ?>
                        <?= $this->terms ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= $this->translate('dam.share.terms.ok') ?></button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
    <?php
    } ?>

    <?= $this->headLink() ?>

    <script>
        // settings
        var websiteConfig = {
            "language": "<?= $this->language ?>"
        };
    </script>

    <script src="/bundles/pimcoredam/vendor/jquery.min.js"></script>
    <script src="/bundles/pimcoredam/vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="/bundles/pimcoredam/js/frontend.js"></script>

    <?= $this->headScript() ?>

    <script>
        $().ready(function() {

            // is touch dev?
            if ('ontouchstart' in window // works on most browsers
                ||
                'onmsgesturechange' in window) {
                $('html').addClass('touch')
            } else {
                $('html').addClass('no-touch')
            }

            var dam = DAM.getInstance();

            $('#download-accept-terms').on('click', function(e) {
                $('#accept-terms-modal').modal();
            });

            $('#accept-terms-button').on('click', function(e) {
                $('#accept-terms-modal').modal('hide');
            });

            $('.show-terms').on('click', function(e) {
                $('#show-terms-modal').modal();
            });
        });
    </script>

    <?php
    $files = \Pimcore\Bundle\DamBundle\PimcoreDamBundle::getConfig()['frontend']['customize']['js'];
    if (is_array($files)) {
        foreach ($files as $file) {
            if ($file) : ?>
                <script src="<?= $file ?>"></script>
            <?php endif;
        }
    } else {
        if ($files) : ?>
            <script src="<?= $files ?>"></script>
    <?php endif;
    }
    ?>



</body>

</html>