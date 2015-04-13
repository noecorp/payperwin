<?php namespace App\Events\Repositories;

use App\Events\Event;
use App\Contracts\Events\Models as ModelsEventInterface;

abstract class ModelsEvent extends Event implements ModelsEventInterface {

}
