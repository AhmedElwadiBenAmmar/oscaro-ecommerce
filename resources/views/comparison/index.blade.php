@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-semibold mb-6">Comparateur de pièces</h1>

    @if($products->isEmpty())
        <p class="text-gray-500">
            Vous n'avez encore ajouté aucune pièce au comparateur.
        </p>
    @else
        {{-- Actions --}}
        <div class="mb-4">
            <form method="POST" action="{{ route('comparison.clear') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    Vider le comparateur
                </button>
            </form>
        </div>

        {{-- Tableau de comparaison --}}
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-4 py-2 text-left">Caractéristique</th>

                        @foreach($products as $product)
                            <th class="border px-4 py-2 text-center align-top">
                                <div class="flex flex-col items-center gap-2">
                                    <a href="{{ route('produits.show', $product->id) }}"
                                       class="text-blue-600 font-semibold hover:underline">
                                        {{ $product->nom }}
                                    </a>

                                    <p class="text-gray-600">
                                        {{ number_format($product->prix, 2, ',', ' ') }} €
                                    </p>

                                    <form method="POST"
                                          action="{{ route('comparison.remove', $product->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-red-500 text-xs hover:underline">
                                            Retirer
                                        </button>
                                    </form>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    {{-- Catégorie --}}
                    <tr>
                        <td class="border px-4 py-2 font-semibold bg-gray-50">
                            Catégorie
                        </td>
                        @foreach($products as $product)
                            <td class="border px-4 py-2 text-center">
                                {{ $product->categorie->nom ?? '—' }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Référence --}}
                    <tr>
                        <td class="border px-4 py-2 font-semibold bg-gray-50">
                            Référence
                        </td>
                        @foreach($products as $product)
                            <td class="border px-4 py-2 text-center">
                                {{ $product->reference }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Stock --}}
                    <tr>
                        <td class="border px-4 py-2 font-semibold bg-gray-50">
                            Stock disponible
                        </td>
                        @foreach($products as $product)
                            <td class="border px-4 py-2 text-center">
                                {{ $product->stock }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Ajoute ici d'autres lignes pour tes colonnes techniques (marque, moteur, etc.) --}}
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
