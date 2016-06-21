<?php namespace Phlex\CliCommand\Config;


use Phlex\Env\ConfigBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;


class Init extends Command {

	protected function configure() {
		$this
			->setName('config:init')
			->setDescription('Inits the project');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$dir = getenv('root');
		if(!is_dir($dir . '/.conf/')) mkdir($dir . '/.conf/');
		if(!is_dir($dir . '/.log/')) mkdir($dir . '/.log/');
		if(!is_dir($dir . '/.templates/')) mkdir($dir . '/.templates/');

		$output->writeln('Done...');
	}
}