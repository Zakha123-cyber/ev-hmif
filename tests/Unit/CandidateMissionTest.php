<?php

use App\Models\Candidate;

uses(Tests\TestCase::class);

function candidateWithMission(?string $mission): Candidate
{
    $candidate = new Candidate;
    $candidate->mission = $mission;

    return $candidate;
}

it('splits the mission into sentences and drops empty items', function () {
    expect(candidateWithMission('Satu. Dua.  . Tiga')->missionItems())
        ->toBe(['Satu.', 'Dua.', 'Tiga.']);
});

it('keeps abbreviations such as S.Kom inside one item', function () {
    expect(candidateWithMission('Lulusan S.Kom. Dua')->missionItems())
        ->toBe(['Lulusan S.Kom.', 'Dua.']);
});

it('splits sentences separated by line breaks', function () {
    expect(candidateWithMission("Pertama.\n      Kedua.\n      ")->missionItems())
        ->toBe(['Pertama.', 'Kedua.']);
});

it('returns no items for an empty mission', function () {
    expect(candidateWithMission('')->missionItems())->toBe([]);
    expect(candidateWithMission(null)->missionItems())->toBe([]);
});
