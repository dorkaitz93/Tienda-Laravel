<?php

namespace App\Listeners;

use App\Events\ProductOutOfStock;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogLowStock
{
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
        Log::warning("Alerta de Stock: El producto '{$event->product->name}' (ID: {$event->product->id}) se ha quedado sin existencias.");
    }
}
