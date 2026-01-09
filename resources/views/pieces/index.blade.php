{{-- resources/views/pieces/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Liste des pièces')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Liste des pièces</h1>

    @if (session('success'))
        <div class="mb-4 px-4 py-2 bg-green-100 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow sm:rounded-lg overflow-hidden">
        <table class="min-w-full text-sm text-gray-900">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left">Référence</th>
                    <th class="px-4 py-2 text-left">Nom</th>
                    <th class="px-4 py-2 text-right">Prix (€)</th>
                    <th class="px-4 py-2 text-right">Stock</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pieces as $piece)
                    <tr class="border-t border-gray-200">
                        <td class="px-4 py-2">{{ $piece->reference }}</td>
                        <td class="px-4 py-2">{{ $piece->nom }}</td>
                        <td class="px-4 py-2 text-right">
                            {{ number_format($piece->prix, 2, ',', ' ') }}
                        </td>
                        <td class="px-4 py-2 text-right">{{ $piece->stock }}</td>
                        <td class="px-4 py-2 text-right space-x-3">
                            <a href="{{ route('pieces.show', $piece) }}" class="text-blue-500 hover:underline">Voir</a>
                            <a href="{{ route('pieces.edit', $piece) }}" class="text-yellow-500 hover:underline">Éditer</a>
                            <form action="{{ route('pieces.destroy', $piece) }}"
                                  method="POST"
                                  class="inline"
                                  onsubmit="return confirm('Supprimer cette pièce ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:underline">
                                    Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500">
                            Aucune pièce pour le moment.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $pieces->links() }}
    </div>
@endsection
