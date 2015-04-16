<?php namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use RuntimeException;

class MakeTest extends GeneratorCommand {

	/**
	 * {@inheritdoc}
	 */
	protected $name = 'make:test';

	/**
	 * {@inheritdoc}
	 */
	protected $description = 'Create a new phpunit test class';

	/**
	 * {@inheritdoc}
	 */
	protected $type = 'Test';

	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	protected function getStub()
	{
		return __DIR__.'/stubs/test.stub';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getPath($name)
	{
		$name = str_replace($this->getAppNamespace(), '', $name);

		return base_path().'/tests/'.str_replace('\\', '/', $name).'Test.php';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getDefaultNamespace($rootNamespace)
	{
		return $rootNamespace;
	}

	/**
	 * Replace the class under test for the given stub.
	 *
	 * @param  string  $stub
	 * @param  string  $name
	 * @return $this
	 */
	protected function replaceUnderTestClass(&$stub, $name)
	{
		$name = str_replace($this->getAppNamespace().'Unit\\', '', $name);
		$name = str_replace($this->getAppNamespace().'Functional\\', '', $name);
		$name = str_replace($this->getAppNamespace().'Integration\\', '', $name);
		
		$stub = str_replace(
			'{{appclass}}', '\App\\'.$name, $stub
		);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function buildClass($name)
	{
		$stub = $this->files->get($this->getStub());

		return $this->replaceNamespace($stub, $name)->replaceUnderTestClass($stub, $name)->replaceClass($stub, $name);
	}

}
