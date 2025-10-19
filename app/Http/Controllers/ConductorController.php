<?php

namespace App\Http\Controllers;

use App\Models\Conductor;
use App\Models\Envio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ConductorController extends Controller
{
    public function index()
    {
        // Tarjetas + envíos sin asignar (con nombre del cliente para mostrar en el modal)
        $conductores = Conductor::withCount('envios')->latest()->paginate(12);

        $enviosNoAsignados = Envio::with('cliente:id,nombre')
            ->whereNull('conductor_id')
            ->orderByDesc('id')
            ->get(['id', 'cliente_id']); // sólo lo necesario

        return view('conductores.index', compact('conductores', 'enviosNoAsignados'));
    }

    public function create()
    {
        return view('conductores.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('conductores', 'public');
        }

        Conductor::create($data);

        return redirect()->route('conductores.index')->with('ok', 'Conductor creado');
    }

    public function edit(Conductor $conductor)
    {
        return view('conductores.edit', compact('conductor'));
    }

    public function update(Request $request, Conductor $conductor)
    {
        $data = $this->validateData($request, $conductor->id);

        if ($request->hasFile('foto')) {
            if ($conductor->foto) {
                Storage::disk('public')->delete($conductor->foto);
            }
            $data['foto'] = $request->file('foto')->store('conductores', 'public');
        }

        $conductor->update($data);

        return redirect()->route('conductores.index')->with('ok', 'Conductor actualizado');
    }

    public function destroy(Conductor $conductor)
    {
        if ($conductor->foto) {
            Storage::disk('public')->delete($conductor->foto);
        }
        $conductor->delete();

        return redirect()->route('conductores.index')->with('ok', 'Conductor eliminado');
    }

    /**
     * Asigna un envío existente (sin conductor) a este conductor.
     */
    public function asignarEnvio(Request $request, Conductor $conductor)
    {
        $data = $request->validate([
            'envio_id'     => ['required', 'exists:envios,id'],
            'origen'       => ['required', 'string', 'max:255'],
            'destino'      => ['required', 'string', 'max:255'],
            'hora_salida'  => ['required', 'date'],
            'hora_llegada' => ['nullable', 'date'],
        ]);

        $envio = Envio::findOrFail($data['envio_id']);

        // (opcional) Evitar reasignar si ya tiene conductor
        if ($envio->conductor_id) {
            return back()->with('ok', 'Este envío ya tiene un conductor asignado.');
        }

        $envio->conductor_id = $conductor->id;
        $envio->origen       = $data['origen'];
        $envio->destino      = $data['destino'];
        $envio->hora_salida  = $data['hora_salida'];
        if (!empty($data['hora_llegada'])) {
            $envio->hora_llegada = $data['hora_llegada'];
        }
        $envio->estado = 'en_camino';
        $envio->save();

        return back()->with('ok', 'Envío asignado');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'nombre'         => ['required', 'string', 'max:120'],
            'apellido'       => ['required', 'string', 'max:120'],
            'tipo_documento' => ['required', 'string', 'max:10'],
            'documento'      => [
                'required',
                'string',
                'max:50',
                Rule::unique('conductores', 'documento')->ignore($ignoreId),
            ],
            'celular'        => ['nullable', 'string', 'max:30'],
            'foto'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'descripcion'    => ['nullable', 'string'],
        ]);
    }
}
