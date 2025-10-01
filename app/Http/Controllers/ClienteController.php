<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::orderBy('nombre')->paginate(10);
        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
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

        Cliente::create($data);
        return redirect()->route('clientes.index')->with('ok','Cliente creado');
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'nombre' => ['required','string','max:120'],
            'documento' => ['nullable','string','max:50'],
            'telefono' => ['nullable','string','max:50'],
            'email' => ['nullable','email','max:120'],
            'direccion' => ['nullable','string','max:180'],
            'ciudad' => ['nullable','string','max:120'],
        ]);

        $cliente->update($data);
        return redirect()->route('clientes.index')->with('ok','Cliente actualizado');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return back()->with('ok','Cliente eliminado');
    }
}
