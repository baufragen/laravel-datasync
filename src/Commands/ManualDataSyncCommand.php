<?php

namespace Baufragen\DataSync\Commands;

use Baufragen\DataSync\DataSync;
use Baufragen\DataSync\Helpers\DataSyncAction;
use Baufragen\DataSync\Interfaces\DataSyncing;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

class ManualDataSyncCommand extends Command {

	/**
	 * The console command signature.
	 *
	 * @var string
	 */
	protected $signature = 'datasync:manual-sync {--debug} {--model=} {--id=} {--skip=} {--take=}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Starts a syncing process for either the given model or for all models';

    protected $debug = false;

    protected $models = null;
    protected $ids = null;

    protected $synced = null;

    protected $skip = null;
    protected $take = null;

    public function __construct()
    {
        parent::__construct();

        $this->synced = collect([]);
    }

    /**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
        $this->debug = $this->option('debug');
        $this->skip = $this->option('skip', null);
        $this->take = $this->option('take', null);

        $model = $this->option('model', null);
        if ($model) {
            $this->info('Limiting synced models to ' . $model);
            $this->models = collect(explode(",", $model));
        }

        $id = $this->option('id', null);
        if ($id) {
            if (!$model) {
                $this->error('Cannot use id parameter without model parameter');
                return;
            }

            $this->info('Limiting syncing to ID(s): ' . $id);
            $this->ids = collect(explode(",", $id));
        }

        if ($this->debug) {
            $this->info('=============');
            $this->info('DEBUGMODE');
            $this->info('=============');
        }

        // get all syncable models
        $models = config('datasync.models');
        if ($models) {
            $models = collect($models);
        }

        if (!$models) {
            $this->error('Could not find any model definitions in config');
            return;
        }

        // cycle through models
        $models
            // filter models by argument first
            // if no argument is set -> allow all models
            ->filter(function ($syncName) {
                if (!$this->models) {
                    return true;
                }

                return in_array($syncName, $this->models->toArray());
            })
            ->keys()
            ->each(function ($modelClass) {

                $this->verboseInfo('====================');
                $this->verboseInfo('Syncing ' . $modelClass);
                $this->verboseInfo('====================');

                if ($this->ids) {
                    $this->ids->each(function($id) use ($modelClass) {
                        $entity = $modelClass::find($id);

                        if (!$entity) {
                            $this->error('Could not find ' . $modelClass . '[' . $id . ']');
                            return;
                        }

                        $this->triggerSync($entity);
                    });
                } else {
                    // for every model, get all instances (possibly chunked to save memory)
                    $modelClass::when($this->skip, function ($query) {
                            $query->skip($this->skip);
                        })
                        ->when($this->take, function ($query) {
                            $query->take($this->take);
                        })
                        ->get()
                        ->each(function ($entity) use ($modelClass) {
                            try {

                                $this->triggerSync($entity);

                            } catch (\Exception $e) {
                                $this->error('Error during sync for ' . $modelClass . ' [' . $entity->id . ']: ' . $e->getMessage());
                            }
                        });
                }
        });

        if (!$this->debug) {
            app('dataSync.handler')->dispatch();
        }

        $this->synced->each(function (DataSyncing $entity) {
            if (method_exists($entity, "afterTotalDataSync")) {
                $entity->afterTotalDataSync();
            }
        });
	}

	protected function triggerSync(DataSyncing $entity) {
        $this->verboseInfo('Syncing model ' . get_class($entity) . ' #' . $entity->id);

        if (!method_exists($entity, "totalDataSync")) {
            $this->error('Model ' . get_class($entity) . ' does not have totalDataSync method');
            return;
        }

        if (!$this->debug) {
            $entity->totalDataSync();
        }

        $this->synced->push($entity);
    }

	protected function verboseInfo($info) {
        if ($this->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->info($info);
        }
    }

}
