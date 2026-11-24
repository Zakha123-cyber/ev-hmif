<?php

use App\Models\Candidate;
use App\Models\Participant;
use Illuminate\Http\UploadedFile;

// PNG 1x1 piksel yang valid
const TINY_PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

beforeEach(function () {
    seedRoles();
    $this->admin = makeUserWithRole('Admin', 'admin1');
    $this->createdFiles = [];
});

afterEach(function () {
    foreach ($this->createdFiles as $path) {
        @unlink($path);
    }
});

function candidatePayload(array $overrides = []): array
{
    return array_merge([
        'candidate_pair' => 1,
        'leader_name' => 'Ketua',
        'vice_leader_name' => 'Wakil',
        'vision' => 'Visi',
        'mission' => 'Misi',
    ], $overrides);
}

function participantPayload(array $overrides = []): array
{
    return array_merge([
        'participant_name' => 'Peserta',
        'nim' => '2210001',
        'whatsapp_number' => '62812',
        'status' => 'not verified',
    ], $overrides);
}

// --- Poster kandidat ---

it('rejects a PHP file uploaded as a candidate poster', function () {
    $file = UploadedFile::fake()->createWithContent('shell.php', '<?php echo "pwned"; ?>');

    $this->actingAs($this->admin)
        ->post('/back-admin-logged/candidate/store', candidatePayload(['poster' => $file]))
        ->assertSessionHasErrors('poster');

    expect(Candidate::count())->toBe(0);
    expect(glob(public_path('assets/upload/kandidat/*shell.php')))->toBeEmpty();
});

it('rejects an SVG uploaded as a candidate poster', function () {
    $file = UploadedFile::fake()->createWithContent('poster.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');

    $this->actingAs($this->admin)
        ->post('/back-admin-logged/candidate/store', candidatePayload(['poster' => $file]))
        ->assertSessionHasErrors('poster');

    expect(Candidate::count())->toBe(0);
});

it('stores a valid candidate poster under a random name in the public upload folder', function () {
    $file = UploadedFile::fake()->createWithContent('Poster Paslon 1.png', base64_decode(TINY_PNG));

    $this->actingAs($this->admin)
        ->post('/back-admin-logged/candidate/store', candidatePayload(['poster' => $file]))
        ->assertSessionHasNoErrors();

    $poster = Candidate::first()->poster;
    $this->createdFiles[] = public_path('assets/upload/kandidat/' . $poster);

    expect($poster)->not->toContain('Poster Paslon');
    expect($poster)->toEndWith('.png');
    expect(file_exists(public_path('assets/upload/kandidat/' . $poster)))->toBeTrue();
});

it('rejects a PHP file when updating a candidate poster', function () {
    $candidate = makeCandidate(1);
    $file = UploadedFile::fake()->createWithContent('shell.php', '<?php echo "pwned"; ?>');

    $this->actingAs($this->admin)
        ->put("/back-admin-logged/candidate/update/{$candidate->id}", candidatePayload(['poster' => $file]))
        ->assertSessionHasErrors('poster');

    expect($candidate->fresh()->poster)->toBe('default.png');
});

// --- Foto peserta ---

it('rejects a PHP file uploaded as a participant photo', function () {
    $file = UploadedFile::fake()->createWithContent('shell.php', '<?php echo "pwned"; ?>');

    $this->actingAs($this->admin)
        ->post('/back-admin-logged/participant/store', participantPayload(['image' => $file]))
        ->assertSessionHasErrors('image');

    expect(Participant::where('nim', '2210001')->exists())->toBeFalse();
});

it('stores a valid participant photo under a random name in the public upload folder', function () {
    $file = UploadedFile::fake()->createWithContent('Foto Budi.png', base64_decode(TINY_PNG));

    $this->actingAs($this->admin)
        ->post('/back-admin-logged/participant/store', participantPayload(['image' => $file]))
        ->assertSessionHasNoErrors();

    $image = Participant::where('nim', '2210001')->value('image');
    $this->createdFiles[] = public_path('assets/upload/participant/' . $image);

    expect($image)->not->toContain('Foto Budi');
    expect(file_exists(public_path('assets/upload/participant/' . $image)))->toBeTrue();
});

it('rejects a PHP file when updating a participant photo', function () {
    $participant = Participant::create(participantPayload());
    $file = UploadedFile::fake()->createWithContent('shell.php', '<?php echo "pwned"; ?>');

    $this->actingAs($this->admin)
        ->put("/back-admin-logged/participant/update/{$participant->id}", participantPayload(['image' => $file]))
        ->assertSessionHasErrors('image');
});

// --- Lapisan kedua: server tidak mengeksekusi PHP di folder upload ---

it('ships an .htaccess that blocks script execution in the upload folders', function () {
    $htaccess = public_path('assets/upload/.htaccess');

    expect(file_exists($htaccess))->toBeTrue();
    expect(file_get_contents($htaccess))->toContain('php');
});
