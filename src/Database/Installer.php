<?php

namespace Hofff\Contao\LanguageRelations\News4ward\Database;

use Contao\Database;
use Hofff\Contao\LanguageRelations\Util\StringUtil;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class Installer {

	/**
	 * @param array $queries
	 * @return void
	 */
	public function hookSQLCompileCommands($queries) {
		$tables = array_flip(Database::getInstance()->listTables(null, true));

		if(!isset($tables['hofff_language_relations_news4ward_item'])) {
			$queries['ALTER_CHANGE'][] = StringUtil::tabsToSpaces($this->getItemView());
		}
		if(!isset($tables['hofff_language_relations_news4ward_relation'])) {
			$queries['ALTER_CHANGE'][] = StringUtil::tabsToSpaces($this->getRelationView());
		}
		if(!isset($tables['hofff_language_relations_news4ward_aggregate'])) {
			$queries['ALTER_CHANGE'][] = StringUtil::tabsToSpaces($this->getAggregateView());
		}
		if(!isset($tables['hofff_language_relations_news4ward_tree'])) {
			$queries['ALTER_CHANGE'][] = StringUtil::tabsToSpaces($this->getTreeView());
		}

		return $queries;
	}

	/**
	 * @return string
	 */
	protected function getItemView() {
		return <<<SQL
CREATE OR REPLACE VIEW hofff_language_relations_news4ward_item AS

SELECT
	root_page.hofff_language_relations_group_id	AS group_id,
	root_page.id								AS root_page_id,
	page.id										AS page_id,
	article.id									AS item_id
FROM
	tl_news4ward_article
	AS article
JOIN
	tl_news4ward
	AS archive
	ON archive.id = article.pid
JOIN
	tl_page
	AS page
	ON page.id = archive.jumpTo
JOIN
	tl_page
	AS root_page
	ON root_page.id = page.hofff_root_page_id
SQL;
	}

	/**
	 * @return string
	 */
	protected function getRelationView() {
		return <<<SQL
CREATE OR REPLACE VIEW hofff_language_relations_news4ward_relation AS

SELECT
	item.group_id									AS group_id,
	item.root_page_id								AS root_page_id,
	item.page_id									AS page_id,
	item.item_id									AS item_id,
	related_item.item_id							AS related_item_id,
	related_item.page_id							AS related_page_id,
	related_item.root_page_id						AS related_root_page_id,
	related_item.group_id							AS related_group_id,
	item.root_page_id != related_item.root_page_id
		AND item.group_id = related_item.group_id	AS is_valid,
	reflected_relation.item_id IS NOT NULL			AS is_primary

FROM
	tl_hofff_language_relations_news4ward
	AS relation
JOIN
	hofff_language_relations_news4ward_item
	AS item
	ON item.item_id = relation.item_id
JOIN
	hofff_language_relations_news4ward_item
	AS related_item
	ON related_item.item_id = relation.related_item_id

LEFT JOIN
	tl_hofff_language_relations_news4ward
	AS reflected_relation
	ON reflected_relation.item_id = relation.related_item_id
	AND reflected_relation.related_item_id = relation.item_id
SQL;
	}

	/**
	 * @return string
	 */
	protected function getAggregateView() {
		return <<<SQL
CREATE OR REPLACE VIEW hofff_language_relations_news4ward_aggregate AS

SELECT
	archive.id					AS aggregate_id,
	CONCAT('a', archive.id)		AS tree_root_id,
	root_page.id				AS root_page_id,
	grp.id						AS group_id,
	grp.title					AS group_title,
	root_page.language			AS language
FROM
	tl_news4ward
	AS archive
JOIN
	tl_page
	AS page
	ON page.id = archive.jumpTo
JOIN
	tl_page
	AS root_page
	ON root_page.id = page.hofff_root_page_id
JOIN
	tl_hofff_language_relations_group
	AS grp
	ON grp.id = root_page.hofff_language_relations_group_id
SQL;
	}

	/**
	 * @return string
	 */
	protected function getTreeView() {
		return <<<SQL
CREATE OR REPLACE VIEW hofff_language_relations_news4ward_tree AS

SELECT
	0																AS pid,
	CONCAT('a', archive.id)											AS id,
	archive.title													AS title,
	0																AS selectable,
	root_page.hofff_language_relations_group_id						AS group_id,
	root_page.language												AS language,
	'archive'														AS type,
	NULL															AS date
FROM
	tl_news4ward
	AS archive
JOIN
	tl_page
	AS page
	ON page.id = archive.jumpTo
JOIN
	tl_page
	AS root_page
	ON root_page.id = page.hofff_root_page_id

UNION SELECT
	CONCAT('a', archive.id)												AS pid,
	CONCAT('a', archive.id, '_', YEAR(FROM_UNIXTIME(article.start)))	AS id,
	YEAR(FROM_UNIXTIME(article.start))									AS title,
	0																	AS selectable,
	root_page.hofff_language_relations_group_id							AS group_id,
	root_page.language													AS language,
	'year'																AS type,
	YEAR(FROM_UNIXTIME(article.start))									AS date
FROM
	tl_news4ward_article
	AS article
JOIN
	tl_news4ward
	AS archive
	ON archive.id = article.pid
JOIN
	tl_page
	AS page
	ON page.id = archive.jumpTo
JOIN
	tl_page
	AS root_page
	ON root_page.id = page.hofff_root_page_id
GROUP BY
	YEAR(FROM_UNIXTIME(article.start)),
	archive.id,
	root_page.language,
	root_page.hofff_language_relations_group_id

UNION SELECT
	CONCAT('a', archive.id, '_', YEAR(FROM_UNIXTIME(article.start)))	AS pid,
	article.id															AS id,
	article.title														AS title,
	1																	AS selectable,
	root_page.hofff_language_relations_group_id							AS group_id,
	root_page.language													AS language,
	'entry'																AS type,
	article.start														AS date
FROM
	tl_news4ward_article
	AS article
JOIN
	tl_news4ward
	AS archive
	ON archive.id = article.pid
JOIN
	tl_page
	AS page
	ON page.id = archive.jumpTo
JOIN
	tl_page
	AS root_page
	ON root_page.id = page.hofff_root_page_id
SQL;
	}

}
