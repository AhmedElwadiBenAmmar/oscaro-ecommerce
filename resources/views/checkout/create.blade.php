@extends('layouts.app')

@section('title', 'Commande')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Finaliser la commande</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        {{-- Récapitulatif panier --}}
        <div>
            <h2 class="text-lg font-semibold mb-2">Votre panier</h2>

            <div class="bg-white shadow-sm rounded-lg p-4">
                @foreach ($cart as $item)
                    <div class="flex justify-between text-sm py-1 border-b border-gray-100">
                        <span>{{ $item['nom'] }} (x{{ $item['quantite'] }})</span>
                        <span>{{ number_format($item['prix'] * $item['quantite'], 2, ',', ' ') }} €</span>
                    </div>
                @endforeach

                <div class="flex justify-between font-bold mt-3 text-red-600">
                    <span>Total</span>
                    <span>{{ number_format($total, 2, ',', ' ') }} €</span>
                </div>
            </div>
        </div>

        {{-- Formulaire client --}}
        <div>
            <h2 class="text-lg font-semibold mb-2">Vos coordonnées</h2>

            <form action="{{ route('checkout.store') }}" method="POST" class="bg-white shadow-sm rounded-lg p-4 space-y-3">
                @csrf

                <div>
                    <label class="block text-sm font-medium mb-1">Nom complet</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="w-full border-gray-300 rounded shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full border-gray-300 rounded shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full border-gray-300 rounded shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Adresse</label>
                    <input type="text" name="address" value="{{ old('address') }}"
                           class="w-full border-gray-300 rounded shadow-sm">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium mb-1">Ville</label>
                        <input type="text" name="city" value="{{ old('city') }}"
                               class="w-full border-gray-300 rounded shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Code postal</label>
                        <input type="text" name="postal_code" value="{{ old('postal_code') }}"
                               class="w-full border-gray-300 rounded shadow-sm">
                    </div>
                </div>

                <button type="submit"
                        class="mt-3 px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded hover:bg-red-700">
                    Valider la commande
                </button>
            </form>
        </div>
    </div>
@endsection
