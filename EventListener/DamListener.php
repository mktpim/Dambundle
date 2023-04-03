<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\EventListener;

use Pimcore\Bundle\DamBundle\Model\Share;

class DamListener
{
    public function onSystemMaintenance(\Pimcore\Event\System\MaintenanceEvent $e)
    {
        $this->cleanUpExpiredShares();
    }

    /**
     * clean up expired shares
     */
    protected function cleanUpExpiredShares()
    {
        // init
        $expireList = new Share\Listing();
        $expireList->addConditionParam('now() > DATE_ADD(FROM_UNIXTIME(expire), INTERVAL 1 MONTH)', '');

        foreach ($expireList as $share) {
            $share->delete();
        }
    }
}
