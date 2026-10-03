<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Edition;
use App\Models\Setting;
use App\Models\Titular;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Last year's printed booklet ("Proljeće/Ljeto 2026.") as the active demo edition.
 * Each coupon uses its booklet page as the visual (files in data/2026).
 * Self-contained, so it also runs in production: php artisan db:seed --class=Edition2026Seeder --force
 */
class Edition2026Seeder extends Seeder
{
    private const TITULARS = [
        'Optika Kraljević' => [
            'locations' => [
                ['Gajeva 19, Zagreb', 'tel. 01/4825 071', 45.8101836, 15.9766500],
                ['Varšavska 1, Zagreb', 'tel. 01/4872 856', 45.8117910, 15.9736944],
                ['Importanne centar, Zagreb', 'tel. 01/4577 123', 45.8046966, 15.9775233],
                ['Trg Stjepana Konzula 4, Zagreb', 'tel. 01/2335 210', 45.8073029, 16.0197904],
            ],
        ],
        'Pickleball Klub 002' => [
            'locations' => [['Veslačka ulica 26a, Zagreb', 'kraj Hendrixovog mosta', 45.7868561, 15.9562630]],
        ],
        'Vayu Reformer Pilates Studio' => [
            'website' => 'https://www.vayu.hr', 'instagram' => '@vayu_reformerstudio',
            'locations' => [['Nike Grškovića 4, Zagreb', null, 45.8193478, 15.9799074]],
        ],
        'Školska knjiga' => ['website' => 'https://shop.skolskaknjiga.hr'],
        'Burek Lab' => [
            'locations' => [['Gajeva ulica 6, Zagreb', null, 45.8115097, 15.9762180]],
        ],
        "Domino's Pizza" => ['website' => 'https://www.dominos.hr'],
        'Bowling Club Zagreb' => [
            'website' => 'https://www.bowlingclub.hr', 'instagram' => '@bowling_club_zagreb',
            'locations' => [['Florijana Andrašeca 14, Zagreb', 'pon–ned 09–24 h, tel. 01 3096 500', 45.8012588, 15.9612080]],
        ],
        'Teeamo Bubble tea' => [
            'website' => 'https://www.teeamo.hr',
            'locations' => [
                ['Ilica 156, Zagreb', 'pon–sub 10:00–21:00', 45.8132591, 15.9567388],
                ['Savska cesta 54, Zagreb', 'pon–sub 11:00–21:00', 45.7999231, 15.9623620],
            ],
        ],
        'Figura Bistro' => [
            'locations' => [['Blaža Trogiranina 22, Zagreb', 'uto–ned 09:00–17:00', 45.7913741, 15.9421474]],
        ],
        'Thai Me Up by Lira' => [
            'website' => 'https://www.pizzeria-lira.com',
            'locations' => [['Ulica Nikole Tesle 5, Zagreb', 'tel. +385 1 4823 222', 45.8112836, 15.9765363]],
        ],
        'Crofna' => [
            'instagram' => '@crofna_hr',
            'locations' => [
                ['Ozaljska 88, Zagreb', null, 45.7982215, 15.9453201],
                ['CCO West, Zagreb', null, 45.7994310, 15.8846919],
            ],
        ],
        'UNIC Virtualni kampus' => [],
        "R'N'R Museum – Muzej novog vala" => [
            'website' => 'https://www.muzejnovogvala.com',
            'locations' => [['Vlaška 67, Zagreb', null, 45.8138656, 15.9855124]],
        ],
        'Muzej povijesti videoigara' => [
            'locations' => [['Draškovićeva ulica 10, Zagreb', null, 45.8129904, 15.9832430]],
        ],
        'Selfie & Memories Museum Zagreb' => [
            'website' => 'https://www.selfiemuseumzagreb.hr',
            'locations' => [['Radićeva ulica 14, Zagreb', null, 45.8143432, 15.9758677]],
        ],
        'Muzej smijeha HaHa House' => [
            'locations' => [['Gajeva 7/1, Zagreb', 'pon–ned 09:00–21:00', 45.8118996, 15.9765332]],
        ],
        'GDK Gavella' => [],
        'HNK u Zagrebu' => ['website' => 'https://www.hnk.hr'],
        'TIX brzi servis' => [
            'instagram' => '@tixservis',
            'locations' => [['Maksimirska 79, Zagreb', 'pon–pet 10–18 h, tel. 099-4646/146', 45.8175285, 16.0067452]],
        ],
        'Copymax' => [
            'instagram' => '@copymax.uniqatno',
            'locations' => [['Maksimirska 57, Zagreb', 'tel. 01/4640 725, 097/725 89 81', 45.8167411, 16.0034788]],
        ],
        'F45 Training Radnička' => [
            'website' => 'https://www.f45-trening.hr',
            'locations' => [['Radnička cesta 20, Zagreb', 'tel. 095 866 8131', 45.8042374, 15.9967790]],
        ],
        'Vještina je reći ne' => [],
        'Knjižnice grada Zagreba' => ['website' => 'https://www.kgz.hr'],
        'Martina Hair' => [
            'locations' => [['Ulica Charlesa Darwina 14, Zagreb', 'tel. 099 44 79 529', 45.8053069, 16.0103337]],
        ],
        'ZET' => ['website' => 'https://www.zet.hr'],
    ];

    /** Logos cut out of the booklet ads (data/2026/logos), until titulari send real ones. */
    private const LOGOS = [
        'Optika Kraljević' => 'optika-kraljevic',
        'Pickleball Klub 002' => 'pickleball-klub-002',
        'Vayu Reformer Pilates Studio' => 'vayu',
        'Školska knjiga' => 'skolska-knjiga',
        'Burek Lab' => 'burek-lab',
        "Domino's Pizza" => 'dominos',
        'Bowling Club Zagreb' => 'bowling-club-zagreb',
        'Teeamo Bubble tea' => 'teeamo',
        'Figura Bistro' => 'figura-bistro',
        'Thai Me Up by Lira' => 'thai-me-up',
        'Crofna' => 'crofna',
        'UNIC Virtualni kampus' => 'unic',
        "R'N'R Museum – Muzej novog vala" => 'muzej-novog-vala',
        'Muzej povijesti videoigara' => 'muzej-videoigara',
        'Selfie & Memories Museum Zagreb' => 'selfie-museum',
        'Muzej smijeha HaHa House' => 'haha-house',
        'GDK Gavella' => 'gdk-gavella',
        'HNK u Zagrebu' => 'hnk-zagreb',
        'TIX brzi servis' => 'tix-brzi-servis',
        'Copymax' => 'copymax',
        'F45 Training Radnička' => 'f45-training',
        'Martina Hair' => 'martina-hair',
        'ZET' => 'zet',
    ];

    /** [page, titular, category, discount, title, description, conditions, extra] — discount null = partner ad. */
    private const COUPONS = [
        [3, 'Optika Kraljević', 'Usluge i kupovina', '-20%', 'Popust na sunčane i dioptrijske naočale',
            '20% popusta na cijene sunčanih i dioptrijskih naočala u svim optikama Kraljević.',
            'Popust se obračunava na blagajni i vrijedi za kupnju sunčanih i dioptrijskih naočala. Popusti se ne zbrajaju, akcije se isključuju.'],
        [4, 'Pickleball Klub 002', 'Sport i rekreacija', '1+1', 'Igraj 2 sata, plati 1',
            '1+1 gratis na jutarnje pickleball termine. Pickleball je spoj tenisa, badmintona i ping-ponga – igra se u dvoje ili četvero.',
            'Obavezna je rezervacija. Akcija vrijedi za rezervacije u trajanju 2 h preko tjedna u prijepodnevnom terminu do 16 h. Ne vrijedi vikendom te iza 16 h.'],
        [5, 'Vayu Reformer Pilates Studio', 'Sport i rekreacija', '120 €', 'Paket od 8 treninga po posebnoj cijeni',
            'Paket od 8 mjesečnih treninga za 120 € (redovna cijena 150 €). Skini aplikaciju, odaberi paket i rezerviraj termine.',
            'Uz kupon je potrebno predočiti i x-icu.', ['link' => 'https://www.vayu.hr']],
        [6, 'Školska knjiga', 'Obrazovanje', null, 'Vitez sedam kraljevina – George R. R. Martin',
            'Svoj primjerak potražite u svim knjižarama i webshopu Školske knjige.', null,
            ['link' => 'https://shop.skolskaknjiga.hr']],
        [7, 'Školska knjiga', 'Obrazovanje', '-20%', 'Studentski popust',
            '20% popusta na sveučilišna izdanja Školske knjige i 10% popusta na potreban pribor.',
            'Uz predočenje indeksa ili x-ice. Ponuda se ne odnosi na artikle na sniženju.',
            ['link' => 'https://shop.skolskaknjiga.hr']],
        [8, 'Burek Lab', 'Hrana i piće', '1+1', 'Buy 1 get 1 free',
            'Studentska ponuda: kupiš jedan, drugi dobivaš gratis.',
            'Uz predočenje važeće studentske iskaznice.'],
        [9, "Domino's Pizza", 'Hrana i piće', '-20%', 'Popust na pizze',
            '20% popusta uz kod na narudžbu.',
            'Ponuda se može iskoristiti samo uz predočenje važeće studentske iskaznice. Ne vrijedi za Promo pizzu i Napravi svoju pizzu. Ne može se kombinirati s drugim ponudama i popustima. Dostava se naplaćuje.',
            ['code' => '1747', 'valid_until' => '2026-12-31', 'link' => 'https://www.dominos.hr']],
        [10, 'Bowling Club Zagreb', 'Sport i rekreacija', '44 €', 'Super studentski paket (za 4 osobe)',
            'Paket uključuje 2 h bowlinga, cipele za bowling i 0,3 l soka ili točene svijetle pive po osobi, ponedjeljkom i utorkom cijeli dan. Cijena 44 € (11 € po osobi).',
            'Svaki korisnik paketa mora predočiti x-icu s osobnom iskaznicom. Jedna osoba ovu pogodnost može koristiti najviše 3 puta.',
            ['valid_until' => '2026-09-30', 'link' => 'https://www.bowlingclub.hr']],
        [11, 'Bowling Club Zagreb', 'Hrana i piće', '-20%', 'Popust na sve pizze',
            '20% popusta na ponudu svih pizza uz x-icu. Na 5 pizza 25%, a na 10 pizza 30% popusta.',
            'Samo za osobno preuzimanje. Kuhinja radi svaki dan od 10:00 do 22:45, tel. 01 3096 500.',
            ['valid_until' => '2026-09-30']],
        [12, 'Teeamo Bubble tea', 'Hrana i piće', '-20%', 'Popust na sva pića',
            '20% popusta na sva ponuđena pića uz kupon.', null],
        [13, 'Figura Bistro', 'Hrana i piće', '-15%', '15% popusta za studente',
            'Macro friendly brunch. Coffee. Study break.', null],
        [14, 'Thai Me Up by Lira', 'Hrana i piće', '-20%', 'Popust na azijsku i tajlandsku hranu',
            '20% popusta na thai food, a 30% za grupe od 10 i više gostiju.',
            'Posebna ponuda za studente koji nas posjete s kuponom u Teslinoj 5.'],
        [15, 'Crofna', 'Hrana i piće', '-50%', '50% popusta na drugu krafnicu',
            'Kupiš jednu, a drugi okus dobivaš za pola cijene!', null],
        [17, 'UNIC Virtualni kampus', 'Obrazovanje', null, 'Još nemaš svoj profil na UNIC Virtualnom kampusu?',
            'Online kolegiji, jezični tečajevi i združeni studiji. Registriraj se s AAI@EduHr i istraži sve mogućnosti.', null],
        [18, "R'N'R Museum – Muzej novog vala", 'Kultura i zabava', '-20%', 'Popust za studente sa Zlatnom kartom',
            'Izložbe, koncerti, pub, kviz, escape room i poklon galerija.', null],
        [19, 'Muzej povijesti videoigara', 'Kultura i zabava', '-20%', 'Popust na ulaznice',
            '20% popusta uz predočenje ovog kupona.', null],
        [20, 'Selfie & Memories Museum Zagreb', 'Kultura i zabava', 'do 20%', 'Popust na ulaznice',
            'Ostvari do 20% popusta na ulaznice preko ovog oglasa!', null],
        [21, 'Muzej smijeha HaHa House', 'Kultura i zabava', '-25%', 'Popust na studentske ulaznice',
            '25% popusta na studentske ulaznice, a uz 4 ulaznice peta je gratis (4+1).', null],
        [22, 'GDK Gavella', 'Kultura i zabava', '5 €', 'Ulaznica po posebnoj cijeni',
            'S kuponom ulaznicu kupite po posebnoj cijeni od 5 €, a za dvije ulaznice izdvojite 8 €.',
            'Kupon možete iskoristiti samo na blagajni kazališta. Ne vrijedi za gostujuće predstave, „Gavelline večeri”, „Noć kazališta” i „Gavella Fair”.'],
        [23, 'HNK u Zagrebu', 'Kultura i zabava', '-30%', 'Popust na ulaznice',
            '30% popusta na ulaznicu kupljenu na blagajni kazališta uz predočenje x-ice ili putem weba uz promo kod.',
            'Vrijedi za sve reprizne izvedbe u sezoni 2025./2026. Ulaz na predstavu uz predočenje x-ice.',
            ['code' => '30HNK23ST', 'link' => 'https://www.hnk.hr']],
        [24, 'TIX brzi servis', 'Usluge i kupovina', 'do 30%', 'Opremi svoj mobitel',
            '30% popusta na sva zaštitna stakla, 20% na sve maskice, 10% na zamjenu baterija za mobitele i 10% na kablove, adaptere i ostalu dodatnu opremu.', null],
        [25, 'Copymax', 'Usluge i kupovina', 'Studentske cijene', 'Posebne cijene za studente',
            'Kopiranje, printanje i skeniranje, uvez, plastificiranje, print velikih formata, izrada žigova, vizitki, letaka i postera, tisak na majice, torbe i šalice.', null],
        [26, 'F45 Training Radnička', 'Sport i rekreacija', '-20%', '7 dana besplatno i 20% na prvu članarinu',
            'Prvih 7 dana treniraš besplatno, a na prvu članarinu ti poklanjamo 20% popusta.',
            'Pokaži ovaj kupon i ostvari pogodnosti.',
            ['valid_until' => '2026-09-01', 'link' => 'https://www.f45-trening.hr']],
        [27, 'Vještina je reći ne', null, null, 'Vještina je reći ne',
            'Provjeri razinu svojih vještina u aplikaciji (Google Play i App Store).', null],
        [28, 'Knjižnice grada Zagreba', 'Obrazovanje', null, 'Otvori aplikaciju eZaKi',
            'Posudi, čitaj i slušaj!', null, ['link' => 'https://www.kgz.hr']],
        [29, 'Martina Hair', 'Usluge i kupovina', '-15%', 'Popust za studente',
            'Profesionalni salon za njegu kose: balayage, botox i keratin.',
            'Uz predočenje x-ice.', ['valid_until' => '2026-09-01']],
        [30, 'ZET', null, null, 'Godišnja učenička i studentska karta',
            'S nama do znanja! ZET kartu jednostavno preuzmi u aplikaciji Moj ZET.', null,
            ['link' => 'https://www.zet.hr']],
    ];

    public function run(): void
    {
        // Contact from the booklet, only where nothing is set yet.
        Setting::store(array_filter([
            'contact_email' => Setting::read('contact_email') ? null : 'zlatnakarta@gmail.com',
            'contact_instagram' => Setting::read('contact_instagram') ? null : '@studentska_zlatna_karta',
        ]));

        $edition = Edition::create([
            'name' => 'Proljeće/Ljeto 2026.',
            'about_title' => 'O projektu',
            'about_body' => "Studentska zlatna karta donosi popuste za studente u Zagrebu – hrana, sport, kultura i usluge na jednom mjestu.\n\n"
                .'Kupone mogu koristiti svi studenti bez obzira na mjesto stanovanja, zato je poželjno uvijek pri ruci imati x-icu ili indeks. '
                ."Kupone možete koristiti do 01.09.2026. ili do datuma koji je oglašivač naveo u svom oglasu.\n\n"
                .'Sufinancirano od strane Studentskog centra u Zagrebu. Organizator projekta: KMT Gornji Grad i USPZ-Hrvatska.',
            'pdf_path' => $this->copy('brosura.pdf', 'pdfs/szk-2026-proljece-ljeto.pdf'),
            'cover_path' => $this->copy('p01.jpg', 'covers/szk-2026-proljece-ljeto.jpg'),
        ]);
        $edition->activate();

        $titulars = collect(self::TITULARS)->map(function (array $data, string $name) {
            $titular = Titular::create([
                'name' => $name,
                'website' => $data['website'] ?? null,
                'instagram' => $data['instagram'] ?? null,
                'logo_path' => isset(self::LOGOS[$name])
                    ? $this->copy('logos/'.self::LOGOS[$name].'.jpg', 'logos/szk-2026-'.self::LOGOS[$name].'.jpg')
                    : null,
            ]);

            foreach ($data['locations'] ?? [] as [$address, $note, $lat, $lng]) {
                $titular->locations()->create(compact('address', 'note', 'lat', 'lng'));
            }

            return $titular;
        });

        $categories = collect(['Hrana i piće', 'Sport i rekreacija', 'Kultura i zabava', 'Usluge i kupovina', 'Obrazovanje'])
            ->mapWithKeys(fn ($name, $i) => [$name => Category::firstOrCreate(['name' => $name], ['sort_order' => $i * 10])->id]);

        foreach (self::COUPONS as $row) {
            [$page, $titular, $category, $discount, $title, $description, $conditions, $extra] = $row + [7 => []];

            $edition->coupons()->create([
                'titular_id' => $titulars[$titular]->id,
                'category_id' => $category ? $categories[$category] : null,
                'discount' => $discount,
                'title' => $title,
                'description' => $description,
                'conditions' => $conditions,
                'visual_path' => $this->copy(sprintf('p%02d.jpg', $page), sprintf('visuals/szk-2026-p%02d.jpg', $page)),
                'sort_order' => $page * 10,
                ...$extra,
            ]);
        }
    }

    private function copy(string $file, string $target): string
    {
        Storage::disk('public')->put($target, file_get_contents(__DIR__.'/data/2026/'.$file));

        return $target;
    }
}
