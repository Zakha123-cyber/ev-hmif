<?php

dataset('cogito files', ['salam.webp', 'salute.webp', 'salam-still.webp', 'salute-still.webp']);

it('ships the transparent COGITO image', function (string $file) {
    $path = public_path('frontend/images/cogito/' . $file);

    expect(is_file($path))->toBeTrue();
    $info = getimagesize($path);
    expect([$info[0], $info[1]])->toBe([278, 385]);
    expect($info['mime'])->toBe('image/webp');
})->with('cogito files');

it('keeps each animation light enough for phones', function (string $file) {
    expect(filesize(public_path('frontend/images/cogito/' . $file)))->toBeLessThan(250000);
})->with(['salam.webp', 'salute.webp']);
