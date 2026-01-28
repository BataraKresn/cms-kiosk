<?php

namespace App\Http\Controllers;

use App\Models\Layout;
use App\Services\LayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomLayoutController extends Controller
{
    public function index($id)
    {
        // Correctly pass the variable name to compact
        return view('components.layouts.editor', compact('id'));
    }

    public function gridStack($id)
    {
        $data = DB::table('spots')
                ->leftJoin('layouts', 'spots.layout_id', '=', 'layouts.id')
                ->leftJoin('screens', 'layouts.screen_id', '=', 'screens.id')
                ->where('layout_id', $id)
                ->get();

        if ($data->isEmpty()) {
            $data = DB::table('screens')
                ->leftJoin('layouts', 'layouts.screen_id', '=', 'screens.id')
                ->where('layouts.id', $id)
                ->get();
            }
        // $layout = Layout::findOrFail($id);
        // $options = LayoutService::build($layout, false);
        // Correctly pass the variable name to compact
        return view('components.custom-template.gridstack-view', compact('data', 'id'));
    }

    public function preview($id) {
        $data = DB::table('custom_layout')
            ->where('id', $id)
            ->select('data_html', 'data_css', 'id')
            ->first(); // Use first() to get a single row
    
        // Check if data exists
        if (!$data) {
            return redirect()->back()->with('error', 'Layout not found.');
        }
    
        return view('components.layouts.preview', compact('data')); // Correct use of compact()
    }
    
}
