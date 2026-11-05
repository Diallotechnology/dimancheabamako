<?php

declare(strict_types=1);

namespace App\Helper;

use App\Models\Image;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
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

    /**
     * Supprime un fichier du disque public à partir de son chemin relatif (ex: product/cover/x.jpg).
     */
    public function deleteStored(?string $path): void
    {
        if ($path !== null && $path !== '') {
            Storage::disk('public')->delete($path);
        }
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
            throw new Exception('file uplode error', 0, $th);
        }
    }
}
