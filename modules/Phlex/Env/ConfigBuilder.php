<?php namespace Phlex\Env;

use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class ConfigBuilder {

	private $path, $outputPath, $parser, $servers;
	private $c_output;

	public function __construct($path, $outputPath, $c_output = null){
		$cwd = getcwd();

		chdir($path);
		$this->path = $path;
		$this->outputPath = $outputPath;
		$this->parser = new ConfigParser($this->path);
		$this->servers = glob('*', GLOB_ONLYDIR);
		$this->c_output = $c_output;
		chdir($cwd);
	}

	public function build($server){
		if(!is_dir($this->path.$server) || !file_exists($this->path.$server.'/config.json')){
			throw new InvalidArgumentException('Server config not found!');
		}
		$cfg = $this->parser->parse($server);
		$cfgJsonPath = $this->outputPath.'config.json';
		$cfgPhpPath = $this->outputPath.'config.php';
		$json = json_encode($cfg, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES);
		$php = '<?php return '.var_export($cfg, true).';';
		$this->c_output->writeln('Server: <fg=magenta;options=bold>'.$server.'</>');
		$this->c_output->write('- config: ');
		if(!file_exists($cfgJsonPath) || md5_file($cfgJsonPath) !== md5($json)) {
			file_put_contents($cfgJsonPath, $json);
			file_put_contents($cfgPhpPath, $php);
			$this->c_output->writeln('<fg=green>saved</>');

		}else{
			$this->c_output->writeln('not changed');
		}

		$this->genVhost($server, $cfg, $cfgJsonPath);
	}

	public function genVhost($server, $cfg){
		if(file_exists($this->path.'vhost/vhost.'.$server.'.conf')) $vhost = file_get_contents($this->path.'../vhost/vhost.'.$server.'.conf');
		else $vhost = file_get_contents($this->path.'../vhost/vhost.template.conf');

		$vhost = str_replace('{{domain}}', $cfg['domain'], $vhost);
		$vhost = str_replace('{{domain-aliases}}', is_array($cfg['domain-aliases']) ? join(' ', $cfg['domain-aliases']) : $cfg['domain-aliases'], $vhost);
		$vhost = str_replace('{{root}}', $cfg['root'], $vhost);
		//$vhost = str_replace('{{server}}', $server, $vhost);
		$vhostFile = $this->outputPath.'/'.'vhost.conf';

		$this->c_output->write('- vhost: ');
		if(!file_exists($vhostFile) || md5_file($vhostFile) !== md5($vhost)) {
			file_put_contents($vhostFile, $vhost);
			$this->c_output->writeln('<fg=green>saved</>');
		}else{
			$this->c_output->writeln('not changed');
		}
		file_put_contents($vhostFile, $vhost);
	}

}