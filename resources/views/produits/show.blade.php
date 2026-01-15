@extends('layouts.app')

@section('title', $piece->nom . ' - Détail produit')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        {{-- Image / visuel --}}
        <div>
            <<div class="w-full h-64 bg-gray-100 rounded flex items-center justify-center">
    @if($piece->image)
        <img src="{{ asset('images/pieces/' . $piece->image) }}"
             alt="{{ $piece->nom }} ({{ $piece->reference }})"
             class="h-full object-contain">
    @else
        <span class="text-gray-400 text-sm">
            Aucune image disponible
        </span>
    @endif
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

            {{-- Formulaire panier + retour --}}
            <form action="{{ route('cart.add', $piece) }}" method="POST" class="mt-6 flex flex-wrap gap-3 items-center">
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

            {{-- Bouton de test manuel pour le tracking (optionnel) --}}
            @auth
                <form action="{{ route('recommendations.api.track') }}" method="POST" class="mt-4">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $piece->id }}">
                    <input type="hidden" name="interaction_type" value="view">
                    <button type="submit" class="px-3 py-1 bg-blue-600 text-white text-sm rounded">
                        TEST: enregistrer vue
                    </button>
                </form>
            @endauth
        </div>
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

    {{-- Tracking automatique de la vue produit --}}
    @auth
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                fetch("{{ route('recommendations.api.track') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: {{ $piece->id }},
                        interaction_type: 'view',
                        context: 'product_page',
                    }),
                }).catch(error => {
                    console.error('Erreur tracking interaction:', error);
                });
            });
        </script>
    @endauth
@endsection
