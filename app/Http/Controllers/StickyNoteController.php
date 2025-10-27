<?php

namespace App\Http\Controllers;

use App\Models\StickyNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StickyNoteController extends Controller
{
    /**
     * Get all sticky notes for current user, menu, and company
     */
    public function index(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|integer|exists:pgsql.sys_menus,id',
            'company_id' => 'nullable|integer|exists:pgsql.sys_companies,id',
        ]);

        $notes = StickyNote::where('user_id', Auth::id())
            ->where('menu_id', $request->menu_id)
            ->where(function ($query) use ($request) {
                if ($request->company_id) {
                    $query->where('company_id', $request->company_id)
                          ->orWhereNull('company_id');
                } else {
                    $query->whereNull('company_id');
                }
            })
            ->orderBy('z_index')
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notes,
        ]);
    }

    /**
     * Store a new sticky note
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'menu_id' => 'required|integer|exists:pgsql.sys_menus,id',
            'company_id' => 'nullable|integer|exists:pgsql.sys_companies,id',
            'content' => 'nullable|string',
            'color' => 'nullable|string|in:yellow,blue,green,pink,purple,orange',
            'position_x' => 'nullable|integer',
            'position_y' => 'nullable|integer',
            'width' => 'nullable|integer',
            'height' => 'nullable|integer',
            'is_minimized' => 'nullable|boolean',
            'is_pinned' => 'nullable|boolean',
            'z_index' => 'nullable|integer',
        ]);

        $note = StickyNote::create([
            'user_id' => Auth::id(),
            'menu_id' => $data['menu_id'],
            'company_id' => $data['company_id'] ?? null,
            'content' => $data['content'] ?? '',
            'color' => $data['color'] ?? 'yellow',
            'position_x' => $data['position_x'] ?? 100,
            'position_y' => $data['position_y'] ?? 100,
            'width' => $data['width'] ?? 300,
            'height' => $data['height'] ?? 180,
            'is_minimized' => $data['is_minimized'] ?? false,
            'is_pinned' => $data['is_pinned'] ?? false,
            'z_index' => $data['z_index'] ?? 1000,
        ]);

        return response()->json([
            'success' => true,
            'data' => $note,
        ]);
    }

    /**
     * Update a sticky note
     */
    public function update(Request $request, $id)
    {
        $note = StickyNote::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $data = $request->validate([
            'content' => 'nullable|string',
            'color' => 'nullable|string|in:yellow,blue,green,pink,purple,orange',
            'position_x' => 'nullable|integer',
            'position_y' => 'nullable|integer',
            'width' => 'nullable|integer',
            'height' => 'nullable|integer',
            'is_minimized' => 'nullable|boolean',
            'is_pinned' => 'nullable|boolean',
            'z_index' => 'nullable|integer',
        ]);

        $note->update($data);

        return response()->json([
            'success' => true,
            'data' => $note,
        ]);
    }

    /**
     * Delete a sticky note
     */
    public function destroy($id)
    {
        $note = StickyNote::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $note->delete();

        return response()->json([
            'success' => true,
            'message' => 'Note deleted successfully',
        ]);
    }

    /**
     * Bulk update positions/sizes for all notes
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'notes' => 'required|array',
            'notes.*.id' => 'required|integer|exists:pgsql.sys_sticky_notes,id',
            'notes.*.position_x' => 'nullable|integer',
            'notes.*.position_y' => 'nullable|integer',
            'notes.*.width' => 'nullable|integer',
            'notes.*.height' => 'nullable|integer',
            'notes.*.z_index' => 'nullable|integer',
        ]);

        foreach ($request->notes as $noteData) {
            StickyNote::where('id', $noteData['id'])
                ->where('user_id', Auth::id())
                ->update([
                    'position_x' => $noteData['position_x'] ?? null,
                    'position_y' => $noteData['position_y'] ?? null,
                    'width' => $noteData['width'] ?? null,
                    'height' => $noteData['height'] ?? null,
                    'z_index' => $noteData['z_index'] ?? null,
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notes updated successfully',
        ]);
    }

    /**
     * Get trashed notes
     */
    public function trash(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|integer|exists:pgsql.sys_menus,id',
            'company_id' => 'nullable|integer|exists:pgsql.sys_companies,id',
        ]);

        $notes = StickyNote::onlyTrashed()
            ->where('user_id', Auth::id())
            ->where('menu_id', $request->menu_id)
            ->where(function ($query) use ($request) {
                if ($request->company_id) {
                    $query->where('company_id', $request->company_id)
                          ->orWhereNull('company_id');
                } else {
                    $query->whereNull('company_id');
                }
            })
            ->orderBy('deleted_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notes,
        ]);
    }

    /**
     * Restore a trashed note
     */
    public function restore($id)
    {
        $note = StickyNote::onlyTrashed()
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $note->restore();

        return response()->json([
            'success' => true,
            'message' => 'Note restored successfully',
            'data' => $note->fresh(),
        ]);
    }

    /**
     * Permanently delete a note
     */
    public function forceDelete($id)
    {
        $note = StickyNote::onlyTrashed()
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $note->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Note permanently deleted',
        ]);
    }
}
