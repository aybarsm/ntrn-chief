<?php

declare(strict_types=1);

namespace App\Commands\Ros;

use App\Attributes\AppBinding;
use App\Enums\RosConfig;
use App\Framework\Commands\Command;
use App\Services\Helper;
use App\Services\Ros;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigInit extends Command
{
    protected $signature = 'ros:config-init
    {hw-id : The initial mac address of the router os initialisation ethernet interface}
    {view=ntrn::ros.config_init : The view to use for the initial configuration}
    {--dry : Dry run}
    {--f|force : Force to apply configuration}';

    protected $description = 'Initialise the router os configuration';

    public function handle(
        #[AppBinding('conf')] Fluent $conf,
    ): void {
        if (! Helper::appIsRos()) {
            $this->error('This command can only be run on a router os');

            return;
        }

        $data = fluent([
            '_args' => $this->arguments(),
            '_opts' => $this->options(),
        ]);

        if (! view()->exists($data->get('_args.view'))) {
            $this->error("The [{$data->get('_args.view')}] view does not exist");

            return;
        }

        if ($conf->get('ros.config.init') === true && $data->get('_opts.force') !== true) {
            $this->error('Initial configuration already applied. Use --force option to reapply');

            return;
        }

        $ros = Ros::getConfig(RosConfig::ARRAY);

        foreach (data_get($ros, 'interfaces.ethernet', []) as $key => $value) {
            if (data_get($value, 'hw-id') === $data->get('_args.hw-id')) {
                $data->set('init.interface.name', $key);
                break;
            }
        }

        if (! $data->has('init.interface.name')) {
            $this->error("No ethernet interface found with the specified [{$data->get('_args.hw-id')}] hw-id");

            return;
        }
        $cmds = (string) view($data->get('_args.view'), compact('data'));
        $cmds = Str::of($cmds)->lines(flags: PREG_SPLIT_NO_EMPTY);

        if ($data->get('_opts.dry')) {
            $this->title('Dry Run');

            foreach ($cmds as $cmd) {
                $this->line($cmd);
            }

            return;
        }

        $res = Ros::cfg($cmds,
            ($this->output->getVerbosity() === OutputInterface::VERBOSITY_DEBUG ? $this->output : null)
        );

        if ($res !== true) {
            $this->error($res);
        } else {
            $this->info('Initial configuration applied successfully');
        }

        $conf->set('ros.config.init', true);
    }
}
