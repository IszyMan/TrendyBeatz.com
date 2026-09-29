<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $roleId = (int) auth()->user()->roleid;

        $isAdministrator = $roleId === (int) config('admin.administrator');
        $isStandard = $roleId === (int) config('admin.standard');
        $isEditor = $roleId === (int) config('admin.editor');

        abort_unless(
            $isAdministrator || $isStandard || $isEditor,
            403
        );

        return view('admin.dashboard', [
            'listingCount' => $isAdministrator || $isStandard
                ? DB::table('listing')->count()
                : null,

            'mixCount' => $isAdministrator || $isStandard
                ? DB::table('dj_mixs')->count()
                : null,

            'blogCount' => $isAdministrator || $isEditor
                ? DB::table('blogs')->count()
                : null,
        ]);
    }
}