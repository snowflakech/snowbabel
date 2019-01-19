<?php
namespace Snowflake\Snowbabel;

/*
 *  Copyright notice
 *
 *  (c) 2019 Guillaume Germain <guillaume@germain.bzh>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class for updating the db
 */
class ext_update {

    public function main()
    {
        $content = '';

        // list lg_iso_2 that are used in multiple languages
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('static_languages');
        $multipleISO2 = $queryBuilder->select('lg_iso_2')
            ->from('static_languages')
            ->groupBy('lg_iso_2')
            ->having('COUNT(*) > 1')
            ->execute()
            ->fetchAll();

        $multipleLanguages = [];
        foreach ($multipleISO2 as $lang) {
            $multipleLanguages[] = $lang['lg_iso_2'];
        }

        // update all multiple languages
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('static_languages');
        $queryBuilder
            ->update('static_languages')
            ->where(
                $queryBuilder->expr()->in('lg_iso_2', $queryBuilder->createNamedParameter($multipleLanguages, Connection::PARAM_STR_ARRAY)),
                $queryBuilder->expr()->neq('lg_country_iso_2', $queryBuilder->createNamedParameter('', Connection::PARAM_STR))
            )
            ->set('tx_snowbabel_override_language_key', 'CONCAT(lg_iso_2, "-", lg_country_iso_2)', FALSE)
            ->execute();

        return $content;
    }

    public function access() {
        return true;
    }
}