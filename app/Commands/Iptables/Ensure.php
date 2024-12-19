<?php

declare(strict_types=1);

namespace App\Commands\Iptables;

use App\Framework\Commands\Command;

class Ensure extends Command
{
    protected $signature = 'iptables:ensure
    {profile : The profile to apply the rules for}';

    protected $description = 'Ensure iptables rules are applied';

    public function handle() {}
}
