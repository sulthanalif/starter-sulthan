<?php

namespace App\Traits\Traits;

use Illuminate\Support\Facades\DB;

trait HandlesPage
{
    public string $search = '';
    public bool $drawer = false;
    public bool $myModal = false;
    public int $perPage = 5;
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];
    public array $input = [];

    public function setInput($input): void
    {
        $this->input = $input;
    }

    public function resetInput(): void
    {
        $this->reset($this->input);
    }

    public function create(): void
    {
        $this->resetInput();
        $this->myModal = true;
    }

    public function edit($id): void
    {
        $this->setRecordId($id); // Simpan ID record untuk update
        $record = $this->model->find($id);

        if (!$record) {
            throw new \Exception("Record not found");
        }

        $this->fill($record->toArray()); // Isi data ke properti Livewire
        $this->role = $record->roles[0]->id; // Tambahkan role
        $this->myModal = true;
    }

    // Delete action
    public function delete($id): void
    {
        try {
            DB::beginTransaction();
            $this->model::find($id)->delete();
            DB::commit();

            $this->success('Deleted.', position: 'toast-bottom');
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->warning("Will delete #$id", $th->getMessage(), position: 'toast-bottom');
        }
    }
}
