<?php namespace Phlex\CliCommand;


use Phlex\Kraft\Parser\TemplateHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


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
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$parser = new TemplateHandler();
		$force = (bool)$input->getOption('force');
		$parser->parse(null, null, $force);
		$output->writeln('Done...');
	}
}