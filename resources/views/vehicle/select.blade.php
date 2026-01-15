@extends('layouts.app')

@section('title', 'Sélection du véhicule')

@section('content')
<h1 class="text-2xl font-bold mb-6">Sélectionner votre véhicule</h1>

<div class="bg-white rounded shadow p-6 max-w-3xl">
    <form method="POST" action="{{ route('vehicle.select') }}" id="vehicle-form" class="space-y-4">
        @csrf

        {{-- Marque --}}
        <div>
            <label class="block text-sm font-medium mb-1">Marque</label>
            <select id="make" class="w-full border rounded px-3 py-2">
                <option value="">Choisissez une marque</option>
                @foreach($makes as $make)
                    <option value="{{ $make->id }}">{{ $make->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Modèle --}}
        <div>
            <label class="block text-sm font-medium mb-1">Modèle</label>
            <select id="model" class="w-full border rounded px-3 py-2" disabled>
                <option value="">Choisissez un modèle</option>
            </select>
        </div>

        {{-- Motorisation --}}
        <div>
            <label class="block text-sm font-medium mb-1">Motorisation</label>
            <select id="engine" name="engine_id" class="w-full border rounded px-3 py-2" disabled>
                <option value="">Choisissez une motorisation</option>
            </select>
            @error('engine_id')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="pt-2">
            <button type="submit"
                    id="vehicle-submit"
                    class="bg-red-600 text-white px-4 py-2 rounded text-sm font-semibold disabled:opacity-50"
                    disabled>
                Valider le véhicule
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const makeSelect   = document.getElementById('make');
    const modelSelect  = document.getElementById('model');
    const engineSelect = document.getElementById('engine');
    const submitBtn    = document.getElementById('vehicle-submit');

    if (!makeSelect || !modelSelect || !engineSelect || !submitBtn) {
        return;
    }

    makeSelect.addEventListener('change', async () => {
        modelSelect.innerHTML  = '<option value="">Choisissez un modèle</option>';
        engineSelect.innerHTML = '<option value="">Choisissez une motorisation</option>';
        modelSelect.disabled   = true;
        engineSelect.disabled  = true;
        submitBtn.disabled     = true;

        if (!makeSelect.value) return;

        const res  = await fetch(`{{ route('vehicle.models') }}?make_id=${makeSelect.value}`);
        const data = await res.json();

        data.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.id;
            opt.textContent = m.name;
            modelSelect.appendChild(opt);
        });

        modelSelect.disabled = false;
    });

    modelSelect.addEventListener('change', async () => {
        engineSelect.innerHTML = '<option value="">Choisissez une motorisation</option>';
        engineSelect.disabled  = true;
        submitBtn.disabled     = true;

        if (!modelSelect.value) return;

        const res  = await fetch(`{{ route('vehicle.engines') }}?model_id=${modelSelect.value}`);
        const data = await res.json();

        data.forEach(e => {
            const opt = document.createElement('option');
            opt.value = e.id;
            opt.textContent = e.name;
            engineSelect.appendChild(opt);
        });

        engineSelect.disabled = false;
    });

    engineSelect.addEventListener('change', () => {
        submitBtn.disabled = !engineSelect.value;
    });
});
</script>
@endpush
@endsection


