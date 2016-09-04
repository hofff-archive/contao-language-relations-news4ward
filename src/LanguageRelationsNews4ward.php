<?php

namespace Hofff\Contao\LanguageRelations\News4ward;

use Contao\PageModel;
use Hofff\Contao\LanguageRelations\Module\ModuleLanguageSwitcher;
use Hofff\Contao\LanguageRelations\News4ward\Util\News4wardUtil;
use Hofff\Contao\LanguageRelations\Relations;
use Hofff\Contao\LanguageRelations\Util\ContaoUtil;
use Psi\News4ward\Model\ArchiveModel;
use Psi\News4ward\Model\ArticleModel;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class LanguageRelationsNews4ward {

	/**
	 * @var Relations
	 */
	private static $relations;

	/**
	 * @return Relations
	 * @deprecated
	 */
	public static function getRelationsInstance() {
		isset(self::$relations) || self::$relations = new Relations(
			'tl_hofff_language_relations_news4ward',
			'hofff_language_relations_news4ward_item',
			'hofff_language_relations_news4ward_relation'
		);
		return self::$relations;
	}

	/**
	 * @param array $items
	 * @param ModuleLanguageSwitcher $module
	 * @return array
	 */
	public function hookLanguageSwitcher(array $items, ModuleLanguageSwitcher $module) {
		$currentPage = $GLOBALS['objPage'];

		$currentArticle = News4wardUtil::findCurrentArticle($currentPage->id);
		if(!$currentArticle) {
			return $items;
		}

		$relatedArticles = self::getRelationsInstance()->getRelations($currentArticle);
		$relatedArticles[$currentPage->hofff_root_page_id] = $currentArticle;

		News4wardUtil::prefetchArticleModels($relatedArticles);

		foreach($items as $rootPageID => &$item) {
			if(!isset($relatedArticles[$rootPageID])) {
				continue;
			}

			$article = ArticleModel::findByPk($relatedArticles[$rootPageID]);
			if(!ContaoUtil::isPublished($article)) {
				continue;
			}

			$archive = ArchiveModel::findByPk($article->pid);
			if(!$archive->jumpTo) {
				continue;
			}

			$page = PageModel::findByPk($archive->jumpTo);
			if(!ContaoUtil::isPublished($page)) {
				continue;
			}

			$item['href']		= News4wardUtil::getArticleURL($article);
			$item['pageTitle']	= strip_tags($article->title);
		}
		unset($item);

		return $items;
	}

}
