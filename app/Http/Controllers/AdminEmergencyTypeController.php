<?php

namespace App\Http\Controllers;

use App\Domains\Emergencies\Models\EmergencyType;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminEmergencyTypeController extends Controller
{
    protected function ensureAdmin(): void
    {
        abort_unless(Auth::check() && Auth::user()->role === 'admin', 403);
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $type = EmergencyType::create($data);

        AdminActivityLog::record('emergency_type_created', "Created emergency type \"{$type->name}\"", $type);

        return redirect()->back()->with('success', 'Emergency type added.');
    }

    public function update(Request $request, $id)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $type = EmergencyType::findOrFail($id);
        $type->update($data);

        AdminActivityLog::record('emergency_type_updated', "Updated emergency type \"{$type->name}\"", $type);

        return redirect()->back()->with('success', 'Emergency type updated.');
    }

    public function destroy($id)
    {
        $this->ensureAdmin();

        $type = EmergencyType::findOrFail($id);

        if ($type->emergencies()->exists()) {
            return redirect()->back()->with('error', "Can't delete \"{$type->name}\" — it has emergencies linked to it.");
        }

        $name = $type->name;
        $type->delete();

        AdminActivityLog::record('emergency_type_deleted', "Deleted emergency type \"{$name}\"");

        return redirect()->back()->with('success', 'Emergency type deleted.');
    }
}
