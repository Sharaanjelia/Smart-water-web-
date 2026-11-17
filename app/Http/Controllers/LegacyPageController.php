<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LegacyPageController extends Controller
{
    public function show(string $page): View
    {
        $pageModules = [
            'admin' => 'php-admin',
            'akademi-air' => 'php-akademi-air',
            'beranda' => 'php-beranda',
            'chat-ai' => 'php-chat-ai',
            'login-daftar' => 'php-login-daftar',
            'pusat-layanan' => 'php-pusat-layanan',
            'tantangan' => 'php-tantangan',
            'tentang' => 'php-tentang',
            'user' => 'php-user',
        ];

        abort_unless(isset($pageModules[$page]), 404);

        $module = $pageModules[$page];
        $baseUrl = rtrim(request()->getBaseUrl(), '/');
        $legacyBaseHref = $baseUrl . '/pages/' . $module . '/';
        $legacyPagePath = resource_path('views/pages/' . $page);
        $legacySharedPath = resource_path('views/shared');
        $SWC_PAGE_MODULE = $module;
        $SWC_PAGE_URL = $baseUrl . '/' . $page;

        require base_path('app/LegacyConfig.php');

        $viewData = get_defined_vars();
        unset(
            $viewData['this'],
            $viewData['pageModules'],
            $viewData['page'],
            $viewData['module'],
            $viewData['baseUrl'],
            $viewData['viewData']
        );

        return view('pages.' . $page, $viewData);
    }
}
