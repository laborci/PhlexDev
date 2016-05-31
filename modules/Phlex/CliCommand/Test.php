<?php namespace Phlex\CliCommand;


use Phlex\Database\Filter;
use Phlex\RedFox\Generator\Generator;
use Phlex\ResourceManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;


class Test extends Command {

	protected function configure() {
		$this
			->setName('test');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$filter = Filter::filter("a = $1", 12)->or('a=$1',1)->and(Filter::filter('c=1')->or('b=1'));
		$filter->and('z = 1');
		echo $filter->getSql(ResourceManager::db('app'));
	}
}