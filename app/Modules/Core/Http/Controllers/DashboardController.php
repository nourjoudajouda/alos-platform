<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * عرض صفحة الداشبورد من موديول Core.
     */
    public function __invoke()
    {
        return view('core::dashboard');
    }
}
