<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    /**
     * @group Gallery
     * List all gallery items for a profile
     */
    public function index(Request $request, $profileId)
    {
        $profile = $request->user()->profiles()->findOrFail($profileId);
        $items = $profile->gallery()->latest()->get();

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
        $profile = $request->user()->profiles()->findOrFail($profileId);

        $request->validate([
            'image'       => 'required|string',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'nullable|numeric|min:0',
        ]);

        $item = $profile->gallery()->create($request->only(['image', 'title', 'description', 'price']));

        return response()->json([
            'message' => 'Gallery item created successfully',
            'item'    => $item,
        ], 201);
    }

    /**
     * @group Gallery
     * Get a specific gallery item
     */
    public function show(Request $request, $profileId, $id)
    {
        $profile = $request->user()->profiles()->findOrFail($profileId);
        $item = $profile->gallery()->findOrFail($id);

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
        $profile = $request->user()->profiles()->findOrFail($profileId);
        $item = $profile->gallery()->findOrFail($id);

        $request->validate([
            'image'       => 'sometimes|string',
            'title'       => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'nullable|numeric|min:0',
        ]);

        $item->update($request->only(['image', 'title', 'description', 'price']));

        return response()->json([
            'message' => 'Gallery item updated successfully',
            'item'    => $item->fresh(),
        ]);
    }

    /**
     * @group Gallery
     * Delete a gallery item
     */
    public function destroy(Request $request, $profileId, $id)
    {
        $profile = $request->user()->profiles()->findOrFail($profileId);
        $item = $profile->gallery()->findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => 'Gallery item deleted successfully',
        ]);
    }
}
