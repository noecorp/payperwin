<?php namespace App\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class MakeTestCommand extends GeneratorCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'make:test';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a new phpunit test class';

	/**
	 * The type of class being generated.
	 *
	 * @var string
	 */
	protected $type = 'Test';

	/**
	 * Get the application namespace from the Composer file.
	 *
	 * @return string
	 *
	 * @throws \RuntimeException
	 */
	protected function getAppNamespace()
	{
		$composer = json_decode(file_get_contents(base_path().'/composer.json'), true);

		foreach ((array) data_get($composer, 'autoload-dev.psr-4') as $namespace => $path)
		{
			foreach ((array) $path as $pathChoice)
			{
				if (realpath(base_path().'/tests') == realpath(base_path().'/'.$pathChoice)) return $namespace;
			}
		}

		throw new RuntimeException("Unable to detect application namespace.");
	}

	/**
	 * Get the stub file for the generator.
	 *
	 * @return string
	 */
	protected function getStub()
	{
		return __DIR__.'/stubs/test.stub';
	}

	/**
	 * Get the destination class path.
	 *
	 * @param  string  $name
	 * @return string
	 */
	protected function getPath($name)
	{
		$name = str_replace($this->getAppNamespace(), '', $name);

		return base_path().'/tests/'.str_replace('\\', '/', $name).'.php';
	}

	/**
	 * Get the default namespace for the class.
	 *
	 * @param  string  $rootNamespace
	 * @return string
	 */
	protected function getDefaultNamespace($rootNamespace)
	{
		return $rootNamespace;
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [];
	}

}
