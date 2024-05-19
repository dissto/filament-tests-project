<?php

use App\Filament\Resources\CommentResource\Pages\ListComments;
use App\Filament\Resources\CommentResource\Pages\CreateComment;
use App\Filament\Resources\CommentResource\Pages\EditComment;
use App\Filament\Resources\CommentResource\Pages\ViewComment;
use App\Filament\Resources\CommentResource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ReplicateAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use App\Models\Comment;
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
    livewire(ListComments::class)
        ->assertSuccessful();
})->group('index',  'page',  'resource');

it('can list records on the index page', function () {
    $records = Comment::factory(3)->create();

    livewire(ListComments::class)
        ->assertCanSeeTableRecords($records);
})->group('index',  'page',  'resource');

it('can list records on the index page with pagination', function () {
    $records = Comment::factory(20)->create();

    livewire(ListComments::class)
        ->call('gotoPage', 2)
        ->assertCanSeeTableRecords($records->skip(10));
})->group('index',  'page',  'resource');

it('cannot display trashed records by default', function () {
    $records = Comment::factory(3)->create();

    $trashedRecords = Comment::factory(6)
        ->trashed()
        ->create();

    livewire(ListComments::class)
        ->assertCanSeeTableRecords($records)
        ->assertCanNotSeeTableRecords($trashedRecords)
        ->assertCountTableRecords(3);
})->group('index',  'page',  'resource');

it('has header actions on the index page', function (string $action) {
    livewire(ListComments::class)
        ->assertActionExists($action);
})->with(['create'])->group('action',  'index',  'page',  'resource');

it('can render header actions on the index page', function (string $action) {
    livewire(ListComments::class)
        ->assertActionVisible($action);
})->with(['create'])->group('action',  'index',  'page',  'resource');

it('has table action', function (string $action) {
    livewire(ListComments::class)
        ->assertTableActionExists($action);
})->with(["go_to_post","edit","delete"])->group('action',  'index',  'page',  'resource',  'table');

it('can soft delete records', function () {
    $record = Comment::factory()->create();

    livewire(ListComments::class)
        ->callTableAction(DeleteAction::class, $record);

    $this->assertSoftDeleted($record);
})->group('action',  'index',  'page',  'resource',  'table');

it('can bulk restore records', function () {
    $records = Comment::factory(3)->create();

    foreach ($records as $record) {
        $record->delete();

        $this->assertSoftDeleted($record);
    }

    livewire(ListComments::class)
        ->filterTable('trashed', true)
        ->assertCanSeeTableRecords($records)
        ->callTableBulkAction(RestoreBulkAction::class, $records);

    expect(Comment::onlyTrashed()->count())
        ->toBe(0);
})->group('bulk-action',  'index',  'page',  'resource',  'table');

it('can bulk force delete records', function () {
    $records = Comment::factory(3)->create();

    foreach ($records as $record) {
        $record->delete();

        $this->assertSoftDeleted($record);
    }

    livewire(ListComments::class)
        ->filterTable('trashed', true)
        ->assertCanSeeTableRecords($records)
        ->callTableBulkAction(ForceDeleteBulkAction::class, $records);

    foreach ($records as $record) {
        $this->assertModelMissing($record);
    }

    expect(Comment::count())
        ->toBe(0);
})->group('bulk-action',  'index',  'page',  'resource',  'table');

it('can bulk delete records', function () {
    $records = Comment::factory(3)->create();

    livewire(ListComments::class)
        ->callTableBulkAction(DeleteBulkAction::class, $records);

    foreach ($records as $record) {
        $this->assertSoftDeleted($record);
    }

    expect(Comment::find($records->pluck('id')))
        ->toBeEmpty();
})->group('bulk-action',  'index',  'page',  'resource',  'table');

it('has table bulk action', function (string $action) {
    livewire(ListComments::class)
        ->assertTableBulkActionExists($action);
})->with(['delete', 'forceDelete', 'restore'])->group('bulk-action',  'index',  'page',  'resource',  'table');

it('has column', function (string $column) {
    livewire(ListComments::class)
        ->assertTableColumnExists($column);
})->with(['id', 'post.id', 'author.name', 'content', 'approved_at', 'created_at', 'updated_at'])->group('column',  'index',  'page',  'resource',  'table');

it('can render column', function (string $column) {
    livewire(ListComments::class)
        ->assertCanRenderTableColumn($column);
})->with(['id', 'author.name', 'content', 'approved_at', 'created_at'])->group('column',  'index',  'page',  'resource',  'table');

it('cannot render column', function (string $column) {
    livewire(ListComments::class)
        ->assertCanNotRenderTableColumn($column);
})->with(['post.id', 'updated_at'])->group('column',  'index',  'page',  'resource',  'table');

it('can sort column', function (string $column) {
    Comment::factory(3)->create();

    livewire(ListComments::class)
        ->sortTable($column)
        ->assertCanSeeTableRecords(Comment::orderBy($column)->get(), inOrder: true)
        ->sortTable($column, 'desc')
        ->assertCanSeeTableRecords(Comment::orderBy($column, 'desc')->get(), inOrder: true);
})->with(['id', 'post.id', 'author.name', 'content', 'approved_at', 'created_at', 'updated_at'])->group('column',  'index',  'page',  'resource',  'table');

it('can search column', function (string $column) {
    $records = Comment::factory(3)->create();

    $search = $records->first()->{$column};

    livewire(ListComments::class)
        ->searchTable($search instanceof BackedEnum ? $search->value : $search)
        ->assertCanSeeTableRecords($records->where($column, $search))
        ->assertCanNotSeeTableRecords($records->where($column, '!=', $search));
})->with(['post.id', 'author.name', 'content'])->group('column',  'index',  'page',  'resource',  'table');

it('can reset table filters', function () {
    $records = Comment::factory(3)->create();

    livewire(ListComments::class)
        ->resetTableFilters()
        ->assertCanSeeTableRecords($records);
})->group('filter',  'index',  'page',  'resource',  'table');

it('can add a table filter', function () {
    //
})->group('filter',  'index',  'page',  'resource',  'table')->todo();

it('can remove a table filter', function () {
    //
})->group('filter',  'index',  'page',  'resource',  'table')->todo();

it('can render the create page', function () {
    livewire(CreateComment::class)
        ->assertSuccessful();
})->group('create',  'page',  'resource');

it('can render action on the create page', function () {
    //
})->group('action',  'create',  'page',  'resource')->todo();

it('can render widget on the create page', function () {
    //
})->group('create',  'page',  'resource',  'widget')->todo();

it('has a field on create form', function (string $field) {
    livewire(CreateComment::class)
        ->assertFormFieldExists($field);
})->with(['user_id', 'content', 'approved_at'])->group('create',  'field',  'form',  'page',  'resource');

it('can validate input on create form', function () {
    //
})->group('create',  'field',  'form',  'page',  'resource')->todo();

it('has create form', function () {
    livewire(CreateComment::class)
        ->assertFormExists();
})->group('create',  'form',  'page',  'resource');

it('can render form on the create page', function () {
    //
})->group('create',  'form',  'page',  'resource')->todo();

it('can validate create form input', function () {
    //
})->group('create',  'form',  'page',  'resource')->todo();

it('can render the edit page', function () {
    $record = Comment::factory()->create();

    livewire(EditComment::class, ['record' => $record->getRouteKey()])
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
    $record = Comment::factory()->create();

    livewire(EditComment::class, ['record' => $record->getRouteKey()])
        ->assertFormFieldExists($field);
})->with(['user_id', 'content', 'approved_at'])->group('edit',  'field',  'form',  'page',  'resource');

it('can validate input on edit form', function () {
    //
})->group('edit',  'field',  'form',  'page',  'resource')->todo();

it('has edit form', function () {
    $record = Comment::factory()->create();

    livewire(EditComment::class, ['record' => $record->getRouteKey()])
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

