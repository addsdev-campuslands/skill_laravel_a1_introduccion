<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Mail\PostCreatedMail;
use App\Models\Post;
use App\Traits\ApiResponse;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    use ApiResponse;
    
    /**
     * @OA\Get(
     *   path="/api/posts",
     *   tags={"Posts"},
     *   summary="Listar posts",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="success"),
     *       @OA\Property(property="data", type="array",
     *         @OA\Items(type="object",
     *           @OA\Property(property="id", type="integer", example=1),
     *           @OA\Property(property="title", type="string"),
     *           @OA\Property(property="slug", type="string"),
     *           @OA\Property(property="status", type="string", example="draft"),
     *           @OA\Property(property="cover_image", type="string", nullable=true),
     *           @OA\Property(property="user", type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string")
     *           ),
     *           @OA\Property(property="categories", type="array",
     *             @OA\Items(type="object",
     *               @OA\Property(property="id", type="integer"),
     *               @OA\Property(property="name", type="string")
     *             )
     *           ),
     *           @OA\Property(property="tags", type="array", @OA\Items(type="string")),
     *           @OA\Property(property="meta", type="object"),
     *           @OA\Property(property="published_at", type="string", format="date-time", nullable=true)
     *         )
     *       )
     *     )
     *   )
     * )
     */

    public function index(): JsonResponse
    {
        $posts = Post::with('user', 'categories')->get();
        //use App\Http\Resources\PostResource
        return $this->success(PostResource::collection($posts));
    }

    /**
     * @OA\Post(
     *   path="/api/posts",
     *   tags={"Posts"},
     *   summary="Crear post",
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         required={"title","content","status"},
     *         @OA\Property(property="title", type="string", example="Mi primer post"),
     *         @OA\Property(property="slug", type="string", example="mi-primer-post"),
     *         @OA\Property(property="content", type="string", example="Contenido..."),
     *         @OA\Property(property="status", type="string", enum={"draft","published","archived","default"}),
     *         @OA\Property(property="published_at", type="string", format="date-time", nullable=true),
     *         @OA\Property(property="tags[]", type="array", @OA\Items(type="string")),
     *         @OA\Property(property="meta[seo_title]", type="string", maxLength=60),
     *         @OA\Property(property="meta[seo_desc]", type="string", maxLength=120),
     *         @OA\Property(property="category_ids[]", type="array", @OA\Items(type="integer")),
     *         @OA\Property(property="cover_image", type="string", format="binary", nullable=true)
     *       )
     *     )
     *   ),
     *   @OA\Response(response=201, description="Creado")
     * )
     */

    public function store(StorePostRequest $request): JsonResponse
    {
        $data = $request->validated();

        //Body no voy a recibir id del usuario
        $data['user_id'] = $request->user()->id; //Siempre se toma del Token

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('posts', 'public');
        }

        $newPost = Post::create($data);

        if (!empty($data['category_ids'])) {
            $newPost->categories()->sync($data['category_ids']);
        }

        $newPost->load(['user', 'categories']);
        Log::debug('Email to send: ' . $newPost->user->email);

        // Enviar a Mailpit (desarrollo)
        Mail::to($newPost->user->email)->queue(new PostCreatedMail($newPost));

        // Enviar al correo real (producción)
        Mail::mailer('real')->to($newPost->user->email)->queue(new PostCreatedMail($newPost));


        return $this->success(new PostResource($newPost), 'Post creado correctamente', 201);
    }

    /**
     * @OA\Get(
     *   path="/api/posts/{id}",
     *   tags={"Posts"},
     *   summary="Detalle de post",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function show(string $id): JsonResponse // Post $post
    {
        //$result = Post::findOrFail($id);
        $result = Post::find($id);
        if ($result) {
            $result->load(['user', 'categories']);
            return $this->success(new PostResource($result), "Todo ok, como dijo el Pibe");
        } else {
            return $this->error("Todo mal, como NO dijo el Pibe", 404, ['id' => 'No se encontro el recurso con el id']);
        }
    }

    /**
     * @OA\Put(
     *   path="/api/posts/{id}",
     *   tags={"Posts"},
     *   summary="Actualizar post",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(
     *     required=false,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         @OA\Property(property="title", type="string"),
     *         @OA\Property(property="slug", type="string"),
     *         @OA\Property(property="content", type="string"),
     *         @OA\Property(property="status", type="string", enum={"draft","published","archived"}),
     *         @OA\Property(property="published_at", type="string", format="date-time", nullable=true),
     *         @OA\Property(property="tags[]", type="array", @OA\Items(type="string")),
     *         @OA\Property(property="meta[seo_title]", type="string"),
     *         @OA\Property(property="meta[seo_desc]", type="string"),
     *         @OA\Property(property="category_ids[]", type="array", @OA\Items(type="integer")),
     *         @OA\Property(property="cover_image", type="string", format="binary", nullable=true)
     *       )
     *     )
     *   ),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=403, description="No autorizado"),
     *   @OA\Response(response=422, description="Validación")
     * )
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        //use Illuminate\Support\Facades\Log;
        Log::debug('all:', $request->all());
        Log::debug('files:', array_keys($request->allFiles()));
        $data = $request->validated();
        if ($request->hasFile('cover_image')) {
            //Borrado (Opcional)
            if ($post->cover_image) {
                //use Illuminate\Support\Facades\Storage;
                Storage::disk('public')->delete($post->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('posts', 'public');
        }
        $post->update($data);

        //$post->refresh();

        if (array_key_exists('category_ids', $data)) {
            $post->categories()->sync($data['category_ids'] ?? []);
        }

        $post->load(['user', 'categories']);
        return $this->success(new PostResource($post));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post): JsonResponse
    {
        $post->delete(); //Soft delete
        return $this->success(null, 'Post eliminado', 204);
    }

    public function restore(int $id): JsonResponse
    {
        Log::debug('restore: ' . $id);
        $post = Post::onlyTrashed()->find($id);
        if (!$post) {
            //throw new ModelNotFoundException('Post no encontrado', 404);
            Log::debug('restore: ' . $id);
            throw new RecordsNotFoundException('Post no encontrado', 404);
        }
        Log::debug('restore: start');
        $post->restore();
        $post->load(['user', 'categories']);
        Log::debug('restore: success');
        return $this->success($post, 'Post restaurado correctamente');
    }
}