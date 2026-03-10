<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * لوحة التيننت — مكتب المحاماة.
 * نفس قالب الأدمن لكن منفصلة وفارغة حالياً؛ لاحقاً يُربط بدومين التيننت والموقع الخارجي.
 */
class OfficeDashboardController extends Controller
{
    public function __invoke(): View
    {
        $pageConfigs = ['myLayout' => 'office', 'customizerHide' => true];
        return view('office.dashboard', ['pageConfigs' => $pageConfigs]);
    }
}
