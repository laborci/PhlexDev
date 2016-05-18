<?php namespace Phlex\CliCommand;


use Phlex\RedFox\Generator\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;


class RedFoxAddCommand extends Command {

	protected function configure() {
		$this
			->setName('redfox:add')
			->addArgument('database', InputArgument::REQUIRED, 'Name of database')
			->addArgument('table', InputArgument::REQUIRED, 'Name of data table')
			->addArgument('entity', InputArgument::OPTIONAL, 'Name of entity')
			->setDescription('Builds configuration files');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$generator = new Generator();
		$generator->add($input->getArgument('database'), $input->getArgument('table'), $input->getArgument('entity'));
		$output->writeln('Kinda Done...');
		//$builder = new \Phlex\Env\ConfigBuilder(__DIR__.'/env/config/', __DIR__.'/.conf/', $output);
		//$server = $input->getArgument('server');
		//$builder->build($server);
		//$output->writeln('Done...');
	}
}