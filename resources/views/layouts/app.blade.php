<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Oscaro Clone')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900">

    {{-- HEADER --}}
    <header class="bg-white shadow">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <a href="{{ url('/') }}" class="text-2xl font-bold text-red-600">
                Oscaro Clone
            </a>

            <nav class="flex items-center space-x-4">
                <a href="{{ url('/') }}" class="text-sm font-medium text-gray-700 hover:text-red-600">
                    Accueil
                </a>
                <a href="{{ url('/produits') }}" class="text-sm font-medium text-gray-700 hover:text-red-600">
                    Produits
                </a>

                @auth
                    <a href="{{ route('cart.index') }}"
                       class="text-sm font-medium text-gray-700 hover:text-red-600 {{ request()->routeIs('cart.*') ? 'font-semibold text-red-600' : '' }}">
                        Panier
                    </a>

                    <a href="{{ url('/dashboard') }}" class="text-sm font-medium text-gray-700 hover:text-red-600">
                        Mon compte
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-red-600">
                        Connexion
                    </a>
                    <a href="{{ route('register') }}" class="text-sm font-medium text-gray-700 hover:text-red-600">
                        Inscription
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    {{-- CONTENU PRINCIPAL --}}
    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="bg-gray-900 text-gray-300 mt-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row justify-between text-sm">
            <p>&copy; {{ date('Y') }} Oscaro Clone. Tous droits réservés.</p>

            <div class="flex space-x-4 mt-2 sm:mt-0">
                <a href="#" class="hover:text-white">Mentions légales</a>
                <a href="#" class="hover:text-white">Conditions générales</a>
                <a href="#" class="hover:text-white">Confidentialité</a>
            </div>
        </div>
    </footer>

</body>
</html>
