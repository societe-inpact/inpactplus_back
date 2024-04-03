<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface ConverterInterface
{
    public function importFile(Request $request);

    public function convertFile(Request $request);
}
