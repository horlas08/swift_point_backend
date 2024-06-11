<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStreakRequest;
use App\Http\Requests\UpdateStreakRequest;
use App\Models\Streak;

class StreakController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Streak::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStreakRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Streak $streak)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStreakRequest $request, Streak $streak)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Streak $streak)
    {
        //
    }
}
