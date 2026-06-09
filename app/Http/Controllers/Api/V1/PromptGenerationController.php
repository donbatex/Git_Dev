<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\GeneratePromptRequest;
use App\Http\Resources\PromptGenerationResource;
use App\Services\OpenAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * @tags Prompt Generation
 */
class PromptGenerationController extends Controller
{

public function __construct(private OpenAiService $openAiService)
    {
        // $this->middleware('auth:sanctum');
    }

    /**
     * List prompt generations.
     *
     * Returns a paginated list of the authenticated user's prompt generations.
     *
     * @response PromptGenerationResource[]
     */
    public function index(Request $request)
    {
        $user = request()->user();
        $query = $user->promptGenerations();

        if ($request->has('search') && !empty($request->search)) {
           $query->where('generated_prompt', 'LIKE', '%' . $request->search . '%');
                // ->orWhere('original_filename', 'like', '%' . $request->search . '%');
        }

        $allowedSortFields = ['created_at', 'generated_prompt', 'original_filename', 'file_size'];
        $sortedField = 'created_at';
        $sortDirection = 'desc';

        if ($request->has('sort') && !empty($request->sort)) {
            $sort = $request->sort;

            if (str_starts_with($sort, '-')) {
                $sortedField = substr($sort, 1);
                $sortDirection = 'desc';
            } else {
                $sortedField = $sort;
                $sortDirection = 'asc';
            }
        }

        if (!in_array($sortedField, $allowedSortFields)) {
            $sortedField = 'created_at';
            $sortDirection = 'desc';
        }

        $query->orderBy($sortedField, $sortDirection);

        if ($request->has('direction') && in_array($request->direction, ['asc', 'desc'])) {
            $sortDirection = $request->direction;
        }

        $promptGenerations = $query->paginate($request->get('per_page'));
        // $promptGenerations = $query->orderBy($sortedField, $sortDirection)->paginate($request->get('per_page'));

        return PromptGenerationResource::collection($promptGenerations);
    }

    /**
     * Generate a prompt from an image.
     *
     * Uploads an image, generates an AI prompt via OpenAI, and stores the result.
     *
     * @bodyParam image file required Image to analyse (jpeg, png, jpg, gif, svg). Max 10MB, min 100×100px. Example: photo.jpg
     *
     * @response 201 PromptGenerationResource
     */
    public function store(GeneratePromptRequest $request)
    {
        $user = $request->user();
        $image = $request->file('image');

        $oridinalName = $image->getClientOriginalName();
        $sanitizedName = preg_replace('/[^A-Za-z0-9._-]/', '_', pathinfo($oridinalName, PATHINFO_FILENAME));
        $extension = $image->getClientOriginalExtension();
        $safeFilename = $sanitizedName . '_' . Str::random(10) . '.' . $extension;

        $imagePath = $image->storeAs('uploads/images', $safeFilename, 'public');
        $generatedPrompt = $this->openAiService->generatePromptFromImage($image);

        $promptGeneration = $user->promptGenerations()->create([
            'img_path' => $imagePath,
            'generated_prompt' => $generatedPrompt,
            'original_filename' => $oridinalName,
            'image_size' => $image->getSize(),
            'mime_type' => $image->getMimeType(),
        ]);

        return new PromptGenerationResource($promptGeneration);

    }
}
