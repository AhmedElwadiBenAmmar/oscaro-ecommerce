@extends('layouts.app') {{-- adapte si ton layout a un autre nom --}}

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Recommandations personnalisées</h1>

    <h2 class="h4">Pour vous</h2>
    @if($personalizedRecommendations->isEmpty())
        <p>Aucune recommandation personnalisée pour le moment.</p>
    @else
        <div class="row">
            @foreach($personalizedRecommendations as $piece)
                <div class="col-md-3 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="{{ route('produits.show', $piece->id) }}">
                                    {{ $piece->nom ?? $piece->name ?? 'Pièce #'.$piece->id }}
                                </a>
                            </h5>
                            @isset($piece->price)
                                <p class="card-text">{{ $piece->price }} €</p>
                            @endisset
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <hr>

    <h2 class="h4">Produits populaires</h2>
    @if($popularProducts->isEmpty())
        <p>Aucun produit populaire pour le moment.</p>
    @else
        <div class="row">
            @foreach($popularProducts as $piece)
                <div class="col-md-3 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="{{ route('produits.show', $piece->id) }}">
                                    {{ $piece->nom ?? $piece->name ?? 'Pièce #'.$piece->id }}
                                </a>
                            </h5>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <hr>

    <h2 class="h4">Récemment consultés</h2>
    @if($recentlyViewed->isEmpty())
        <p>Vous n’avez pas encore consulté de pièces.</p>
    @else
        <div class="row">
            @foreach($recentlyViewed as $piece)
                <div class="col-md-3 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="{{ route('produits.show', $piece->id) }}">
                                    {{ $piece->nom ?? $piece->name ?? 'Pièce #'.$piece->id }}
                                </a>
                            </h5>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <hr>

    <h2 class="h4">Tendances</h2>
    @if($trendingProducts->isEmpty())
        <p>Aucun produit tendance pour l’instant.</p>
    @else
        <div class="row">
            @foreach($trendingProducts as $piece)
                <div class="col-md-3 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="{{ route('produits.show', $piece->id) }}">
                                    {{ $piece->nom ?? $piece->name ?? 'Pièce #'.$piece->id }}
                                </a>
                            </h5>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
