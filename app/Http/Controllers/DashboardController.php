<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $co = Person::operationalClients()->where('type_person_id', 1)->count();
        $cp = Person::operationalClients()->where('type_person_id', 2)->count();
        $personToken = Person::operationalClients()->whereNotNull('token')->where('token', '!=', '')->count();

        return view('dashboard', compact('co', 'cp', 'personToken'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
