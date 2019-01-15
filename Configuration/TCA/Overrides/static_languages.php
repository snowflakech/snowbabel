<?php
defined('TYPO3_MODE') or die();

$tempColumns = [
    'tx_snowbabel_override_language_key' => [
        'label' => 'LLL:EXT:snowbabel/Resources/Private/Language/locallang_tca.xlf:static_languages.tx_snowbabel_override_language_key',
        'exclude' => '1',
        'config' => [
            'type' => 'input',
            'max' => 6,
            'size' => 10,
            'eval' => 'trim',
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('static_languages', $tempColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('static_languages', 'tx_snowbabel_override_language_key');