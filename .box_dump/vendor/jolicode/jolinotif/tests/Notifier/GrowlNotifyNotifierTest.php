<?php










namespace Joli\JoliNotif\tests\Notifier;

use Joli\JoliNotif\Notifier;
use Joli\JoliNotif\Notifier\GrowlNotifyNotifier;

/**
@group
*/
class GrowlNotifyNotifierTest extends NotifierTestCase
{
use CliBasedNotifierTestTrait;

private const BINARY = 'growlnotify';

public function testGetBinary()
{
$notifier = $this->getNotifier();

$this->assertSame(self::BINARY, $notifier->getBinary());
}

public function testGetPriority()
{
$notifier = $this->getNotifier();

$this->assertSame(Notifier::PRIORITY_HIGH, $notifier->getPriority());
}

protected function getNotifier(): Notifier
{
return new GrowlNotifyNotifier();
}

protected function getExpectedCommandLineForNotification(): string
{
return <<<'CLI'
            'growlnotify' '--message' 'I'\''m the notification body'
            CLI;
}

protected function getExpectedCommandLineForNotificationWithATitle(): string
{
return <<<'CLI'
            'growlnotify' '--message' 'I'\''m the notification body' '--title' 'I'\''m the notification title'
            CLI;
}

protected function getExpectedCommandLineForNotificationWithAnIcon(): string
{
$iconDir = $this->getIconDir();

return <<<CLI
            'growlnotify' '--message' 'I'\\''m the notification body' '--image' '{$iconDir}/image.gif'
            CLI;
}

protected function getExpectedCommandLineForNotificationWithAllOptions(): string
{
$iconDir = $this->getIconDir();

return <<<CLI
            'growlnotify' '--message' 'I'\\''m the notification body' '--title' 'I'\\''m the notification title' '--image' '{$iconDir}/image.gif'
            CLI;
}
}
