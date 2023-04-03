<?php
/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */
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

    <?= $this->headLink() ?>

</head>

<body>

<div class="container">
    <?php $this->slots()->output('_content') ?>
</div>

<script src="/bundles/pimcoredam/vendor/jquery.min.js"></script>
<script src="/bundles/pimcoredam/vendor/bootstrap/js/bootstrap.min.js"></script>

<?= $this->headScript() ?>

</body>
</html>
