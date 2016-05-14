<?php namespace Entity;

use Entity\Base\ArticleBase;
use Entity\Base\ArticleModel;


class Article extends ArticleBase{

	public $title;
	public $lead;
	
	/**
	 * @return \Entity\Base\ArticleModel
	 */
	public static function decorateModel($model){
		$model->authorId->setMin(50);
		$model->publishDate->default = function(){ return time(); };
		return $model;
	}


}