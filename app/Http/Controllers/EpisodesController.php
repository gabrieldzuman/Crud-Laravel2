<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EpisodesController extends Controller
{
    /**
     * Exibe a lista de episódios de uma temporada específica.
     *
     * @param Season $season
     * @return \Illuminate\View\View
     */
    public function index(Season $season)
    {
        if (Gate::denies('view', $season)) {
            abort(403, 'Access denied');
        }

        return view('episodes.index', [
            'episodes' => $season->episodes()->orderBy('number')->get(),
            'mensagemSucesso' => session('mensagem.sucesso')
        ]);
    }

    /**
     * Atualiza os episódios marcados como assistidos.
     *
     * @param Request $request
     * @param Season $season
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Season $season)
    {
        if (Gate::denies('update', $season)) {
            abort(403, 'Access denied');
        }

        $validatedData = $request->validate([
            'episodes' => 'array',
            'episodes.*' => 'integer|exists:episodes,id'
        ]);

        $watchedEpisodesIds = $validatedData['episodes'] ?? [];

        $season->episodes()->update(['watched' => false]); 
        $season->episodes()->whereIn('id', $watchedEpisodesIds)->update(['watched' => true]);
        return redirect()->route('episodes.index', $season->id)
            ->with('mensagem.sucesso', 'Episódios marcados como assistidos');
    }
}
