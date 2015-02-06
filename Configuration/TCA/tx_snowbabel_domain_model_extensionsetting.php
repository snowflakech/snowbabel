<?php

return array (
	'ctrl' => array (
		'title' => 'tx_snowbabel_domain_model_extensionsetting',
		'label' => 'extension_key',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser',
		'default_sortby' => 'ORDER BY sorting',
		'sortby' => 'sorting',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'hideTable' => TRUE,
	),
	'interface' => array (),
	'types' => array (),
	'palettes' => array (),

	'columns' => array (
		'hidden' => array (
			'l10n_mode' => 'exclude',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => array (
				'type' => 'check',
			),
		),
		'starttime' => array (
			'l10n_mode' => 'exclude',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
			'config' => array (
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array (
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'endtime' => array (
			'l10n_mode' => 'exclude',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
			'config' => array (
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array (
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'extension_key' => array (
			'label' => 'extension_key',
			'config' => array (
				'type' => 'input'
			)
		),
		'selected' => array (
			'label' => 'selected',
			'config' => array (
				'type' => 'check',
				'default' => '0'
			)
		),
	)
);