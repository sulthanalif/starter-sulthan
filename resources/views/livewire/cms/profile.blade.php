<?php

use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use Toast, WithFileUploads;

    public string $selectedTab = 'profile-tab';

    public array $config = [
        'guides' => false,
        'aspectRatio' => 1, // Maintain square aspect ratio
    ];

    // Profile variables
    public ?\Illuminate\Http\UploadedFile $image = null;
    public string $oldImage = '';
    public string $name = '';
    public string $email = '';
    public string $address = '';
    public $phone = '';

    //email variables
    public string $newEmail = '';

    //password variables
    public string $oldPassword = '';
    public string $newPassword = '';
    public string $confirmPassword  = '';

    public function mount(): void
    {
        $user = Auth::user();

        $this->oldImage = $user->image ?? 'images/empty-image.png'; // Pastikan properti `image` ada di User
        $this->name = $user->name ?? '';
        $this->email = $user->email ?? '';
        $this->address = $user->address ?? '';
        $this->phone = $user->phone ?? '';
    }

    //profile
    public function save(): void
    {
        // dd($this->phone);
        $this->validate([
            'name' => 'required|string|max:255',
            // 'email' => 'required|email|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // Validasi file gambar
        ]);

        $user = Auth::user();

        try {
            DB::beginTransaction();

            if ($this->image) {
                if ($user->image) {
                    $this->deleteImage($user->image);
                }
                $user->image = $this->uploadImage($this->image);
            }

            $user->name = $this->name;
            // $user->email = $this->email;
            $user->address = $this->address;
            $user->phone = $this->phone;

            $user->save();

            DB::commit();

            $this->success('Profile updated.', position: 'toast-bottom');
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->warning('Will update profile', $th->getMessage(), position: 'toast-bottom');
        }
    }

    public function saveEmail()
    {
        $this->warning('Belum seting Email.', position: 'toast-bottom');
        $this->reset(['email']);
    }

    public function savePassword(): void
    {
        $this->validate([
            'oldPassword' => 'required|string|min:6',
            'newPassword' => 'required|string|min:6',
            'confirmPassword' => 'required|string|min:6|same:newPassword',
        ]);

        if (Hash::check($this->oldPassword, Auth::user()->password)) {
            try {
                DB::beginTransaction();
                $user = Auth::user();
                $user->password = Hash::make($this->newPassword);
                $user->save();
                DB::commit();
                $this->success('Password updated.', position: 'toast-bottom');
                $this->reset(['oldPassword', 'newPassword', 'confirmPassword']);
            } catch (\Throwable $th) {
                DB::rollBack();
                $this->warning('Will update password', $th->getMessage(), position: 'toast-bottom');
            }
        } else {
            $this->addError('oldPassword', 'Old password is incorrect.');
        }
    }

    private function uploadImage($image): string
    {
        return $image->store('images/users', 'public');
    }

    private function deleteImage($image): void
    {
        Storage::disk('public')->delete($image);
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Profile" separator progress-indicator>
    </x-header>

    <x-card>
        <x-tabs wire:model="selectedTab">
            <x-tab name="profile-tab" label="Profil" icon="o-user">
                <div class="mt-4">
                    <x-form wire:submit="save">
                        <div class="flex flex-wrap gap-4">
                            <div class="w-full lg:w-1/3 flex justify-center">
                                <x-file wire:model="image" accept="image/png, image/jpeg, image/jpg, image/webp"
                                    crop-after-change change-text="Change" crop-text="Crop" crop-title-text="Crop image"
                                    crop-cancel-text="Cancel" crop-save-text="Crop" :crop-config="$config">
                                    <img src="{{ asset('storage/'.$oldImage) }}"
                                        class="h-40 rounded-lg" />
                                </x-file>
                            </div>
                            <div class="w-full lg:w-1/2">
                                <x-input label="Name" icon="o-user" type="text" wire:model="name" inline />
                                <div class="my-3"></div>
                                <x-input label="Email" icon="o-user" type="email" wire:model="email" inline readonly />
                                <div class="my-3"></div>
                                <x-input label="Phone" icon="o-phone" type="number" wire:model="phone" inline />
                                <div class="my-3"></div>
                                <x-textarea label="address" wire:model="address" icon="o-location-marker"
                                    hint="Max 500 chars" rows="3" inline />

                                <x-slot:actions>
                                    <x-button label="Save" class="btn-primary" type="submit" spinner="save" />
                                </x-slot:actions>
                            </div>
                        </div>
                    </x-form>
                </div>
            </x-tab>
            <x-tab name="email-tab" label="Ganti Email" icon="o-envelope">
                <div class="mt-4">
                    <x-form wire:submit="saveEmail">
                        <div class="flex flex-wrap justify-center">
                            <div class="w-full lg:w-1/2">
                                <x-input label="Masukan Email Baru" icon="o-envelope" type="email" wire:model="email" />
                            </div>
                        </div>
                        <x-slot:actions>
                            <x-button label="Kirim" class="btn-primary" type="submit" spinner="save" />
                        </x-slot:actions>
                    </x-form>
                </div>
            </x-tab>
            <x-tab name="password-tab" label="Ganti Password" icon="o-key">
                <div class="mt-4">
                    <x-form wire:submit="savePassword">
                        <div class="flex flex-wrap justify-center">
                            <div class="w-full lg:w-1/2">
                                <x-password label="Password Lama" wire:model="oldPassword" right />
                                <div class="my-3"></div>
                                <x-password label="Password Baru" wire:model="newPassword" right />
                                <div class="my-3"></div>
                                <x-password label="Konfirmasi Password" wire:model="confirmPassword" right />
                            </div>
                        </div>
                        <x-slot:actions>
                            <x-button label="Save" class="btn-primary" type="submit" spinner="save" />
                        </x-slot:actions>
                    </x-form>
                </div>
            </x-tab>
        </x-tabs>
    </x-card>
</div>

