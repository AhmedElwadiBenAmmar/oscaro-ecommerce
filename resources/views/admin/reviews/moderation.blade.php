{{-- resources/views/admin/reviews/moderation.blade.php --}}
@extends('layouts.app') {{-- ou layouts.admin --}}

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-semibold mb-6">Modération des avis</h1>

    {{-- Filtres rapides --}}
    <div class="mb-4 flex gap-3">
        <a href="{{ route('admin.reviews.index', ['status' => 'pending']) }}"
           class="btn btn-outline-secondary">
            En attente ({{ $counts['pending'] ?? 0 }})
        </a>
        <a href="{{ route('admin.reviews.index', ['status' => 'approved']) }}"
           class="btn btn-outline-success">
            Approuvés ({{ $counts['approved'] ?? 0 }})
        </a>
        <a href="{{ route('admin.reviews.index', ['status' => 'rejected']) }}"
           class="btn btn-outline-danger">
            Rejetés ({{ $counts['rejected'] ?? 0 }})
        </a>
    </div>

    @forelse($reviews as $review)
        <div class="bg-white shadow rounded p-4 mb-4">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm text-gray-500">
                        Produit :
                        <a href="{{ route('produits.show', $review->product_id) }}"
                           class="text-blue-600 underline">
                            {{ $review->product->nom ?? 'Produit #' . $review->product_id }}
                        </a>
                    </p>
                    <p class="font-semibold">
                        {{ $review->user->name ?? 'Client' }}
                        · {{ $review->rating }} / 5
                    </p>
                    <p class="text-gray-700 mt-2">
                        {{ $review->comment }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        Créé le {{ $review->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>

                <div class="flex flex-col gap-2">
                    @if($review->status !== 'approved')
                        <form method="POST"
                              action="{{ route('admin.reviews.approve', $review) }}">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-success btn-sm" type="submit">
                                Approuver
                            </button>
                        </form>
                    @endif

                    @if($review->status !== 'rejected')
                        <form method="POST"
                              action="{{ route('admin.reviews.reject', $review) }}">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-danger btn-sm" type="submit">
                                Rejeter
                            </button>
                        </form>
                    @endif

                    <form method="POST"
                          action="{{ route('admin.reviews.destroy', $review) }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-outline-secondary btn-sm" type="submit"
                                onclick="return confirm('Supprimer définitivement cet avis ?')">
                            Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <p class="text-gray-500">Aucun avis à afficher pour ce filtre.</p>
    @endforelse

    <div class="mt-4">
        {{ $reviews->links() }}
    </div>
</div>
@endsection
