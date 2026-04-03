<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = User::where('role', 'karyawan')->with(['department', 'faceDescriptors', 'workSchedules'])->latest()->paginate(10);
        return view('admin.employees.index', compact('employees'));
    }

    public function create()
    {
        $departments = Department::all();
        return view('admin.employees.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'position' => 'nullable|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'phone' => 'nullable|string|max:20',
        ]);

        $validated['role'] = 'karyawan';
        $validated['employee_id'] = User::generateEmployeeId();

        User::create($validated);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function edit(User $employee)
    {
        $departments = Department::all();
        return view('admin.employees.edit', compact('employee', 'departments'));
    }

    public function update(Request $request, User $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $employee->id,
            'password' => 'nullable|min:6|confirmed',
            'position' => 'nullable|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'phone' => 'nullable|string|max:20',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $employee->update($validated);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(User $employee)
    {
        $employee->delete();
        return redirect()->route('admin.employees.index')
            ->with('success', 'Karyawan berhasil dihapus.');
    }
}
