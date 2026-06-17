<?php

namespace Cachet\Http\Controllers\StatusPage;

use Cachet\Actions\Subscriber\CreateSubscriber;
use Cachet\Actions\Subscriber\UnsubscribeSubscriber;
use Cachet\Actions\Subscriber\UpdateSubscriber;
use Cachet\Models\Component;
use Cachet\Models\Subscriber;
use Cachet\Settings\AppSettings;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriberController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected AppSettings $appSettings)
    {
        //
    }

    /**
     * Show the subscribe form.
     */
    public function showSubscribeForm(): View
    {
        abort_unless($this->appSettings->subscriber_notifications_enabled, 404);

        return view('cachet::status-page.subscribe', [
            'components' => $this->components(),
        ]);
    }

    /**
     * Subscribe a new subscriber.
     */
    public function subscribe(Request $request, CreateSubscriber $createSubscriber): RedirectResponse
    {
        abort_unless($this->appSettings->subscriber_notifications_enabled, 404);

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'global' => ['sometimes', 'boolean'],
            'components' => ['sometimes', 'array'],
            'components.*' => ['integer', 'exists:components,id'],
        ]);

        $global = (bool) ($validated['global'] ?? false);
        $components = $global ? [] : ($validated['components'] ?? []);

        $createSubscriber->handle(
            email: $validated['email'],
            global: $global,
            components: $components,
        );

        return redirect()
            ->route('cachet.subscriber.subscribe')
            ->with('success', __('cachet::subscriber.flash.subscribed'));
    }

    /**
     * Verify a subscriber.
     */
    public function verify(string $verifyCode): RedirectResponse
    {
        $subscriber = $this->resolveSubscriber($verifyCode);

        $subscriber->verify();

        return redirect()
            ->route('cachet.subscriber.manage', $subscriber->verify_code)
            ->with('success', __('cachet::subscriber.flash.verified'));
    }

    /**
     * Show the manage subscription form.
     */
    public function showManageForm(string $verifyCode): View
    {
        $subscriber = $this->resolveSubscriber($verifyCode);

        return view('cachet::status-page.manage-subscription', [
            'subscriber' => $subscriber->load('components'),
            'components' => $this->components(),
        ]);
    }

    /**
     * Update a subscriber's subscriptions.
     */
    public function updateSubscriptions(Request $request, string $verifyCode, UpdateSubscriber $updateSubscriber): RedirectResponse
    {
        $subscriber = $this->resolveSubscriber($verifyCode);

        $validated = $request->validate([
            'global' => ['sometimes', 'boolean'],
            'components' => ['sometimes', 'array'],
            'components.*' => ['integer', 'exists:components,id'],
        ]);

        $global = (bool) ($validated['global'] ?? false);
        $components = $global ? [] : ($validated['components'] ?? []);

        $updateSubscriber->handle(
            subscriber: $subscriber,
            global: $global,
            components: $components,
        );

        return redirect()
            ->route('cachet.subscriber.manage', $subscriber->verify_code)
            ->with('success', __('cachet::subscriber.flash.updated'));
    }

    /**
     * Unsubscribe a subscriber.
     */
    public function unsubscribe(string $verifyCode, UnsubscribeSubscriber $unsubscribeSubscriber): RedirectResponse
    {
        $subscriber = $this->resolveSubscriber($verifyCode);

        $unsubscribeSubscriber->handle($subscriber);

        return redirect()
            ->route('cachet.status-page')
            ->with('success', __('cachet::subscriber.flash.unsubscribed'));
    }

    /**
     * Resolve a subscriber by their verify code or fail.
     */
    private function resolveSubscriber(string $verifyCode): Subscriber
    {
        abort_unless($this->appSettings->subscriber_notifications_enabled, 404);

        return Subscriber::query()->where('verify_code', $verifyCode)->firstOrFail();
    }

    /**
     * Get the enabled components available for subscription.
     *
     * @return Collection<int, Component>
     */
    private function components(): Collection
    {
        return Component::query()->enabled()->orderBy('order')->get();
    }
}
