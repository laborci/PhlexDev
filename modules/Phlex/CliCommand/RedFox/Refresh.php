<?php namespace Phlex\CliCommand\RedFox;


use Phlex\RedFox\Generator\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;


class Refresh extends Command {

	protected function configure() {
		$this
			->setName('redfox:refresh')
			->setDescription('Refreshes all json descriptors');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$generator = new Generator();
		$generator->refresh();
		$output->writeln('Done...');
	}
}