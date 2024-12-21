<?php

use Livewire\Volt\Component;

new class extends Component {
    public string $email;
    public string $password;

    public function save(): void
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        auth()->attempt($this->only('email', 'password'));

        if (auth()->check()) {
            $this->redirect(route('dashboard'));
        } else {
            $this->addError('email', 'Invalid credentials');
        }
    }
}; ?>

<div class="flex items-center justify-center min-h-screen">
    <div class="relative">
        <div class="rounded-lg shadow">
            <div class="font-bold text-2xl text-center">
                Login
            </div>
            <div class="mt-4">
                <x-form wire:submit="save" no-separator>
                    <x-input label="Email" icon="o-user" type="email" wire:model="email" inline autofocus />
                    <x-password label="Password" type="password" wire:model="password" inline />

                    <x-slot:actions>
                        <x-button label="Cancel" />
                        <x-button label="Login" class="btn-primary" type="submit" spinner="save" />
                    </x-slot:actions>
                </x-form>
            </div>
        </div>
    </div>
</div>

