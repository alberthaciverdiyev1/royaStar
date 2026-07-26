<?php

namespace Database\Seeders;

use App\Modules\City\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            'Bakı', 'Gəncə', 'Sumqayıt', 'Mingəçevir', 'Şirvan',
            'Naxçıvan', 'Lənkəran', 'Şəki', 'Yevlax', 'Xankəndi',
            'Abşeron', 'Ağcabədi', 'Ağdaş', 'Ağstafa', 'Ağsu',
            'Astara', 'Balakən', 'Bərdə', 'Biləsuvar', 'Cəlilabad',
            'Daşkəsən', 'Göyçay', 'Göygöl', 'Hacıqabul', 'İmişli',
            'İsmayıllı', 'Kürdəmir', 'Qax', 'Qazax', 'Qəbələ',
            'Qobustan', 'Quba', 'Qubadlı', 'Qusar', 'Laçın',
            'Lerik', 'Masallı', 'Neftçala', 'Oğuz', 'Ordubad',
            'Saatlı', 'Sabirabad', 'Salyan', 'Samux', 'Siyəzən',
            'Şabran', 'Şamaxı', 'Şəmkir', 'Şuşa', 'Tərtər',
            'Tovuz', 'Ucar', 'Xaçmaz', 'Xızı', 'Xocalı',
            'Xocavənd', 'Yardımlı', 'Zaqatala', 'Zəngilan', 'Zərdab',
        ];

        foreach ($cities as $name) {
            City::create(['name' => $name]);
        }
    }
}
