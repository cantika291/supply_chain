<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Country;
use App\Models\Port;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $stats = [
            'users'     => User::count(),
            'countries' => Country::count(),
            'ports'     => Port::count(),
            'articles'  => Article::count(),
        ];

        $recentUsers = User::latest()->take(10)->get();
        $articles    = Article::with('author')->latest()->take(10)->get();

        return view('admin.index', compact('stats', 'recentUsers', 'articles'));
    }

    // ── Users ──────────────────────────────────────────────────
    public function users(): View
    {
        $users = User::latest()->paginate(15);
        return view('admin.users', compact('users'));
    }

    public function updateUserRole(Request $request, User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Kamu tidak bisa mengubah role akun sendiri.');
        }
        $request->validate(['role' => ['required', 'in:admin,user']]);
        $user->update(['role' => $request->role]);
        return back()->with('success', "Role {$user->name} berhasil diubah ke {$request->role}.");
    }

    public function destroyUser(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Kamu tidak bisa menghapus akun sendiri.');
        }
        $user->delete();
        return back()->with('success', "User {$user->name} berhasil dihapus.");
    }

    // ── Articles ──────────────────────────────────────────────
    public function articles(): View
    {
        $articles = Article::with('author', 'country')->latest()->paginate(10);
        $countries = Country::orderBy('name')->get(['id', 'name', 'cca3']);
        return view('admin.articles', compact('articles', 'countries'));
    }

    public function storeArticle(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'      => ['required', 'string', 'max:255'],
            'content'    => ['required', 'string'],
            'country_id' => ['nullable', 'exists:countries,id'],
        ]);

        Article::create([
            'user_id'      => auth()->id(),
            'title'        => $validated['title'],
            'content'      => $validated['content'],
            'country_id'   => $validated['country_id'] ?? null,
            'slug'         => \Illuminate\Support\Str::slug($validated['title'].'-'.time()),
            'published_at' => now(),
        ]);

        return back()->with('success', 'Artikel berhasil dipublikasikan.');
    }

    public function destroyArticle(Article $article): RedirectResponse
    {
        $article->delete();
        return back()->with('success', 'Artikel berhasil dihapus.');
    }

    // ── Ports ──────────────────────────────────────────────────
    public function ports(): View
    {
        $ports = Port::with('country')->latest()->paginate(20);
        $countries = Country::orderBy('name')->get(['id', 'name', 'cca3']);
        return view('admin.ports', compact('ports', 'countries'));
    }

    public function storePort(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'latitude'   => ['required', 'numeric'],
            'longitude'  => ['required', 'numeric'],
            'harbor_type'=> ['nullable', 'string', 'max:100'],
        ]);
        Port::create($validated);
        return back()->with('success', "Pelabuhan {$validated['name']} berhasil ditambahkan.");
    }

    public function destroyPort(Port $port): RedirectResponse
    {
        $port->delete();
        return back()->with('success', 'Pelabuhan berhasil dihapus.');
    }
}