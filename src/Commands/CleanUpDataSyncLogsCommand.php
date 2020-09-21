<?php

namespace Baufragen\DataSync\Commands;

use Baufragen\DataSync\DataSync;
use Baufragen\DataSync\DataSyncLog;
use Baufragen\DataSync\Helpers\DataSyncAction;
use Baufragen\DataSync\Interfaces\DataSyncing;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

class CleanUpDataSyncLogsCommand extends Command {

	protected $signature = 'datasync:clean-up {--debug} {--successful} {--failed} {--age=3}';

	protected $description = 'Deletes logs older than the given age (in months)';

    protected $debug = false;

    protected $cleanSuccessful = false;
    protected $cleanFailed = false;

    protected $age = 3;

	public function handle()
	{
        $this->debug = $this->option('debug');

        if ($this->debug) {
            $this->info('=============');
            $this->info('DEBUGMODE');
            $this->info('=============');
        }

        $this->cleanFailed = $this->option('failed');
        $this->cleanSuccessful = $this->option('successful');

        $age = $this->option('age');
        if (is_numeric($age)) {
            $this->age = $age;
        }

        $deleting = [];

        $query = DataSyncLog::where('created_at', '<', now()->subMonths($this->age));

        if ($this->cleanSuccessful) {
            $query->successful();
            $deleting[] = 'successful';
        }

        if ($this->cleanFailed) {
            $query->failed();
            $deleting[] = 'failed';
        }

        $this->info('Deleting ' . $query->count() . ' ' . implode(" and ", $deleting) . ' DataSyncLogs that are older than ' . $this->age . ' months', OutputInterface::VERBOSITY_VERBOSE);

        $query->delete();
	}

}
