<?php

namespace App\Services;

use App\Models\Anuncio;
use App\Models\GaleriaAnuncio;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageUploadService
{
    public const MAX_GALERIA = 5;

    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver);
    }

    /**
     * Resize an uploaded image down to a max bounding box and store it as a
     * JPEG, mirroring the legacy site's upload pipeline (max 1400x1400,
     * re-compressed if still too large) - except this always runs, since the
     * legacy code only re-compressed when the result exceeded 2MB.
     */
    public function store(UploadedFile $file, string $directory, int $maxDimension = 1400): string
    {
        $image = $this->manager->read($file->getRealPath());
        $image->scaleDown($maxDimension, $maxDimension);

        $filename = $directory.'/'.date('Y/m').'/'.Str::random(20).'.jpg';

        $encoded = $image->toJpeg(quality: 82);
        Storage::disk('public')->put($filename, (string) $encoded);

        return $filename;
    }

    public function delete(string $path): void
    {
        Storage::disk('public')->delete($path);
    }

    /**
     * Saves the "imagens[]" files of a request into an anúncio's gallery, up
     * to MAX_GALERIA in total. Shared by the public edit/create flow and the CRM.
     */
    public function storeGaleria(Anuncio $anuncio, Request $request): void
    {
        if (! $request->hasFile('imagens')) {
            return;
        }

        $ordem = $anuncio->galeria()->max('ordem') ?? 0;
        $existentes = $anuncio->galeria()->count();

        foreach ($request->file('imagens') as $file) {
            if ($existentes >= self::MAX_GALERIA) {
                break;
            }

            $path = $this->store($file, 'anuncios');

            GaleriaAnuncio::create([
                'anuncio_id' => $anuncio->id,
                'imagem' => $path,
                'ordem' => ++$ordem,
            ]);

            $existentes++;
        }
    }

    /**
     * Rotates a stored image in place by a quarter turn. $clockwise = true
     * turns it to the right. The file keeps its own format (the legacy
     * uploads include png/jpeg files, new ones are always jpg).
     */
    public function rotate(string $path, bool $clockwise): void
    {
        $disk = Storage::disk('public');

        $image = $this->manager->read($disk->path($path));
        // Intervention rotates counter-clockwise for positive angles.
        $image->rotate($clockwise ? -90 : 90);

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION)) ?: 'jpg';
        $disk->put($path, (string) $image->encodeByExtension($extension, quality: 85));
    }
}
