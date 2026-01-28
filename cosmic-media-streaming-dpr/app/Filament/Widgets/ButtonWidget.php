<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Http;

class ButtonWidget extends Widget
{
    protected static string $view = 'filament.widgets.button-widget';

    public function performAction()
    {
        // Pass the API URL to the view
        // return view('filament.widgets.button-widget', [
        //     'apiUrl' => 'http://62.72.59.91:3333/generate-pdf?url=http://localhost:8777/view-pdf'
        // ]);
    }
}
