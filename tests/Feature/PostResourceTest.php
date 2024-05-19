<?php

use App\Models\Post;
use App\Models\User;
use App\Setup\Resources\PostResource\Pages\CreatePost;
use App\Setup\Resources\PostResource\Pages\EditPost;
use App\Setup\Resources\PostResource\Pages\ListPosts;
use App\Setup\Resources\PostResource\RelationManagers\CommentsRelationManager;
use Filament\Pages\Auth\Login;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses()->group('filament-tests');

beforeEach(function () {
    actingAs(User::factory()->create());
});

it('can render the login page', function () {
    Auth::logout();

    livewire(Login::class)
        ->assertSuccessful();
})->group('auth', 'login', 'page');

it('can render the index page', function () {
    livewire(ListPosts::class)
        ->assertSuccessful();
})->group('index', 'page', 'resource');

it('can list records on the index page', function () {
    $records = Post::factory(3)->create();

    livewire(ListPosts::class)
        ->assertCanSeeTableRecords($records);
})->group('index', 'page', 'resource');

it('can list records on the index page with pagination', function () {
    $records = Post::factory(20)->create();

    livewire(ListPosts::class)
        ->call('gotoPage', 2)
        ->assertCanSeeTableRecords($records->skip(10));
})->group('index', 'page', 'resource');

it('cannot display trashed records by default', function () {
    $records = Post::factory(3)->create();

    $trashedRecords = Post::factory(6)
        ->trashed()
        ->create();

    livewire(ListPosts::class)
        ->assertCanSeeTableRecords($records)
        ->assertCanNotSeeTableRecords($trashedRecords)
        ->assertCountTableRecords(3);
})->group('index', 'page', 'resource');

it('has header actions on the index page', function (string $action) {
    livewire(ListPosts::class)
        ->assertActionExists($action);
})->with(['create'])->group('action', 'index', 'page', 'resource');

it('can render header actions on the index page', function (string $action) {
    livewire(ListPosts::class)
        ->assertActionVisible($action);
})->with(['create'])->group('action', 'index', 'page', 'resource');

it('has table action', function (string $action) {
    livewire(ListPosts::class)
        ->assertTableActionExists($action);
})->with(['edit', 'delete'])->group('action', 'index', 'page', 'resource', 'table');

it('can soft delete records', function () {
    $record = Post::factory()->create();

    livewire(ListPosts::class)
        ->callTableAction(DeleteAction::class, $record);

    $this->assertSoftDeleted($record);
})->group('action', 'index', 'page', 'resource', 'table');

it('can bulk restore records', function () {
    $records = Post::factory(3)->create();

    foreach ($records as $record) {
        $record->delete();

        $this->assertSoftDeleted($record);
    }

    livewire(ListPosts::class)
        ->filterTable('trashed', true)
        ->assertCanSeeTableRecords($records)
        ->callTableBulkAction(RestoreBulkAction::class, $records);

    expect(Post::onlyTrashed()->count())
        ->toBe(0);
})->group('bulk-action', 'index', 'page', 'resource', 'table');

it('can bulk force delete records', function () {
    $records = Post::factory(3)->create();

    foreach ($records as $record) {
        $record->delete();

        $this->assertSoftDeleted($record);
    }

    livewire(ListPosts::class)
        ->filterTable('trashed', true)
        ->assertCanSeeTableRecords($records)
        ->callTableBulkAction(ForceDeleteBulkAction::class, $records);

    foreach ($records as $record) {
        $this->assertModelMissing($record);
    }

    expect(Post::count())
        ->toBe(0);
})->group('bulk-action', 'index', 'page', 'resource', 'table');

it('can bulk delete records', function () {
    $records = Post::factory(3)->create();

    livewire(ListPosts::class)
        ->callTableBulkAction(DeleteBulkAction::class, $records);

    foreach ($records as $record) {
        $this->assertSoftDeleted($record);
    }

    expect(Post::find($records->pluck('id')))
        ->toBeEmpty();
})->group('bulk-action', 'index', 'page', 'resource', 'table');

it('has table bulk action', function (string $action) {
    livewire(ListPosts::class)
        ->assertTableBulkActionExists($action);
})->with(['delete', 'forceDelete', 'restore'])->group('bulk-action', 'index', 'page', 'resource', 'table');

it('has column', function (string $column) {
    livewire(ListPosts::class)
        ->assertTableColumnExists($column);
})->with(['id', 'title', 'comments_count', 'published_at', 'created_at', 'updated_at'])->group('column', 'index', 'page', 'resource', 'table');

it('can render column', function (string $column) {
    livewire(ListPosts::class)
        ->assertCanRenderTableColumn($column);
})->with(['id', 'title', 'comments_count', 'published_at', 'created_at'])->group('column', 'index', 'page', 'resource', 'table');

it('cannot render column', function (string $column) {
    livewire(ListPosts::class)
        ->assertCanNotRenderTableColumn($column);
})->with(['updated_at'])->group('column', 'index', 'page', 'resource', 'table');

it('can sort column', function (string $column) {
    Post::factory(3)->create();

    livewire(ListPosts::class)
        ->sortTable($column)
        ->assertCanSeeTableRecords(Post::orderBy($column)->get(), inOrder: true)
        ->sortTable($column, 'desc')
        ->assertCanSeeTableRecords(Post::orderBy($column, 'desc')->get(), inOrder: true);
})->with(['id', 'title', 'comments_count', 'published_at', 'created_at', 'updated_at'])->group('column', 'index', 'page', 'resource', 'table');

it('can search column', function (string $column) {
    $records = Post::factory(3)->create();

    $search = $records->first()->{$column};

    livewire(ListPosts::class)
        ->searchTable($search instanceof BackedEnum ? $search->value : $search)
        ->assertCanSeeTableRecords($records->where($column, $search))
        ->assertCanNotSeeTableRecords($records->where($column, '!=', $search));
})->with(['title'])->group('column', 'index', 'page', 'resource', 'table');

it('can reset table filters', function () {
    $records = Post::factory(3)->create();

    livewire(ListPosts::class)
        ->resetTableFilters()
        ->assertCanSeeTableRecords($records);
})->group('filter', 'index', 'page', 'resource', 'table');

it('can add a table filter', function () {
    //
})->group('filter', 'index', 'page', 'resource', 'table')->todo();

it('can remove a table filter', function () {
    //
})->group('filter', 'index', 'page', 'resource', 'table')->todo();

it('can render the create page', function () {
    livewire(CreatePost::class)
        ->assertSuccessful();
})->group('create', 'page', 'resource');

it('can render action on the create page', function () {
    //
})->group('action', 'create', 'page', 'resource')->todo();

it('can render widget on the create page', function () {
    //
})->group('create', 'page', 'resource', 'widget')->todo();

it('has a field on create form', function (string $field) {
    livewire(CreatePost::class)
        ->assertFormFieldExists($field);
})->with(['title', 'slug', 'content', 'published_at'])->group('create', 'field', 'form', 'page', 'resource');

it('can validate input on create form', function () {
    //
})->group('create', 'field', 'form', 'page', 'resource')->todo();

it('has create form', function () {
    livewire(CreatePost::class)
        ->assertFormExists();
})->group('create', 'form', 'page', 'resource');

it('can render form on the create page', function () {
    //
})->group('create', 'form', 'page', 'resource')->todo();

it('can validate create form input', function () {
    //
})->group('create', 'form', 'page', 'resource')->todo();

it('can render the edit page', function () {
    $record = Post::factory()->create();

    livewire(EditPost::class, ['record' => $record->getRouteKey()])
        ->assertSuccessful();
})->group('edit', 'page', 'resource');

it('can render action on the edit page', function () {
    //
})->group('action', 'edit', 'page', 'resource')->todo();

it('can render form on the edit page', function () {
    //
})->group('edit', 'form', 'page', 'resource')->todo();

it('can render widget on the edit page', function () {
    //
})->group('edit', 'page', 'resource', 'widget')->todo();

it('has a field on edit form', function (string $field) {
    $record = Post::factory()->create();

    livewire(EditPost::class, ['record' => $record->getRouteKey()])
        ->assertFormFieldExists($field);
})->with(['title', 'slug', 'content', 'published_at'])->group('edit', 'field', 'form', 'page', 'resource');

it('can validate input on edit form', function () {
    //
})->group('edit', 'field', 'form', 'page', 'resource')->todo();

it('has edit form', function () {
    $record = Post::factory()->create();

    livewire(EditPost::class, ['record' => $record->getRouteKey()])
        ->assertFormExists();
})->group('edit', 'form', 'page', 'resource');

it('can validate edit form input', function () {
    //
})->group('edit', 'form', 'page', 'resource')->todo();

it('can fill the form on the edit page', function () {
    //
})->group('edit', 'form', 'page', 'resource')->todo();

it('can render action on the view page', function () {
    //
})->group('action', 'page', 'resource', 'view')->todo();

it('can render form on the view page', function () {
    //
})->group('form', 'page', 'resource', 'view')->todo();

it('can render widget on the view page', function () {
    //
})->group('page', 'resource', 'view', 'widget')->todo();

it('can render the comments relation manager on the edit page', function () {
    $ownerRecord = Post::factory()
        ->hasComments(3)
        ->create();

    livewire(CommentsRelationManager::class, [
        'ownerRecord' => $ownerRecord,
        'pageClass' => EditPost::class,
    ])
        ->assertSuccessful();
})->group('edit', 'page', 'relation-manager', 'resource');

it('can list records on the comments relation manager on the edit page', function () {
    $ownerRecord = Post::factory()
        ->hasComments(3)
        ->create();

    livewire(CommentsRelationManager::class, [
        'ownerRecord' => $ownerRecord,
        'pageClass' => EditPost::class,
    ])
        ->assertCanSeeTableRecords($ownerRecord->comments);
})->group('edit', 'page', 'relation-manager', 'resource');

it('can list records on the comments relation manager on the edit page with pagination', function () {
    $ownerRecord = Post::factory()
        ->hasComments(20)
        ->create();

    livewire(CommentsRelationManager::class, [
        'ownerRecord' => $ownerRecord,
        'pageClass' => EditPost::class,
    ])
        ->call('gotoPage', 2)
        ->assertCanSeeTableRecords($ownerRecord->comments->skip(10));
})->group('edit', 'page', 'relation-manager', 'resource');

it('can render column on the comments relation manager on the edit page', function (string $column) {
    $ownerRecord = Post::factory()
        ->hasComments(3)
        ->create();

    livewire(CommentsRelationManager::class, [
        'ownerRecord' => $ownerRecord,
        'pageClass' => EditPost::class,
    ])
        ->assertCanRenderTableColumn($column);
})->with(['id', 'author.name', 'content', 'created_at'])->group('column', 'edit', 'page', 'relation-manager', 'resource', 'table');

it('cannot render column on the comments relation manager on the edit page', function (string $column) {
    $ownerRecord = Post::factory()
        ->hasComments(3)
        ->create();

    livewire(CommentsRelationManager::class, [
        'ownerRecord' => $ownerRecord,
        'pageClass' => EditPost::class,
    ])
        ->assertCanRenderTableColumn($column);
})->with(['updated_at'])->group('column', 'edit', 'page', 'relation-manager', 'resource', 'table');

it('has column on the comments relation manager on the edit page', function (string $column) {
    $ownerRecord = Post::factory()
        ->hasComments(3)
        ->create();

    livewire(CommentsRelationManager::class, [
        'ownerRecord' => $ownerRecord,
        'pageClass' => EditPost::class,
    ])
        ->assertTableColumnExists($column);
})->with(['id', 'author.name', 'content', 'created_at', 'updated_at'])->group('column', 'edit', 'page', 'relation-manager', 'resource', 'table');

it('can search column on the comments relation manager on the edit page', function (string $column) {
    $ownerRecord = Post::factory()
        ->hasComments(3)
        ->create();

    $search = $ownerRecord->{$column};

    livewire(CommentsRelationManager::class, [
        'ownerRecord' => $ownerRecord,
        'pageClass' => EditPost::class,
    ])
        ->searchTable($search instanceof BackedEnum ? $search->value : $search)
        ->assertCanSeeTableRecords($ownerRecord->comments->where($column, $search))
        ->assertCanNotSeeTableRecords($ownerRecord->comments->where($column, '!=', $search));
})->with(['author.name', 'content'])->group('column', 'edit', 'page', 'relation-manager', 'resource', 'table');

it('can sort column on the comments relation manager on the edit page', function (string $column) {
    $ownerRecord = Post::factory()
        ->hasComments(3)
        ->create();

    livewire(CommentsRelationManager::class, [
        'ownerRecord' => $ownerRecord,
        'pageClass' => EditPost::class,
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
})->with(['id', 'author.name', 'content', 'created_at', 'updated_at'])->group('column', 'edit', 'page', 'relation-manager', 'resource', 'table');
