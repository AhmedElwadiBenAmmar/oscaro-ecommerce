@extends('layouts.app')

@section('title', 'Détail de la pièce')

@section('content')
    <a href="{{ route('pieces.index') }}" class="text-sm text-gray-500 hover:underline">
        ← Retour à la liste
    </a>

    <div class="bg-white shadow sm:rounded-lg p-6 mt-4 space-y-4">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $piece->nom }}
                </h1>
                <p class="text-sm text-gray-500">
                    Référence : {{ $piece->reference }}
                </p>
            </div>

            <div class="text-right">
                <p class="text-lg font-semibold text-gray-900">
                    {{ number_format($piece->prix, 2, ',', ' ') }} €
                </p>
                <p class="text-sm text-gray-500">
                    Stock : {{ $piece->stock }}
                </p>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-700 mb-1">
                Description
            </h3>
            <p class="text-sm text-gray-800">
                {{ $piece->description ?: 'Aucune description.' }}
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="font-medium text-gray-700">Catégorie :</span>
                <span class="text-gray-800">
                    {{ $piece->categorie ?: 'Non définie' }}
                </span>
            </div>
            <div>
                <span class="font-medium text-gray-700">Créée le :</span>
                <span class="text-gray-800">
                    {{ $piece->created_at->format('d/m/Y H:i') }}
                </span>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
            <a href="{{ route('pieces.edit', $piece) }}"
               class="px-4 py-2 bg-yellow-500 text-white text-sm font-semibold rounded hover:bg-yellow-600">
                Modifier
            </a>

            <form action="{{ route('pieces.destroy', $piece) }}"
                  method="POST"
                  onsubmit="return confirm('Supprimer cette pièce ?');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded hover:bg-red-700">
                    Supprimer
                </button>
            </form>
        </div>
    </div>
@endsection
