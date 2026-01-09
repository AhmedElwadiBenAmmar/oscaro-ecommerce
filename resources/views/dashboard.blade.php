@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Dashboard</h1>

    @if (session('status'))
        <div class="mb-4 px-4 py-2 bg-green-100 text-green-800 rounded">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Nombre total de pièces --}}
        <div class="bg-white shadow-sm rounded p-6">
            <h3 class="text-sm font-medium text-gray-500">
                Nombre de pièces
            </h3>
            <p class="mt-2 text-2xl font-bold text-gray-900">
                {{ \App\Models\Piece::count() }}
            </p>
        </div>

        {{-- Stock total --}}
        <div class="bg-white shadow-sm rounded p-6">
            <h3 class="text-sm font-medium text-gray-500">
                Stock total
            </h3>
            <p class="mt-2 text-2xl font-bold text-gray-900">
                {{ \App\Models\Piece::sum('stock') }}
            </p>
        </div>

        {{-- Dernière pièce ajoutée --}}
        <div class="bg-white shadow-sm rounded p-6">
            <h3 class="text-sm font-medium text-gray-500">
                Dernière pièce ajoutée
            </h3>
            @php
                $lastPiece = \App\Models\Piece::latest()->first();
            @endphp
            <p class="mt-2 text-lg font-semibold text-gray-900">
                {{ $lastPiece?->nom ?? 'Aucune pour le moment' }}
            </p>
            @if($lastPiece)
                <p class="text-sm text-gray-500 mt-1">
                    Réf. {{ $lastPiece->reference }} ·
                    {{ number_format($lastPiece->prix, 2, ',', ' ') }} € ·
                    Stock {{ $lastPiece->stock }}
                </p>
            @endif
        </div>
    </div>

    {{-- Actions rapides --}}
    <div class="bg-white shadow-sm rounded p-6 mt-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            Actions rapides
        </h3>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('pieces.index') }}"
               class="px-4 py-2 bg-slate-700 text-white text-sm font-semibold rounded hover:bg-slate-800">
                Voir les pièces
            </a>
            <a href="{{ route('pieces.create') }}"
               class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded hover:bg-red-700">
                Ajouter une pièce
            </a>
        </div>
    </div>
@endsection
