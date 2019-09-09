<?php

namespace Baufragen\DataSync\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\DetectsApplicationNamespace;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command {
    use DetectsApplicationNamespace;

	/**
	 * The console command signature.
	 *
	 * @var string
	 */
	protected $signature = 'datasync:install';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Installs the necessary files etc for datasyncing';

    /**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
        $this->comment('Publishing DataSync Service Provider...');
        $this->callSilent('vendor:publish', ['--tag' => 'datasync-provider']);

        $this->comment('Publishing DataSync Assets...');
        $this->callSilent('vendor:publish', ['--tag' => 'datasync-assets']);
        $this->replaceImagePathsInAssets();

        $this->comment('Publishing DataSync Configuration...');
        $this->callSilent('vendor:publish', ['--tag' => 'datasync-config']);

        $this->registerDataSyncServiceProvider();
        $this->info('DataSync scaffolding installed successfully.');
	}

	protected function registerDataSyncServiceProvider() {
        $namespace = Str::replaceLast('\\', '', $this->getAppNamespace());
        $appConfig = file_get_contents(config_path('app.php'));
        if (Str::contains($appConfig, $namespace.'\\Providers\\DataSyncServiceProvider::class')) {
            return;
        }
        file_put_contents(config_path('app.php'), str_replace(
            "{$namespace}\\Providers\EventServiceProvider::class,".PHP_EOL,
            "{$namespace}\\Providers\EventServiceProvider::class,".PHP_EOL."        {$namespace}\Providers\DataSyncServiceProvider::class,".PHP_EOL,
            $appConfig
        ));
        file_put_contents(app_path('Providers/DataSyncServiceProvider.php'), str_replace(
            "namespace App\Providers;",
            "namespace {$namespace}\Providers;",
            file_get_contents(app_path('Providers/DataSyncServiceProvider.php'))
        ));
    }

    protected function replaceImagePathsInAssets() {
	    $cssPath = public_path("vendor/datasync/css/app.css");
	    if (file_exists($cssPath)) {
	        file_put_contents($cssPath, str_replace(
	            "/images/vendor/json-tree-viewer/libs/jsonTree/icons.svg",
                "/vendor/datasync/img/icons.svg",
                file_get_contents($cssPath)
            ));
        }
    }

}
