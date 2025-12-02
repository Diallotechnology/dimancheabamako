<?php

declare(strict_types=1);

namespace App\Helper;

use App\Models\Image;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Throwable;

trait DeleteAction
{
    public function supp(Model $delete)
    {
        $delete->delete();

        return response()->json([
            'success' => true,
            'message' => $delete ? class_basename($delete).' supprimer avec success ' : class_basename($delete).' non trouvé',
        ]);
    }

    public function file_delete(Model $model): bool
    {

        $fileDeleted = false;
        if (File::exists(public_path($model->DocLink()))) {
            $fileDeleted = File::delete(public_path($model->DocLink()));
        }

        return $fileDeleted;
    }

    public function file_multiple_delete(Collection $path): bool
    {
        $fileDeleted = false;
        foreach ($path as $key => $row) {
            if (File::exists(public_path($row))) {
                $fileDeleted = File::delete(public_path($row));
            }
        }

        return $fileDeleted;
    }

    public function file_uplode($request, Model $model): void
    {
        try {
            $path = 'product/image';

            foreach ($request->file('image') as $key => $file) {
                $filename = $file->hashName();
                $chemin = $file->storeAs($path, $filename, 'public');
                Image::create([
                    'product_id' => $model->id,
                    'chemin' => $chemin,
                ]);

            }
        } catch (Throwable $th) {
            new Exception('file uplode error');
        }
    }
}
