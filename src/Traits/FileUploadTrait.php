<?php

namespace Guiszytko\LaravelFileManager\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Carbon\Carbon;
use Guiszytko\LaravelFileManager\Models\File;

trait FileUploadTrait
{
    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function uploadFile(array $options)
    {
        // Extrair as opções
        $uploadedFile = $options['file'] ?? null; // Instância de UploadedFile

        // Verificar se o arquivo foi fornecido e é válido
        if (!$uploadedFile || !$uploadedFile->isValid()) {
            throw new \Exception('O arquivo não foi fornecido ou é inválido.');
        }

        $generateThumbnail = $options['generate_thumbnail'] ?? config('files.generate_thumbnail', false);
        $thumbnailSize = $options['thumbnail_size'] ?? config('files.thumbnail_size', [150, 150]);
        $storagePath = $options['storage_path'] ?? ''; // Ajuste: caminho padrão vazio
        $thumbnailPath = $options['thumbnail_path'] ?? ''; // Ajuste: caminho padrão vazio
        $folderPath = $options['folder_path'] ?? '';
        $useDateFolders = $options['use_date_folders'] ?? true;

        // Gerar UUID e preparar nomes e caminhos dos arquivos
        $uuid = (string)Str::uuid();
        $fileExtension = $uploadedFile->getClientOriginalExtension();
        $fileName = $uuid . '.' . $fileExtension;

        // Construir o caminho base do arquivo
        if (!empty($folderPath)) {
            $fileStorageBasePath = trim($folderPath, '/');
            $thumbnailStorageBasePath = trim($folderPath, '/') . '/thumbnail'; // Adiciona a subpasta 'thumbnail'
        } else {
            $fileStorageBasePath = trim($storagePath, '/');
            $thumbnailStorageBasePath = trim($thumbnailPath, '/');
        }

        // Adicionar pastas de data, se aplicável
        if ($useDateFolders) {
            $currentDate = Carbon::now();
            $year = $currentDate->format('Y');
            $month = $currentDate->format('m');
            $day = $currentDate->format('d');

            $fileStorageBasePath .= "/{$year}/{$month}/{$day}";
            $thumbnailStorageBasePath .= "/{$year}/{$month}/{$day}";
        }

        // Adicionar o nome do arquivo
        $fileStoragePath = $fileStorageBasePath . '/' . $fileName;

        // Salvar o arquivo original no disco 'public'
        Storage::disk('public')->put($fileStoragePath, file_get_contents($uploadedFile));

        // Obter informações do arquivo
        $originalName = $uploadedFile->getClientOriginalName();
        $mimeType = $uploadedFile->getMimeType();
        $fileSize = $uploadedFile->getSize();

        // Inicializar variável da miniatura
        $thumbnailStoragePathFull = null;

        // Se for imagem e a opção de gerar miniatura estiver ativada
        if ($generateThumbnail && str_starts_with($mimeType, 'image/')) {
            // Construir o caminho completo da miniatura
            $thumbnailStoragePathFull = $thumbnailStorageBasePath . '/' . $fileName;

            // Criar instância do ImageManager com o driver Imagick
            $manager = new ImageManager(new Driver());

            // Fazer o processamento da imagem
            $image = $manager->read($uploadedFile->getRealPath());

            $image->scale($thumbnailSize[0], $thumbnailSize[1]); // Correção aqui

            // Codificar a imagem em binário
            $thumbnailData = (string)$image->encode(); // Correção aqui

            // Salvar a miniatura no disco 'public'
            Storage::disk('public')->put($thumbnailStoragePathFull, $thumbnailData);
        }

        // Criar o registro do arquivo no banco de dados
        $file = new File([
            'id' => $uuid,
            'file_path' => $fileStoragePath,
            'thumbnail_path' => $thumbnailStoragePathFull,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
        ]);

        // Associar o arquivo ao modelo atual (usando o método files())
        $this->files()->save($file);

        return $file;
    }

    public function deleteFile($fileId)
    {
        // Buscar o arquivo associado ao modelo atual (ex: Post)
        $file = $this->files()->where('id', $fileId)->first();

        if (!$file) {
            throw new \Exception('Arquivo não encontrado ou não pertence a este modelo.');
        }

        // Chamar o método deleteFile() do modelo File para deletar o arquivo
        $file->deleteFile();

        return true;
    }
}
