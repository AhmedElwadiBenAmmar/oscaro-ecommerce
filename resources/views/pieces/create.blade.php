@extends('layouts.app')

@section('title', 'Nouvelle pièce')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Nouvelle pièce</h1>

    @if ($errors->any())
        <div class="mb-4 px-4 py-2 bg-red-100 text-red-800 rounded">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white shadow sm:rounded-lg p-6">
        <form action="{{ route('pieces.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1">Référence</label>
                <input type="text" name="reference" value="{{ old('reference') }}"
                       class="w-full border-gray-300 rounded shadow-sm">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Nom</label>
                <input type="text" name="nom" value="{{ old('nom') }}"
                       class="w-full border-gray-300 rounded shadow-sm">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea name="description" rows="3"
                          class="w-full border-gray-300 rounded shadow-sm">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Prix (€)</label>
                    <input type="number" step="0.01" name="prix" value="{{ old('prix') }}"
                           class="w-full border-gray-300 rounded shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Stock</label>
                    <input type="number" name="stock" value="{{ old('stock', 0) }}"
                           class="w-full border-gray-300 rounded shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Catégorie</label>
                    <input type="text" name="categorie" value="{{ old('categorie') }}"
                           class="w-full border-gray-300 rounded shadow-sm">
                </div>
            </div>

            <div class="flex items-center space-x-3 mt-4">
                <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded hover:bg-red-700">
                    Enregistrer
                </button>
                <a href="{{ route('pieces.index') }}"
                   class="text-sm text-gray-700 hover:underline">
                    Annuler
                </a>
            </div>
        </form>
    </div>
@endsection
