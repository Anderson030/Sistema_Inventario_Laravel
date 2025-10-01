<?php

namespace App\Http\Controllers;

use App\Models\Envio;
use Illuminate\Http\Request;

class EnvioController extends Controller
{
    public function index()
    {
        $envios = Envio::with('conductor')->latest()->paginate(10);
        $totalGanancia = Envio::sum('ganancia_total');
        return view('envios.index', compact('envios', 'totalGanancia'));
    }

    public function create() { return view('envios.create'); }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        foreach (['valor_envio','valor_bulto','ganancia_total','pago_contado','pago_a_plazo'] as $k) {
            $data[$k] = $this->parseMoney($data[$k] ?? 0);
        }
        Envio::create($data);
        return redirect()->route('envios.index')->with('ok','Envío registrado');
    }

    public function edit(Envio $envio)
    {
        return view('envios.edit', compact('envio'));
    }

    public function update(Request $request, Envio $envio)
    {
        $data = $this->validateData($request);
        foreach (['valor_envio','valor_bulto','ganancia_total','pago_contado','pago_a_plazo'] as $k) {
            $data[$k] = $this->parseMoney($data[$k] ?? 0);
        }
        $envio->update($data);
        return redirect()->route('envios.index')->with('ok','Envío actualizado');
    }

    public function destroy(Envio $envio)
    {
        $envio->delete();
        return redirect()->route('envios.index')->with('ok','Envío eliminado');
    }

    // Confirmar entrega
    public function entregar(Envio $envio, Request $request)
    {
        $envio->estado = 'entregado';
        // opcional: si envían hora_llegada, se guarda
        if ($request->filled('hora_llegada')) {
            $envio->hora_llegada = $request->date('hora_llegada');
        }
        $envio->save();
        return back()->with('ok', 'Entrega confirmada');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'valor_envio'    => ['required','string'],
            'valor_bulto'    => ['required','string'],
            'ganancia_total' => ['required','string'],
            'pago_contado'   => ['nullable','string'],
            'pago_a_plazo'   => ['nullable','string'],
            'numero_bulto'   => ['required','integer','min:1'],
            'fecha_contado'  => ['nullable','date'],
            'fecha_plazo'    => ['nullable','date'],
            'fecha_envio'    => ['nullable','date'],
            // los de asignación se gestionan en ConductorController
        ]);
    }

    private function parseMoney($value): int
    {
        if ($value === null || $value === '') return 0;
        $digits = preg_replace('/[^\d]/', '', (string)$value);
        return (int) $digits;
    }
}
