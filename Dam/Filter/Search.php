<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Dam\Filter;

use Pimcore\Bundle\DamBundle\Dam\Events;
use Pimcore\Bundle\DamBundle\Dam\Facade;
use Pimcore\Bundle\DamBundle\Event;
use Pimcore\Bundle\DamBundle\Model\Asset;

class Search extends AbstractFilter
{
    public function __construct()
    {
        $this->setId('search');
        $this->setName('search');
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->getParam('q') ? true : false;
    }

    /**
     * @return bool
     */
    public function hasFrontend()
    {
        return false;
    }

    /**
     * @param Asset\Listing $list
     *
     * @return void
     */
    public function apply(Asset\Listing $list)
    {
        // sanitize the search string before send request - remove "-" character from the front and the end position of the search string
        $query = $this->getParam('q');
        $query = trim($query, '-');


        if (!empty($query)) {
            // trigger search event
            $eventSearch = new Event\Search();
            $eventSearch->setQuery($query);
            \Pimcore::getEventDispatcher()->dispatch(Events::MODIFY_SEARCH_QUERY, $eventSearch);
            $query = $eventSearch->getQuery();

            $words = explode(' ', $query);


            list($fulltextSubQueryParts, $fulltextSubQueryParams) = $this->buildSearchQueryParams($query);

            $fulltextSubQueryParts = array_merge(['(`maintype` IN ("asset"))'], $fulltextSubQueryParts);
            $fulltextSubQueryString = '(SELECT id FROM search_backend_data WHERE ' . implode(' AND ', $fulltextSubQueryParts) . ')';

            $conditionParts[] = 'id IN ' . $fulltextSubQueryString;



            //SKU and Collection Search global
            $collection = Facade::getCollectionList();
            $cond = [];
            $cond1 = [];
            foreach ($words as $word) {
                $cond[] = $list->quote(trim($word));
                $cond1[] = sprintf('name like %s', $collection->quote('%' . $word . '%'));
            }

            // condition for Market SKU
            if ($cond) {
                $conditionParts[] = sprintf('id IN(
                    SELECT OCAPP.image 
                        FROM `object_collection_AssetsProperty_PROD` AS OCAPP 
                            WHERE OCAPP.o_id IN (
                                SELECT OP.oo_id FROM `object_PROD` AS OP WHERE OP.marketSKU IN (%s)
                            )
                    )', implode(',', $cond));
            }

            // condition for Collection
            if ($cond1) {
                $assignedIds = [];
                $collection->addConditionParam(sprintf('(%s)', implode(' OR ', $cond1)), '');
                foreach ($collection as $coll) {
                    $assignedIds = array_merge($assignedIds, $coll->getAssignedIds());
                }

                if ($assignedIds) {
                    $conditionParts[] = sprintf('id IN (%s)', implode(',', $assignedIds));
                }
            }

            $list->addConditionParam('(' . implode(' OR ', $conditionParts) . ')', $fulltextSubQueryParams);
        }
    }

    protected function buildSearchQueryParams($query)
    {
        //check if query has short tokens -> special query generation necessary
        $hasShortTokens = preg_match('#([\s"\+\-\~][^\s\+\-\~\*"]{1,3}[\s"\*])#', ' ' . $query . ' ');

        $fulltextSubQueryParams = [];
        $fulltextSubQueryParts = [];

        if ($hasShortTokens) {
            $processedQuery = $query;

            //strip mysql fulltext search special characters for like queries
            $stripCharacters = ['"', '+', '-', '~', '*'];

            $fullTextQueryTokens = [];
            $likeQueryTokens = [];
            $excludeLikeQueryTokens = [];

            //extract quoted tokens
            $quotedTokens = [];
            $foundQuotedTokes = preg_match_all('#([-+~]?".*?")#', $processedQuery, $quotedTokens);
            if ($foundQuotedTokes) {
                foreach ($quotedTokens[0] as $quotedToken) {
                    $unquotedToken = str_replace('"', '', $quotedToken);

                    //check if quoted token is short subtoken or has stripcharacters in it
                    if (strlen(str_replace($stripCharacters, '', $unquotedToken)) <= 3 || strlen(str_replace($stripCharacters, '', substr($unquotedToken, 1))) != strlen(substr($unquotedToken, 1))) {
                        //add like query
                        if (substr($unquotedToken, 0, 1) == '-') {
                            $excludeLikeQueryTokens[] = substr($unquotedToken, 1);
                        } else {
                            $likeQueryTokens[] = str_replace($stripCharacters, '', substr($unquotedToken, 0, 1)) . substr($unquotedToken, 1);
                        }
                    } else {
                        //add fulltext search query
                        $fullTextQueryTokens[] = str_replace('%', '*', $quotedToken);
                    }

                    //remove quoted token from query
                    $processedQuery = str_replace($quotedToken, '', $processedQuery);
                }
            }

            //process remaining (not quoted) tokens
            $processedQuery = preg_replace('#\s{2,}#', ' ', $processedQuery);

            //check if query has short tokens -> special query generation necessary
            $hasShortTokens = preg_match('#([\s"\+\-\~][^\s\+\-\~\*"]{1,3}[\s"\*])#', ' ' . $processedQuery . ' ');

            if ($hasShortTokens) {
                foreach (explode(' ', $processedQuery) as $token) {
                    $isShortToken = strlen(str_replace($stripCharacters, '', $token)) <= 3;
                    $isExcludeToken = substr($token, 0, 1) == '-';

                    //if short token or exlude token an no other fulltext search token currently inserted -> add like query
                    if ($isShortToken || ($isExcludeToken && empty($fullTextQueryTokens))) {
                        //add like query
                        if ($isExcludeToken) {
                            $excludeLikeQueryTokens[] = str_replace($stripCharacters, '', $token);
                        } else {
                            $likeQueryTokens[] = str_replace($stripCharacters, '', $token);
                        }
                    } else {
                        $fullTextQueryTokens[] = str_replace('%', '*', $token);
                    }
                }
            } else {
                $fullTextQueryTokens[] = str_replace('%', '*', $processedQuery);
            }

            if (array_filter($fullTextQueryTokens)) {
                $fulltextSubQueryParts[] = '( MATCH (`data`,`properties`) AGAINST (? IN BOOLEAN MODE) )';
                $fulltextSubQueryParams[] = implode(' ', $fullTextQueryTokens);
            }

            if ($likeQueryTokens) {
                foreach ($likeQueryTokens as $token) {
                    $fulltextSubQueryParts[] = '(`data` LIKE ?)';
                    $fulltextSubQueryParams[] = '%' . $token . '%';
                }
            }
            if ($excludeLikeQueryTokens) {
                foreach ($excludeLikeQueryTokens as $token) {
                    $fulltextSubQueryParts[] = '(`data` NOT LIKE ?)';
                    $fulltextSubQueryParams[] = '%' . $token . '%';
                }
            }
        } else {
            $fulltextSubQueryParts[] = '( MATCH (`data`,`properties`) AGAINST (? IN BOOLEAN MODE) )';
            $fulltextSubQueryParams[] = $query;
        }

        return [
            $fulltextSubQueryParts,
            $fulltextSubQueryParams
        ];
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'q' => $this->getParam('q')
        ];
    }
}
