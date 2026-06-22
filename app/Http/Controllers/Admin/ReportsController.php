<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
class ReportsController extends Controller
{
    public function facebook_catelogue() {
        return view('backEnd.reports.facebook_catelogue');
    }
}
