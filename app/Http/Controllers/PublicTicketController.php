<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicTicketRequest;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\Unit;
use App\Services\UserResolutionService;
use App\Settings\AccountSettings;
use App\Settings\TicketSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicTicketController extends Controller
{
    protected UserResolutionService $userResolutionService;
    protected TicketSettings $ticketSettings;

    public function __construct(UserResolutionService $userResolutionService, TicketSettings $ticketSettings)
    {
        $this->userResolutionService = $userResolutionService;
        $this->ticketSettings = $ticketSettings;
    }

    /**
     * Display public ticket submission form
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        $units = Unit::all();
        $user = auth()->user();
        $accountSettings = app(AccountSettings::class);

        return view('public.tickets.create', [
            'units' => $units,
            'user' => $user,
            'accountSettings' => $accountSettings,
        ]);
    }

    /**
     * Handle form submission
     *
     * @param PublicTicketRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(PublicTicketRequest $request)
    {
        $validated = $request->validated();

        // Ensure unit_id defaults to 1 if not provided
        $validated['unit_id'] = $validated['unit_id'] ?? 1;

        // Resolve or create user account
        $userData = [
            'name' => $validated['name'] ?? (auth()->check() ? auth()->user()->name : null),
            'email' => $validated['email'] ?? (auth()->check() ? auth()->user()->email : null),
            'phone' => $validated['phone'],
        ];

        $result = $this->userResolutionService->resolveOrCreate($userData, true);
        $user = $result['user'];
        $isNew = $result['is_new'];
        $password = $result['password'];

        // Create ticket
        $ticket = Ticket::create([
            'unit_id' => $validated['unit_id'],
            'category_id' => $validated['category_id'],
            'owner_id' => $user->id,
            'priority_id' => $this->ticketSettings->default_priority,
            'ticket_statuses_id' => 1, // Default status (Open)
            'title' => $validated['title'],
            'description' => $validated['description'],
            'voucher_number' => $validated['voucher_number'] ?? null,
            'guest_name' => !auth()->check() ? $validated['name'] : null,
            'guest_email' => !auth()->check() ? ($validated['email'] ?? null) : null,
            'guest_phone' => !auth()->check() ? $validated['phone'] : null,
        ]);

        // Auto-login the user
        if (!auth()->check()) {
            Auth::login($user);
        }

        // Store password in session for display
        if ($isNew && $password) {
            session()->flash('temporary_password', $password);
            session()->flash('is_new_account', true);
        }

        return redirect()->route('public.tickets.success', ['ticket' => $ticket->id])
            ->with('success', 'Ticket submitted successfully!');
    }

    /**
     * Display success page with password (if new account)
     *
     * @param Request $request
     * @param int $ticket
     * @return \Illuminate\View\View
     */
    public function success(Request $request, $ticket)
    {
        $ticket = Ticket::findOrFail($ticket);
        $temporaryPassword = session('temporary_password');
        $isNewAccount = session('is_new_account', false);

        return view('public.tickets.success', [
            'ticket' => $ticket,
            'temporaryPassword' => $temporaryPassword,
            'isNewAccount' => $isNewAccount,
        ]);
    }
}
