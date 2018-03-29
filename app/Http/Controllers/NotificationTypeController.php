<?php

namespace App\Http\Controllers;

use App\NotificationType;
use Illuminate\Http\Request;

class NotificationTypeController extends Controller
{
    public function index()
    {
        $notificationTypes = NotificationType::all();

        return response()->json(['data' => $notificationTypes], 200);
    }
}
