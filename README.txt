ext dependencies:
- static_info_tables (tested with 2.0.10) -> needed for language pool

tested with typo3 4.2 - should also work with all 4.x versions


Installation:

- be aware all required ext are installed (see above)
- install snowbabel
- go to snowbabel -> settings -> translation -> general
- check local extension path
- check system extension path
- check global extension path
- set default language -> sys_lang = 0 (en by default)
- add all languages which are configurated in typo3 (inclusive default language) which should be available for the administrator

Contentmanager Configuration:

- see be_user or be_group -> rightmanagement