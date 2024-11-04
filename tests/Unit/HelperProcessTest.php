<?php

use App\Services\Helper;

describe('Helper Process', function () {
    $expected['args'] = [
        '--working-dir=path/to/working-dir',
        '--config=path/to/config.json',
        '--debug',
    ];

    it('process command args when args is list', function () use ($expected) {
        $args = ['working-dir=path/to/working-dir', '--config=path/to/config.json', '-debug'];
        expect(Helper::buildProcessArgs([], $args))->toBe($expected['args']);
    });

    it('process command args when args is assoc', function () use ($expected) {
        $args = ['working-dir' => 'path/to/working-dir', '--config' => 'path/to/config.json', '-debug'];
        expect(Helper::buildProcessArgs([], $args))->toBe($expected['args']);
    });
});
