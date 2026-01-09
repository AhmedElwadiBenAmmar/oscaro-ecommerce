<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Modifier la pièce
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-4">
                <a href="{{ route('pieces.index') }}"
                   class="text-sm text-gray-500 hover:underline">
                    ← Retour à la liste
                </a>
            </div>

            @if ($errors->any())
                <div class="mb-4 px-4 py-2 bg-red-100 text-red-800 rounded">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('pieces.update', $piece) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium mb-1">Référence</label>
                        <input type="text"
                               name="reference"
                               value="{{ old('reference', $piece->reference) }}"
                               class="w-full border-gray-300 rounded shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Nom</label>
                        <input type="text"
                               name="nom"
                               value="{{ old('nom', $piece->nom) }}"
                               class="w-full border-gray-300 rounded shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <textarea name="description" rows="3"
                                  class="w-full border-gray-300 rounded shadow-sm">{{ old('description', $piece->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Prix (€)</label>
                            <input type="number" step="0.01"
                                   name="prix"
                                   value="{{ old('prix', $piece->prix) }}"
                                   class="w-full border-gray-300 rounded shadow-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Stock</label>
                            <input type="number"
                                   name="stock"
                                   value="{{ old('stock', $piece->stock) }}"
                                   class="w-full border-gray-300 rounded shadow-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Catégorie</label>
                            <input type="text"
                                   name="categorie"
                                   value="{{ old('categorie', $piece->categorie) }}"
                                   class="w-full border-gray-300 rounded shadow-sm">
                        </div>
                    </div>

                    <div class="flex items-center gap-3 mt-4">
                        <button type="submit"
                                class="px-4 py-2 bg-yellow-500 text-white text-sm font-semibold rounded hover:bg-yellow-600">
                            Mettre à jour
                        </button>
                        <a href="{{ route('pieces.show', $piece) }}"
                           class="text-sm text-gray-700 hover:underline">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
