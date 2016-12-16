<?php
namespace Snowflake\Snowbabel\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Daniel Alder <info@snowflake.ch>
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
 ***************************************************************/

use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class TranslationController
 *
 * @package Snowflake\Snowbabel\Controller
 */
class TranslationController extends ActionController
{

    public function indexAction()
    {
        $compatibility           = 1;
        //snowbabel_generals script path for versions 7 and 8
        $snowbabel_generals_path = 'JavaScript/Miscellaneous/snowbabel_generals_compatible.js';
        $version                 = VersionNumberUtility::convertVersionNumberToInteger(VersionNumberUtility::getNumericTypo3Version());
        $version7                = VersionNumberUtility::convertVersionNumberToInteger(7);
        $version8                = VersionNumberUtility::convertVersionNumberToInteger(8);
        if ($version < $version7) {
            $compatibility           = 0;
            //snowbabel_generals script path for versions below 7
            $snowbabel_generals_path = 'JavaScript/Miscellaneous/snowbabel_generals.js';
        }
        if ($version >= $version8) {
             //Template path for versions greater than or equal to 8.
            $this->view->setTemplatePathAndFileName(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('snowbabel') . 'Resources/Private/Templates/Translation/IndexCompatible.html');
        }
        $this->view->assign('compatibility', $compatibility);
        $this->view->assign('snowbabel_generals_path', $snowbabel_generals_path);
    }
}
