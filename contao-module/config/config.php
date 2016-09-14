<?php

if(
	isset($GLOBALS['BE_MOD']['content']['news4ward']['stylesheet'])
	&& is_string($GLOBALS['BE_MOD']['content']['news4ward']['stylesheet'])
) {
	$GLOBALS['BE_MOD']['content']['news4ward']['stylesheet'] = [
		$GLOBALS['BE_MOD']['content']['news4ward']['stylesheet'],
	];
}

$GLOBALS['BE_MOD']['content']['news4ward']['stylesheet'][]
	= 'system/modules/hofff_language_relations/assets/css/style.css';

$GLOBALS['TL_HOOKS']['loadDataContainer']['hofff_language_relations_news4ward']
	= [ 'Hofff\\Contao\\LanguageRelations\\News4ward\\DCA\\News4wardArticleDCA', 'hookLoadDataContainer' ];
$GLOBALS['TL_HOOKS']['sqlCompileCommands']['hofff_language_relations_news4ward']
	= [ 'Hofff\\Contao\\LanguageRelations\\News4ward\\Database\\Installer', 'hookSQLCompileCommands' ];
$GLOBALS['TL_HOOKS']['hofff_language_relations_language_switcher']['hofff_language_relations_news4ward']
	= [ 'Hofff\\Contao\\LanguageRelations\\News4ward\\LanguageRelationsNews', 'hookLanguageSwitcher' ];
