<?php










namespace Symfony\Component\HttpKernel;






interface RebootableInterface
{








public function reboot(?string $warmupDir): void;
}
