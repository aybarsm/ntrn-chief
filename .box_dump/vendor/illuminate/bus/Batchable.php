<?php

namespace Illuminate\Bus;

use Carbon\CarbonImmutable;
use Illuminate\Container\Container;
use Illuminate\Support\Str;
use Illuminate\Support\Testing\Fakes\BatchFake;

trait Batchable
{





public $batchId;






private $fakeBatch;






public function batch()
{
if ($this->fakeBatch) {
return $this->fakeBatch;
}

if ($this->batchId) {
return Container::getInstance()->make(BatchRepository::class)?->find($this->batchId);
}
}






public function batching()
{
$batch = $this->batch();

return $batch && ! $batch->cancelled();
}







public function withBatchId(string $batchId)
{
$this->batchId = $batchId;

return $this;
}
















public function withFakeBatch(string $id = '',
string $name = '',
int $totalJobs = 0,
int $pendingJobs = 0,
int $failedJobs = 0,
array $failedJobIds = [],
array $options = [],
?CarbonImmutable $createdAt = null,
?CarbonImmutable $cancelledAt = null,
?CarbonImmutable $finishedAt = null)
{
$this->fakeBatch = new BatchFake(
empty($id) ? (string) Str::uuid() : $id,
$name,
$totalJobs,
$pendingJobs,
$failedJobs,
$failedJobIds,
$options,
$createdAt ?? CarbonImmutable::now(),
$cancelledAt,
$finishedAt,
);

return [$this, $this->fakeBatch];
}
}
