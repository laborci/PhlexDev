<?php namespace Phlex\Database;

interface RequestConverter {
	public function DBRequestConvert(array $data, $multiple = false);
}