<?php










namespace Joli\JoliNotif\tests\Notifier;

use Joli\JoliNotif\Notifier;
use Joli\JoliNotif\Notifier\SnoreToastNotifier;

/**
@group
*/
class SnoreToastNotifierTest extends NotifierTestCase
{
use BinaryProviderTestTrait;
use CliBasedNotifierTestTrait;

private const BINARY = 'snoretoast';

public function testGetBinary()
{
$notifier = $this->getNotifier();

$this->assertSame(self::BINARY, $notifier->getBinary());
}

public function testGetPriority()
{
$notifier = $this->getNotifier();

$this->assertSame(Notifier::PRIORITY_MEDIUM, $notifier->getPriority());
}

protected function getNotifier(): Notifier
{
return new SnoreToastNotifier();
}

protected function getExpectedCommandLineForNotification(): string
{
return <<<'CLI'
            'snoretoast' '-m' 'I'\''m the notification body'
            CLI;
}

protected function getExpectedCommandLineForNotificationWithATitle(): string
{
return <<<'CLI'
            'snoretoast' '-m' 'I'\''m the notification body' '-t' 'I'\''m the notification title'
            CLI;
}

protected function getExpectedCommandLineForNotificationWithAnIcon(): string
{
$iconDir = $this->getIconDir();

return <<<CLI
            'snoretoast' '-m' 'I'\\''m the notification body' '-p' '{$iconDir}/image.gif'
            CLI;
}

protected function getExpectedCommandLineForNotificationWithAllOptions(): string
{
$iconDir = $this->getIconDir();

return <<<CLI
            'snoretoast' '-m' 'I'\\''m the notification body' '-t' 'I'\\''m the notification title' '-p' '{$iconDir}/image.gif'
            CLI;
}
}
