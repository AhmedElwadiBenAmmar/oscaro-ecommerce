@extends('layouts.app')

@section('title', $piece->nom . ' - Détail produit')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        {{-- Image / visuel --}}
        <div>
            <div class="w-full h-64 bg-gray-100 rounded flex items-center justify-center">
                <img src="{{ asset('storage/filtre-huile.png') }}"
                     alt="Filtre à huile {{ $piece->reference }}"
                     class="h-full object-contain">
            </div>

            @if($piece->categorie)
                <p class="mt-4 text-sm text-gray-500">
                    Catégorie :
                    <span class="font-medium text-gray-800">{{ $piece->categorie }}</span>
                </p>
            @endif
        </div>

        {{-- Informations produit --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $piece->nom }}
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Référence : {{ $piece->reference }}
            </p>

            <p class="mt-4 text-3xl font-bold text-red-600">
                {{ number_format($piece->prix, 2, ',', ' ') }} €
            </p>

            <p class="mt-2 text-sm">
                @if($piece->stock > 0)
                    <span class="text-green-600 font-medium">
                        En stock ({{ $piece->stock }} disponibles)
                    </span>
                @else
                    <span class="text-red-600 font-medium">
                        Rupture de stock
                    </span>
                @endif
            </p>

            <div class="mt-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">
                    Description
                </h2>
                <p class="text-sm text-gray-700 leading-relaxed">
                    {{ $piece->description ?: 'Aucune description détaillée pour cette pièce.' }}
                </p>
            </div>

            {{-- Formulaire panier --}}
            <form action="{{ route('cart.add', $piece) }}" method="POST" class="mt-6 flex flex-wrap gap-3">
                @csrf
                <button
                    type="submit"
                    class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded hover:bg-red-700">
                    Ajouter au panier
                </button>

                <a href="{{ route('produits.index') }}"
                   class="text-sm text-gray-700 hover:underline">
                    ← Retour au catalogue
                </a>
            </form>
        </div>
    </div>
@endsection
