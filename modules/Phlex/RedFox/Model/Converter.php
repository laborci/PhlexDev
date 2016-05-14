<?php namespace Phlex\RedFox\Model;

interface Converter {
	public function convertRead($value);
	public function convertWrite($value);
}