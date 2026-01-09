<?php

use Illuminate\Support\Facades\Session;

if (! function_exists('cart_count')) {
    /**
     * Retourne le nombre total d’articles dans le panier (session).
     */
    function cart_count(): int
    {
        $cart = Session::get('cart', []);

        // Somme des quantités de chaque ligne
        return collect($cart)->sum('quantite');
    }
}
