<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\CultureWork;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CultureWorkController extends Controller
{
    /**
     * Estante Pública de Cordéis e Obras de Arte de Sergipe
     */
    public function index(Request $request)
    {
        $query = CultureWork::with('user', 'ad')->published();

        // Filtro por Palavra-chave (Título ou Conteúdo)
        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Filtro por Categoria
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Filtro por Tema
        if ($request->filled('theme')) {
            $query->where('theme', $request->input('theme'));
        }

        // Filtro por Artista/Autor
        if ($request->filled('author')) {
            $author = $request->input('author');
            $query->whereHas('user', function ($q) use ($author) {
                $q->where('name', 'like', "%{$author}%");
            });
        }

        $works = $query->latest()->paginate(9);

        // Se for requisição AJAX (Infinite Scroll)
        if ($request->ajax()) {
            return response()->json([
                'html' => view('culture._work_grid', compact('works'))->render(),
                'next_page' => $works->nextPageUrl(),
                'has_more' => $works->hasMorePages(),
            ]);
        }

        $themes = CultureWork::published()->whereNotNull('theme')->pluck('theme')->unique()->filter();

        return view('culture.index', compact('works', 'themes'));
    }

    /**
     * Leitor / Visualizador da Obra / Cordel
     */
    public function show($slug)
    {
        $work = CultureWork::with('user', 'ad')->where('slug', $slug)->firstOrFail();
        
        // Se a obra for rascunho, apenas o autor pode ver
        if ($work->status === 'draft' && auth()->id() !== $work->user_id) {
            abort(404);
        }

        $work->increment('views_count');

        $otherWorks = CultureWork::published()
            ->where('id', '!=', $work->id)
            ->where('user_id', $work->user_id)
            ->take(3)
            ->get();

        return view('culture.show', compact('work', 'otherWorks'));
    }

    /**
     * Perfil Público do Artista/Cordelista
     */
    public function authorProfile($username)
    {
        $author = \App\Models\User::where('username', $username)->firstOrFail();
        
        $works = CultureWork::published()
            ->where('user_id', $author->id)
            ->latest()
            ->paginate(12);

        return view('culture.author', compact('author', 'works'));
    }

    /**
     * Minhas Obras (Painel do Escritor / Artista)
     */
    public function myWorks()
    {
        $works = CultureWork::where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('culture.my-works', compact('works'));
    }

    /**
     * Formulário de Criação de Obra
     */
    public function create()
    {
        // Anúncios do tipo 'products' ou 'services' do usuário para vincular à obra (venda de livros/folhetos)
        $userAds = Ad::where('user_id', auth()->id())->get();

        return view('culture.create', compact('userAds'));
    }

    /**
     * Salvar nova Obra
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|in:cordel,literatura,musica,arte_visual',
            'summary' => 'nullable|string|max:500',
            'content' => 'nullable|string',
            'cover' => 'nullable|image|max:4096',
            'theme' => 'nullable|string|max:100',
            'external_url' => 'nullable|url|max:255',
            'embed_media_url' => 'nullable|string',
            'status' => 'required|in:draft,published',
            'ad_id' => 'nullable|exists:ads,id',
        ]);

        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = ImageOptimizer::convertToWebp($request->file('cover'), 'culture_cover');
        }

        $work = CultureWork::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . Str::random(5),
            'summary' => $request->summary,
            'content' => $request->content,
            'cover_path' => $coverPath,
            'category' => $request->category,
            'theme' => $request->theme,
            'external_url' => $request->external_url,
            'embed_media_url' => $request->embed_media_url,
            'status' => $request->status,
            'version' => 1,
            'ad_id' => $request->ad_id,
        ]);

        $message = $work->status === 'published' 
            ? 'Sua obra foi publicada com sucesso no Espaço Cultural!' 
            : 'Rascunho salvo com sucesso!';

        return redirect()->route('culture.my-works')->with('success', $message);
    }

    /**
     * Formulário de Edição
     */
    public function edit($id)
    {
        $work = CultureWork::where('user_id', auth()->id())->findOrFail($id);
        $userAds = Ad::where('user_id', auth()->id())->get();

        return view('culture.edit', compact('work', 'userAds'));
    }

    /**
     * Atualizar Obra
     */
    public function update(Request $request, $id)
    {
        $work = CultureWork::where('user_id', auth()->id())->findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|in:cordel,literatura,musica,arte_visual',
            'summary' => 'nullable|string|max:500',
            'content' => 'nullable|string',
            'cover' => 'nullable|image|max:4096',
            'theme' => 'nullable|string|max:100',
            'external_url' => 'nullable|url|max:255',
            'embed_media_url' => 'nullable|string',
            'status' => 'required|in:draft,published',
            'ad_id' => 'nullable|exists:ads,id',
            'bump_version' => 'nullable|boolean',
        ]);

        if ($request->hasFile('cover')) {
            $coverPath = ImageOptimizer::convertToWebp($request->file('cover'), 'culture_cover');
            if ($coverPath) {
                $work->cover_path = $coverPath;
            }
        }

        $work->title = $request->title;
        $work->summary = $request->summary;
        $work->content = $request->content;
        $work->category = $request->category;
        $work->theme = $request->theme;
        $work->external_url = $request->external_url;
        $work->embed_media_url = $request->embed_media_url;
        $work->status = $request->status;
        $work->ad_id = $request->ad_id;

        if ($request->boolean('bump_version')) {
            $work->version = $work->version + 1;
        }

        $work->save();

        return redirect()->route('culture.my-works')->with('success', 'Obra atualizada com sucesso!');
    }

    /**
     * Excluir Obra
     */
    public function destroy($id)
    {
        $work = CultureWork::where('user_id', auth()->id())->findOrFail($id);
        $work->delete();

        return redirect()->route('culture.my-works')->with('success', 'Obra removida com sucesso.');
    }
}
