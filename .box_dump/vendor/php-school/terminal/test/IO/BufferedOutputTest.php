<?php

namespace PhpSchool\TerminalTest\IO;

use PhpSchool\Terminal\IO\BufferedOutput;
use PHPUnit\Framework\TestCase;




class BufferedOutputTest extends TestCase
{
public function testFetch() : void
{
$output = new BufferedOutput;
$output->write('one');

static::assertEquals('one', $output->fetch());
}

public function testFetchWithMultipleWrites() : void
{
$output = new BufferedOutput;
$output->write('one');
$output->write('two');

static::assertEquals('onetwo', $output->fetch());
}

public function testFetchCleansBufferByDefault() : void
{
$output = new BufferedOutput;
$output->write('one');

static::assertEquals('one', $output->fetch());
static::assertEquals('', $output->fetch());
}

public function testFetchWithoutCleaning() : void
{
$output = new BufferedOutput;
$output->write('one');

static::assertEquals('one', $output->fetch(false));

$output->write('two');

static::assertEquals('onetwo', $output->fetch(false));
}

public function testToString() : void
{
$output = new BufferedOutput;
$output->write('one');

static::assertEquals('one', (string) $output);
}
}
