<?php

namespace App\Http\Controllers;

use App\Http\Requests\SeriesFormRequest;
use App\Jobs\DeleteSeriesCover;
use App\Models\Series;
use App\Repositories\SeriesRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class SeriesController extends Controller
{
    public function __construct(private SeriesRepository $repository)
    {
        $this->middleware('auth')->except('index');
    }

    /**
     * Exibe a lista de séries.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $series = Series::all();
        $mensagemSucesso = session('mensagem.sucesso');

        return view('series.index', [
            'series' => $series,
            'mensagemSucesso' => $mensagemSucesso,
        ]);
    }

    /**
     * Exibe o formulário para criar uma nova série.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('series.create');
    }

    /**
     * Armazena uma nova série.
     *
     * @param \App\Http\Requests\SeriesFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(SeriesFormRequest $request)
    {
        $coverPath = $request->file('cover')?->store('series_cover', 'public');
        $serie = $this->repository->add($request->validated());

        \App\Events\SeriesCreated::dispatch(
            $serie->nome,
            $serie->id,
            $request->seasonsQty,
            $request->episodesPerSeason,
        );

        return redirect()->route('series.index')
            ->with('mensagem.sucesso', "Série '{$serie->nome}' adicionada com sucesso");
    }

    /**
     * Exclui uma série.
     *
     * @param \App\Models\Series $series
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Series $series)
    {
        if (Gate::denies('delete', $series)) {
            abort(403, 'Acesso negado');
        }
        $coverPath = $series->cover;
        $series->delete();
        DeleteSeriesCover::dispatch($coverPath);

        return redirect()->route('series.index')
            ->with('mensagem.sucesso', "Série '{$series->nome}' removida com sucesso");
    }

    /**
     * Exibe o formulário para editar uma série existente.
     *
     * @param \App\Models\Series $series
     * @return \Illuminate\View\View
     */
    public function edit(Series $series)
    {
        if (Gate::denies('update', $series)) {
            abort(403, 'Acesso negado');
        }

        return view('series.edit', [
            'serie' => $series,
        ]);
    }

    /**
     * Atualiza uma série existente.
     *
     * @param \App\Models\Series $series
     * @param \App\Http\Requests\SeriesFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Series $series, SeriesFormRequest $request)
    {
        if (Gate::denies('update', $series)) {
            abort(403, 'Acesso negado');
        }
        $series->fill($request->validated());
        $series->save();

        return redirect()->route('series.index')
            ->with('mensagem.sucesso', "Série '{$series->nome}' atualizada com sucesso");
    }
}
