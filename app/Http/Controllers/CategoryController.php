<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        $fields = array (
  0 => 
  array (
    'name' => 'title',
    'type' => 'string',
    'nullable' => false,
  ),
  1 => 
  array (
    'name' => 'nom',
    'type' => 'string',
    'nullable' => false,
  ),
  2 => 
  array (
    'name' => 'prix',
    'type' => 'float',
    'nullable' => false,
  ),
  3 => 
  array (
    'name' => 'produit',
    'type' => 'string',
    'nullable' => false,
  ),
);
        return view('categories.index', compact('categories', 'fields'));
    }

    public function create()
    {
        $fields = array (
  0 => 
  array (
    'name' => 'title',
    'type' => 'string',
    'nullable' => false,
  ),
  1 => 
  array (
    'name' => 'nom',
    'type' => 'string',
    'nullable' => false,
  ),
  2 => 
  array (
    'name' => 'prix',
    'type' => 'float',
    'nullable' => false,
  ),
  3 => 
  array (
    'name' => 'produit',
    'type' => 'string',
    'nullable' => false,
  ),
);
        return view('categories.create', compact('fields'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
            'prix' => 'required|numeric',
            'produit' => 'required|string|max:255'
        ]);
        Category::create($validated);
        return redirect()->route('categories.index');
    }

    public function edit(Category $category)
    {
        $fields = array (
  0 => 
  array (
    'name' => 'title',
    'type' => 'string',
    'nullable' => false,
  ),
  1 => 
  array (
    'name' => 'nom',
    'type' => 'string',
    'nullable' => false,
  ),
  2 => 
  array (
    'name' => 'prix',
    'type' => 'float',
    'nullable' => false,
  ),
  3 => 
  array (
    'name' => 'produit',
    'type' => 'string',
    'nullable' => false,
  ),
);
        return view('categories.edit', compact('category', 'fields'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
            'prix' => 'required|numeric',
            'produit' => 'required|string|max:255'
        ]);
        $category->update($validated);
        return redirect()->route('categories.index');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('categories.index');
    }
}