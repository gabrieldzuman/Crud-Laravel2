<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SeriesCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Cria uma nova instância do evento.
     *
     * @param string $seriesName Nome da série.
     * @param int $seriesId ID da série.
     * @param int $seriesSeasonsQty Quantidade de temporadas da série.
     * @param int $seriesEpisodesPerSeason Quantidade de episódios por temporada.
     * @return void
     */
    public function __construct(
        public readonly string $seriesName,
        public readonly int $seriesId,
        public readonly int $seriesSeasonsQty,
        public readonly int $seriesEpisodesPerSeason
    ) {}

    /**
     * Obtém os canais nos quais o evento deve ser transmitido.
     *
     * @return Channel|array
     */
    public function broadcastOn(): Channel|array
    {
        return new PrivateChannel('series-created.' . $this->seriesId);
    }
}
