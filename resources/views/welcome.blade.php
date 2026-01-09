@extends('layouts.app')

@section('title', 'Accueil')

@section('content')
    <div class="space-y-6">
        <h1 class="text-3xl font-bold text-red-600">
            Bienvenue sur Oscaro Clone
        </h1>

        <p class="text-gray-700">
            Plateforme e-commerce pour pièces automobiles développée avec Laravel 10 et Tailwind CSS.
        </p>

        <a href="{{ url('/produits') }}"
           class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded hover:bg-red-700">
            Voir les produits
        </a>
    </div>
@endsection
