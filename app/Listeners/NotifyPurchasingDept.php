<?php

namespace App\Listeners;

use App\Events\ProductOutOfStock;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifyPurchasingDept implements ShouldQueue
{

    use InteractsWithQueue;

    public $tries = 3; // 3 intentos
    
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ProductOutOfStock $event): void
    {
        Log::info("Enviando orden de reposición para: {$event->product->name}");
    }

    public function failed(ProductOutOfStock $event, $exception){

        Log::critical("ERROR CRÍTICO: No se pudo notificar al departamento de compras para el producto ID: {$event->product->id}.");
    }

    
}
