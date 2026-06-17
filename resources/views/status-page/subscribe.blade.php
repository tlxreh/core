<x-cachet::cachet>
    <x-cachet::header />

    <div class="container mx-auto flex max-w-2xl flex-col space-y-6 px-4 py-10 sm:px-6 lg:px-8">
        <div>
            <h1 class="text-3xl font-semibold">{{ __('cachet::subscriber.subscribe.title') }}</h1>
            <p class="mt-1 text-zinc-500 dark:text-zinc-400">{{ __('cachet::subscriber.subscribe.description') }}</p>
        </div>

        @if (session('success'))
            <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900 dark:bg-green-950 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('cachet.subscriber.subscribe.store') }}" class="flex flex-col space-y-6">
            @csrf

            <div class="flex flex-col space-y-1">
                <label for="email" class="text-sm font-medium">{{ __('cachet::subscriber.subscribe.email_label') }}</label>
                <input type="email" name="email" id="email" required value="{{ old('email') }}"
                    class="rounded-md border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900" />
                @error('email')
                    <span class="text-sm text-red-600">{{ $message }}</span>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="global" value="1" {{ old('global') ? 'checked' : '' }}
                    class="rounded border-zinc-300 text-accent dark:border-zinc-700" />
                {{ __('cachet::subscriber.subscribe.global_label') }}
            </label>

            @if ($components->isNotEmpty())
                <fieldset class="flex flex-col space-y-2">
                    <legend class="text-sm font-medium">{{ __('cachet::subscriber.subscribe.components_label') }}</legend>
                    @foreach ($components as $component)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="components[]" value="{{ $component->id }}"
                                {{ collect(old('components'))->contains($component->id) ? 'checked' : '' }}
                                class="rounded border-zinc-300 text-accent dark:border-zinc-700" />
                            {{ $component->name }}
                        </label>
                    @endforeach
                </fieldset>
            @endif

            <div>
                <button type="submit" class="rounded-sm bg-accent px-4 py-2 text-sm font-semibold text-accent-foreground transition hover:opacity-80">
                    {{ __('cachet::subscriber.subscribe.submit') }}
                </button>
            </div>
        </form>
    </div>

    <x-cachet::footer />
</x-cachet::cachet>
