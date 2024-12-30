<?php
declare(strict_types=1);

namespace App\Commands\Ros;

use App\Enums\VyOSConfig;
use App\Framework\Commands\Command;
use App\Services\Helper;
use App\Services\VyOs;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process as SymfonyProcess;
use App\Attributes\AppBinding;
class ConfigInit extends Command
{
    protected $signature = 'ros:config-init
    {hw-id : The initial mac address of the router os initialisation ethernet interface}
    {view=ntrn::ros.config_init : The view to use for the initial configuration}
    {--f|force : Force to apply configuration}';

    protected $description = 'Initialise the router os configuration';

    public function handle(
        #[AppBinding('conf')] Fluent $conf,
    ): void
    {
//        if (! Helper::appIsVyOS()) {
//            $this->error('This command can only be run on a VyOS router');
//            return;
//        }

        $data = fluent([
            '_args' => $this->arguments(),
            '_opts' => $this->options(),
        ]);

        if ($conf->get('ros.config.init') === true && $data->get('_opts.force') !== true){
            $this->error('Initial configuration already applied. Use --force option to reapply');
            return;
        }

//        $vyos = VyOs::getConfig(VyOSConfig::ARRAY);
        $vyos = '{"interfaces": {"ethernet": {"eth0": {"hw-id": "e4:43:4b:0e:0e:86"}, "eth1": {"hw-id": "e4:43:4b:0e:0e:88"}, "eth2": {"hw-id": "e4:43:4b:0e:0e:a7"}, "eth3": {"address": ["10.10.11.1/24"], "hw-id": "bc:24:11:2b:47:89"}, "eth4": {"hw-id": "3c:fd:fe:38:3e:20"}, "eth5": {"hw-id": "3c:fd:fe:38:3e:22"}, "eth6": {"hw-id": "f8:f2:1e:95:19:40"}, "eth7": {"hw-id": "f8:f2:1e:95:19:41"}, "eth8": {"hw-id": "e4:1d:2d:bc:f2:d1"}, "eth9": {"hw-id": "e4:1d:2d:bc:f2:d2"}}, "loopback": {"lo": {}}}, "protocols": {"static": {"route": {"0.0.0.0/0": {"next-hop": {"10.10.11.2": {}}}}}}, "service": {"ntp": {"allow-client": {"address": ["127.0.0.0/8", "169.254.0.0/16", "10.0.0.0/8", "172.16.0.0/12", "192.168.0.0/16", "::1/128", "fe80::/10", "fc00::/7"]}, "server": {"time1.vyos.net": {}, "time2.vyos.net": {}, "time3.vyos.net": {}}}, "ssh": {}}, "system": {"config-management": {"commit-revisions": "100"}, "conntrack": {"modules": {"ftp": {}, "h323": {}, "nfs": {}, "pptp": {}, "sip": {}, "sqlnet": {}, "tftp": {}}}, "console": {"device": {"ttyS0": {"speed": "115200"}}}, "host-name": "vyos", "login": {"user": {"vyos": {"authentication": {"encrypted-password": "$6$QxPS.uk6mfo$9QBSo8u1FkH16gMyAVhus6fU3LOzvLR9Z9.82m3tiHFAxTtIkhaZSWssSgzt4v4dGAL8rhVQxTg0oAG9/q11h/", "plaintext-password": ""}}}}, "syslog": {"global": {"facility": {"all": {"level": "info"}, "local7": {"level": "debug"}}}}}}';
        $vyos = json_decode($vyos, true);

        foreach(data_get($vyos, 'interfaces.ethernet', []) as $key => $value) {
            if (data_get($value, 'hw-id') === $data->get('_args.hw-id')) {
                $data->set('init.interface.name', $key);
                break;
            }
        }

        if (! $data->has('init.interface.name')) {
            $this->error("No ethernet interface found with the specified [{$data->get('_args.hw-id')}] hw-id");
            return;
        }

        $cmds = Str::of((string) view($data->get('_args.view'), compact('data')))
            ->lines(flags: PREG_SPLIT_NO_EMPTY);
        $cmds = VyOS::prepareCfg($cmds);

        dump($cmds);
//        $data = fluent([
//            'deneme' => 'eth0'

//        $hwId = $this->argument('hw-id');
//        $view = $this->argument('view');
//
//        if (! view()->exists($view)) {
//            $this->error("View [{$view}] not found for initial configuration");
//            return;
//        }


    }
}
