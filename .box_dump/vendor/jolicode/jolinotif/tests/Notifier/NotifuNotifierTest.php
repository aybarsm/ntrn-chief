<?php










namespace Joli\JoliNotif\tests\Notifier;

use Joli\JoliNotif\Notifier;
use Joli\JoliNotif\Notifier\NotifuNotifier;

/**
@group
*/
class NotifuNotifierTest extends NotifierTestCase
{
use BinaryProviderTestTrait;
use CliBasedNotifierTestTrait;

private const BINARY = 'notifu';

public function testGetBinary()
{
$notifier = $this->getNotifier();

$this->assertSame(self::BINARY, $notifier->getBinary());
}

public function testGetPriority()
{
$notifier = $this->getNotifier();

$this->assertSame(Notifier::PRIORITY_LOW, $notifier->getPriority());
}

protected function getNotifier(): Notifier
{
return new NotifuNotifier();
}

protected function getExpectedCommandLineForNotification(): string
{
return <<<'CLI'
            'notifu' '/m' 'I'\''m the notification body'
            CLI;
}

protected function getExpectedCommandLineForNotificationWithATitle(): string
{
return <<<'CLI'
            'notifu' '/m' 'I'\''m the notification body' '/p' 'I'\''m the notification title'
            CLI;
}

protected function getExpectedCommandLineForNotificationWithAnIcon(): string
{
$iconDir = $this->getIconDir();

return <<<CLI
            'notifu' '/m' 'I'\\''m the notification body' '/i' '{$iconDir}/image.gif'
            CLI;
}

protected function getExpectedCommandLineForNotificationWithAllOptions(): string
{
$iconDir = $this->getIconDir();

return <<<CLI
            'notifu' '/m' 'I'\\''m the notification body' '/p' 'I'\\''m the notification title' '/i' '{$iconDir}/image.gif'
            CLI;
}
}
