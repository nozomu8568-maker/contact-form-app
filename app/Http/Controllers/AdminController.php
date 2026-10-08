<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;

class AdminController extends Controller
{
    public function index(IndexContactRequest $request)
    {
        $contacts = Contact::with(['category', 'tags'])
            ->search($request->validated())
            ->orderByDesc('id')
            ->paginate(7);
        $categories = Category::all();
        $tags = Tag::all();

        return view('admin.index', compact('contacts', 'categories', 'tags'));
    }

    public function show(Contact $contact)
    {
        $contact->load(['category', 'tags']);

        return view('admin.show', compact('contact'));
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect('/admin');
    }
}
