<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index()
    {
        $proveedores = Proveedor::orderBy('nombre')->paginate(10);
        return view('proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required','string','max:120'],
            'documento' => ['nullable','string','max:50'],
            'telefono' => ['nullable','string','max:50'],
            'email' => ['nullable','email','max:120'],
            'direccion' => ['nullable','string','max:180'],
            'ciudad' => ['nullable','string','max:120'],
        ]);

        Proveedor::create($data);
        return redirect()->route('proveedores.index')->with('ok','Proveedor creado');
    }

    public function edit(Proveedor $proveedor)
    {
        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $data = $request->validate([
            'nombre' => ['required','string','max:120'],
            'documento' => ['nullable','string','max:50'],
            'telefono' => ['nullable','string','max:50'],
            'email' => ['nullable','email','max:120'],
            'direccion' => ['nullable','string','max:180'],
            'ciudad' => ['nullable','string','max:120'],
        ]);

        $proveedor->update($data);
        return redirect()->route('proveedores.index')->with('ok','Proveedor actualizado');
    }

    public function destroy(Proveedor $proveedor)
    {
        $proveedor->delete();
        return back()->with('ok','Proveedor eliminado');
    }
}
