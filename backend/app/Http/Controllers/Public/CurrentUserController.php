<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CurrentUserController extends Controller
{
    public function show(Request $request)
    {
        return $request->user();
    }
}
