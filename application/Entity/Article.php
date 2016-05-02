<?php namespace Entity;

use Entity\Base\ArticleBase;


class Article extends ArticleBase{

	public $title;
	public $lead;

	public static function polishModel($model){
		$model['publishDate']->addValidator(
			//DateTimeValidator::InTheFuture()
		);
		$model['publishDate']->default = function(){ return time(); };
		return $model;
	}



}