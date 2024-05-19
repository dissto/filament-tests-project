<?php

use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ViewUser;
use App\Filament\Resources\UserResource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ReplicateAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

uses()->group('filament-tests');

beforeEach(function () {
    actingAs(User::factory()->create());
});

it('can render the login page', function () {
        Auth::logout();

        livewire(\Filament\Pages\Auth\Login::class)
            ->assertSuccessful();
})->group('auth',  'login',  'page');

it('can render the index page', function () {
    livewire(ListUsers::class)
        ->assertSuccessful();
})->group('index',  'page',  'resource');

it('can list records on the index page', function () {
    $records = User::factory(3)->create();

    livewire(ListUsers::class)
        ->assertCanSeeTableRecords($records);
})->group('index',  'page',  'resource');

it('can list records on the index page with pagination', function () {
    $records = User::factory(20)->create();

    livewire(ListUsers::class)
        ->call('gotoPage', 2)
        ->assertCanSeeTableRecords($records->skip(10));
})->group('index',  'page',  'resource');

it('has header actions on the index page', function (string $action) {
    livewire(ListUsers::class)
        ->assertActionExists($action);
})->with(['create'])->group('action',  'index',  'page',  'resource');

it('can render header actions on the index page', function (string $action) {
    livewire(ListUsers::class)
        ->assertActionVisible($action);
})->with(['create'])->group('action',  'index',  'page',  'resource');

it('has table action', function (string $action) {
    livewire(ListUsers::class)
        ->assertTableActionExists($action);
})->with(["edit"])->group('action',  'index',  'page',  'resource',  'table');

it('has table bulk action', function (string $action) {
    livewire(ListUsers::class)
        ->assertTableBulkActionExists($action);
})->with(['delete'])->group('bulk-action',  'index',  'page',  'resource',  'table');

it('has column', function (string $column) {
    livewire(ListUsers::class)
        ->assertTableColumnExists($column);
})->with(['id', 'name', 'email', 'email_verified_at', 'created_at', 'updated_at'])->group('column',  'index',  'page',  'resource',  'table');

it('can render column', function (string $column) {
    livewire(ListUsers::class)
        ->assertCanRenderTableColumn($column);
})->with(['id', 'name', 'email', 'email_verified_at', 'created_at'])->group('column',  'index',  'page',  'resource',  'table');

it('cannot render column', function (string $column) {
    livewire(ListUsers::class)
        ->assertCanNotRenderTableColumn($column);
})->with(['updated_at'])->group('column',  'index',  'page',  'resource',  'table');

it('can sort column', function (string $column) {
    User::factory(3)->create();

    livewire(ListUsers::class)
        ->sortTable($column)
        ->assertCanSeeTableRecords(User::orderBy($column)->get(), inOrder: true)
        ->sortTable($column, 'desc')
        ->assertCanSeeTableRecords(User::orderBy($column, 'desc')->get(), inOrder: true);
})->with(['id', 'name', 'email', 'email_verified_at', 'created_at', 'updated_at'])->group('column',  'index',  'page',  'resource',  'table');

it('can search column', function (string $column) {
    $records = User::factory(3)->create();

    $search = $records->first()->{$column};

    livewire(ListUsers::class)
        ->searchTable($search instanceof BackedEnum ? $search->value : $search)
        ->assertCanSeeTableRecords($records->where($column, $search))
        ->assertCanNotSeeTableRecords($records->where($column, '!=', $search));
})->with(['name', 'email'])->group('column',  'index',  'page',  'resource',  'table');

it('can render the create page', function () {
    livewire(CreateUser::class)
        ->assertSuccessful();
})->group('create',  'page',  'resource');

it('can render action on the create page', function () {
    //
})->group('action',  'create',  'page',  'resource')->todo();

it('can render widget on the create page', function () {
    //
})->group('create',  'page',  'resource',  'widget')->todo();

it('has a field on create form', function (string $field) {
    livewire(CreateUser::class)
        ->assertFormFieldExists($field);
})->with(['name', 'email', 'password', 'email_verified_at'])->group('create',  'field',  'form',  'page',  'resource');

it('can validate input on create form', function () {
    //
})->group('create',  'field',  'form',  'page',  'resource')->todo();

it('has create form', function () {
    livewire(CreateUser::class)
        ->assertFormExists();
})->group('create',  'form',  'page',  'resource');

it('can render form on the create page', function () {
    //
})->group('create',  'form',  'page',  'resource')->todo();

it('can validate create form input', function () {
    //
})->group('create',  'form',  'page',  'resource')->todo();

it('can render the edit page', function () {
    $record = User::factory()->create();

    livewire(EditUser::class, ['record' => $record->getRouteKey()])
        ->assertSuccessful();
})->group('edit',  'page',  'resource');

it('can render action on the edit page', function () {
    //
})->group('action',  'edit',  'page',  'resource')->todo();

it('can render form on the edit page', function () {
    //
})->group('edit',  'form',  'page',  'resource')->todo();

it('can render widget on the edit page', function () {
    //
})->group('edit',  'page',  'resource',  'widget')->todo();

it('has a field on edit form', function (string $field) {
    $record = User::factory()->create();

    livewire(EditUser::class, ['record' => $record->getRouteKey()])
        ->assertFormFieldExists($field);
})->with(['name', 'email', 'password', 'email_verified_at'])->group('edit',  'field',  'form',  'page',  'resource');

it('can validate input on edit form', function () {
    //
})->group('edit',  'field',  'form',  'page',  'resource')->todo();

it('has edit form', function () {
    $record = User::factory()->create();

    livewire(EditUser::class, ['record' => $record->getRouteKey()])
        ->assertFormExists();
})->group('edit',  'form',  'page',  'resource');

it('can validate edit form input', function () {
    //
})->group('edit',  'form',  'page',  'resource')->todo();

it('can fill the form on the edit page', function () {
    //
})->group('edit',  'form',  'page',  'resource')->todo();

it('can render action on the view page', function () {
    //
})->group('action',  'page',  'resource',  'view')->todo();

it('can render form on the view page', function () {
    //
})->group('form',  'page',  'resource',  'view')->todo();

it('can render widget on the view page', function () {
    //
})->group('page',  'resource',  'view',  'widget')->todo();

it('can render the comments relation manager on the edit page', function () {
    $ownerRecord = User::factory()
        ->hasComments(3)
        ->create();

    livewire(App\Filament\Resources\UserResource\RelationManagers\CommentsRelationManager::class, [
        'ownerRecord' => $ownerRecord,
        'pageClass' => EditUser::class
    ])
        ->assertSuccessful();
})->group('edit',  'page',  'relation-manager',  'resource');

it('can list records on the comments relation manager on the edit page', function () {
    $ownerRecord = User::factory()
        ->hasComments(3)
        ->create();

    livewire(App\Filament\Resources\UserResource\RelationManagers\CommentsRelationManager::class, [
        'ownerRecord' => $ownerRecord,
        'pageClass' => EditUser::class
    ])
        ->assertCanSeeTableRecords($ownerRecord->comments);
})->group('edit',  'page',  'relation-manager',  'resource');

it('can list records on the comments relation manager on the edit page with pagination', function () {
    $ownerRecord = User::factory()
        ->hasComments(20)
        ->create();

    livewire(App\Filament\Resources\UserResource\RelationManagers\CommentsRelationManager::class, [
        'ownerRecord' => $ownerRecord,
        'pageClass' => EditUser::class
    ])
        ->call('gotoPage', 2)
        ->assertCanSeeTableRecords($ownerRecord->comments->skip(10));
})->group('edit',  'page',  'relation-manager',  'resource');

it('can render column on the comments relation manager on the edit page', function (string $column) {
    $ownerRecord = User::factory()
        ->hasComments(3)
        ->create();

    livewire(App\Filament\Resources\UserResource\RelationManagers\CommentsRelationManager::class, [
        'ownerRecord' => $ownerRecord,
        'pageClass' => EditUser::class
        ])
        ->assertCanRenderTableColumn($column);
})->with(['id', 'author.name', 'content', 'approved_at', 'created_at'])->group('column',  'edit',  'page',  'relation-manager',  'resource',  'table');

it('cannot render column on the comments relation manager on the edit page', function (string $column) {
    $ownerRecord = User::factory()
        ->hasComments(3)
        ->create();

    livewire(App\Filament\Resources\UserResource\RelationManagers\CommentsRelationManager::class, [
        'ownerRecord' => $ownerRecord,
        'pageClass' => EditUser::class
        ])
        ->assertCanRenderTableColumn($column);
})->with(['updated_at'])->group('column',  'edit',  'page',  'relation-manager',  'resource',  'table');

it('has column on the comments relation manager on the edit page', function (string $column) {
    $ownerRecord = User::factory()
        ->hasComments(3)
        ->create();

    livewire(App\Filament\Resources\UserResource\RelationManagers\CommentsRelationManager::class, [
        'ownerRecord' => $ownerRecord,
        'pageClass' => EditUser::class
        ])
        ->assertTableColumnExists($column);
})->with(['id', 'author.name', 'content', 'approved_at', 'created_at', 'updated_at'])->group('column',  'edit',  'page',  'relation-manager',  'resource',  'table');

it('can search column on the comments relation manager on the edit page', function (string $column) {
    $ownerRecord = User::factory()
        ->hasComments(3)
        ->create();

    $search = $ownerRecord->{$column};

    livewire(App\Filament\Resources\UserResource\RelationManagers\CommentsRelationManager::class, [
        'ownerRecord' => $ownerRecord,
        'pageClass' => EditUser::class
    ])
    ->searchTable($search instanceof BackedEnum ? $search->value : $search)
    ->assertCanSeeTableRecords($ownerRecord->comments->where($column, $search))
    ->assertCanNotSeeTableRecords($ownerRecord->comments->where($column, '!=', $search));
})->with(['author.name', 'content'])->group('column',  'edit',  'page',  'relation-manager',  'resource',  'table');

it('can sort column on the comments relation manager on the edit page', function (string $column) {
    $ownerRecord = User::factory()
        ->hasComments(3)
        ->create();

    livewire(App\Filament\Resources\UserResource\RelationManagers\CommentsRelationManager::class, [
        'ownerRecord' => $ownerRecord,
        'pageClass' => EditUser::class
    ])
    ->sortTable($column)
    ->assertCanSeeTableRecords(
        $ownerRecord->comments()->orderBy($column)->get(),
        inOrder: true
    )
    ->sortTable($column, 'desc')
    ->assertCanSeeTableRecords(
        $ownerRecord->comments()->orderBy($column, 'desc')->get(),
        inOrder: true
    );
})->with(['id', 'author.name', 'content', 'approved_at', 'created_at', 'updated_at'])->group('column',  'edit',  'page',  'relation-manager',  'resource',  'table');

it('can render the posts relation manager on the edit page', function () {
    $ownerRecord = User::factory()
        ->hasPosts(3)
        ->create();

    livewire(App\Filament\Resources\UserResource\RelationManagers\PostsRelationManager::class, [
        'ownerRecord' => $ownerRecord,
        'pageClass' => EditUser::class
    ])
        ->assertSuccessful();
})->group('edit',  'page',  'relation-manager',  'resource');

it('can list records on the posts relation manager on the edit page', function () {
    $ownerRecord = User::factory()
        ->hasPosts(3)
        ->create();

    livewire(App\Filament\Resources\UserResource\RelationManagers\PostsRelationManager::class, [
        'ownerRecord' => $ownerRecord,
        'pageClass' => EditUser::class
    ])
        ->assertCanSeeTableRecords($ownerRecord->posts);
})->group('edit',  'page',  'relation-manager',  'resource');

it('can list records on the posts relation manager on the edit page with pagination', function () {
    $ownerRecord = User::factory()
        ->hasPosts(20)
        ->create();

    livewire(App\Filament\Resources\UserResource\RelationManagers\PostsRelationManager::class, [
        'ownerRecord' => $ownerRecord,
        'pageClass' => EditUser::class
    ])
        ->call('gotoPage', 2)
        ->assertCanSeeTableRecords($ownerRecord->posts->skip(10));
})->group('edit',  'page',  'relation-manager',  'resource');

it('can render column on the posts relation manager on the edit page', function (string $column) {
    $ownerRecord = User::factory()
        ->hasPosts(3)
        ->create();

    livewire(App\Filament\Resources\UserResource\RelationManagers\PostsRelationManager::class, [
        'ownerRecord' => $ownerRecord,
        'pageClass' => EditUser::class
        ])
        ->assertCanRenderTableColumn($column);
})->with(['id', 'title', 'published_at', 'created_at'])->group('column',  'edit',  'page',  'relation-manager',  'resource',  'table');

it('cannot render column on the posts relation manager on the edit page', function (string $column) {
    $ownerRecord = User::factory()
        ->hasPosts(3)
        ->create();

    livewire(App\Filament\Resources\UserResource\RelationManagers\PostsRelationManager::class, [
        'ownerRecord' => $ownerRecord,
        'pageClass' => EditUser::class
        ])
        ->assertCanRenderTableColumn($column);
})->with(['updated_at'])->group('column',  'edit',  'page',  'relation-manager',  'resource',  'table');

it('has column on the posts relation manager on the edit page', function (string $column) {
    $ownerRecord = User::factory()
        ->hasPosts(3)
        ->create();

    livewire(App\Filament\Resources\UserResource\RelationManagers\PostsRelationManager::class, [
        'ownerRecord' => $ownerRecord,
        'pageClass' => EditUser::class
        ])
        ->assertTableColumnExists($column);
})->with(['id', 'title', 'published_at', 'created_at', 'updated_at'])->group('column',  'edit',  'page',  'relation-manager',  'resource',  'table');

it('can search column on the posts relation manager on the edit page', function (string $column) {
    $ownerRecord = User::factory()
        ->hasPosts(3)
        ->create();

    $search = $ownerRecord->{$column};

    livewire(App\Filament\Resources\UserResource\RelationManagers\PostsRelationManager::class, [
        'ownerRecord' => $ownerRecord,
        'pageClass' => EditUser::class
    ])
    ->searchTable($search instanceof BackedEnum ? $search->value : $search)
    ->assertCanSeeTableRecords($ownerRecord->posts->where($column, $search))
    ->assertCanNotSeeTableRecords($ownerRecord->posts->where($column, '!=', $search));
})->with(['title'])->group('column',  'edit',  'page',  'relation-manager',  'resource',  'table');

it('can sort column on the posts relation manager on the edit page', function (string $column) {
    $ownerRecord = User::factory()
        ->hasPosts(3)
        ->create();

    livewire(App\Filament\Resources\UserResource\RelationManagers\PostsRelationManager::class, [
        'ownerRecord' => $ownerRecord,
        'pageClass' => EditUser::class
    ])
    ->sortTable($column)
    ->assertCanSeeTableRecords(
        $ownerRecord->posts()->orderBy($column)->get(),
        inOrder: true
    )
    ->sortTable($column, 'desc')
    ->assertCanSeeTableRecords(
        $ownerRecord->posts()->orderBy($column, 'desc')->get(),
        inOrder: true
    );
})->with(['id', 'title', 'published_at', 'created_at', 'updated_at'])->group('column',  'edit',  'page',  'relation-manager',  'resource',  'table');

