<?php

use App\Models\Candidate;
use App\Models\Count;
use App\Models\ElectionSetting;
use App\Models\Participant;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    makeCandidate(1);
    makeCandidate(2);
});

function demoVoters(string $status)
{
    return User::role('Participant')->where('username', 'like', 'pemilih%')->where('status', $status)->orderBy('username')->get();
}

it('creates 12 voters who have not voted yet, each able to log in with demo1234', function () {
    $this->seed(DemoSeeder::class);

    $voters = demoVoters('not voted');
    expect($voters->pluck('username')->all())->toBe(array_map(fn ($n) => sprintf('pemilih%02d', $n), range(1, 12)));
    expect($voters->every(fn ($u) => Hash::check('demo1234', $u->password)))->toBeTrue();
    expect($voters->every(fn ($u) => $u->details->status === 'verified'))->toBeTrue();
    expect(Count::whereIn('user_id', $voters->pluck('id'))->exists())->toBeFalse();
});

it('creates 13 voters who already voted, 8 for pair 1 and 5 for pair 2', function () {
    $this->seed(DemoSeeder::class);

    $voted = demoVoters('voted');
    expect($voted->pluck('username')->all())->toBe(array_map(fn ($n) => sprintf('pemilih%02d', $n), range(13, 25)));

    $pair = Candidate::pluck('candidate_pair', 'id');
    $tally = Count::whereIn('user_id', $voted->pluck('id'))->get()->countBy(fn ($c) => $pair[$c->candidate_id]);
    expect($tally->all())->toBe([1 => 8, 2 => 5]);
});

it('opens the election from one hour ago until tomorrow', function () {
    $this->freezeSecond();
    $this->seed(DemoSeeder::class);

    $setting = ElectionSetting::current();
    expect($setting->status())->toBe(ElectionSetting::STATUS_OPEN);
    expect($setting->vote_start->equalTo(now()->subHour()))->toBeTrue();
    expect($setting->vote_end->equalTo(now()->addDay()))->toBeTrue();
});

it('creates admin.demo with the password written to the credentials file', function () {
    $this->seed(DemoSeeder::class);

    $admin = User::where('username', 'admin.demo')->firstOrFail();
    expect($admin->hasRole('Admin'))->toBeTrue();

    $file = Storage::disk('local')->get('demo/akun-uji.txt');
    preg_match('/admin\.demo\s+(\S+)/', $file, $m);
    expect(Hash::check($m[1], $admin->password))->toBeTrue();
    expect($file)->toContain('pemilih01')->toContain('demo1234');
});

it('does not create participants without accounts (import is tested separately)', function () {
    $this->seed(DemoSeeder::class);

    expect(Participant::where('status', 'not verified')->count())->toBe(0);
});

it('can run twice without duplicating accounts or votes', function () {
    $this->seed(DemoSeeder::class);
    $this->seed(DemoSeeder::class);

    expect(User::where('username', 'like', 'pemilih%')->count())->toBe(25);
    expect(Count::count())->toBe(13);
    expect(Participant::where('nim', 'like', '0000000%')->count())->toBe(26);
});

it('uses the candidates already in the database instead of adding new ones', function () {
    $this->seed(DemoSeeder::class);

    expect(Candidate::count())->toBe(2);
});

it('refuses to run in production', function () {
    $this->app->detectEnvironment(fn () => 'production');

    // --force melewati konfirmasi bawaan Laravel, jadi yang diuji adalah pengaman seeder ini.
    $this->artisan('db:seed', ['--class' => DemoSeeder::class, '--force' => true]);

    expect(User::where('username', 'like', 'pemilih%')->count())->toBe(0);
    expect(ElectionSetting::current()->status())->toBe(ElectionSetting::STATUS_NOT_SET);
});
