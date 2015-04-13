<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ClearApc extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'clear:apc';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Clear APC cache if installed and enabled.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		if (extension_loaded('apc') && ini_get('apc.enabled'))
		{
			apc_clear_cache();
			apc_clear_cache('user');
			apc_clear_cache('opcode');
		}
	}

}
