<?php

namespace App\Http\Controllers;
use App\Models\Doctor;

use Illuminate\Http\Request;

class DoctorController extends Controller
{
     public function index()
    {
        return response()->json(Doctor::latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'unique:doctors,user_id'],
            'specialty' => ['required', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:9'],
        ]);

        $doctor = Doctor::create($validated);

        return response()->json($doctor, 201);
    }

    public function show(Doctor $doctor)
    {
        return response()->json($doctor);
    }

    public function update(Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'user_id' => ['sometimes', 'integer', 'unique:doctors,user_id,' . $doctor->id],
            'specialty' => ['sometimes', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:9'],
        ]);

        $doctor->update($validated);

        return response()->json($doctor);
    }

    public function destroy(Doctor $doctor)
    {
        $doctor->delete();

        return response()->json([
            'message' => 'Doctor eliminado correctamente'
        ]);
    }
}

