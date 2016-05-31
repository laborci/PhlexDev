<?php namespace Entity;

use \EntityBase\ArticleRepositoryBase;
use Phlex\Database\Filter;


class ArticleRepository extends ArticleRepositoryBase{

	public function getSome(){
		print_r($this->find()->desc('title')->getAll());
	}
}