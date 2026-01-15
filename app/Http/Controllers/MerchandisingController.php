<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Merchandising;

class MerchandisingController extends Controller
{
    
    // Muestra la lista de noticias
    public function index()
    {
        $merchandising = Merchandising::orderBy('nombre', 'asc')->paginate(10);
        return view('admin.merchandising.index', compact('merchandising'));
    }

}
