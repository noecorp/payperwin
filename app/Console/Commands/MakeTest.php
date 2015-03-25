<?php namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;

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

		return base_path().'/tests/'.str_replace('\\', '/', $name).'.php';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getDefaultNamespace($rootNamespace)
	{
		return $rootNamespace;
	}

}
