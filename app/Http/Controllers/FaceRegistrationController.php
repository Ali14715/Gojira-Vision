<?php

namespace App\Http\Controllers;

use App\Models\FaceDescriptor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FaceRegistrationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $descriptorCount = $user->faceDescriptors()->count();
        return view('karyawan.face-register', compact('user', 'descriptorCount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'descriptors' => 'required|array|min:1',
            'descriptors.*' => 'required|array',
        ]);

        $user = Auth::user();

        // Hapus descriptor lama jika ada (re-register)
        $user->faceDescriptors()->delete();

        foreach ($request->descriptors as $index => $descriptor) {
            FaceDescriptor::create([
                'user_id' => $user->id,
                'descriptor' => $descriptor,
                'label' => $user->name . '_' . ($index + 1),
            ]);
        }

        $user->update(['face_registered' => true]);

        return response()->json(['message' => 'Wajah berhasil didaftarkan.']);
    }

    public function allDescriptors()
    {
        $descriptors = FaceDescriptor::with('user:id,name')->get()
            ->map(function ($fd) {
                return [
                    'user_id' => $fd->user_id,
                    'name' => $fd->user->name,
                    'descriptor' => $fd->descriptor,
                ];
            });

        return response()->json($descriptors);
    }
}
