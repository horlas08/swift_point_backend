<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNotificationRequest;
use App\Http\Requests\UpdateNotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        return $user->notifications;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNotificationRequest $request)
    {
        //
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        foreach ($user->notifications as $notification) {
            $notification->read = true;
            $notification->save();
        }
    }
    public function markAsRead(Notification $notification)
    {
        $notification->read = true;
        $notification->save();
    }

    /**
     * Display the specified resource.
     */
    public function show(Notification $notification)
    {
        return new NotificationResource($notification);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNotificationRequest $request, Notification $notification)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $user = $request->user();
        $user->notifications->delete();

        return new JsonResponse([
            'message' => 'Successful',
        ]);
    }
}
