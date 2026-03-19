<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Contracts\GalleryRepositoryInterface;

class GalleryController extends Controller
{
    protected $gallery;

    public function __construct(GalleryRepositoryInterface $gallery)
    {
        $this->gallery = $gallery;
    }

    /**
     * @group Gallery
     * List all gallery items for a profile
     */
    public function index(Request $request, $profileId)
    {
        $items = $this->gallery->allByProfile($profileId);

        return response()->json([
            'gallery' => $items,
            'total'   => $items->count(),
        ]);
    }

    /**
     * @group Gallery
     * Create/add gallery item to a profile
     */
    public function store(Request $request, $profileId)
    {
        $request->validate([
            'image'       => 'required|string',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'nullable|numeric|min:0',
        ]);

        $item = $this->gallery->create($profileId, $request->only(['image', 'title', 'description', 'price']));

        return response()->json([
            'message' => 'Gallery item created successfully',
            'item'    => $item,
        ], 201);
    }

    /**
     * @group Gallery
     * Get a specific gallery item
     * 
     * @urlParam profileId integer required The ID of the profile. Example: 1
     * @urlParam id integer required The ID of the gallery item. Example: 1
     */
    public function show(Request $request, $profileId, $id)
    {
        $item = $this->gallery->find($profileId, $id);

        if (!$item) {
            return response()->json([
                'message' => 'Gallery item not found',
            ], 404);
        }

        return response()->json([
            'item' => $item,
        ]);
    }

    /**
     * @group Gallery
     * Update a gallery item
     */
    public function update(Request $request, $profileId, $id)
    {
        $request->validate([
            'image'       => 'sometimes|string',
            'title'       => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'nullable|numeric|min:0',
        ]);

        $item = $this->gallery->update($profileId, $id, $request->only(['image', 'title', 'description', 'price']));

        if (!$item) {
            return response()->json([
                'message' => 'Gallery item not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Gallery item updated successfully',
            'item'    => $item,
        ]);
    }

    /**
     * @group Gallery
     * Delete a gallery item
     */
    public function destroy(Request $request, $profileId, $id)
    {
        $deleted = $this->gallery->delete($profileId, $id);

        if (!$deleted) {
            return response()->json([
                'message' => 'Gallery item not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Gallery item deleted successfully',
        ]);
    }
}
