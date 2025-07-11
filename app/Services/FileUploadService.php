<?php
namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    public function productImageUpload(UploadedFile $productImage, string $folder = 'products'): array
    {

        $path = Storage::disk('cloudinary')->putFile($folder, $productImage);
        $url = Storage::disk('cloudinary')->url($path);

        return [
            'url' => $url,
            'public_id' => $path,
        ];
    }

    public function productImageUpdate(UploadedFile $newProductImage, string $product_image_id, string $folder = 'products'): array
    {
        $this->deleteProductImage($product_image_id);

        return $this->productImageUpload($newProductImage, $folder);
    }

    public function deleteProductImage(string $product_image_id): void
    {
        Storage::disk('cloudinary')->delete($product_image_id);
    }

}
