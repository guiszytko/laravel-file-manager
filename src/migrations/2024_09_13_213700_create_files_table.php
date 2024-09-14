<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('fileable_type');
            $table->unsignedBigInteger('fileable_id');
            $table->string('file_path');
            $table->string('thumbnail_path')->nullable();
            $table->string('original_name');
            $table->string('mime_type');
            $table->integer('file_size');
            $table->timestamps();

            // Índices para a relação polimórfica
            $table->index(['fileable_type', 'fileable_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('files');
    }
}
