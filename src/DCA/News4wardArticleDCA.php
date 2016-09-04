<?php

namespace Hofff\Contao\LanguageRelations\News4ward\DCA;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class News4wardArticleDCA {

	/**
	 * @param string $table
	 * @return void
	 */
	public function hookLoadDataContainer($table) {
		if($table != 'tl_news4ward_article') {
			return;
		}

		$palettes = &$GLOBALS['TL_DCA']['tl_news4ward_article']['palettes'];
		foreach($palettes as $key => &$palette) {
			if($key != '__selector__') {
				$palette .= ';{hofff_language_relations_legend}';
				$palette .= ',hofff_language_relations';
			}
		}
		unset($palette, $palettes);
	}

}
