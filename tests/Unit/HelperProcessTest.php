<?php

use App\Services\Helper;

describe('Helper Process', function () {
    $expected['defaults'] = [
        '--working-dir=path/to/default-working-dir',
    ];
    $expected['args'] = [
        '--working-dir=path/to/working-dir',
        '--config=path/to/config.json',
        '--debug',
    ];
    $expected['full'] = $expected['defaults'] + $expected['args'];

    it('process command args when args is list', function () use ($expected) {
        $args = ['working-dir=path/to/working-dir', '--config=path/to/config.json', '-debug'];
        expect(Helper::buildProcessArgs($args))->toBe($expected['args']);
    });

    it('process command args when args is assoc', function () use ($expected) {
        $args = ['working-dir' => 'path/to/working-dir', '--config' => 'path/to/config.json', '-debug'];
        expect(Helper::buildProcessArgs($args))->toBe($expected['args']);
    });

    it('process command args with defaults', function () use ($expected) {
        $defaults = ['-working-dir=path/to/default-working-dir'];
        $args = ['working-dir' => 'path/to/working-dir', '--config' => 'path/to/config.json', '-debug'];
        expect(Helper::buildProcessArgs($args, $defaults))->toBe($expected['full']);
    });
});
