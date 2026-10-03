<?php

// Only the rules this app uses. Anything missing falls back to English.
return [
    'date' => 'Polje :attribute mora biti ispravan datum.',
    'between' => [
        'numeric' => 'Polje :attribute mora biti između :min i :max.',
    ],
    'email' => 'Polje :attribute mora biti ispravna email adresa.',
    'exists' => 'Odabrano polje :attribute ne postoji.',
    'file' => 'Polje :attribute mora biti datoteka.',
    'image' => 'Polje :attribute mora biti slika.',
    'integer' => 'Polje :attribute mora biti cijeli broj.',
    'max' => [
        'file' => 'Datoteka :attribute ne smije biti veća od :max KB.',
        'numeric' => 'Polje :attribute ne smije biti veće od :max.',
        'string' => 'Polje :attribute može imati najviše :max znakova.',
    ],
    'mimes' => 'Polje :attribute mora biti datoteka tipa: :values.',
    'min' => [
        'numeric' => 'Polje :attribute mora biti barem :min.',
        'string' => 'Polje :attribute mora imati barem :min znakova.',
    ],
    'numeric' => 'Polje :attribute mora biti broj.',
    'required' => 'Polje :attribute je obavezno.',
    'string' => 'Polje :attribute mora biti tekst.',
    'unique' => 'Ovaj :attribute je već zauzet.',
    'uploaded' => 'Prijenos datoteke :attribute nije uspio (možda je prevelika).',
    'url' => 'Polje :attribute mora biti ispravan link (počinje s https://).',

    'attributes' => [
        'name' => 'naziv',
        'email' => 'email',
        'password' => 'lozinka',
        'title' => 'naslov',
        'discount' => 'popust',
        'titular_id' => 'titular',
        'category_id' => 'kategorija',
        'visual' => 'vizual',
        'logo' => 'logotip',
        'cover' => 'naslovnica',
        'pdf' => 'PDF',
        'address' => 'adresa',
        'website' => 'web stranica',
        'contact_email' => 'email',
        'contact_link' => 'link',
        'countdown_seconds' => 'odbrojavanje',
        'sort_order' => 'redoslijed',
        'code' => 'promo kod',
        'link' => 'link',
        'valid_until' => 'vrijedi do',
        'note' => 'napomena',
    ],
];
