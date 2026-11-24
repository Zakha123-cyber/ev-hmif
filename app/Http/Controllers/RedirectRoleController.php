<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RedirectRoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private $param;
    public function index(){
        $roleUser = \Auth::user()->roles->pluck('name')[0];
        if ($roleUser == 'Admin') {
            return redirect('/back-admin-logged/dashboard');
        } elseif ($roleUser == 'Participant') {
            return redirect('/vote');
        }else {
            return redirect('/logout');
        }
    }
}
