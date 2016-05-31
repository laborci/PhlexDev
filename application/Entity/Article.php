<?php namespace Entity;

use EntityBase\ArticleBase;

/**
 * Class Article
 *
 * @package Entity
 * @property-read ArticleBase $author
 * @property-read string      $title
 * @property \DateTime        $publishDate
 */
class Article extends ArticleBase{

	function myMethod(){
		return ArticleRepository::instance()->get(12);
	}

}