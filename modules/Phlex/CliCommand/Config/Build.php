<?php namespace Phlex\CliCommand\Config;


use Phlex\Env\ConfigBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;


class Build extends Command {

	protected function configure() {
		$this
			->setName('config:build')
			->addArgument('server', InputArgument::REQUIRED, 'Name of server')
			->setDescription('Builds configuration files');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$dir = getenv('root');
		$builder = new ConfigBuilder( $dir . '/env/config/', $dir . '/.conf/', $output);
		$server = $input->getArgument('server');
		$builder->build($server);
		$output->writeln('Done...');
	}
}