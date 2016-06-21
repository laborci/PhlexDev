<?php namespace Phlex\RedFox;

use Phlex\RedFox\Model\Converter;
use Phlex\RedFox\Model\Field;
use Phlex\RedFox\Model\Model;

/**
 * Class Entity
 * @package Phlex\RedFox
 * @property $id integer
 */
abstract class Entity {

	/** @var EntityRepository */
	public $_repository;

	/** @var integer */
	protected $id = null;

	public function __construct( EntityRepository $repository ) { $this->_repository = $repository; }

	/** @return Model */
	abstract public function getModel();

	/** @return string */
	public function __toString() { return (string)$this->id; }

	/** @return static */
	public function copy() {
		$clone = new static( $this->_repository );
		$data = $this->_dataOut();
		unset($data[ 'id' ]);
		$clone->_dataIn( $data );
		return $clone;
	}


	#region data transfer

	/**
	 * Fills a blank object with data. Should not be used!
	 * @param array $data
	 * @return static
	 */
	public function _dataIn( $data ) {
		$model = $this->getModel();

		foreach( $data as $key => $value ) {
			if( property_exists( $model, $key ) && $model->$key instanceof Field ) {
				if( $model->$key instanceof Converter ) {
					$this->$key = $model->$key->convertRead( $value );
				} else {
					$this->$key = $value;
				}
			}
		}
	}

	/**
	 * Colects the object data to an array. Should not be used!
	 * @return array
	 */
	public function _dataOut() {
		$model = $this->getModel();
		$data = array();

		return $data;
		//TODO: implement this method
	}
	#endregion

	#region getters / setters

	/** @return integer */
	protected function getId() { return $this->id; }

	/** @param $id integer */
	protected function setId( $id ) {
		if( $this->id == null ) $this->id = $id;
		else trigger_error( 'Entity ID can not be set', E_USER_WARNING );
	}

	#endregion

	public function __get( $propertyName ) {
		$method = 'get' . ucfirst( $propertyName );
		if( method_exists( $this, $method ) ) {
			return $this->$method();
		}
		return null;
	}

	public function __set( $propertyName, $value ) {
		$method = 'set' . ucfirst( $propertyName );
		if( method_exists( $this, $method ) ) {
			$this->$method( $value );
		}
	}
	public function __unset( $propertyName ) {
		$method = 'unset' . ucfirst( $propertyName );
		if( method_exists( $this, $method ) ) {
			$this->$method();
		}
	}

	public function __isset( $propertyName ) {
		$method = 'isset' . ucfirst( $propertyName );
		if( method_exists( $this, $method ) ) {
			return $this->$method();
		}
		else return false;
	}

}