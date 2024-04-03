<?php

namespace App\Http\Controllers;

use App\Interfaces\ConverterInterface;
use Illuminate\Http\Request;

class ConverterController extends Controller
{
    public function convert(Request $request, ConverterInterface $file)
    {
        $csv = $file->importFile($request);
        return $file->convertFile($csv);
    }
}
