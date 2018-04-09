<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class WarningRecordController extends Controller
{
    public function index()
    {
        $warningRecords = Auth::user()->warningRecords;

        return response()->json(['data' => $warningRecords], 200);
    }
}
