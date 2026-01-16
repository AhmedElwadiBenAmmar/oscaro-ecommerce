{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.app') {{-- ou layouts.admin si tu en as un --}}

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-semibold mb-6">Dashboard admin</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white shadow rounded p-6">
            <h2 class="text-sm text-gray-500 mb-2">Nombre de pièces</h2>
            <p class="text-2xl font-bold">{{ $piecesCount ?? 0 }}</p>
        </div>

        <div class="bg-white shadow rounded p-6">
            <h2 class="text-sm text-gray-500 mb-2">Stock total</h2>
            <p class="text-2xl font-bold">{{ $totalStock ?? 0 }}</p>
        </div>

        <div class="bg-white shadow rounded p-6">
            <h2 class="text-sm text-gray-500 mb-2">Dernière pièce ajoutée</h2>
            <p class="font-semibold">{{ $lastPiece->nom ?? '—' }}</p>
            @isset($lastPiece)
                <p class="text-xs text-gray-500">
                    Réf. {{ $lastPiece->reference }} · {{ number_format($lastPiece->prix, 2, ',', ' ') }} €
                    · Stock {{ $lastPiece->stock }}
                </p>
            @endisset
        </div>
    </div>

    <div class="mt-8">
        <h2 class="text-lg font-semibold mb-4">Actions rapides</h2>
        <div class="flex gap-4">
            <a href="{{ route('pieces.index') }}" class="btn btn-secondary">Voir les pièces</a>
            <a href="{{ route('pieces.create') }}" class="btn btn-primary">Ajouter une pièce</a>
        </div>
    </div>
</div>
@endsection
