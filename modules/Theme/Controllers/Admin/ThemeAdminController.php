<?php

namespace Theme\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use Theme\Libraries\ThemeConfig;
use Theme\Models\ThemeModel;

class ThemeAdminController extends BaseAdminController
{
    protected ThemeModel $themeModel;

    public function __construct()
    {
        $this->themeModel = new ThemeModel();
    }

    public function index()
    {
        if ($redirect = $this->requirePermission('themes.view')) return $redirect;

        $data['active'] = 'themes';
        $data['title']  = 'Themes';
        $data['items']  = $this->themeModel->findAll();
        $data['canActivate'] = $this->can('themes.activate');

        return $this->render('admin/themes/index', $data);
    }

    public function activate(int $id)
    {
        if ($redirect = $this->requirePermission('themes.activate')) return $redirect;

        $item = $this->themeModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/themes')->with('error', 'Theme not found.');
        }

        $this->themeModel->activate($id);

        return redirect()->to('/admin/themes')->with('success', 'Theme activated successfully.');
    }

    public function config(int $id)
    {
        if ($redirect = $this->requirePermission('themes.view')) return $redirect;

        $item = $this->themeModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/themes')->with('error', 'Theme not found.');
        }

        $configLib = new ThemeConfig();
        $schema    = $configLib->schema($item['slug'] ?? 'default');
        $saved     = $configLib->saved($item);

        $data['active'] = 'themes';
        $data['title']  = 'Configure ' . ($item['name'] ?? 'Theme');
        $data['item']   = $item;
        $data['fields'] = $schema;
        $data['values'] = $saved;
        $data['hasSchema'] = ! empty($schema);

        return $this->render('admin/themes/config', $data);
    }

    public function saveConfig(int $id)
    {
        if ($redirect = $this->requirePermission('themes.activate')) return $redirect;

        $item = $this->themeModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/themes')->with('error', 'Theme not found.');
        }

        $values = (array) $this->request->getPost('config');

        if (! (new ThemeConfig())->save($id, $values)) {
            return redirect()->back()->with('error', 'Could not save theme configuration.');
        }

        $this->logActivity('updated', 'theme', $id, "Updated theme config: {$item['name']}");

        return redirect()->to('/admin/themes')->with('success', 'Theme configuration saved.');
    }
}
