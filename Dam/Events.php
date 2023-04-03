<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam;

class Events
{
    const DOWNLOAD_ASSET = 'dam.download-asset';
    const PRE_LOGIN_REDIRECT = 'dam.pre-login-redirect';
    const MODIFY_SEARCH_QUERY = 'dam.filter.search.modify-query';

    /**
     * Use event to set download permissions, etc.
     * only use setAllowShare
     */
    const ITEM_RIGHTS = 'dam.item-rights';
}
