<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domains\Responders\Models\Hospital;
use Illuminate\Support\Facades\Auth;

class HospitalController extends Controller
{
    public function update(Request $request)
    {
        $hospital = Hospital::where('user_id', Auth::id())->first();
        
        if (!$hospital) {
            return redirect()->back()->with('error', 'Hospital record not found.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:20',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'available_beds' => 'required|integer|min:0',
            'icu_beds' => 'required|integer|min:0',
        ]);

        $hospital->update([
            'name' => $request->name,
            'contact_phone' => $request->contact_phone,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'available_beds' => $request->available_beds,
            'icu_beds' => $request->icu_beds,
        ]);

        return redirect()->back()->with('success', 'Facility profile updated successfully.');
    }
}
