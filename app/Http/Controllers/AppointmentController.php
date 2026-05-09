<?php

namespace Modules\Appointment\Http\Controllers;

use App\Http\Controllers\Controller;

class AppointmentController extends Controller
{
    public function index()
    {
        return view('appointment::index');
    }
}
