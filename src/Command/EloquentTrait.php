<?php

namespace Appkr\Importer\Command;

use Illuminate\Database\Capsule\Manager as Capsule;

trait EloquentTrait
{
    protected $config = __DIR__.'/../config/database.php';

    /**
     * Bootup mysql database and instantiate Eloquent.
     */
    public function bootDatabase()
    {
        if (! file_exists($this->config)) {
            throw new \Exception("'config/database.php' file not found.");
        }

        // Boot Eloquent
        $capsule = new Capsule;
        $capsule->addConnection(include($this->config));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

    /**
     * Get the list of table headings.
     *
     * @param $table
     * @return array
     */
    public function getColumnListings($table)
    {
        return Capsule::schema()->getColumnListing($table);
    }
}

