<x-cachet::cachet>
    <x-cachet::header />

    <div class="container mx-auto flex max-w-2xl flex-col space-y-6 px-4 py-10 sm:px-6 lg:px-8">
        <div>
            <h1 class="text-3xl font-semibold">{{ __('cachet::subscriber.manage.title') }}</h1>
            <p class="mt-1 text-zinc-500 dark:text-zinc-400">{{ __('cachet::subscriber.manage.description') }}</p>
            <p class="mt-2 text-sm font-medium">{{ $subscriber->email }}</p>
        </div>

        @if (session('success'))
            <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900 dark:bg-green-950 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('cachet.subscriber.manage.update', $subscriber->verify_code) }}" class="flex flex-col space-y-6">
            @csrf
            @method('PUT')

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="global" value="1" {{ $subscriber->global ? 'checked' : '' }}
                    class="rounded border-zinc-300 text-accent dark:border-zinc-700" />
                {{ __('cachet::subscriber.subscribe.global_label') }}
            </label>

            @if ($components->isNotEmpty())
                <fieldset class="flex flex-col space-y-2">
                    <legend class="text-sm font-medium">{{ __('cachet::subscriber.subscribe.components_label') }}</legend>
                    @foreach ($components as $component)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="components[]" value="{{ $component->id }}"
                                {{ $subscriber->components->contains($component->id) ? 'checked' : '' }}
                                class="rounded border-zinc-300 text-accent dark:border-zinc-700" />
                            {{ $component->name }}
                        </label>
                    @endforeach
                </fieldset>
            @endif

            <div>
                <button type="submit" class="rounded-sm bg-accent px-4 py-2 text-sm font-semibold text-accent-foreground transition hover:opacity-80">
                    {{ __('cachet::subscriber.manage.update_submit') }}
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('cachet.subscriber.unsubscribe', $subscriber->verify_code) }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm font-medium text-red-600 transition hover:opacity-80">
                {{ __('cachet::subscriber.manage.unsubscribe_submit') }}
            </button>
        </form>
    </div>

    <x-cachet::footer />
</x-cachet::cachet>
