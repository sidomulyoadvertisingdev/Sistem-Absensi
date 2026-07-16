<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IntegrationWebhook;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $webhooks = IntegrationWebhook::query()
            ->with('user')
            ->when(
                !$user->isOwner(),
                fn ($query) => $query->where('user_id', $user->id)
            )
            ->latest()
            ->get();

        return view('admin.integration-webhooks.index', [
            'webhooks' => $webhooks,
            'eventOptions' => $this->eventOptions(),
            'generatedWebhook' => session('generated_webhook'),
        ]);
    }

    public function create(): View
    {
        return view('admin.integration-webhooks.create', [
            'eventOptions' => $this->eventOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'webhook_url' => ['required', 'url', 'max:500'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['string', 'in:attendance'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $events = array_values(array_intersect($data['events'], array_keys($this->eventOptions())));

        if (empty($events)) {
            return back()
                ->withInput()
                ->withErrors(['events' => 'Pilih minimal satu event.']);
        }

        $webhook = IntegrationWebhook::create([
            'user_id' => $request->user()->id,
            'name' => trim($data['name']),
            'webhook_url' => $data['webhook_url'],
            'secret' => Str::random(32),
            'events' => $events,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return redirect()
            ->route('admin.integration-webhooks.index')
            ->with('success', 'Webhook integrasi berhasil dibuat.')
            ->with('generated_webhook', [
                'name' => $webhook->name,
                'webhook_url' => $webhook->webhook_url,
                'secret' => $webhook->secret,
                'events' => $webhook->events,
            ]);
    }

    public function destroy(Request $request, IntegrationWebhook $webhook): RedirectResponse
    {
        abort_if(!$this->canManage($request->user(), $webhook), 403, 'Anda tidak dapat menghapus webhook ini.');

        $webhook->delete();

        return redirect()
            ->route('admin.integration-webhooks.index')
            ->with('success', 'Webhook integrasi berhasil dihapus.');
    }

    private function canManage(\App\Models\User $user, IntegrationWebhook $webhook): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        return (int) $webhook->user_id === (int) $user->id;
    }

    private function eventOptions(): array
    {
        return [
            'attendance' => 'Absensi karyawan (saat user absen di aplikasi)',
        ];
    }
}
