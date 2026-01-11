<x-app-layout>
    {{-- Bandeau promo haut --}}
    <div class="bg-orange-500 text-white text-center py-2 text-sm">
        EXCLU APP - LIVRAISON À 4,99€* !
        <a href="#" class="underline ml-2">Voir les conditions</a>
    </div>

    {{-- Barre bleue logo + recherche + icônes --}}
    <header class="bg-blue-900 text-white">
        <div class="max-w-6xl mx-auto flex items-center justify-between py-4">
            <div class="flex items-center space-x-4">
                <div class="text-2xl font-bold">Oscaro Clone</div>

                {{-- Barre de recherche --}}
                <form action="{{ route('produits.index') }}" method="GET" class="hidden md:flex ml-6">
                    <input type="text" name="q"
                           placeholder="Ex : Batterie, GDB1330..."
                           class="w-96 px-4 py-2 rounded-l-full text-gray-900 text-sm">
                    <button class="bg-orange-500 px-4 rounded-r-full text-sm">
                        Rechercher
                    </button>
                </form>
            </div>

            <nav class="flex items-center space-x-6 text-sm">
                <a href="#" class="flex items-center space-x-1">
                    <span>?</span><span>Aide et contact</span>
                </a>
                <a href="{{ route('profile.edit') }}">Mon compte</a>
                <a href="{{ route('cart.index') }}">Mon panier</a>
            </nav>
        </div>

        {{-- Menu catégories principal --}}
        <div class="bg-blue-800">
            <div class="max-w-6xl mx-auto flex items-center space-x-8 text-sm py-2">
                <x-nav-link href="{{ route('produits.index') }}">Pièces auto</x-nav-link>
                <x-nav-link href="#">Huile moteur</x-nav-link>
                <x-nav-link href="#">Pneus</x-nav-link>
                <x-nav-link href="#">Accessoires & entretien</x-nav-link>
                <x-nav-link href="#">Outillage</x-nav-link>
                <x-nav-link href="#">Pièces d'occasion</x-nav-link>
                <x-nav-link href="#">Tutoriels</x-nav-link>
            </div>
        </div>
    </header>

    {{-- Contenu principal --}}
    <main class="bg-gray-100 py-8">
        <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Colonne gauche : sélection véhicule --}}
            <section class="bg-white rounded shadow p-4 lg:col-span-1">
                <h2 class="font-semibold mb-4 text-lg">Sélectionnez votre véhicule</h2>

                <form>
                    <label class="block text-xs font-semibold mb-2">PAR IMMATRICULATION</label>
                    <div class="flex">
                        <span class="bg-blue-900 text-white px-3 flex items-center">F</span>
                        <input type="text" class="flex-1 border px-3 py-2 text-sm" placeholder="AA-456-BB">
                        <button class="bg-orange-500 text-white px-4">OK</button>
                    </div>

                    <div class="flex justify-between mt-4 text-xs text-blue-700">
                        <button type="button">PAR MODÈLE</button>
                        <button type="button">PAR CARTE GRISE OU VIN</button>
                    </div>
                </form>
            </section>

            {{-- Colonne droite : slider promo --}}
            <section class="bg-blue-500 rounded shadow p-6 text-white lg:col-span-2">
                <h2 class="text-sm font-semibold mb-2">PROMOTION</h2>
                <p class="text-3xl font-bold mb-4">BATTERIES</p>
                <p class="mb-6 text-sm">Pour un démarrage au quart de tour</p>

                <div class="bg-blue-400 inline-block px-4 py-2 rounded mb-6">
                    <span class="text-xs">JUSQU'À</span>
                    <span class="text-2xl font-bold ml-2">-10%</span>
                </div>

                <div>
                    <a href="#" class="bg-orange-500 text-white px-5 py-2 rounded text-sm">
                        J’en profite
                    </a>
                </div>
            </section>
        </div>

        {{-- Section marques ou produits en bas --}}
        <section class="max-w-6xl mx-auto mt-10">
            <h2 class="text-xl font-semibold mb-4">Nos meilleures ventes</h2>
            {{-- Réutiliser tes cartes produits existantes ici --}}
            @include('partials.home-products')
        </section>
    </main>
</x-app-layout>
