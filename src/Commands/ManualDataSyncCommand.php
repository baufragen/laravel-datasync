<?php

namespace Baufragen\DataSync\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

class ManualDataSyncCommand extends Command {

	/**
	 * The console command signature.
	 *
	 * @var string
	 */
	protected $signature = 'datasync:manual-sync {--debug}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Creates a report for manufacturer landingpage visits';

    protected $debug = false;

    /**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
        $this->debug = $this->option('debug');

        if ($this->debug) {
            $this->info('=============');
            $this->info('DEBUGMODE');
            $this->info('=============');
        }

        // get all syncable models
        $models = config('datasync.models');
        if ($models) {
            $models = collect(array_keys($models));
        }

        if (!$models) {
            $this->error('Could not find any model definitions in config');
            return;
        }

        // cycle through models
        $models->each(function ($modelClass) {

            $this->verboseInfo('====================');
            $this->verboseInfo('Syncing ' . $modelClass);
            $this->verboseInfo('====================');

            // for every model, get all instances (possibly chunked to save memory)
            $modelClass::chunk(100, function ($entities) use ($modelClass) {

                $entities->each(function ($entity) use ($modelClass) {
                    // for every instance check if the last successful sync log is more than ~5 seconds before the updated_at

                    if ($entity->needsInitialDataSync()) {

                        if (!$this->debug) {
                            $entity->triggerInitialDataSync();
                        }
                        $this->verboseInfo('Triggered initial data sync for ' . $modelClass . ' [' . $entity->id . ']');
                        return;

                    } else if ($entity->needsManualDataSync()) {

                        if (!$this->debug) {
                            $entity->triggerManualDataSync();
                        }
                        $this->verboseInfo('Triggered manual data sync for ' . $modelClass . '[' . $entity->id . ']');
                        return;

                    }

                });
            });

        });
	}

	protected function verboseInfo($info) {
        if ($this->verbosity === OutputInterface::VERBOSITY_VERBOSE) {
            $this->info($info);
        }
    }

}
