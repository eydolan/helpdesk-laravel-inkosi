<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Filament\Resources\TicketResource\RelationManagers\CommentsRelationManager;
use App\Models\Category;
use App\Models\Priority;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\Unit;
use App\Models\User;
use App\Settings\GeneralSettings;
use App\Settings\TicketSettings;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return __('Ticket');
    }

    public static function getNavigationItems(): array
    {
        $navigationsItems = parent::getNavigationItems();
        $navigationsItems[0]->isActiveWhen(function () {
            return request()->routeIs(static::getRouteBaseName().'.*')
                && ! collect(request()->query())->dot()->get('tableFilters.only_my_tickets.isActive');
        });

        return $navigationsItems;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    Forms\Components\Select::make('unit_id')
                        ->label(__('Work Unit'))
                        ->options(Unit::where(function ($query) {
                            $user = auth()->user();

                            if ($user->hasAnyRole(['Super Admin'])) {
                                return;
                            }

                            if ($user->unit_id) {
                                $query->whereId($user->unit_id);
                            }
                        })->get()->pluck('name', 'id'))
                        ->default(auth()->user()->unit_id)
                        ->searchable()
                        ->required()
                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                            $unit = Unit::find($state);
                            if ($unit) {
                                $categoryId = (int) $get('category_id');
                                if ($categoryId && $category = Category::find($categoryId)) {
                                    if ($category->unit_id !== $unit->id) {
                                        $set('category_id', null);
                                    }
                                }
                            }
                        })
                        ->reactive(),

                    Forms\Components\Select::make('category_id')
                        ->label(__('Category'))
                        ->options(function (callable $get, callable $set) {
                            return Category::where(function ($query) use ($get) {
                                $query->whereNull('unit_id');
                                if ($get('unit_id')) {
                                    $query->orWhere('unit_id', $get('unit_id'));
                                }
                            })->get()->pluck('name', 'id');
                            $unit = Unit::find($get('unit_id'));
                            if ($unit) {
                                return $unit->categories->pluck('name', 'id');
                            }

                            return Category::all()->pluck('name', 'id');
                        })
                        ->searchable()
                        ->required(),

                    Forms\Components\TextInput::make('voucher_number')
                        ->label(__('Voucher Number'))
                        ->maxLength(255)
                        ->columnSpan([
                            'sm' => 2,
                        ]),

                    Forms\Components\TextInput::make('title')
                        ->label(__('Title'))
                        ->required()
                        ->maxLength(255)
                        ->columnSpan([
                            'sm' => 2,
                        ]),

                    Forms\Components\RichEditor::make('description')
                        ->label(__('Description'))
                        ->required()
                        ->maxLength(65535)
                        ->columnSpan([
                            'sm' => 2,
                        ]),
                ])->columns([
                    'sm' => 2,
                ])->columnSpan(2),

                Section::make()->schema([
                    Forms\Components\Select::make('priority_id')
                        ->label(__('Priority'))
                        ->options(Priority::all()->pluck('name', 'id'))
                        ->default(app(TicketSettings::class)->default_priority)
                        ->searchable()
                        ->required(),

                    Forms\Components\Select::make('ticket_statuses_id')
                        ->label(__('Status'))
                        ->options(TicketStatus::all()->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->hiddenOn('create')
                        ->hidden(
                            fn () => ! auth()
                                ->user()
                                ->hasAnyRole(['Super Admin', 'Admin Unit', 'Staff Unit']),
                        ),

                    Forms\Components\Placeholder::make('status')
                        ->label(__('Status'))
                        ->hiddenOn(['create', 'edit'])
                        ->content(fn (
                            ?Ticket $record,
                        ): string => $record ? $record->ticketStatus->name : '-')
                        ->hidden(
                            fn () => auth()
                                ->user()
                                ->hasAnyRole(['Super Admin', 'Admin Unit', 'Staff Unit']),
                        ),

                    Forms\Components\Select::make('responsible_id')
                        ->label(__('Responsible'))
                        ->options(User::ByRole()
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->hiddenOn('create')
                        ->hidden(
                            fn () => ! auth()
                                ->user()
                                ->hasAnyRole(['Super Admin', 'Admin Unit']),
                        ),

                    Forms\Components\Placeholder::make('owner_id')
                        ->label(__('Owner'))
                        ->content(function (?Ticket $record): string {
                            if (!$record) {
                                return '-';
                            }
                            if ($record->owner) {
                                return $record->owner->name;
                            }
                            // Show guest info if owner is null
                            return $record->guest_name ?? 'Guest';
                        }),
                    
                    Forms\Components\Placeholder::make('guest_info')
                        ->label(__('Guest Contact'))
                        ->content(function (?Ticket $record): string {
                            if (!$record || $record->owner) {
                                return '-';
                            }
                            $info = [];
                            if ($record->guest_email) {
                                $info[] = $record->guest_email;
                            }
                            if ($record->guest_phone) {
                                $info[] = $record->guest_phone;
                            }
                            return $info ? implode(' / ', $info) : '-';
                        })
                        ->visible(fn (?Ticket $record) => $record && !$record->owner),

                    Forms\Components\Placeholder::make('created_at')
                        ->translateLabel()
                        ->content(fn (
                            ?Ticket $record,
                        ): string => $record ? $record->created_at->diffForHumans() : '-'),

                    Forms\Components\Placeholder::make('updated_at')
                        ->translateLabel()
                        ->content(fn (
                            ?Ticket $record,
                        ): string => $record ? $record->updated_at->diffForHumans() : '-'),
                ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->translateLabel()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column content exceeds the length limit.
                        return $state;
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(app(GeneralSettings::class)->datetime_format)
                    ->translateLabel()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('owner.name')
                    ->searchable()
                    ->label(__('Owner'))
                    ->toggleable()
                    ->formatStateUsing(function (Ticket $record) {
                        if ($record->owner) {
                            return $record->owner->name;
                        }
                        return $record->guest_name ?? 'Guest';
                    }),
                Tables\Columns\TextColumn::make('category.name')
                    ->searchable()
                    ->label(__('Category'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('ticketStatus.name')
                    ->label(__('Status'))
                    ->sortable()
                    ->badge()
                    ->color(function (Ticket $ticket) {
                        return $ticket->ticketStatus->color ? Color::hex($ticket->ticketStatus->color) : 'gray';
                    }),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),

                Tables\Filters\Filter::make('only_my_tickets')
                    ->translateLabel()
                    ->toggle()
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->where('owner_id', auth()->user()->id);
                    }),

                Tables\Filters\SelectFilter::make('owner')
                    ->translateLabel()
                    ->visible(auth()->user()->roles->isNotEmpty())
                    ->relationship('owner', 'name'),

                Tables\Filters\SelectFilter::make('status')
                    ->translateLabel()
                    ->relationship('ticketStatus', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('')
                    ->size('lg')
                    ->tooltip(__('filament-actions::view.single.label')),

                Tables\Actions\EditAction::make()
                    ->label('')
                    ->size('lg')
                    ->tooltip(__('filament-actions::edit.single.label')),

                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->size('lg')
                    ->tooltip(__('filament-actions::delete.single.label')),

                Tables\Actions\RestoreAction::make()
                    ->label('')
                    ->size('lg')
                    ->tooltip(__('filament-actions::restore.single.label')),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'view' => Pages\ViewTicket::route('/{record}'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }

    /**
     * Display tickets based on each role.
     *
     * If it is a Super Admin/Global Viewer, then display all tickets.
     * If it is a Admin Unit/Unit Viewer, then display tickets based on the tickets they have created and their unit id.
     * If it is a Staff Unit, then display tickets based on the tickets they have created and the tickets assigned to them.
     * If it is a Regular User, then display tickets based on the tickets they have created.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where(function ($query) {
            $user = auth()->user();

            if ($user->hasAnyRole(['Super Admin', 'Global Viewer'])) {
                return;
            }

            if ($user->hasAnyRole(['Admin Unit', 'Unit Viewer'])) {
                $query->where('tickets.unit_id', $user->unit_id)->orWhere('tickets.owner_id', $user->id);
            } elseif ($user->hasRole('Staff Unit')) {
                $query->where('tickets.responsible_id', $user->id)->orWhere('tickets.owner_id', $user->id);
            } else {
                $query->where('tickets.owner_id', $user->id);
            }
        })
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
