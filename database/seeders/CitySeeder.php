<?php

namespace Database\Seeders;

use App\Modules\City\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            ['az' => 'Bakı', 'en' => 'Baku', 'ru' => 'Баку'],
            ['az' => 'Gəncə', 'en' => 'Ganja', 'ru' => 'Гянджа'],
            ['az' => 'Sumqayıt', 'en' => 'Sumgait', 'ru' => 'Сумгаит'],
            ['az' => 'Mingəçevir', 'en' => 'Mingachevir', 'ru' => 'Мингечевир'],
            ['az' => 'Şirvan', 'en' => 'Shirvan', 'ru' => 'Ширван'],
            ['az' => 'Naxçıvan', 'en' => 'Nakhchivan', 'ru' => 'Нахичевань'],
            ['az' => 'Lənkəran', 'en' => 'Lankaran', 'ru' => 'Ленкорань'],
            ['az' => 'Şəki', 'en' => 'Sheki', 'ru' => 'Шеки'],
            ['az' => 'Yevlax', 'en' => 'Yevlakh', 'ru' => 'Евлах'],
            ['az' => 'Xankəndi', 'en' => 'Khankendi', 'ru' => 'Ханкенди'],
            ['az' => 'Abşeron', 'en' => 'Absheron', 'ru' => 'Абшерон'],
            ['az' => 'Ağcabədi', 'en' => 'Aghjabadi', 'ru' => 'Агджабеди'],
            ['az' => 'Ağdaş', 'en' => 'Agdash', 'ru' => 'Агдаш'],
            ['az' => 'Ağstafa', 'en' => 'Agstafa', 'ru' => 'Агстафа'],
            ['az' => 'Ağsu', 'en' => 'Agsu', 'ru' => 'Агсу'],
            ['az' => 'Astara', 'en' => 'Astara', 'ru' => 'Астара'],
            ['az' => 'Balakən', 'en' => 'Balakan', 'ru' => 'Балакен'],
            ['az' => 'Bərdə', 'en' => 'Barda', 'ru' => 'Барда'],
            ['az' => 'Biləsuvar', 'en' => 'Bilasuvar', 'ru' => 'Билясувар'],
            ['az' => 'Cəlilabad', 'en' => 'Jalilabad', 'ru' => 'Джалилабад'],
            ['az' => 'Daşkəsən', 'en' => 'Dashkasan', 'ru' => 'Дашкесан'],
            ['az' => 'Göyçay', 'en' => 'Goychay', 'ru' => 'Гёйчай'],
            ['az' => 'Göygöl', 'en' => 'Goygol', 'ru' => 'Гёйгёль'],
            ['az' => 'Hacıqabul', 'en' => 'Hajigabul', 'ru' => 'Гаджигабул'],
            ['az' => 'İmişli', 'en' => 'Imishli', 'ru' => 'Имишли'],
            ['az' => 'İsmayıllı', 'en' => 'Ismayilli', 'ru' => 'Исмаиллы'],
            ['az' => 'Kürdəmir', 'en' => 'Kurdamir', 'ru' => 'Кюрдамир'],
            ['az' => 'Qax', 'en' => 'Gakh', 'ru' => 'Гах'],
            ['az' => 'Qazax', 'en' => 'Gazakh', 'ru' => 'Казах'],
            ['az' => 'Qəbələ', 'en' => 'Gabala', 'ru' => 'Габала'],
            ['az' => 'Qobustan', 'en' => 'Gobustan', 'ru' => 'Гобустан'],
            ['az' => 'Quba', 'en' => 'Guba', 'ru' => 'Губа'],
            ['az' => 'Qubadlı', 'en' => 'Gubadly', 'ru' => 'Губадлы'],
            ['az' => 'Qusar', 'en' => 'Gusar', 'ru' => 'Гусар'],
            ['az' => 'Laçın', 'en' => 'Lachin', 'ru' => 'Лачин'],
            ['az' => 'Lerik', 'en' => 'Lerik', 'ru' => 'Лерик'],
            ['az' => 'Masallı', 'en' => 'Masally', 'ru' => 'Масаллы'],
            ['az' => 'Neftçala', 'en' => 'Neftchala', 'ru' => 'Нефтчала'],
            ['az' => 'Oğuz', 'en' => 'Oghuz', 'ru' => 'Огуз'],
            ['az' => 'Ordubad', 'en' => 'Ordubad', 'ru' => 'Ордубад'],
            ['az' => 'Saatlı', 'en' => 'Saatly', 'ru' => 'Саатлы'],
            ['az' => 'Sabirabad', 'en' => 'Sabirabad', 'ru' => 'Сабирабад'],
            ['az' => 'Salyan', 'en' => 'Salyan', 'ru' => 'Сальян'],
            ['az' => 'Samux', 'en' => 'Samukh', 'ru' => 'Самух'],
            ['az' => 'Siyəzən', 'en' => 'Siazan', 'ru' => 'Сиазань'],
            ['az' => 'Şabran', 'en' => 'Shabran', 'ru' => 'Шабран'],
            ['az' => 'Şamaxı', 'en' => 'Shamakhi', 'ru' => 'Шемаха'],
            ['az' => 'Şəmkir', 'en' => 'Shamkir', 'ru' => 'Шамкир'],
            ['az' => 'Şuşa', 'en' => 'Shusha', 'ru' => 'Шуша'],
            ['az' => 'Tərtər', 'en' => 'Tartar', 'ru' => 'Тертер'],
            ['az' => 'Tovuz', 'en' => 'Tovuz', 'ru' => 'Товуз'],
            ['az' => 'Ucar', 'en' => 'Ujar', 'ru' => 'Уджар'],
            ['az' => 'Xaçmaz', 'en' => 'Khachmaz', 'ru' => 'Хачмаз'],
            ['az' => 'Xızı', 'en' => 'Khizi', 'ru' => 'Хызы'],
            ['az' => 'Xocalı', 'en' => 'Khojaly', 'ru' => 'Ходжалы'],
            ['az' => 'Xocavənd', 'en' => 'Khojavend', 'ru' => 'Ходжавенд'],
            ['az' => 'Yardımlı', 'en' => 'Yardimly', 'ru' => 'Ярдымлы'],
            ['az' => 'Zaqatala', 'en' => 'Zagatala', 'ru' => 'Загатала'],
            ['az' => 'Zəngilan', 'en' => 'Zangilan', 'ru' => 'Зангилан'],
            ['az' => 'Zərdab', 'en' => 'Zardab', 'ru' => 'Зардаб'],
        ];

        foreach ($cities as $name) {
            City::create(['name' => $name]);
        }
    }
}
