<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" 
                          type="email" 
                          name="email" 
                          :value="old('email')" 
                          required autofocus 
                          autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Šifra')" />

            <div class="relative mt-1">
                <x-text-input id="password" class="block w-full pr-16" 
                              type="password" 
                              name="password" 
                              required autocomplete="current-password" />

                <!-- Dugme za prikaz/skrivanje šifre -->
                <button type="button" onclick="togglePassword()" 
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-sm text-gray-500 hover:text-gray-700 font-medium px-2 py-1">
                    Prikaži
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" 
                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" 
                       name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Zapamti me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md 
                          focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" 
                   href="{{ route('password.request') }}">
                    {{ __('Zaboravili ste šifru?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Uloguj se') }}
            </x-primary-button>
        </div>
    </form>

    <!-- JavaScript za toggle password -->
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const button = event.currentTarget;
            if (input.type === 'password') {
                input.type = 'text';
                button.innerText = 'Sakrij';
            } else {
                input.type = 'password';
                button.innerText = 'Prikaži';
            }
        }
    </script>
</x-guest-layout>

