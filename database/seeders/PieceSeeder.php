<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Piece;
use App\Models\Category;

class PieceSeeder extends Seeder
{
    public function run(): void
    {
        $filtration = Category::firstOrCreate(['nom' => 'Filtration']);
        $freinage   = Category::firstOrCreate(['nom' => 'Freinage']);
        $moteur     = Category::firstOrCreate(['nom' => 'Moteur']);
        $suspension = Category::firstOrCreate(['nom' => 'Suspension']);

        $pieces = [
            ['Filtre à huile 1.6 HDi',   'FILT-HUILE-001',  45, 17, $filtration->id, 'filtre-huile.jpg'],
            ['Filtre à air moteur',      'FILT-AIR-002',    30, 25, $filtration->id, 'filtre-air.jpg'],
            ['Filtre à carburant',       'FILT-CARB-003',   55, 12, $filtration->id, 'filtre-carburant.jpg'],
            ['Filtre habitacle',         'FILT-HAB-004',    28, 40, $filtration->id, 'filtre-habitacle.jpg'],

            ['Plaquettes AV Peugeot',    'PLAQ-AV-005',     60, 20, $freinage->id,   'plaquettes-av.jpg'],
            ['Plaquettes AR Peugeot',    'PLAQ-AR-006',     58, 15, $freinage->id,   'plaquettes-ar.jpg'],
            ['Disque de frein AV',       'DISC-AV-007',     95, 10, $freinage->id,   'disque-av.jpg'],
            ['Disque de frein AR',       'DISC-AR-008',     90,  8, $freinage->id,   'disque-ar.jpg'],

            ['Bougie d’allumage',        'BOUGIE-009',      15, 40, $moteur->id,     'bougie.jpg'],
            ['Bobine d’allumage',        'BOBINE-010',      85, 10, $moteur->id,     'bobine.jpg'],
            ['Courroie de distribution', 'COURR-DIST-011', 120,  7, $moteur->id,     'courroie.jpg'],
            ['Pompe à eau',              'POMPE-EAU-012',  110,  9, $moteur->id,     'pompe-eau.jpg'],
            ['Joint de culasse',         'JOINT-CUL-013',  150,  5, $moteur->id,     'joint-culasse.jpg'],

            ['Amortisseur AV gauche',    'AMORT-AV-G-014', 130, 12, $suspension->id, 'amortisseur-av-gauche.jpg'],
            ['Amortisseur AV droit',     'AMORT-AV-D-015', 130, 12, $suspension->id, 'amortisseur-av-droit.jpg'],
            ['Amortisseur AR gauche',    'AMORT-AR-G-016', 125, 10, $suspension->id, 'amortisseur-ar-gauche.jpg'],
            ['Amortisseur AR droit',     'AMORT-AR-D-017', 125, 10, $suspension->id, 'amortisseur-ar-droit.jpg'],
            ['Biellette de barre stab',  'BIEL-STAB-018',   40, 25, $suspension->id, 'biellette.jpg'],
            ['Triangle de suspension',   'TRI-SUSP-019',    95,  8, $suspension->id, 'triangle-suspension.jpg'],
            ['Ressort de suspension',    'RESS-SUSP-020',   70, 14, $suspension->id, 'ressort-suspension.jpg'],
        ];

        foreach ($pieces as $p) {
            Piece::create([
                'nom'         => $p[0],
                'reference'   => $p[1],
                'prix'        => $p[2],
                'stock'       => $p[3],
                'category_id' => $p[4],
                'image'       => $p[5],
            ]);
        }
    }
}
