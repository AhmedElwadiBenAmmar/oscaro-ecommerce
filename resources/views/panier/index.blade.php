@extends('layouts.app')

@section('title', 'Mon panier')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Mon panier</h1>

    @if(session('success'))
        <div class="mb-4 text-sm text-green-700 bg-green-100 px-4 py-2 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(empty($cart))
        <p>Votre panier est vide.</p>
        <a href="{{ route('produits.index') }}" class="text-red-600 hover:underline">
            Voir le catalogue
        </a>
    @else
        <table class="min-w-full text-sm mb-6">
            <thead>
            <tr class="border-b">
                <th class="text-left py-2">Pièce</th>
                <th class="text-right py-2">Prix</th>
                <th class="text-center py-2">Quantité</th>
                <th class="text-right py-2">Sous-total</th>
                <th class="py-2"></th>
            </tr>
            </thead>
            <tbody>
            @foreach($cart as $item)
                <tr class="border-b">
                    <td class="py-2">
                        {{ $item['nom'] }}
                        <span class="text-xs text-gray-500">
                            ({{ $item['reference'] }})
                        </span>
                    </td>
                    <td class="py-2 text-right">
                        {{ number_format($item['prix'], 2, ',', ' ') }} €
                    </td>
                    <td class="py-2 text-center">
                        <form action="{{ route('cart.update', $item['id']) }}" method="POST"
                              class="inline-flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <input type="number" name="quantite" value="{{ $item['quantite'] }}"
                                   min="1"
                                   class="w-16 border rounded px-1 py-0.5 text-center">
                            <button type="submit" class="text-xs text-blue-600 hover:underline">
                                OK
                            </button>
                        </form>
                    </td>
                    <td class="py-2 text-right">
                        {{ number_format($item['prix'] * $item['quantite'], 2, ',', ' ') }} €
                    </td>
                    <td class="py-2 text-center">
                        <form action="{{ route('cart.remove', $item['id']) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 hover:underline">
                                Retirer
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <td colspan="3" class="py-2 text-right font-semibold">
                    Total
                </td>
                <td class="py-2 text-right font-semibold">
                    {{ number_format($total, 2, ',', ' ') }} €
                </td>
                <td></td>
            </tr>
            </tfoot>
        </table>

        <div class="flex gap-3">
            <a href="{{ route('produits.index') }}"
               class="px-4 py-2 border rounded text-sm">
                ← Continuer mes achats
            </a>

            <form action="{{ route('cart.clear') }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="px-4 py-2 bg-gray-200 text-sm rounded hover:bg-gray-300">
                    Vider le panier
                </button>
            </form>

            <a href="{{ route('checkout.create') }}"
   class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded hover:bg-red-700">
    Commander
</a>

        </div>
    @endif
@endsection
