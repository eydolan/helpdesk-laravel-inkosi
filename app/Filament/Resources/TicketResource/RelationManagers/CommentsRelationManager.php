<?php

namespace App\Filament\Resources\TicketResource\RelationManagers;

use App\Models\TicketStatus;
use App\Settings\GeneralSettings;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Livewire\Component as Livewire;

class CommentsRelationManager extends RelationManager
{
    protected $listeners = ['refreshComments' => '$refresh'];

    protected static string $relationship = 'comments';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Comments');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    protected function canCreate(): bool
    {
        return Gate::allows('create', [$this->getTable()->getModel(), $this->ownerRecord]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    Forms\Components\Select::make('ticket_statuses_id')
                        ->label(__('Change Status'))
                        ->options(TicketStatus::all()->pluck('name', 'id'))
                        ->searchable()
                        ->default(fn (Livewire $livewire) => $livewire->ownerRecord->ticket_statuses_id)
                        ->hiddenOn('edit')
                        ->hidden(
                            fn () => ! auth()
                                ->user()
                                ->hasAnyRole(['Super Admin', 'Admin Unit', 'Staff Unit']),
                        ),

                    Forms\Components\RichEditor::make('comment')
                        ->translateLabel()
                        ->required(),

                    Forms\Components\FileUpload::make('attachments')
                        ->translateLabel()
                        ->directory('comment-attachments/'.date('m-y'))
                        ->maxSize(2000)
                        ->downloadable(),
                ]),
            ]);
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getTableQuery()->with('user'); // Eager load user to avoid N+1 queries
    }

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel(__('Comment'))
            ->columns([
                Stack::make([
                    Split::make([
                        TextColumn::make('user.name')
                            ->translateLabel()
                            ->weight('bold')
                            ->grow(false),
                        TextColumn::make('created_at')
                            ->translateLabel()
                            ->dateTime(app(GeneralSettings::class)->datetime_format)
                            ->color('secondary'),
                    ]),
                    TextColumn::make('comment')
                        ->wrap()
                        ->html(),
                ]),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\Action::make('refresh')
                    ->translateLabel()
                    ->outlined()
                    ->dispatchSelf('refreshComments'),

                Tables\Actions\CreateAction::make()
                    ->label(__('Add Comment'))
                    ->mutateFormDataUsing(function (array $data, Livewire $livewire): array {
                        $data['user_id'] = auth()->id();

                        return $data;
                    })
                    ->before(function (array $data, Livewire $livewire) {
                        $ticket = $livewire->ownerRecord;
                        if (array_key_exists('ticket_statuses_id', $data) && ! empty($data['ticket_statuses_id'])) {
                            $ticket->update(['ticket_statuses_id' => $data['ticket_statuses_id']]);
                        }
                    })
                    ->after(function (array $data, Livewire $livewire) {
                        $livewire->dispatch('refreshTicketFormView');
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('attachment')
                    ->translateLabel()
                    ->action(function ($record) {
                        return response()->download('storage/'.$record->attachments);
                    })
                    ->hidden(fn ($record) => $record->attachments == ''),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([])
            ->paginated(false);
    }
}
