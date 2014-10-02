<?php

$incPath = t3lib_extMgm::extPath('snowbabel') . 'Classes/';

return array (

    'tx_snowbabel_configuration'			=>		$incPath . 'Configuration/class.tx_snowbabel_configuration.php',
    'tx_snowbabel_db'						=>		$incPath . 'Db/class.tx_snowbabel_db.php',
    'tx_snowbabel_cache'					=>		$incPath . 'Cache/class.tx_snowbabel_cache.php',

    'tx_snowbabel_extensions'				=>		$incPath . 'Extensions/class.tx_snowbabel_extensions_list.php',
    'tx_snowbabel_labels'					=>		$incPath . 'Labels/class.tx_snowbabel_labels.php',
    'tx_snowbabel_languages'				=>		$incPath . 'Languages/class.tx_snowbabel_languages.php',
    'tx_snowbabel_columns'					=>		$incPath . 'Columns/class.tx_snowbabel_columns.php',

    'tx_snowbabel_application_translation'	=>		$incPath . 'Application/class.tx_snowbabel_application_translation.php',
    'tx_snowbabel_application_settings'		=>		$incPath . 'Application/class.tx_snowbabel_application_settings.php',

    'tx_snowbabel_system_indexing'			=>		$incPath . 'System/class.tx_snowbabel_system_indexing.php',
    'tx_snowbabel_system_translations'		=>		$incPath . 'System/class.tx_snowbabel_system_translations.php',
    'tx_snowbabel_system_statistics'		=>		$incPath . 'System/class.tx_snowbabel_system_statistics.php'
);

?>