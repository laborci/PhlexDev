<?php namespace Phlex\CliCommand\RedFox;


use Phlex\RedFox\Generator\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;


class Generate extends Command {

	protected function configure() {
		$this
			->setName('redfox:generate')
			->setDescription('Generates entity classes');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$generator = new Generator();
		$generator->generate();
		$output->writeln('Done...');
	}
}