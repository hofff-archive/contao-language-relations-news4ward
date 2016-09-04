<?php

namespace Hofff\Contao\LanguageRelations\News4ward\Util;

use Contao\Config;
use Contao\Input;
use Contao\PageModel;
use Hofff\Contao\LanguageRelations\Util\QueryUtil;
use Psi\News4ward\Helper;
use Psi\News4ward\Model\ArchiveModel;
use Psi\News4ward\Model\ArticleModel;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class News4wardUtil {

	/**
	 */
	public function __construct() {
	}

	/**
	 * @param integer|null $jumpTo
	 * @return integer|null
	 */
	public static function findCurrentArticle($jumpTo = null) {
		if(isset($_GET['items'])) {
			$idOrAlias = Input::get('items', false, true);
		} elseif(isset($_GET['auto_item']) && Config::get('useAutoItem')) {
			$idOrAlias = Input::get('auto_item', false, true);
		} else {
			return null;
		}

		$sql = <<<SQL
SELECT
	article.id		AS article_id,
	archive.jumpTo	AS archive_jump_to
FROM
	tl_news4ward_article
	AS article
JOIN
	tl_news4ward
	AS archive
	ON archive.id = article.pid
WHERE
	article.id = ? OR article.alias = ?
SQL;
		$result = QueryUtil::query(
			$sql,
			null,
			[ $idOrAlias, $idOrAlias ]
		);

		if(!$result->numRows) {
			return null;
		}

		if($jumpTo === null || $jumpTo == $result->archive_jump_to) {
			return $result->article_id;
		}

		return null;
	}

	/**
	 * @param ArticleModel $article
	 * @return string
	 */
	public static function getArticleURL(ArticleModel $article) {
		static $helper;
		$helper || $helper = new Helper;

		$data = $article->row();
		$data['parentJumpTo'] = ArchiveModel::findByPk($article->pid)->jumpTo;

		return $helper->generateUrl($data);
	}

	/**
	 * @param array $ids
	 * @return void
	 */
	public static function prefetchArticleModels(array $ids) {
		$archives = [];
		foreach(ArticleModel::findMultipleByIds(array_values($ids)) as $article) {
			$archives[] = $article->pid;
		}

		$pages = [];
		foreach(ArchiveModel::findMultipleByIds($archives) as $archive) {
			$archive->jumpTo && $pages[] = $archive->jumpTo;
		}

		PageModel::findMultipleByIds($pages);
	}

}
