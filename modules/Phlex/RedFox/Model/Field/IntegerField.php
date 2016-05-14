<?php

/**
 * Author: Laborci Gergely
 * Copyright: 365 Media Ltd. (www.365media.hu)
 * Created: 02/05/16 21:48
 */

namespace Phlex\RedFox\Model\Field;
use Phlex\RedFox\Model\EntityRepository;
use Phlex\RedFox\Model\Field;

class IntegerField extends Field{
	protected $min;
	protected $max;

	public function __construct($name, $null, $min, $max) {
		parent::__construct($name, $null);
		$this->min = $min;
		$this->max = $max;
	}

	public function setMax($max){
		if($max>=$this->min && $max<=$this->max) $this->max = $max;
	}
	public function setMin($min){
		if($min>=$this->min && $min<=$this->max) $this->min = $min;
	}


	function typevalidator($value) {
		if( !is_numeric($value) ) return 1;
		if( $value<$this->min || $value>$this->max ) return 2;
		return 0;
	}


	/**
	 * @var EntityRepository
	 */
	protected $relatedRepository;

	/**
	 * @param $repository EntityRepository
	 */
	function setRelation($repository){
		$this->relatedRepository = $repository;
	}

	/**
	 * @param $id
	 *
	 * @return mixed
	 */
	function getRelatedObject($id){
		return $this->relatedRepository->get($id);
	}

}