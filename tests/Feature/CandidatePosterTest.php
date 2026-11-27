<?php

use App\Models\Candidate;

const TEST_POSTER = 'zz-test-poster.png';

afterEach(function () {
    @unlink(public_path('assets/upload/kandidat/' . TEST_POSTER));
});

function candidateWithPoster(?string $poster): Candidate
{
    return (new Candidate())->forceFill(['candidate_pair' => 1, 'poster' => $poster]);
}

it('reads the real size of an existing poster', function () {
    $image = imagecreatetruecolor(3, 5);
    imagepng($image, public_path('assets/upload/kandidat/' . TEST_POSTER));

    $candidate = candidateWithPoster(TEST_POSTER);

    expect($candidate->hasPoster())->toBeTrue();
    expect($candidate->posterSize())->toBe([3, 5]);
    expect($candidate->posterUrl())->toBe(asset('assets/upload/kandidat/' . TEST_POSTER));
});

it('falls back to 600x800 when the poster file is missing', function () {
    $candidate = candidateWithPoster('tidak-ada.png');

    expect($candidate->hasPoster())->toBeFalse();
    expect($candidate->posterSize())->toBe([600, 800]);
});

it('has no poster when the column is empty', function () {
    expect(candidateWithPoster(null)->hasPoster())->toBeFalse();
});
