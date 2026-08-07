<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class AuthController extends BaseController
{
    public function login()
    {
        return view('admin/login');
    }

    public function logout()
    {
        // Clear session if using sessions
        session()->destroy();
        return redirect()->to('/admin/login');
    }
}
