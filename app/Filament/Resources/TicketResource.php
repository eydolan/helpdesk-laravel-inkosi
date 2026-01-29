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
use Illuminate\Support\HtmlString;

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
                        ->content(function (?Ticket $record): HtmlString|string {
                            if (!$record) {
                                return '-';
                            }
                            if ($record->owner) {
                                $html = '<div class="space-y-1">';
                                $html .= '<div class="font-semibold">' . e($record->owner->name) . '</div>';
                                
                                // Add phone number if available
                                if ($record->owner->phone) {
                                    // Format phone for WhatsApp (remove non-numeric, handle South African numbers)
                                    $phoneForWhatsApp = preg_replace('/[^0-9]/', '', $record->owner->phone);
                                    // If starts with 0, replace with 27 (South Africa country code)
                                    if (strlen($phoneForWhatsApp) == 10 && substr($phoneForWhatsApp, 0, 1) === '0') {
                                        $phoneForWhatsApp = '27' . substr($phoneForWhatsApp, 1);
                                    }
                                    $whatsappUrl = 'https://wa.me/' . $phoneForWhatsApp;
                                    
                                    $html .= '<div class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">';
                                    $html .= '<span>📱 ' . e($record->owner->phone) . '</span>';
                                    $html .= '<a href="' . e($whatsappUrl) . '" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-primary-600 dark:text-primary-400 hover:underline" title="Chat on WhatsApp">';
                                    $html .= '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>';
                                    $html .= '<span class="text-xs">WhatsApp</span>';
                                    $html .= '</a>';
                                    $html .= '</div>';
                                }
                                
                                // Add email if available (and not @winsms.net)
                                if ($record->owner->email && !str_ends_with($record->owner->email, '@winsms.net')) {
                                    $html .= '<div class="text-sm text-gray-600 dark:text-gray-400">✉️ ' . e($record->owner->email) . '</div>';
                                } elseif ($record->owner->email && str_ends_with($record->owner->email, '@winsms.net') && $record->owner->phone) {
                                    // Show SMS indicator for winsms.net emails
                                    $html .= '<div class="text-sm text-gray-600 dark:text-gray-400">📱 SMS: ' . e($record->owner->phone) . '</div>';
                                }
                                
                                $html .= '</div>';
                                return new HtmlString($html);
                            }
                            // Show guest info if owner is null
                            $html = '<div class="space-y-1">';
                            $html .= '<div class="font-semibold">' . e($record->guest_name ?? 'Guest') . '</div>';
                            if ($record->guest_phone) {
                                // Format phone for WhatsApp
                                $phoneForWhatsApp = preg_replace('/[^0-9]/', '', $record->guest_phone);
                                if (strlen($phoneForWhatsApp) == 10 && substr($phoneForWhatsApp, 0, 1) === '0') {
                                    $phoneForWhatsApp = '27' . substr($phoneForWhatsApp, 1);
                                }
                                $whatsappUrl = 'https://wa.me/' . $phoneForWhatsApp;
                                
                                $html .= '<div class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">';
                                $html .= '<span>📱 ' . e($record->guest_phone) . '</span>';
                                $html .= '<a href="' . e($whatsappUrl) . '" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-primary-600 dark:text-primary-400 hover:underline" title="Chat on WhatsApp">';
                                $html .= '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>';
                                $html .= '<span class="text-xs">WhatsApp</span>';
                                $html .= '</a>';
                                $html .= '</div>';
                            }
                            if ($record->guest_email) {
                                $html .= '<div class="text-sm text-gray-600 dark:text-gray-400">✉️ ' . e($record->guest_email) . '</div>';
                            }
                            $html .= '</div>';
                            return new HtmlString($html);
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
        return parent::getEloquentQuery()
            ->with(['owner', 'responsible', 'category', 'priority', 'unit', 'ticketStatus']) // Eager load relationships
            ->where(function ($query) {
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
