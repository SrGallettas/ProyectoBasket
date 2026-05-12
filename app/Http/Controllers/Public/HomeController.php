<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Basket\HomePageService;

class HomeController extends Controller
{
    public function __construct(private readonly HomePageService $homePageService) {}

    public function inicio()
    {
        return view('basket.inicio', $this->homePageService->buildViewData());
    }

    public function contacto()
    {
        return view('basket.contacto');
    }
}
