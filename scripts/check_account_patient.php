<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
Illuminate\Support\Facades\DB::purge('sqlite');
$schema = Illuminate\Support\Facades\Schema::connection('sqlite');
$schema->create('users', function ($table) {
    $table->increments('user_id');
    $table->string('full_name');
    $table->string('email');
    $table->string('phone')->nullable();
    $table->timestamps();
});
$schema->create('patients', function ($table) {
    $table->increments('patient_id');
    $table->integer('account_user_id');
    $table->string('patient_code')->unique();
    $table->string('relationship_to_account');
    $table->string('first_name');
    $table->string('last_name');
    $table->string('sex');
    $table->string('email')->nullable();
    $table->string('phone')->nullable();
    $table->boolean('active');
    $table->integer('created_by');
    $table->timestamps();
});
function check(bool $ok, string $message): void { if (!$ok) { throw new RuntimeException($message); } }
$user = App\Models\User::create(['full_name'=>'Asha Perera','email'=>'test@example.test']);
$service = app(App\Services\AccountPatientService::class);
$self = $service->ensure($user);
check($self->full_name === 'Asha Perera' && $self->relationship_to_account === 'Self', 'Self profile missing');
check($self->sex === 'UNKNOWN', 'Unprovided demographics were invented');
check($service->ensure($user)->patient_id === $self->patient_id, 'Repeated visits duplicate profiles');
$family = $self->replicate();
$family->fill(['patient_code'=>'FAMILY','relationship_to_account'=>'Son','first_name'=>'Child'])->save();
check($service->ensure($user)->patient_id === $self->patient_id, 'Family became default');
check($user->patients()->where('active',true)->count() === 2, 'Family profile missing');
$single = App\Models\User::create(['full_name'=>'Saman','email'=>'single@example.test']);
check($service->ensure($single)->full_name === 'Saman', 'Single-word name failed');
echo "PASS: self profile creation, reuse, family profiles, and single-word names. In-memory database only.".PHP_EOL;
