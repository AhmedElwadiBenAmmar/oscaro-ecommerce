@extends('layouts.app')

@section('title', 'Catalogue des pièces')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Catalogue des pièces</h1>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- Sidebar filtres --}}
        <aside class="lg:col-span-1 space-y-4">
            <form method="GET" action="{{ route('produits.index') }}" class="space-y-4">

                {{-- Recherche texte (déjà gérée aussi dans le header, mais on la reflète ici) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
                    <input type="text"
                           name="q"
                           value="{{ $filters['q'] }}"
                           class="w-full px-3 py-2 border rounded"
                           placeholder="Nom, réf, description...">
                </div>

                {{-- Catégorie --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                    <select name="category" class="w-full px-3 py-2 border rounded">
                        <option value="">Toutes</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}"
                                @selected($filters['category'] == $category->id)>
                                {{ $category->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Côté --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Côté</label>
                    <select name="side" class="w-full px-3 py-2 border rounded">
                        <option value="">Indifférent</option>
                        <option value="left" @selected($filters['side'] === 'left')>Gauche</option>
                        <option value="right" @selected($filters['side'] === 'right')>Droite</option>
                        <option value="both" @selected($filters['side'] === 'both')>Les deux</option>
                    </select>
                </div>

                {{-- Position --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                    <select name="position" class="w-full px-3 py-2 border rounded">
                        <option value="">Indifférent</option>
                        <option value="front" @selected($filters['position'] === 'front')>Avant</option>
                        <option value="rear" @selected($filters['position'] === 'rear')>Arrière</option>
                    </select>
                </div>

                {{-- Prix --}}
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Prix min (€)</label>
                        <input type="number" step="0.01" name="min_price"
                               value="{{ $filters['min_price'] }}"
                               class="w-full px-3 py-2 border rounded">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Prix max (€)</label>
                        <input type="number" step="0.01" name="max_price"
                               value="{{ $filters['max_price'] }}"
                               class="w-full px-3 py-2 border rounded">
                    </div>
                </div>

                {{-- Tri --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Trier par</label>
                    <select name="sort" class="w-full px-3 py-2 border rounded">
                        <option value="">Nouveautés</option>
                        <option value="price_asc"  @selected($filters['sort'] === 'price_asc')>Prix croissant</option>
                        <option value="price_desc" @selected($filters['sort'] === 'price_desc')>Prix décroissant</option>
                        <option value="name_asc"   @selected($filters['sort'] === 'name_asc')>Nom A → Z</option>
                        <option value="name_desc"  @selected($filters['sort'] === 'name_desc')>Nom Z → A</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit"
                            class="flex-1 bg-red-600 text-white px-4 py-2 rounded text-sm font-semibold">
                        Appliquer
                    </button>
                    <a href="{{ route('produits.index') }}"
                       class="text-sm text-gray-600 underline">
                        Réinitialiser
                    </a>
                </div>
            </form>
        </aside>

        {{-- Liste produits --}}
        <section class="lg:col-span-3">
            @if($selectedEngineId)
                <p class="mb-4 text-sm text-green-700">
                    Résultats filtrés pour le véhicule sélectionné.
                </p>
            @endif

            @if($pieces->count() === 0)
                <div class="p-6 border rounded bg-white text-center text-sm text-gray-600">
                    Aucune pièce ne correspond aux critères de recherche.
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($pieces as $piece)
                        <article class="bg-white rounded shadow-sm overflow-hidden flex flex-col">
                            {{-- Image placeholder ou champ image si tu en as un --}}
                            {{-- Image produit --}}
<<div class="h-40 bg-gray-100 flex items-center justify-center">
    @if($piece->image)
        <img src="{{ asset('storage/images/pieces/' . $piece->image) }}"
             alt="{{ $piece->nom }}"
             class="h-full object-contain">
    @else
        <span class="text-gray-400 text-sm">Image pièce</span>
    @endif
</div>



                            <div class="p-4 flex-1 flex flex-col">
                                <h2 class="font-semibold text-gray-900 mb-1">
                                    {{ $piece->nom }}
                                </h2>
                                <p class="text-xs text-gray-500 mb-1">
                                    Réf. {{ $piece->reference }}
                                </p>
                                @if($piece->category)
                                    <p class="text-xs text-gray-500 mb-2">
                                        Catégorie : {{ $piece->category->nom }}
                                    </p>
                                @endif

                                <p class="text-lg font-bold text-red-600 mb-1">
                                    {{ number_format($piece->prix, 2, ',', ' ') }} €
                                </p>
                                <p class="text-xs text-gray-600 mb-3">
                                    En stock ({{ $piece->stock }})
                                </p>

                                <div class="mt-auto flex items-center justify-between gap-2">
                                    <a href="{{ route('produits.show', $piece) }}"
                                       class="text-sm text-blue-600 hover:underline">
                                        Voir la pièce
                                    </a>

                                    <form action="{{ route('cart.add', $piece) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                class="bg-red-600 text-white text-sm px-3 py-2 rounded">
                                            Ajouter au panier
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $pieces->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
