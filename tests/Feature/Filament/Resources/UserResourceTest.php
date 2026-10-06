<?php

use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use Database\Seeders\ShieldSeeder;
use Filament\Facades\Filament;
use Filament\Tables\Actions\EditAction;
use Spatie\Permission\Models\Role;

use function Pest\Livewire\livewire;

// UserResource lists sign-in accounts and edits their roles (accounts are created with employees or on hire).
beforeEach(function () {
    $this->seed(ShieldSeeder::class);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    auth()->user()->assignRole('Admin');
});

it('can render the index page', function () {
    livewire(ListUsers::class)->assertSuccessful();
});

it('has column', function (string $column) {
    livewire(ListUsers::class)->assertTableColumnExists($column);
})->with(['name', 'email', 'roles.name', 'created_at', 'updated_at']);

it('can sort column', function (string $column) {
    $records = User::factory(5)->create()->push(auth()->user());
    livewire(ListUsers::class)
        ->loadTable()
        ->sortTable($column)
        ->assertCanSeeTableRecords($records->sortBy($column), inOrder: true)
        ->sortTable($column, 'desc')
        ->assertCanSeeTableRecords($records->sortByDesc($column), inOrder: true);
})->with(['name', 'email']);

it('can search column', function (string $column) {
    $records = User::factory(5)->create();
    $value = $records->first()->{$column};
    livewire(ListUsers::class)
        ->loadTable()
        ->searchTable($value)
        ->assertCanSeeTableRecords($records->where($column, $value))
        ->assertCanNotSeeTableRecords($records->where($column, '!=', $value));
})->with(['name', 'email']);

it('can change the roles of a user', function () {
    $record = User::factory()->create();
    $role = Role::findByName('Finance Manager');

    livewire(ListUsers::class)
        ->loadTable()
        ->callTableAction(EditAction::class, $record, data: ['roles' => [$role->id]])
        ->assertHasNoTableActionErrors();

    expect($record->fresh()->hasRole('Finance Manager'))->toBeTrue();
});
