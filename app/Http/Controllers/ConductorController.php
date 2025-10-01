<?php

namespace App\Http\Controllers;

use App\Models\Conductor;
use App\Models\Envio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConductorController extends Controller
{
    public function index()
    {
        // tarjetas + necesitamos los envíos no asignados para el modal
        $conductores = Conductor::withCount('envios')->latest()->paginate(12);
        $enviosNoAsignados = Envio::whereNull('conductor_id')->orderByDesc('id')->get(['id','origen','destino']);
        return view('conductores.index', compact('conductores','enviosNoAsignados'));
    }

    public function create()
    {
        return view('conductores.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        if ($request->hasFile('foto')) {
            // Guardamos en storage/app/public/conductores
            $data['foto'] = $request->file('foto')->store('conductores', 'public');
        }

        Conductor::create($data);
        return redirect()->route('conductores.index')->with('ok','Conductor creado');
    }

    public function edit(Conductor $conductor)
    {
        return view('conductores.edit', compact('conductor'));
    }

    public function update(Request $request, Conductor $conductor)
    {
        $data = $this->validateData($request);

        if ($request->hasFile('foto')) {
            if ($conductor->foto) Storage::disk('public')->delete($conductor->foto);
            $data['foto'] = $request->file('foto')->store('conductores', 'public');
        }

        $conductor->update($data);
        return redirect()->route('conductores.index')->with('ok','Conductor actualizado');
    }

    public function destroy(Conductor $conductor)
    {
        if ($conductor->foto) Storage::disk('public')->delete($conductor->foto);
        $conductor->delete();
        return redirect()->route('conductores.index')->with('ok','Conductor eliminado');
    }

    // Asignar envío (desde el modal)
    public function asignarEnvio(Request $request, Conductor $conductor)
    {
        $data = $request->validate([
            'envio_id'     => ['required','exists:envios,id'],
            'origen'       => ['required','string','max:255'],
            'destino'      => ['required','string','max:255'],
            'hora_salida'  => ['required','date'],
            'hora_llegada' => ['nullable','date'],
        ]);

        $envio = Envio::findOrFail($data['envio_id']);
        $envio->conductor_id = $conductor->id;
        $envio->origen       = $data['origen'];
        $envio->destino      = $data['destino'];
        $envio->hora_salida  = $data['hora_salida'];
        if (!empty($data['hora_llegada'])) $envio->hora_llegada = $data['hora_llegada'];
        $envio->estado = 'en_camino';
        $envio->save();

        return back()->with('ok','Envío asignado');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'nombre'         => ['required','string','max:120'],
            'apellido'       => ['required','string','max:120'],
            'tipo_documento' => ['required','string','max:10'],
            'documento'      => ['required','string','max:50','unique:conductores,documento,' . ($request->route('conductor')->id ?? 'null')],
            'celular'        => ['nullable','string','max:30'],
            'foto'           => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'descripcion'    => ['nullable','string'],
        ]);
    }
}
