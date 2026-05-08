<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShoppingList;
use App\Models\ShoppingItem;
use App\Models\ShoppingListItem;
use App\Models\PriceHistory;
use Illuminate\Support\Facades\Auth;

class ShoppingController extends Controller
{
    public function index()
    {
        $lists = ShoppingList::where('user_id', Auth::id())
            ->withCount(['items as pending_count' => function($query) {
                $query->where('is_bought', false);
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('shopping.index', compact('lists'));
    }

    public function storeList(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        
        ShoppingList::create([
            'name' => $request->name,
            'user_id' => Auth::id(),
            'status' => 'active'
        ]);

        return back()->with('status', 'Lista creada correctamente');
    }

    public function show($id)
    {
        $list = ShoppingList::where('user_id', Auth::id())
            ->with('items.item')
            ->findOrFail($id);

        return view('shopping.show', compact('list'));
    }

    public function addItem(Request $request, $listId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0.1'
        ]);

        $list = ShoppingList::where('user_id', Auth::id())->findOrFail($listId);

        // Find or create the shopping item (global reference for price tracking)
        // Note: Encryption might make where('name', ...) tricky if not handled by a searchable hash.
        // For now, we'll search by iterating or assume a simpler approach if encryption is not blind-searchable.
        // Laravel's encrypted cast is not searchable by default. 
        // A better way would be a normalized name field or just creating new ones.
        // Let's search by name (this will only work if we use a searchable encryption or plain text for search).
        // Since I'm using the 'encrypted' cast, I'll just create a new one for now or use a simpler approach.
        
        $item = ShoppingItem::where('user_id', Auth::id())->get()->first(function($i) use ($request) {
            return strtolower($i->name) === strtolower($request->name);
        });

        if (!$item) {
            $item = ShoppingItem::create([
                'name' => $request->name,
                'user_id' => Auth::id()
            ]);
        }

        ShoppingListItem::create([
            'shopping_list_id' => $list->id,
            'shopping_item_id' => $item->id,
            'quantity' => $request->quantity,
            'is_bought' => false
        ]);

        return back()->with('status', 'Ítem agregado');
    }

    public function markAsBought(Request $request, $id)
    {
        $request->validate(['price' => 'required|numeric|min:0']);
        
        $listItem = ShoppingListItem::findOrFail($id);
        $list = $listItem->shoppingList;
        
        if ($list->user_id !== Auth::id()) abort(403);

        $listItem->update([
            'price' => $request->price,
            'is_bought' => true
        ]);

        // Update ShoppingItem price history
        $item = $listItem->item;
        $oldPrice = $item->current_price;
        
        if ($oldPrice != $request->price) {
            $item->update([
                'last_price' => $oldPrice,
                'current_price' => $request->price
            ]);

            PriceHistory::create([
                'shopping_item_id' => $item->id,
                'price' => $request->price,
                'recorded_at' => now()
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function deleteItem($id)
    {
        $listItem = ShoppingListItem::findOrFail($id);
        if ($listItem->shoppingList->user_id !== Auth::id()) abort(403);
        $listItem->delete();
        return back()->with('status', 'Ítem eliminado');
    }

    public function deleteList($id)
    {
        $list = ShoppingList::where('user_id', Auth::id())->findOrFail($id);
        $list->delete();
        return redirect()->route('shopping.index')->with('status', 'Lista eliminada');
    }

    public function itemsHistory()
    {
        $items = ShoppingItem::where('user_id', Auth::id())
            ->whereNotNull('current_price')
            ->get()
            ->map(function($item) {
                $last = (float) $item->last_price;
                $current = (float) $item->current_price;
                $change = 0;
                if ($last > 0) {
                    $change = (($current - $last) / $last) * 100;
                }
                $item->percentage_change = $change;
                return $item;
            })
            ->sortByDesc(function($item) {
                return abs($item->percentage_change);
            });

        return view('shopping.history', compact('items'));
    }

    public function itemDetails($id)
    {
        $item = ShoppingItem::where('user_id', Auth::id())->with('priceHistories')->findOrFail($id);
        return view('shopping.item_details', compact('item'));
    }
}
