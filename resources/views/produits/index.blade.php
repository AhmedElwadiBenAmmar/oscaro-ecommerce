@extends('layouts.app')

@section('title', 'Catalogue des pièces')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Catalogue des pièces</h1>

    @if ($pieces->isEmpty())
        <p class="text-gray-600">Aucune pièce disponible pour le moment.</p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($pieces as $piece)
                <div class="bg-white shadow-sm rounded-lg p-4 flex flex-col">
                    <div class="h-32 mb-3 bg-gray-100 rounded flex items-center justify-center text-gray-400 text-sm">
                        Image produit
                    </div>

                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ $piece->nom }}
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Réf. {{ $piece->reference }}
                    </p>

                    @if($piece->categorie)
                        <p class="text-xs text-gray-400 mt-1">
                            Catégorie : {{ $piece->categorie }}
                        </p>
                    @endif

                    <p class="mt-3 text-xl font-bold text-red-600">
                        {{ number_format($piece->prix, 2, ',', ' ') }} €
                    </p>

                    <p class="text-sm text-gray-500 mt-1">
                        @if($piece->stock > 0)
                            En stock ({{ $piece->stock }})
                        @else
                            <span class="text-red-600">Rupture de stock</span>
                        @endif
                    </p>

                    <div class="mt-4 flex justify-between items-center">
                        <a href="{{ route('produits.show', $piece) }}"
                           class="text-sm text-blue-600 hover:underline">
                            Voir la pièce
                        </a>

                        {{-- Bouton pour le panier que l'on implémentera plus tard --}}
                        <button
                            class="px-3 py-1 bg-red-600 text-white text-xs font-semibold rounded hover:bg-red-700"
                            type="button"
                            disabled>
                            Ajouter au panier
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $pieces->links() }}
        </div>
    @endif
@endsection
