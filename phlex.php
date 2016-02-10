#!/usr/bin/env php
<?php
// http://symfony.com/doc/current/components/console/introduction.html

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/modules/autoload.php';

putenv('root='.__DIR__);

error_reporting(E_ALL);
ini_set('display_errors', true);

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class ConfigCommand extends Command {
	protected function configure() {
		$this
			->setName('config')
			->addArgument('server', InputArgument::REQUIRED, 'Name of server')
			->setDescription('Builds configuration files')
		;
	}


	protected function execute(InputInterface $input, OutputInterface $output) {
		$builder = new \Phlex\Config\ConfigBuilder(__DIR__.'/env/config/', __DIR__.'/.conf/', $output);
		$server = $input->getArgument('server');
		$builder->build($server);
		$output->writeln('Done...');
	}
}

class BuildCommand extends Command {
	protected function configure() {
		$this
			->setName('build')
			->addArgument('force')
			->setDescription('Builds kraft template files')
			->addOption(
				'force',
				'f',
				InputOption::VALUE_NONE,
				'If set, forces full build'
			)
		;
	}
	protected function execute(InputInterface $input, OutputInterface $output) {
		$parser = new Phlex\Kraft\Parser\TemplateHandler();
		$force = (bool) $input->getOption('force');
		$parser->parse(null, null, $force);
		$output->writeln('Done...');
	}
}


$application = new Application();
$application->add(new ConfigCommand());
$application->add(new BuildCommand());
$application->run();
