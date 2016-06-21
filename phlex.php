#!/usr/bin/env php
<?php
// http://symfony.com/doc/current/components/console/introduction.html

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/modules/autoload.php';

putenv('root='.__DIR__.'/');

error_reporting(E_ALL);
ini_set('display_errors', true);

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class RedFoxRefreshCommand extends Command {
	protected function configure() {
		$this
			->setName('redfox:refresh')
			->addArgument('entity', InputArgument::OPTIONAL, 'Name of entity')
			->setDescription('Builds configuration files')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		//$builder = new \Phlex\Env\ConfigBuilder(__DIR__.'/env/config/', __DIR__.'/.conf/', $output);
		//$server = $input->getArgument('server');
		//$builder->build($server);
		//$output->writeln('Done...');
	}
}

class RedFoxGenerateCommand extends Command {
	protected function configure() {
		$this
			->setName('redfox:generate')
			->addArgument('entity', InputArgument::OPTIONAL, 'Name of entity')
			->setDescription('Builds configuration files')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		//$builder = new \Phlex\Env\ConfigBuilder(__DIR__.'/env/config/', __DIR__.'/.conf/', $output);
		//$server = $input->getArgument('server');
		//$builder->build($server);
		//$output->writeln('Done...');
	}
}



$application = new Application();
$application->add(new \Phlex\CliCommand\Config\Build());
$application->add(new \Phlex\CliCommand\Config\Init());
$application->add(new \Phlex\CliCommand\Kraft\Build());
$application->add(new \Phlex\CliCommand\RedFox\Add());
$application->add(new \Phlex\CliCommand\RedFox\Refresh());
$application->add(new \Phlex\CliCommand\RedFox\Generate());
$application->add(new \Phlex\CliCommand\Test());
$application->run();
