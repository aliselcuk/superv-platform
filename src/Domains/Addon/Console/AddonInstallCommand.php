<?php

namespace SuperV\Platform\Domains\Addon\Console;

use Exception;
use Illuminate\Console\Command;
use SuperV\Platform\Domains\Addon\Installer;
use SuperV\Platform\Exceptions\ValidationException;

class AddonInstallCommand extends Command
{
    protected $signature = 'addon:install {path}  {--seed}';

    public function handle(Installer $installer)
    {
        try {

            $installer->setCommand($this);
            $installer->setPath($this->argument('path'));

            $this->comment('Installing Addon '.$installer->getIdentifier());

            try {
                $installer->install();
            } catch (Exception $e) {
                dd($e);
            }

            if ($this->option('seed')) {
                $installer->seed();
            }

            $this->comment(sprintf("Addon %s installed \n", $installer->getIdentifier()));
        } catch (ValidationException $e) {
            $this->error($e->getErrorsAsString());
        } catch (\Exception $e) {
            dd($e->getMessage());
            $this->error($e->getMessage());
        }
    }
}
