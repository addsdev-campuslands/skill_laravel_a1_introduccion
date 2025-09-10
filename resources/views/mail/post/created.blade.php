@php
    
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Facades\File;

    $cid = null;
    if(!empty($post->cover_image)) {
        $absPath = Storage::disk('public')->path($post->cover_image);
        if(File::exists($absPath)){
            $cid = $message->embed($absPath);
        }
    }
@endphp

<x-mail::message>
# Nueva publicación: creada 🤓

**Título**: {{ $post->title }}

**Autor**: {{ $author }}

**Fecha de publicación**: {{ $published_at ?? 'No definida' }}
---

{{ Str::limit($post->content, 200) }}

<x-mail::button :url="''">
Ver Publicación completa
</x-mail::button>

---

> Nota: La mala para Santiago que No Vino a Clase, Julian lo extraña Mucho.

@if ($cid)
<p style="text-align: center; margin: 0 0 16px;">
    <img src="{{ $cid }}" alt="Portada del post" style="max-width: 100%; height:auto; border-radius:8px;">
</p>
@endif

---

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
