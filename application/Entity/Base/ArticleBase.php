<?php
/**
 * Created by PhpStorm.
 * User: elvis
 * Date: 13/02/16
 * Time: 13:52
 */

namespace Entity;

/*

visibility:
   public
	protected
	readonly (protected with autogetter)
	rock (protected / can be write only if current value is null) |

fields: {
	id: {
		name: "id",
		type: numeric,
		visibility: rock
	}
	authorId:{
		name: "authorId",
		type: numeric,
		visibility: public
		refers: User
	}
}

*/


class ArticleBase extends \Phlex\RedFox\Entity {

	protected $id;
	protected $title;
	protected $authorId;
	protected $author; // just to prevent writes
	protected $body;

	function __get($name) {
		switch ($name) {
			case 'id':
				return $this->id;
				break;
			case 'author':
				return User::load($this->authorId);
				break;
		}
		$getterMethodName = '__get' . ucfirst($name);
		if (method_exists($this, $getterMethodName)) return $this->$getterMethodName();
	}

	protected function dehidrate() {
		return array(
			'id'    => $this->id,
			'title' => $this->title,
			'body'  => $this->body
		);
	}

	protected function hidrate($data) {
		if (array_key_exists('id', $data)) $this->id = $data['id'];
		if (array_key_exists('title', $data)) $this->title = $data['title'];
		if (array_key_exists('body', $data)) $this->body = $data['body'];
	}

}