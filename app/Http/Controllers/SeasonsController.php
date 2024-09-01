<?php

namespace App\Http\Controllers;

use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SeasonsController extends Controller
{
    /**
     * Exibe a lista de temporadas para uma série específica.
     *
     * @param \App\Models\Series $series
     * @return \Illuminate\View\View
     */
    public function index(Series $series)
    {
        if (Gate::denies('view', $series)) {
            abort(403, 'Acesso negado');
        }

        $seasons = $series->seasons()->with('episodes')->get();

        return view('seasons.index', [
            'seasons' => $seasons,
            'series' => $series,
        ]);
    }
}
