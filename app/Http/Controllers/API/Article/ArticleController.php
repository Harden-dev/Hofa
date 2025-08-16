<?php

namespace App\Http\Controllers\API\Article;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleImage;
use App\Models\ArticleTranslation;
use App\Services\TranslationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Articles",
 *     description="API Endpoints for managing articles"
 * )
 */
class ArticleController extends BaseController
{
    /**
     * @OA\Post(
     *     path="/api/v1/articles",
     *     operationId="storeArticle",
     *     tags={"Articles"},
     *     summary="Create a new article",
     *     description="Create a new article with translations and optional images",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "content"},
     *                 @OA\Property(property="title", type="string", example="Mon Article", description="Article title in French"),
     *                 @OA\Property(property="content", type="string", example="Contenu de l'article...", description="Article content in French"),
     *                 @OA\Property(property="cover_image", type="file", format="binary", description="Cover image (max 2MB)"),
     *                 @OA\Property(property="gallery[]", type="array", @OA\Items(type="file", format="binary"), description="Gallery images (max 2MB each)"),
     *                 @OA\Property(property="category", type="string", example="education", description="Article category")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Article created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Article créé avec succès"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|max:2048',
            'gallery.*' => 'nullable|image|max:2048',
            'gallery' => 'nullable|array',
            'category' => 'required|string|in:education,santé,formation,humanitaire,developpement_communautaire,actions_sociales,insertion,autre',
        ]);

        // Génération du slug unique
        $slug = Str::slug($data['title']);
        $originalSlug = $slug;
        $i = 1;
        while (Article::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $i++;
        }

        // Image de couverture
        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('covers', 'public');
        }

        // Création de l'article
        $article = Article::create([
            'slug' => $slug,
            'cover_image' => $coverPath,
        ]);

        // Traductions
        $locales = ['fr', 'en', 'es', 'zh'];
        $sourceLang = 'fr';

        foreach ($locales as $locale) {
            $title = $locale === $sourceLang
                ? $data['title']
                : TranslationService::translate($data['title'], $sourceLang, $locale);

            $content = $locale === $sourceLang
                ? $data['content']
                : TranslationService::translate($data['content'], $sourceLang, $locale);

            $description = $locale === $sourceLang ? $data['description'] : TranslationService::translate($data['description'], $sourceLang, $locale);
            //$category = $locale === $sourceLang ? $data['category'] : TranslationService::translate($data['category'], $sourceLang, $locale);
            ArticleTranslation::create([
                'article_id' => $article->id,
                'locale' => $locale,
                'title' => $title,
                'content' => $content,
                'description' => $description,
                'category' => $data['category'],
            ]);
        }

        // Galerie
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $i => $image) {
                $path = $image->store('article_gallery', 'public');
                $caption = $request->captions[$i] ?? null;
                $slug = 'IMG-' . Str::uuid();
                $article->images()->create([
                    'slug' => $slug,
                    'path' => $path,
                    'caption' => $caption,
                ]);
            }
        }

        return $this->sendResponse([], 'Article créé avec succès');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/articles",
     *     operationId="indexArticles",
     *     tags={"Articles"},
     *     summary="Get all articles",
     *     description="Retrieve a paginated list of articles with optional filtering and search",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by article status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"activated", "desactivated", "all"}, default="activated")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for title and content",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="locale",
     *         in="query",
     *         description="Language for translations",
     *         required=false,
     *         @OA\Schema(type="string", enum={"fr", "en", "es", "zh"}, default="fr")
     *     ),
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Language header for translations",
     *         required=false,
     *         @OA\Schema(type="string", enum={"fr", "en", "es", "zh"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Articles retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="items", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *                     @OA\Property(property="slug", type="string", example="mon-article"),
     *                     @OA\Property(property="cover_image", type="string", nullable=true, example="http://example.com/storage/covers/image.jpg"),
     *                     @OA\Property(property="title", type="string", example="Mon Article"),
     *                     @OA\Property(property="content", type="string", example="Contenu de l'article..."),
     *                     @OA\Property(property="gallery", type="array", @OA\Items(
     *                         @OA\Property(property="url", type="string", example="http://example.com/storage/article_gallery/image.jpg"),
     *                         @OA\Property(property="caption", type="string", nullable=true, example="Description de l'image")
     *                     ))
     *                 )),
     *                 @OA\Property(property="pagination", type="object",
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="last_page", type="integer", example=5),
     *                     @OA\Property(property="per_page", type="integer", example=10),
     *                     @OA\Property(property="total", type="integer", example=50)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $locale = $request->header('Accept-Language') ?? $request->query('locale', 'fr');
        $perPage = $request->query('per_page', 50);
        $page = $request->query('page', 1);
        $status = $request->query('status', 'activated'); // activated, desactivated, all
        $category = $request->query('category', 'all'); // all, education, santé, formation, humanitaire, developpement_communautaire, actions_sociales, insertion
        $search = $request->query('search', '');

        $query = Article::with([
            'translations' => fn($q) => $q->where('locale', $locale),
            'images',
        ]);

        // Filtrage par statut
        if ($status === 'activated') {
            $query->where('is_active', true);
        } elseif ($status === 'desactivated') {
            $query->where('is_active', false);
        }

        // filtrage par catégorie
        if ($category !== 'all') {
            $query->whereHas('translations', function ($q) use ($category, $locale) {
                $q->where('locale', $locale)
                    ->where('category', $category);
            });
        }
        // Si status === 'all', pas de filtre

        // Recherche dans les traductions
        if (!empty($search)) {
            $query->whereHas('translations', function ($q) use ($search, $locale) {
                $q->where('locale', $locale)
                    ->where(function ($subQ) use ($search) {
                        $subQ->where('title', 'LIKE', "%{$search}%")
                            ->orWhere('content', 'LIKE', "%{$search}%");
                    });
            });
        }

        $articles = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => [
                'items' => $articles->getCollection()->map(function ($article) {
                    $t = $article->translations->first();
                    return [
                        'id' => $article->id,
                        'slug' => $article->slug,
                        'cover_image' => $article->cover_image ? asset('storage/' . $article->cover_image) : null,
                        'title' => $t?->title ?? '[non traduit]',
                        'content' => $t?->content ?? '[non traduit]',
                        'description' => $t?->description ?? '[non traduit]',
                        'category' => $t?->category ?? '[non traduit]',
                        'gallery' => $article->images->map(fn($img) => [
                            'url' => asset('storage/' . $img->path),
                            'caption' => $img->caption,
                        ]),
                        'created_at' => $article->created_at->toDateTimeString(),
                        'updated_at' => $article->updated_at->toDateTimeString(),
                    ];
                }),
                'pagination' => [
                    'current_page' => $articles->currentPage(),
                    'last_page' => $articles->lastPage(),
                    'per_page' => $articles->perPage(),
                    'total' => $articles->total(),
                ]
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/articles/{slug}",
     *     operationId="showArticle",
     *     tags={"Articles"},
     *     summary="Get a specific article",
     *     description="Retrieve a specific article by its slug",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         description="Article slug",
     *         required=true,
     *         @OA\Schema(type="string", example="mon-article")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by article status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"activated", "desactivated", "all"}, default="activated")
     *     ),
     *     @OA\Parameter(
     *         name="locale",
     *         in="query",
     *         description="Language for translations",
     *         required=false,
     *         @OA\Schema(type="string", enum={"fr", "en", "es", "zh"}, default="fr")
     *     ),
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Language header for translations",
     *         required=false,
     *         @OA\Schema(type="string", enum={"fr", "en", "es", "zh"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Article retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="01jywbsemp4vdwx02h17z5mgah"),
     *             @OA\Property(property="slug", type="string", example="mon-article"),
     *             @OA\Property(property="cover_image", type="string", nullable=true, example="http://example.com/storage/covers/image.jpg"),
     *             @OA\Property(property="title", type="string", example="Mon Article"),
     *             @OA\Property(property="content", type="string", example="Contenu de l'article..."),
     *             @OA\Property(property="category", type="string", example="education", description="Article category"),
     *             @OA\Property(property="gallery", type="array", @OA\Items(
     *                 @OA\Property(property="url", type="string", example="http://example.com/storage/article_gallery/image.jpg"),
     *                 @OA\Property(property="caption", type="string", nullable=true, example="Description de l'image")
     *             )),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-28T22:35:27.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Article non trouvé")
     *         )
     *     )
     * )
     */
    public function show(Request $request, $slug)
    {
        $locale = $request->header('Accept-Language') ?? $request->query('locale', 'fr');
        $status = $request->query('status', 'activated'); // activated, desactivated, all

        $query = Article::with([
            'translations' => fn($q) => $q->where('locale', $locale),
            'images',
        ])->where('slug', $slug);

        // Filtrage par statut
        if ($status === 'activated') {
            $query->where('is_active', true);
        } elseif ($status === 'desactivated') {
            $query->where('is_active', false);
        }
        // Si status === 'all', pas de filtre

        $article = $query->first();

        if (!$article) {
            return response()->json(['message' => 'Article non trouvé'], 404);
        }

        $translation = $article->translations->first();

        return response()->json([
            'id' => $article->id,
            'slug' => $article->slug,
            'cover_image' => $article->cover_image ? asset('storage/' . $article->cover_image) : null,
            'title' => $translation?->title ?? '[non traduit]',
            'content' => $translation?->content ?? '[non traduit]',
            'gallery' => $article->images->map(fn($img) => [
                'url' => asset('storage/' . $img->path),
                'caption' => $img->caption,
            ]),
            'created_at' => $article->created_at,
            'updated_at' => $article->updated_at,
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/articles/{article}",
     *     operationId="updateArticle",
     *     tags={"Articles"},
     *     summary="Update an article",
     *     description="Update an existing article with new content, images, and translations",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="article",
     *         in="path",
     *         description="Article ID",
     *         required=true,
     *         @OA\Schema(type="string", example="01jywbsemp4vdwx02h17z5mgah")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="title", type="string", example="Nouveau titre", description="New article title in French"),
     *                 @OA\Property(property="content", type="string", example="Nouveau contenu...", description="New article content in French"),
     *                 @OA\Property(property="cover_image", type="file", format="binary", description="New cover image (max 2MB)"),
     *                 @OA\Property(property="gallery[]", type="array", @OA\Items(type="file", format="binary"), description="Additional gallery images (max 2MB each)"),
     *                 @OA\Property(property="category", type="string", example="education", description="Article category")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Article updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Article mis à jour avec succès"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Article non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(Request $request, Article $article)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string',
            'content' => 'sometimes|required|string',
            'cover_image' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
            'gallery.*' => 'nullable|image|max:2048',
            'captions' => 'nullable|array',
            'captions.*' => 'nullable|string',
        ]);

        if (isset($data['title'])) {
            $newSlug = Str::slug($data['title']);

            // Vérifier si le slug existe déjà pour un autre article
            $existingArticle = Article::where('slug', $newSlug)
                ->where('id', '!=', $article->id)
                ->first();

            if ($existingArticle) {
                // Générer un slug unique en ajoutant un suffixe
                $counter = 1;
                $originalSlug = $newSlug;

                do {
                    $newSlug = $originalSlug . '-' . $counter;
                    $counter++;

                    $existingArticle = Article::where('slug', $newSlug)
                        ->where('id', '!=', $article->id)
                        ->first();
                } while ($existingArticle);
            }

            // Mettre à jour le slug de l'article
            $article->update(['slug' => $newSlug]);
        }

        // Mise à jour de l'image de couverture si fournie
        if ($request->hasFile('cover_image')) {
            // Supprimer l'ancienne image si elle existe
            if ($article->cover_image) {
                Storage::disk('public')->delete($article->cover_image);
            }
            $coverPath = $request->file('cover_image')->store('covers', 'public');
            $article->update(['cover_image' => $coverPath]);
        }

        // Mise à jour des traductions si title ou content fournis
        if (isset($data['title']) || isset($data['content'])) {
            $locales = ['fr', 'en', 'es', 'zh'];
            $sourceLang = 'fr';

            foreach ($locales as $locale) {
                $translation = $article->translations()->where('locale', $locale)->first();

                if (!$translation) {
                    $translation = new ArticleTranslation(['locale' => $locale]);
                    $article->translations()->save($translation);
                }

                if (isset($data['title'])) {
                    $title = $locale === $sourceLang
                        ? $data['title']
                        : TranslationService::translate($data['title'], $sourceLang, $locale);
                    $translation->title = $title;
                }

                if (isset($data['content'])) {
                    $content = $locale === $sourceLang
                        ? $data['content']
                        : TranslationService::translate($data['content'], $sourceLang, $locale);
                    $translation->content = $content;
                }

                if(isset($data['description'])) {
                    $description = $locale === $sourceLang
                        ? $data['description']
                        : TranslationService::translate($data['description'], $sourceLang, $locale);
                    $translation->description = $description;
                }

                $translation->save();
            }
        }

        // Ajout de nouvelles images à la galerie
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $i => $image) {
                $path = $image->store('article_gallery', 'public');
                $caption = $request->captions[$i] ?? null;
                $slug = 'IMG-' . Str::uuid();
                $article->images()->create([
                    'slug' => $slug,
                    'path' => $path,
                    'caption' => $caption,
                ]);
            }
        }

        return response()->json([
            'message' => 'Article mis à jour avec succès',
            'data' => $article->load(['translations', 'images'])
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/articles/images/{slug}",
     *     operationId="updateArticleImage",
     *     tags={"Articles"},
     *     summary="Update an article image",
     *     description="Replace an existing article image with a new one",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         description="Image slug",
     *         required=true,
     *         @OA\Schema(type="string", example="IMG-12345678-1234-1234-1234-123456789abc")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"image"},
     *                 @OA\Property(property="image", type="file", format="binary", description="New image file (max 2MB)"),
     *                 @OA\Property(property="caption", type="string", description="New caption for the image")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Image updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Image remplacée avec succès"),
     *             @OA\Property(property="image", type="object",
     *                 @OA\Property(property="slug", type="string", example="IMG-12345678-1234-1234-1234-123456789abc"),
     *                 @OA\Property(property="url", type="string", example="http://example.com/storage/article_gallery/new-image.jpg"),
     *                 @OA\Property(property="caption", type="string", example="Nouvelle description de l'image")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Image not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Image not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function updateImage(Request $request, string $slug)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
            'caption' => 'nullable|string',
        ]);

        $image = ArticleImage::where('slug', $slug)->firstOrFail();

        // Supprimer l'ancienne image du disque
        if ($image->path && Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->delete($image->path);
        }

        // Enregistrer la nouvelle image
        $newPath = $request->file('image')->store('article_gallery', 'public');

        $image->update([
            'path' => $newPath,
            'caption' => $request->caption ?? $image->caption,
        ]);

        return response()->json([
            'message' => 'Image remplacée avec succès',
            'image' => [
                'slug' => $image->slug,
                'url' => asset('storage/' . $image->path),
                'caption' => $image->caption,
            ]
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/articles/images/{slug}",
     *     operationId="deleteArticleImage",
     *     tags={"Articles"},
     *     summary="Delete an article image",
     *     description="Delete a specific image from an article gallery",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         description="Image slug",
     *         required=true,
     *         @OA\Schema(type="string", example="IMG-12345678-1234-1234-1234-123456789abc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Image deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Image supprimée avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Image not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Image not found")
     *         )
     *     )
     * )
     */
    public function deleteImage(string $slug)
    {
        $image = ArticleImage::where('slug', $slug)->firstOrFail();

        // Supprimer l'image du disque
        if ($image->path && Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->delete($image->path);
        }

        $image->delete();

        return response()->json([
            'message' => 'Image supprimée avec succès',
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/articles/{article}",
     *     operationId="desactivateArticle",
     *     tags={"Articles"},
     *     summary="Deactivate an article",
     *     description="Soft delete an article by setting is_active to false",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="article",
     *         in="path",
     *         description="Article ID",
     *         required=true,
     *         @OA\Schema(type="string", example="01jywbsemp4vdwx02h17z5mgah")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Article deactivated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Article deleted successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Article not found")
     *         )
     *     )
     * )
     */
    public function desactivate(Article $article)
    {
        try {
            $article->update(['is_active' => false]);

            return $this->sendResponse([], 'Article deleted successfully', [], 200);
        } catch (Exception $th) {
            Log::info("Error deleting article: " . $th->getMessage());
            return $this->sendError('Error deleting article', [], 500);
        }
    }
}
