@extends('layouts.app')

@section('title', 'Détail de la pièce')

@section('content')
    <a href="{{ route('pieces.index') }}" class="text-sm text-gray-500 hover:underline">
        ← Retour à la liste
    </a>

    <div class="bg-white shadow sm:rounded-lg p-6 mt-4 space-y-4">
        {{-- Détails de la pièce (ton code actuel) --}}
        {{-- ... tout ce que tu as déjà ... --}}
    </div>

    {{-- Pièces similaires --}}
    @if(isset($similarPieces) && $similarPieces->count())
        <div class="mt-8">
            <h2 class="text-lg font-semibold mb-3">Pièces similaires</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($similarPieces as $p)
                    <div class="bg-white shadow rounded p-4 text-sm">
                        <p class="font-medium">{{ $p->nom }}</p>
                        <p class="text-gray-500">Réf : {{ $p->reference }}</p>
                        <p class="text-gray-900 font-semibold mt-1">
                            {{ number_format($p->prix, 2, ',', ' ') }} €
                        </p>
                        <a href="{{ route('produits.show', $p) }}"
                           class="text-red-600 text-xs mt-2 inline-block">
                            Voir la pièce
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Produits complémentaires --}}
    @if(isset($complementary) && $complementary->count())
        <div class="mt-8">
            <h2 class="text-lg font-semibold mb-3">Produits complémentaires</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($complementary as $p)
                    <div class="bg-white shadow rounded p-4 text-sm">
                        <p class="font-medium">{{ $p->nom }}</p>
                        <p class="text-gray-500">Réf : {{ $p->reference }}</p>
                        <p class="text-gray-900 font-semibold mt-1">
                            {{ number_format($p->prix, 2, ',', ' ') }} €
                        </p>
                        <a href="{{ route('produits.show', $p) }}"
                           class="text-red-600 text-xs mt-2 inline-block">
                            Voir la pièce
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection
