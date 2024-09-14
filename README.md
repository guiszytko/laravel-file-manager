
# Laravel File Manager

**Laravel File Manager** é um pacote simples para gerenciar o upload e armazenamento de arquivos no Laravel, incluindo suporte à geração de miniaturas para imagens. Ele permite associar arquivos a modelos usando relações polimórficas e oferece métodos para upload e exclusão de arquivos.

## Índice

- [Instalação](#instalação)
- [Configuração](#configuração)
- [Uso](#uso)
  - [No Modelo](#no-modelo)
  - [No Controller](#no-controller)
  - [Na View](#na-view)
  - [Exclusão de Arquivos](#exclusão-de-arquivos)
- [Publicação de Arquivos e Migrações](#publicação-de-arquivos-e-migrações)
- [License](#license)

---

## Instalação

Para instalar o pacote via Composer, execute o seguinte comando:

```bash
composer require guiszytko/laravel-file-manager
```

Após a instalação, o **Laravel Package Auto-Discovery** deve registrar automaticamente o `FileManagerServiceProvider`.

---

## Configuração

### 1. Executar as Migrações

O pacote inclui uma migração para criar a tabela `files`. Para criar a tabela, execute as migrações:

```bash
php artisan migrate
```

### 2. Publicar as Configurações (Opcional)

Se desejar personalizar as configurações padrão, você pode publicar o arquivo de configuração do pacote:

```bash
php artisan vendor:publish --provider="Guiszytko\LaravelFileManager\Providers\FileManagerServiceProvider" --tag="config"
```

Isso criará o arquivo `config/file-manager.php`, onde você poderá ajustar opções como:

- `generate_thumbnail`: Se as miniaturas devem ser geradas automaticamente.
- `thumbnail_size`: O tamanho das miniaturas em pixels.
- `storage_path`: O caminho onde os arquivos serão armazenados.
- `thumbnail_path`: O caminho onde as miniaturas serão armazenadas.

---

## Uso

Este pacote usa uma **trait** que permite fazer o upload e o gerenciamento de arquivos diretamente em seus modelos.

### No Modelo

No seu modelo, adicione a trait `FileUploadTrait` para permitir o upload de arquivos associados a ele.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Guiszytko\LaravelFileManager\Traits\FileUploadTrait;

class Post extends Model
{
    use FileUploadTrait;

    protected $fillable = ['title', 'content'];

    // O método files() será automaticamente adicionado pela trait
}
```

### No Controller

Você pode utilizar o método `uploadFile()` para fazer o upload de um arquivo e associá-lo ao modelo. Aqui está um exemplo de como fazer isso em um controlador:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    public function store(Request $request)
    {
        // Validação dos dados
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'file' => 'required|file|max:10240', // Máximo de 10MB
        ]);

        // Criar o post
        $post = Post::create($request->only('title', 'content'));

        // Fazer o upload do arquivo, se houver
        if ($request->hasFile('file')) {
            $post->uploadFile([
                'file' => $request->file('file'),
                'generate_thumbnail' => true, // Gera uma miniatura automaticamente
                'folder_path' => 'posts/files', // Define uma pasta específica
                'use_date_folders' => true, // Organiza por data
            ]);
        }

        return redirect()->route('posts.index')->with('success', 'Post criado com sucesso!');
    }
}
```

### Na View

Para exibir os arquivos associados a um modelo, como um `Post`, você pode iterar sobre os arquivos e exibi-los na sua view:

```blade
@extends('layouts.app')

@section('content')
    <h1>{{ $post->title }}</h1>
    <p>{{ $post->content }}</p>

    @if($post->files->count())
        @foreach($post->files as $file)
            <div class="mb-2">
                <p><strong>{{ $file->original_name }}</strong></p>
                @if($file->thumbnail_url)
                    <img src="{{ $file->thumbnail_url }}" alt="Miniatura" class="img-thumbnail">
                @endif
                <img src="{{ $file->url }}" alt="Imagem Original" class="img-fluid">
            </div>
        @endforeach
    @endif

    <a href="{{ route('posts.index') }}" class="btn btn-secondary">Voltar</a>
@endsection
```

### Exclusão de Arquivos

Para deletar arquivos associados a um modelo, o pacote oferece o método `deleteFile()` na `FileUploadTrait`. Isso facilita a remoção do arquivo tanto do banco de dados quanto do disco.

#### No Controller

Para deletar um arquivo específico, você pode fazer o seguinte no seu controlador:

```php
public function destroyFile(Request $request, Post $post, $fileId)
{
    try {
        // Deletar o arquivo usando o método deleteFile da trait
        $post->deleteFile($fileId);

        return back()->with('success', 'Arquivo deletado com sucesso!');
    } catch (\Exception $e) {
        return back()->withErrors('Erro ao deletar o arquivo: ' . $e->getMessage());
    }
}
```

#### Na View

Você pode adicionar um botão de exclusão para cada arquivo na sua view:

```blade
@foreach($post->files as $file)
    <div class="mb-2">
        <p><strong>{{ $file->original_name }}</strong></p>
        @if($file->thumbnail_url)
            <img src="{{ $file->thumbnail_url }}" alt="Miniatura" class="img-thumbnail">
        @endif
        <img src="{{ $file->url }}" alt="Imagem Original" class="img-fluid">

        <form action="{{ route('posts.files.destroy', [$post, $file->id]) }}" method="POST"
              onsubmit="return confirm('Tem certeza que deseja deletar este arquivo?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Deletar Arquivo</button>
        </form>
    </div>
@endforeach
```

---

## Publicação de Arquivos e Migrações

Caso precise publicar migrações ou arquivos de configuração, o pacote fornece a opção de publicação de arquivos.

Para publicar os arquivos de configuração e migração, use o seguinte comando:

```bash
php artisan vendor:publish --provider="Guiszytko\LaravelFileManager\Providers\FileManagerServiceProvider"
```

Este comando publicará os seguintes arquivos:

1. **Configurações** em `config/file-manager.php`.
2. **Migrações** para a criação da tabela `files`.

---

## License

Este pacote é licenciado sob a [MIT License](LICENSE).

---

## Contribuições

Contribuições são bem-vindas! Se encontrar algum bug ou tiver sugestões de melhorias, sinta-se à vontade para abrir uma _issue_ ou enviar um _pull request_ no [repositório do GitHub](https://github.com/guiszytko/laravel-file-manager).

---

Essa documentação deve estar pronta para ser usada no seu projeto. Se precisar de mais ajustes ou tiver mais dúvidas, sinta-se à vontade para perguntar!# laravel-file-manager
