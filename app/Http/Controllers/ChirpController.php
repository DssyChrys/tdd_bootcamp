<?php

namespace App\Http\Controllers;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request; 
use App\Models\Chirp;
use Illuminate\Support\Facades\Gate;

class ChirpController extends Controller

{

    public function index(): View
    {
        $chirps = Chirp::where('created_at', '>=', now()->subDays(7))
        ->latest()
        ->get();
        
        return view('chirps.index', [

            'chirps' => Chirp::with('user')->latest()->get(),

        ]);
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

     public function store(Request $request): RedirectResponse
    {

        $user = $request->user();

        if ($user->chirps()->count() >= 10) {
            return redirect()->back()->withErrors(['message' => 'Vous ne pouvez pas créer plus de 10 chirps.']);
        }

        
        $validated = $request->validate([

            'message' => 'required|string|max:255',

        ]);

 

        $request->user()->chirps()->create($validated);

 

        return redirect(route('chirps.index'));
    }

 

    /**

     * Display the specified resource.

     */

    public function show(Chirp $chirp)

    {

        //

    }

 

    /**

     * Show the form for editing the specified resource.

     */

     public function edit(Chirp $chirp): View
    {
        Gate::authorize('update', $chirp);

 

        return view('chirps.edit', [

            'chirp' => $chirp,

        ]);
    }

 

    /**

     * Update the specified resource in storage.

     */

     public function update(Request $request, Chirp $chirp): RedirectResponse
    {
        Gate::authorize('update', $chirp);

 

        $validated = $request->validate([

            'message' => 'required|string|max:255',

        ]);

 

        $chirp->update($validated);

 

        return redirect(route('chirps.index'));
    }

 

    /**

     * Remove the specified resource from storage.

     */

     public function destroy(Chirp $chirp): RedirectResponse
    {
        Gate::authorize('delete', $chirp);

 

        $chirp->delete();

 

        return redirect(route('chirps.index'));

    }

 

}