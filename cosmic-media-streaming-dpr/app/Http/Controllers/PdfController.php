<?php

namespace App\Http\Controllers;

// use PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use mikehaertl\wkhtmlto\Pdf;
use Barryvdh\Snappy\Facades\SnappyPdf;

class PdfController extends Controller
{
    public function viewPDF()
    {
        return view('pdf.chart');
    }
}
