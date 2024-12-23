<?php

namespace App\Traits\Traits;

// use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

trait HandlesSaveOrUpdate
{
    public $recordId = null; // ID untuk update (null jika create)
    public $model;

    public function setModel($model): void
    {
        $this->model = $model;
    }

    public function setRecordId($id): void
    {
        $this->recordId = $id;
    }

    public function saveOrUpdate(array $validationRules, callable $beforeSave = null, callable $afterSave = null): void
    {
        $this->validate($validationRules);

        try {
            DB::beginTransaction();

            if ($this->recordId) {
                $record = $this->model->find($this->recordId);
                if (!$record) {
                    throw new \Exception("Record not found");
                }

                // Jalankan callback sebelum menyimpan
                if ($beforeSave) {
                    $beforeSave($record, $this); // Callback menerima model dan komponen
                }


                $record->fill($this->only(array_keys(array_diff_key($validationRules, array_flip(['image'])))));
                $record->save();
            } else {
                $record = new $this->model;

                // Jalankan callback sebelum menyimpan
                if ($beforeSave) {
                    $beforeSave($record, $this);
                }

                $record->fill($this->only(array_keys(array_diff_key($validationRules, array_flip(['image'])))));
                $record->save();
            }

            if ($afterSave) {
                $afterSave($record, $this);
            }

            DB::commit();
            $this->myModal = false;
            $this->success($this->recordId ? 'Data updated.' : 'Data created.', position: 'toast-bottom');
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->warning("An error occurred", $th->getMessage(), position: 'toast-bottom');
        }
    }

    private function uploadImage($image, $folder = null): string
    {
        return $image->store('images/'. $folder, 'public');
    }

    private function deleteImage($image): void
    {
        Storage::disk('public')->delete($image);
    }
}
