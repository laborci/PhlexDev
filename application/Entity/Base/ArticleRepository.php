<?php
/**
 * Author: Laborci Gergely
 * Copyright: 365 Media Ltd. (www.365media.hu)
 * Created: 14/05/16 23:31
 */

namespace Entity\Base;


use Phlex\RedFox\EntityRepository;
use Phlex\ResourceManager;

class ArticleRepository extends EntityRepository{
	protected static $__nstance = null;
	public static $database = 'default';
	protected static $table= 'article';
}