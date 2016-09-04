<?php

Database::getInstance()->query('DROP VIEW IF EXISTS hofff_language_relations_news4ward_tree');
Database::getInstance()->query('DROP VIEW IF EXISTS hofff_language_relations_news4ward_aggregate');
Database::getInstance()->query('DROP VIEW IF EXISTS hofff_language_relations_news4ward_relation');
Database::getInstance()->query('DROP VIEW IF EXISTS hofff_language_relations_news4ward_item');
