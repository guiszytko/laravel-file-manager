<?php

namespace Guiszytko\LaravelFileManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    protected $fillable = [
        'id',
        'file_path',
        'thumbnail_path',
        'original_name',
        'mime_type',
        'file_size',
    ];

    protected $appends = ['url', 'thumbnail_url'];

    public $incrementing = false;
    protected $keyType = 'string';

    public function fileable()
    {
        return $this->morphTo();
    }

    public function getUrlAttribute()
    {
        return Storage::disk('public')->url($this->file_path);
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail_path) {
            return Storage::disk('public')->url($this->thumbnail_path);
        }

        return null;
    }

    public function deleteFile()
    {
        // Deletar o arquivo original
        if (Storage::disk('public')->exists($this->file_path)) {
            Storage::disk('public')->delete($this->file_path);
        }

        // Deletar a miniatura, se existir
        if ($this->thumbnail_path && Storage::disk('public')->exists($this->thumbnail_path)) {
            Storage::disk('public')->delete($this->thumbnail_path);
        }

        // Deletar o registro do banco de dados
        $this->delete();
    }
}
