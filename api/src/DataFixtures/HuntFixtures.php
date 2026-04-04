<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Company;
use App\Entity\Hunt;
use App\Entity\HuntTranslation;
use App\Entity\Rarity;
use App\Entity\Reward;
use App\Entity\RewardTranslation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class HuntFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $huntsData = [
            'Lootopia' => [
                [
                    'category' => 'tech',
                    'rarity' => 'legendary',
                    'lat' => 48.8351,
                    'lon' => 2.3532,
                    'loc_fr' => 'Paris 13e',
                    'loc_en' => 'Paris 13th',
                    'title_fr' => 'L\'Origine du Butin',
                    'title_en' => 'The Loot Origin',
                    'desc_fr' => 'Rendez-vous à l\'endroit où tout a commencé. Le premier trésor de Lootopia est caché ici.',
                    'desc_en' => 'Go to where it all began. The first Lootopia treasure is hidden here.',
                    'quest_fr' => 'Quel célèbre animal orne la façade du cinéma de cette rue ?',
                    'quest_en' => 'What animal decorates the cinema facade?',
                    'ans_fr' => 'Fauvette',
                    'ans_en' => 'Fauvette',
                    'reward_fr' => 'Badge Fondateur',
                    'reward_en' => 'Founder Badge',
                    'reward_code' => 'LOOTOPIA-ORIGIN',
                ]
            ],
            'Burger King' => [
                [
                    'category' => 'food_bev',
                    'rarity' => 'common',
                    'lat' => 48.8925,
                    'lon' => 2.2388,
                    'loc_fr' => 'La Défense',
                    'loc_en' => 'La Defense',
                    'title_fr' => 'Le Whopper du Parvis',
                    'title_en' => 'The Plaza Whopper',
                    'desc_fr' => 'Au pied des immenses tours se cache le roi des burgers.',
                    'desc_en' => 'At the foot of the huge towers hides the burger king.',
                    'quest_fr' => 'Quelle est la forme du grand monument qui domine ce parvis ?',
                    'quest_en' => 'What is the shape of the large monument here?',
                    'ans_fr' => 'Arche',
                    'ans_en' => 'Arch',
                    'reward_fr' => 'Menu Whopper Offert',
                    'reward_en' => 'Free Whopper Meal',
                    'reward_code' => 'BK-DEFENSE-1',
                ]
            ],
            'Red Bull' => [
                [
                    'category' => 'food_bev',
                    'rarity' => 'epic',
                    'lat' => 48.8918,
                    'lon' => 2.2410,
                    'loc_fr' => 'La Défense',
                    'loc_en' => 'La Defense',
                    'title_fr' => 'Ailes de Métal',
                    'title_en' => 'Metal Wings',
                    'desc_fr' => 'L\'énergie se trouve près des étranges signaux métalliques.',
                    'desc_en' => 'Energy is found near the strange metal signals.',
                    'quest_fr' => 'Combien de feux signaux se trouvent dans le bassin ?',
                    'quest_en' => 'How many signal lights are in the basin?',
                    'ans_fr' => '49',
                    'ans_en' => '49',
                    'reward_fr' => 'Un an de Red Bull',
                    'reward_en' => 'One Year of Red Bull',
                    'reward_code' => 'WINGS-DEFENSE-99',
                ]
            ],
            'Danone' => [
                [
                    'category' => 'food_bev',
                    'rarity' => 'common',
                    'lat' => 48.8251,
                    'lon' => 2.2355,
                    'loc_fr' => 'Boulogne-Billancourt',
                    'loc_en' => 'Boulogne-Billancourt',
                    'title_fr' => 'La Source Lactée',
                    'title_en' => 'The Milky Source',
                    'desc_fr' => 'Cherchez près du grand paquebot de verre où naissent les yaourts.',
                    'desc_en' => 'Look near the great glass ship where yogurts are born.',
                    'quest_fr' => 'De quelle couleur est le logo historique de la marque ?',
                    'quest_en' => 'What color is the brand historical logo?',
                    'ans_fr' => 'Bleu',
                    'ans_en' => 'Blue',
                    'reward_fr' => '1 Mois de desserts',
                    'reward_en' => '1 Month of desserts',
                    'reward_code' => 'DANONE-MIAM',
                ]
            ],
            'Paul' => [
                [
                    'category' => 'food_bev',
                    'rarity' => 'common',
                    'lat' => 48.8710,
                    'lon' => 2.3045,
                    'loc_fr' => 'Champs-Elysees',
                    'loc_en' => 'Champs-Elysees',
                    'title_fr' => 'Le Pain Doré',
                    'title_en' => 'The Golden Bread',
                    'desc_fr' => 'La plus belle avenue du monde a une bonne odeur de croissant.',
                    'desc_en' => 'The most beautiful avenue smells like croissants.',
                    'quest_fr' => 'En quelle année la boulangerie a-t-elle été fondée (voir vitrine) ?',
                    'quest_en' => 'In what year was the bakery founded?',
                    'ans_fr' => '1889',
                    'ans_en' => '1889',
                    'reward_fr' => 'Café & Croissant offerts',
                    'reward_en' => 'Free Coffee & Croissant',
                    'reward_code' => 'PAUL-MORNING',
                ]
            ],
            'L\'Oréal' => [
                [
                    'category' => 'fashion_beauty',
                    'rarity' => 'rare',
                    'lat' => 48.9030,
                    'lon' => 2.3040,
                    'loc_fr' => 'Clichy',
                    'loc_en' => 'Clichy',
                    'title_fr' => 'Parce que vous le valez bien',
                    'title_en' => 'Because you\'re worth it',
                    'desc_fr' => 'Le berceau de la beauté mondiale abrite un secret cosmétique.',
                    'desc_en' => 'The cradle of global beauty hides a cosmetic secret.',
                    'quest_fr' => 'Quel est le prénom du fondateur de la marque ?',
                    'quest_en' => 'What is the first name of the brand founder?',
                    'ans_fr' => 'Eugène',
                    'ans_en' => 'Eugene',
                    'reward_fr' => 'Coffret Soin Prestige',
                    'reward_en' => 'Prestige Skincare Box',
                    'reward_code' => 'LOREAL-VIP',
                ]
            ],
            'Chanel' => [
                [
                    'category' => 'fashion_beauty',
                    'rarity' => 'legendary',
                    'lat' => 48.8675,
                    'lon' => 2.3268,
                    'loc_fr' => 'Paris 1er',
                    'loc_en' => 'Paris 1st',
                    'title_fr' => 'L\'Escalier aux Miroirs',
                    'title_en' => 'The Mirrored Staircase',
                    'desc_fr' => 'Là où Gabrielle créait la légende, un parfum de mystère flotte encore.',
                    'desc_en' => 'Where Gabrielle created the legend, a scent of mystery remains.',
                    'quest_fr' => 'Quel célèbre numéro est associé à cette adresse ?',
                    'quest_en' => 'What famous number is associated with this address?',
                    'ans_fr' => '5',
                    'ans_en' => '5',
                    'reward_fr' => 'Flacon N°5 Gravé',
                    'reward_en' => 'Engraved N°5 Bottle',
                    'reward_code' => 'CHANEL-NO5',
                ]
            ],
            'Sephora' => [
                [
                    'category' => 'fashion_beauty',
                    'rarity' => 'epic',
                    'lat' => 48.8705,
                    'lon' => 2.3060,
                    'loc_fr' => 'Champs-Élysées',
                    'loc_en' => 'Champs-Elysees',
                    'title_fr' => 'Le Temple Noir et Blanc',
                    'title_en' => 'The Black and White Temple',
                    'desc_fr' => 'Des rayures iconiques vous guideront vers le maquillage parfait.',
                    'desc_en' => 'Iconic stripes will guide you to the perfect makeup.',
                    'quest_fr' => 'De quelle couleur est le tapis à l\'entrée du magasin ?',
                    'quest_en' => 'What color is the carpet at the store entrance?',
                    'ans_fr' => 'Rouge',
                    'ans_en' => 'Red',
                    'reward_fr' => 'Carte Cadeau 50€',
                    'reward_en' => '50€ Gift Card',
                    'reward_code' => 'SEPHO-BEAUTY50',
                ]
            ],
            'Yves Rocher' => [
                [
                    'category' => 'fashion_beauty',
                    'rarity' => 'common',
                    'lat' => 48.8440,
                    'lon' => 2.3600, // Près du Jardin des Plantes
                    'loc_fr' => 'Paris 5e',
                    'loc_en' => 'Paris 5th',
                    'title_fr' => 'Botanique Cachée',
                    'title_en' => 'Hidden Botany',
                    'desc_fr' => 'La beauté par les plantes commence au cœur du plus vieux jardin de Paris.',
                    'desc_en' => 'Plant-based beauty starts in the oldest garden of Paris.',
                    'quest_fr' => 'Quelle fleur est le symbole historique de la marque ?',
                    'quest_en' => 'Which flower is the brand historical symbol?',
                    'ans_fr' => 'Camomille',
                    'ans_en' => 'Chamomile',
                    'reward_fr' => 'Trousse Nature',
                    'reward_en' => 'Nature Pouch',
                    'reward_code' => 'YR-PLANT',
                ]
            ],
            'Carrefour' => [
                [
                    'category' => 'retail',
                    'rarity' => 'epic',
                    'lat' => 48.6360,
                    'lon' => 2.3240,
                    'loc_fr' => 'Sainte-Geneviève-des-Bois',
                    'loc_en' => 'Sainte-Genevieve-des-Bois',
                    'title_fr' => 'Le Premier Hyper',
                    'title_en' => 'The First Hypermarket',
                    'desc_fr' => 'C\'est ici, en 1963, que le concept de l\'hypermarché est né.',
                    'desc_en' => 'Here, in 1963, the concept of the hypermarket was born.',
                    'quest_fr' => 'Quelle est l\'année de naissance de ce magasin (cherchez la plaque) ?',
                    'quest_en' => 'What is the birth year of this store?',
                    'ans_fr' => '1963',
                    'ans_en' => '1963',
                    'reward_fr' => '100€ sur la carte de fidélité',
                    'reward_en' => '100€ loyalty bonus',
                    'reward_code' => 'CARREFOUR-1963',
                ]
            ],
            'E.Leclerc' => [
                [
                    'category' => 'retail',
                    'rarity' => 'rare',
                    'lat' => 48.8150,
                    'lon' => 2.3850,
                    'loc_fr' => 'Ivry-sur-Seine',
                    'loc_en' => 'Ivry-sur-Seine',
                    'title_fr' => 'Le Juste Prix',
                    'title_en' => 'The Right Price',
                    'desc_fr' => 'Défendre le pouvoir d\'achat demande de résoudre cette énigme.',
                    'desc_en' => 'Defending purchasing power requires solving this riddle.',
                    'quest_fr' => 'Quelle est la couleur principale du logo ?',
                    'quest_en' => 'What is the main color of the logo?',
                    'ans_fr' => 'Bleu',
                    'ans_en' => 'Blue',
                    'reward_fr' => 'Bon d\'achat 20€',
                    'reward_en' => '20€ Voucher',
                    'reward_code' => 'LECLERC-20',
                ]
            ],
            'Fnac' => [
                [
                    'category' => 'retail',
                    'rarity' => 'common',
                    'lat' => 48.8615,
                    'lon' => 2.3470,
                    'loc_fr' => 'Châtelet',
                    'loc_en' => 'Chatelet',
                    'title_fr' => 'L\'Agitateur Culturel',
                    'title_en' => 'The Cultural Agitator',
                    'desc_fr' => 'Entre les livres et la tech, un code est caché sous la Canopée.',
                    'desc_en' => 'Between books and tech, a code is hidden under the Canopy.',
                    'quest_fr' => 'Combien de lettres composent le mot "FNAC" ?',
                    'quest_en' => 'How many letters in the word "FNAC"?',
                    'ans_fr' => '4',
                    'ans_en' => '4',
                    'reward_fr' => 'Livre de poche offert',
                    'reward_en' => 'Free paperback book',
                    'reward_code' => 'FNAC-READ',
                ]
            ],
            'Auchan' => [
                [
                    'category' => 'retail',
                    'rarity' => 'common',
                    'lat' => 48.7810,
                    'lon' => 2.2190,
                    'loc_fr' => 'Vélizy 2',
                    'loc_en' => 'Velizy 2',
                    'title_fr' => 'La Vie en Rouge',
                    'title_en' => 'Life in Red',
                    'desc_fr' => 'Le célèbre petit oiseau vous donne rendez-vous dans ce centre commercial mythique.',
                    'desc_en' => 'The famous little bird meets you in this legendary mall.',
                    'quest_fr' => 'Quel animal est sur le logo d\'Auchan ?',
                    'quest_en' => 'What animal is on the Auchan logo?',
                    'ans_fr' => 'Oiseau',
                    'ans_en' => 'Bird',
                    'reward_fr' => 'Livraison offerte',
                    'reward_en' => 'Free delivery',
                    'reward_code' => 'AUCHAN-FREE',
                ]
            ],
            'Decathlon' => [
                [
                    'category' => 'sport_outdoor',
                    'rarity' => 'epic',
                    'lat' => 48.4044,
                    'lon' => 2.6996,
                    'loc_fr' => 'Fontainebleau',
                    'loc_en' => 'Fontainebleau',
                    'title_fr' => 'La Boussole Perdue',
                    'title_en' => 'The Lost Compass',
                    'desc_fr' => 'La nature vous appelle. Explorez les blocs de grès de Fontainebleau.',
                    'desc_en' => 'Nature calls. Explore the sandstone boulders of Fontainebleau.',
                    'quest_fr' => 'Quel est le nom de la marque de randonnée de l\'enseigne ?',
                    'quest_en' => 'What is the name of their hiking brand?',
                    'ans_fr' => 'Quechua',
                    'ans_en' => 'Quechua',
                    'reward_fr' => 'Tente 2 secondes offerte',
                    'reward_en' => 'Free 2-second tent',
                    'reward_code' => 'DECA-TENT',
                ]
            ],
            'Salomon' => [
                [
                    'category' => 'sport_outdoor',
                    'rarity' => 'rare',
                    'lat' => 48.8860,
                    'lon' => 2.3430,
                    'loc_fr' => 'Montmartre',
                    'loc_en' => 'Montmartre',
                    'title_fr' => 'Trail Urbain',
                    'title_en' => 'Urban Trail',
                    'desc_fr' => 'Gravissez les plus hautes marches de Paris pour prouver votre endurance.',
                    'desc_en' => 'Climb the highest steps of Paris to prove your endurance.',
                    'quest_fr' => 'Combien de marches environ compte l\'escalier du funiculaire ?',
                    'quest_en' => 'How many steps does the funicular staircase have?',
                    'ans_fr' => '222',
                    'ans_en' => '222',
                    'reward_fr' => '-30% sur chaussures de Trail',
                    'reward_en' => '-30% on Trail shoes',
                    'reward_code' => 'SALOMON-RUN',
                ]
            ],
            'Rossignol' => [
                [
                    'category' => 'sport_outdoor',
                    'rarity' => 'rare',
                    'lat' => 48.8780,
                    'lon' => 2.3820, // Buttes-Chaumont
                    'loc_fr' => 'Buttes-Chaumont',
                    'loc_en' => 'Buttes-Chaumont',
                    'title_fr' => 'Le Sommet de Paris',
                    'title_en' => 'The Peak of Paris',
                    'desc_fr' => 'Un air de montagne flotte sur ce parc escarpé.',
                    'desc_en' => 'A mountain breeze blows over this steep park.',
                    'quest_fr' => 'Comment s\'appelle le temple au sommet du rocher ?',
                    'quest_en' => 'What is the name of the temple on top of the rock?',
                    'ans_fr' => 'Sibylle',
                    'ans_en' => 'Sibylle',
                    'reward_fr' => 'Bonnet de ski en laine',
                    'reward_en' => 'Wool ski beanie',
                    'reward_code' => 'ROSS-SNOW',
                ]
            ],
            'Le Coq Sportif' => [
                [
                    'category' => 'sport_outdoor',
                    'rarity' => 'legendary',
                    'lat' => 48.9240,
                    'lon' => 2.3590,
                    'loc_fr' => 'Stade de France',
                    'loc_en' => 'Stade de France',
                    'title_fr' => 'L\'Esprit Olympique',
                    'title_en' => 'The Olympic Spirit',
                    'desc_fr' => 'Rendez-vous là où les légendes du sport français ont triomphé.',
                    'desc_en' => 'Go where French sports legends triumphed.',
                    'quest_fr' => 'En quelle année le stade a-t-il été inauguré ?',
                    'quest_en' => 'In what year was the stadium inaugurated?',
                    'ans_fr' => '1998',
                    'ans_en' => '1998',
                    'reward_fr' => 'Maillot Officiel de l\'Équipe de France',
                    'reward_en' => 'Official National Team Jersey',
                    'reward_code' => 'COQ-VICTOIRE',
                ]
            ],
            'Orange' => [
                [
                    'category' => 'tech',
                    'rarity' => 'common',
                    'lat' => 48.8280,
                    'lon' => 2.2610,
                    'loc_fr' => 'Issy-les-Moulineaux',
                    'loc_en' => 'Issy-les-Moulineaux',
                    'title_fr' => 'Le Réseau Absolu',
                    'title_en' => 'The Absolute Network',
                    'desc_fr' => 'Le centre névralgique des télécommunications cache un signal 5G mystère.',
                    'desc_en' => 'The telecom hub hides a mystery 5G signal.',
                    'quest_fr' => 'Quelle est la couleur historique du logo ?',
                    'quest_en' => 'What is the historical color of the logo?',
                    'ans_fr' => 'Orange',
                    'ans_en' => 'Orange',
                    'reward_fr' => '50 Go d\'internet offerts',
                    'reward_en' => '50 GB of free data',
                    'reward_code' => 'ORANGE-50G',
                ]
            ],
            'Free' => [
                [
                    'category' => 'tech',
                    'rarity' => 'epic',
                    'lat' => 48.8340,
                    'lon' => 2.3710,
                    'loc_fr' => 'Station F',
                    'loc_en' => 'Station F',
                    'title_fr' => 'L\'Incubateur Pirate',
                    'title_en' => 'The Pirate Incubator',
                    'desc_fr' => 'Dans la plus grande halle de startups au monde, trouvez la borne secrète.',
                    'desc_en' => 'In the world\'s largest startup campus, find the secret kiosk.',
                    'quest_fr' => 'Quel célèbre entrepreneur français a fondé Station F ?',
                    'quest_en' => 'Which famous French entrepreneur founded Station F?',
                    'ans_fr' => 'Niel',
                    'ans_en' => 'Niel',
                    'reward_fr' => 'Abonnement Freebox 6 mois',
                    'reward_en' => '6-month Freebox subscription',
                    'reward_code' => 'FREE-HACKER',
                ]
            ],
            'Boulanger' => [
                [
                    'category' => 'tech',
                    'rarity' => 'rare',
                    'lat' => 48.8490,
                    'lon' => 2.2820,
                    'loc_fr' => 'Centre Beaugrenelle',
                    'loc_en' => 'Beaugrenelle Mall',
                    'title_fr' => 'Tech sur Seine',
                    'title_en' => 'Tech on the Seine',
                    'desc_fr' => 'Près de la Seine, l\'électroménager cache des puces intelligentes.',
                    'desc_en' => 'Near the Seine, appliances hide smart chips.',
                    'quest_fr' => 'Comment s\'appelle l\'île juste à côté du centre ?',
                    'quest_en' => 'What is the name of the island right next to the mall?',
                    'ans_fr' => 'Cygnes',
                    'ans_en' => 'Swans',
                    'reward_fr' => 'Carte cadeau 30€',
                    'reward_en' => '30€ Gift Card',
                    'reward_code' => 'BOULANGER-TECH',
                ]
            ],
            'LDLC' => [
                [
                    'category' => 'tech',
                    'rarity' => 'common',
                    'lat' => 48.8410,
                    'lon' => 2.2850,
                    'loc_fr' => 'Paris 15e',
                    'loc_en' => 'Paris 15th',
                    'title_fr' => 'L\'Antre du PC',
                    'title_en' => 'The PC Lair',
                    'desc_fr' => 'Le repaire des gamers parisiens détient le composant manquant.',
                    'desc_en' => 'The Parisian gamers\' lair holds the missing component.',
                    'quest_fr' => 'Quel animal est la mascotte de l\'assemblage PC ?',
                    'quest_en' => 'What animal is the PC assembly mascot?',
                    'ans_fr' => 'Renard',
                    'ans_en' => 'Fox',
                    'reward_fr' => 'Souris Gamer Offerte',
                    'reward_en' => 'Free Gaming Mouse',
                    'reward_code' => 'LDLC-GAMER',
                ]
            ],
            'Pathé' => [
                [
                    'category' => 'entertainment',
                    'rarity' => 'rare',
                    'lat' => 48.8830,
                    'lon' => 2.3270,
                    'loc_fr' => 'Place de Clichy',
                    'loc_en' => 'Place de Clichy',
                    'title_fr' => 'Le Billet Doré',
                    'title_en' => 'The Golden Ticket',
                    'desc_fr' => 'Le cinéma historique cache un accès pour les avant-premières.',
                    'desc_en' => 'The historic cinema hides access for premieres.',
                    'quest_fr' => 'Quel oiseau est l\'emblème historique de Pathé ?',
                    'quest_en' => 'What bird is the historical emblem of Pathe?',
                    'ans_fr' => 'Coq',
                    'ans_en' => 'Rooster',
                    'reward_fr' => '2 Places de Cinéma',
                    'reward_en' => '2 Movie Tickets',
                    'reward_code' => 'PATHE-MOVIE',
                ]
            ],
            'Parc Astérix' => [
                [
                    'category' => 'entertainment',
                    'rarity' => 'epic',
                    'lat' => 48.8445,
                    'lon' => 2.3525,
                    'loc_fr' => 'Arènes de Lutèce',
                    'loc_en' => 'Arenes de Lutece',
                    'title_fr' => 'Le Secret de Lutèce',
                    'title_en' => 'The Lutetia Secret',
                    'desc_fr' => 'Par Toutatis ! Un parchemin gaulois est dissimulé dans ces ruines romaines.',
                    'desc_en' => 'By Toutatis! A Gallic scroll is hidden in these Roman ruins.',
                    'quest_fr' => 'Quel était le nom de Paris à l\'époque romaine ?',
                    'quest_en' => 'What was the name of Paris in Roman times?',
                    'ans_fr' => 'Lutèce',
                    'ans_en' => 'Lutetia',
                    'reward_fr' => 'Entrée Parc Astérix',
                    'reward_en' => 'Parc Asterix Ticket',
                    'reward_code' => 'ASTERIX-GAULOIS',
                ]
            ],
            'Puy du Fou' => [
                [
                    'category' => 'entertainment',
                    'rarity' => 'legendary',
                    'lat' => 48.8420,
                    'lon' => 2.4430,
                    'loc_fr' => 'Vincennes',
                    'loc_en' => 'Vincennes',
                    'title_fr' => 'L\'Anneau du Donjon',
                    'title_en' => 'The Keep Ring',
                    'desc_fr' => 'Pour vivre l\'histoire, cherchez près du plus haut donjon d\'Europe.',
                    'desc_en' => 'To live history, look near the highest keep in Europe.',
                    'quest_fr' => 'Quel roi de France a fait construire ce donjon ?',
                    'quest_en' => 'Which King of France built this keep?',
                    'ans_fr' => 'Charles V',
                    'ans_en' => 'Charles V',
                    'reward_fr' => 'Pass Séjour Puy du Fou',
                    'reward_en' => 'Puy du Fou Stay Pass',
                    'reward_code' => 'PUY-HISTOIRE',
                ]
            ],
            'Gaumont' => [
                [
                    'category' => 'entertainment',
                    'rarity' => 'common',
                    'lat' => 48.8700,
                    'lon' => 2.3050,
                    'loc_fr' => 'Champs-Élysées',
                    'loc_en' => 'Champs-Elysees',
                    'title_fr' => 'La Marguerite de Celluloïd',
                    'title_en' => 'The Celluloid Daisy',
                    'desc_fr' => 'La plus vieille société de cinéma au monde a laissé une trace sur l\'avenue.',
                    'desc_en' => 'The oldest film company in the world left a trace on the avenue.',
                    'quest_fr' => 'Quelle fleur est le logo de Gaumont ?',
                    'quest_en' => 'What flower is the Gaumont logo?',
                    'ans_fr' => 'Marguerite',
                    'ans_en' => 'Daisy',
                    'reward_fr' => 'Affiche de film collector',
                    'reward_en' => 'Collector movie poster',
                    'reward_code' => 'GAUMONT-POSTER',
                ]
            ],
            'Air France' => [
                [
                    'category' => 'tourism',
                    'rarity' => 'legendary',
                    'lat' => 49.0090,
                    'lon' => 2.5470,
                    'loc_fr' => 'Roissy-CDG',
                    'loc_en' => 'Roissy-CDG',
                    'title_fr' => 'L\'Envol Secret',
                    'title_en' => 'The Secret Flight',
                    'desc_fr' => 'Sous les verrières du Terminal 2E se trouve la clé du voyage.',
                    'desc_en' => 'Under the glass roof of Terminal 2E lies the key to travel.',
                    'quest_fr' => 'Comment s\'appelle l\'hippocampe ailé, emblème de la compagnie ?',
                    'quest_en' => 'What is the name of the winged seahorse, the company\'s emblem?',
                    'ans_fr' => 'Crevette',
                    'ans_en' => 'Shrimp',
                    'reward_fr' => 'Billet Aller-Retour Europe',
                    'reward_en' => 'Round-trip Ticket Europe',
                    'reward_code' => 'AIRFR-FLY',
                ]
            ],
            'SNCF' => [
                [
                    'category' => 'tourism',
                    'rarity' => 'epic',
                    'lat' => 48.8443,
                    'lon' => 2.3744,
                    'loc_fr' => 'Gare de Lyon',
                    'loc_en' => 'Gare de Lyon',
                    'title_fr' => 'Le Train Bleu',
                    'title_en' => 'The Blue Train',
                    'desc_fr' => 'Sous l\'horloge majestueuse, un départ vers le Sud se prépare.',
                    'desc_en' => 'Under the majestic clock, a departure towards the South is preparing.',
                    'quest_fr' => 'Comment s\'appelle le restaurant classé monument historique de cette gare ?',
                    'quest_en' => 'What is the name of the historical monument restaurant in this station?',
                    'ans_fr' => 'Train Bleu',
                    'ans_en' => 'Train Bleu',
                    'reward_fr' => 'Carte Avantage Adulte',
                    'reward_en' => 'Adult Discount Card',
                    'reward_code' => 'SNCF-TGV',
                ]
            ],
            'Club Med' => [
                [
                    'category' => 'tourism',
                    'rarity' => 'rare',
                    'lat' => 48.8920,
                    'lon' => 2.3880,
                    'loc_fr' => 'Paris 19e',
                    'loc_en' => 'La Villette Park',
                    'title_fr' => 'L\'Oasis Urbaine',
                    'title_en' => 'The Urban Oasis',
                    'desc_fr' => 'Trouvez le trident caché au milieu des grandes pelouses.',
                    'desc_en' => 'Find the hidden trident in the middle of the large lawns.',
                    'quest_fr' => 'Comment appelle-t-on les animateurs dans les villages Club Med ?',
                    'quest_en' => 'What are the entertainers called in Club Med resorts?',
                    'ans_fr' => 'GO',
                    'ans_en' => 'GO',
                    'reward_fr' => 'Bon Cadeau Séjour de 500€',
                    'reward_en' => '500€ Stay Voucher',
                    'reward_code' => 'CLUBMED-SUN',
                ]
            ],
            'Accor' => [
                [
                    'category' => 'tourism',
                    'rarity' => 'common',
                    'lat' => 48.8270,
                    'lon' => 2.2630,
                    'loc_fr' => 'Tour Séquana',
                    'loc_en' => 'Sequana Tower',
                    'title_fr' => 'La Clé de la Suite',
                    'title_en' => 'The Suite Key',
                    'desc_fr' => 'Le géant de l\'hôtellerie vous accueille au pied de sa tour.',
                    'desc_en' => 'The hotel giant welcomes you at the foot of its tower.',
                    'quest_fr' => 'Quel oiseau migrateur est le symbole de la marque ?',
                    'quest_en' => 'Which migratory bird is the brand\'s symbol?',
                    'ans_fr' => 'Bernache',
                    'ans_en' => 'Goose',
                    'reward_fr' => 'Nuit d\'hôtel offerte',
                    'reward_en' => 'Free Hotel Night',
                    'reward_code' => 'ACCOR-NIGHT',
                ]
            ],
            'Les Restos du Cœur' => [
                [
                    'category' => 'charity',
                    'rarity' => 'rare',
                    'lat' => 48.8730,
                    'lon' => 2.3510, // Rue d'Enghien
                    'loc_fr' => 'Paris 10e',
                    'loc_en' => 'Paris 10th',
                    'title_fr' => 'L\'Héritage de Coluche',
                    'title_en' => 'Coluche\'s Legacy',
                    'desc_fr' => 'La solidarité a commencé ici. Trouvez le symbole de la main tendue.',
                    'desc_en' => 'Solidarity started here. Find the symbol of the reaching hand.',
                    'quest_fr' => 'En quelle année l\'association a-t-elle été fondée ?',
                    'quest_en' => 'In what year was the association founded?',
                    'ans_fr' => '1985',
                    'ans_en' => '1985',
                    'reward_fr' => 'Pin\'s Solidaire & Diplôme',
                    'reward_en' => 'Solidarity Pin & Diploma',
                    'reward_code' => 'RESTOS-HEART',
                ]
            ],
            'Secours Populaire' => [
                [
                    'category' => 'charity',
                    'rarity' => 'common',
                    'lat' => 48.8870,
                    'lon' => 2.3420, // Montmartre
                    'loc_fr' => 'Montmartre',
                    'loc_en' => 'Montmartre',
                    'title_fr' => 'Copain du Monde',
                    'title_en' => 'Friend of the World',
                    'desc_fr' => 'Tout le monde a le droit aux vacances. Un trésor solidaire se cache ici.',
                    'desc_en' => 'Everyone has the right to a vacation. A solidarity treasure hides here.',
                    'quest_fr' => 'Que tient l\'enfant dans le logo de l\'association ?',
                    'quest_en' => 'What is the child holding in the association\'s logo?',
                    'ans_fr' => 'Colombe',
                    'ans_en' => 'Dove',
                    'reward_fr' => 'T-shirt Solidaire',
                    'reward_en' => 'Solidarity T-shirt',
                    'reward_code' => 'SPF-DON',
                ]
            ],
            'Emmaüs' => [
                [
                    'category' => 'charity',
                    'rarity' => 'epic',
                    'lat' => 48.8900,
                    'lon' => 2.3720, // Bric a brac Riquet
                    'loc_fr' => 'Paris 19e',
                    'loc_en' => 'Paris 19th',
                    'title_fr' => 'La Seconde Vie',
                    'title_en' => 'The Second Life',
                    'desc_fr' => 'Donner une seconde chance aux objets et aux Hommes.',
                    'desc_en' => 'Giving a second chance to objects and Men.',
                    'quest_fr' => 'Quel abbé célèbre a fondé le mouvement Emmaüs ?',
                    'quest_en' => 'Which famous abbot founded the Emmaus movement?',
                    'ans_fr' => 'Pierre',
                    'ans_en' => 'Pierre',
                    'reward_fr' => 'Bon de 50€ en boutique',
                    'reward_en' => '50€ Shop Voucher',
                    'reward_code' => 'EMMAUS-LIFE',
                ]
            ],
            'Croix-Rouge' => [
                [
                    'category' => 'charity',
                    'rarity' => 'common',
                    'lat' => 48.8180,
                    'lon' => 2.3170, // Campus Montrouge
                    'loc_fr' => 'Montrouge',
                    'loc_en' => 'Montrouge',
                    'title_fr' => 'Les Premiers Secours',
                    'title_en' => 'First Aid',
                    'desc_fr' => 'Apprenez à sauver des vies, le premier indice est près du centre de formation.',
                    'desc_en' => 'Learn to save lives, the first clue is near the training center.',
                    'quest_fr' => 'Combien de branches possède la croix rouge du logo ?',
                    'quest_en' => 'How many branches does the red cross logo have?',
                    'ans_fr' => '4',
                    'ans_en' => '4',
                    'reward_fr' => 'Formation PSC1 Offerte',
                    'reward_en' => 'Free First Aid Training',
                    'reward_code' => 'CROIX-R-PSC1',
                ]
            ],

            'Le Louvre' => [
                [
                    'category' => 'culture',
                    'rarity' => 'rare',
                    'lat' => 48.8606,
                    'lon' => 2.3376, // Pyramide
                    'loc_fr' => 'Louvre',
                    'loc_en' => 'Louvre',
                    'title_fr' => 'L\'Illusion de Verre',
                    'title_en' => 'The Glass Illusion',
                    'desc_fr' => 'Un grand architecte a laissé sa marque de verre au milieu du palais.',
                    'desc_en' => 'A great architect left his glass mark in the middle of the palace.',
                    'quest_fr' => 'Quel est l\'architecte de la célèbre Pyramide du Louvre ?',
                    'quest_en' => 'Who is the architect of the famous Louvre Pyramid?',
                    'ans_fr' => 'Pei',
                    'ans_en' => 'Pei',
                    'reward_fr' => 'Billet coupe-file',
                    'reward_en' => 'Skip-the-line ticket',
                    'reward_code' => 'LOUVRE-VIP-1',
                ]
            ],
            'Musée d\'Orsay' => [
                [
                    'category' => 'culture',
                    'rarity' => 'epic',
                    'lat' => 48.8590,
                    'lon' => 2.3260, // Parvis
                    'loc_fr' => 'Musée d\'Orsay',
                    'loc_en' => 'Musee d\'Orsay',
                    'title_fr' => 'L\'Heure des Impressionnistes',
                    'title_en' => 'The Impressionist Hour',
                    'desc_fr' => 'Le temps s\'est arrêté dans cette ancienne gare dédiée à l\'art.',
                    'desc_en' => 'Time stopped in this former train station dedicated to art.',
                    'quest_fr' => 'Que regarde l\'immense horloge vitrée de l\'intérieur du musée ?',
                    'quest_en' => 'What does the huge glass clock look at from inside the museum?',
                    'ans_fr' => 'Montmartre',
                    'ans_en' => 'Montmartre',
                    'reward_fr' => 'Pass Annuel Duo',
                    'reward_en' => 'Annual Duo Pass',
                    'reward_code' => 'ORSAY-TIME',
                ]
            ],
            'Château de Versailles' => [
                [
                    'category' => 'culture',
                    'rarity' => 'legendary',
                    'lat' => 48.8040,
                    'lon' => 2.1200, // Galerie des Glaces
                    'loc_fr' => 'Versailles',
                    'loc_en' => 'Versailles',
                    'title_fr' => 'Le Trésor du Roi Soleil',
                    'title_en' => 'The Sun King\'s Treasure',
                    'desc_fr' => 'L\'or brille partout, mais le vrai butin se cache dans les jardins de Le Nôtre.',
                    'desc_en' => 'Gold shines everywhere, but the real loot is hidden in Le Notre\'s gardens.',
                    'quest_fr' => 'Quel roi est surnommé le Roi Soleil ?',
                    'quest_en' => 'Which king is nicknamed the Sun King?',
                    'ans_fr' => 'Louis XIV',
                    'ans_en' => 'Louis XIV',
                    'reward_fr' => 'Visite Privée du Trianon',
                    'reward_en' => 'Private Tour of Trianon',
                    'reward_code' => 'VERSAILLES-SUN',
                ]
            ],
            'Centre Pompidou' => [
                [
                    'category' => 'culture',
                    'rarity' => 'rare',
                    'lat' => 48.8600,
                    'lon' => 2.3520, // Piazza
                    'loc_fr' => 'Paris 4e',
                    'loc_en' => 'Paris 4th',
                    'title_fr' => 'Les Tuyaux de l\'Art',
                    'title_en' => 'The Art Pipes',
                    'desc_fr' => 'Le bâtiment a ses entrailles à l\'extérieur. Suivez les tuyaux colorés.',
                    'desc_en' => 'The building wears its guts on the outside. Follow the colored pipes.',
                    'quest_fr' => 'De quelle couleur sont les tuyaux de climatisation sur la façade ?',
                    'quest_en' => 'What color are the AC pipes on the facade?',
                    'ans_fr' => 'Bleu',
                    'ans_en' => 'Blue',
                    'reward_fr' => 'Affiche Expo Temporaire',
                    'reward_en' => 'Temporary Expo Poster',
                    'reward_code' => 'BEAUBOURG-ART',
                ]
            ],
        ];

        $companyRepo = $manager->getRepository(Company::class);

        foreach ($huntsData as $companyName => $hunts) {
            $company = $companyRepo->findOneBy(['name' => $companyName]);

            if (!$company) {
                continue;
            }

            foreach ($hunts as $data) {
                $categoryReference = $this->getReference(CategoryFixtures::CATEGORY_REFERENCE_PREFIX . $data['category'], Category::class);
                if (!$categoryReference) {
                    throw new \RuntimeException(sprintf('Invalid category reference: %s', $data['category']));
                }

                $rarityReference = $this->getReference(RarityFixtures::RARITY_REFERENCE_PREFIX . $data['rarity'], Rarity::class);
                if (!$rarityReference) {
                    throw new \RuntimeException(sprintf('Invalid rarity reference: %s', $data['rarity']));
                }

                $hunt = new Hunt();
                $hunt->setCompany($company);
                $hunt->setLat($data['lat']);
                $hunt->setLon($data['lon']);

                $hunt->setCategory($categoryReference);
                $hunt->setRarity($rarityReference);

                $huntTranslationFr = new HuntTranslation();
                $huntTranslationFr->setLocale('fr');
                $huntTranslationFr->setTitle($data['title_fr']);
                $huntTranslationFr->setDescription($data['desc_fr']);
                $huntTranslationFr->setQuestion($data['quest_fr']);
                $huntTranslationFr->setAnswer($data['ans_fr']);
                $huntTranslationFr->setLocation($data['loc_fr']);

                $huntTranslationEn = new HuntTranslation();
                $huntTranslationEn->setLocale('en');
                $huntTranslationEn->setTitle($data['title_en']);
                $huntTranslationEn->setDescription($data['desc_en']);
                $huntTranslationEn->setQuestion($data['quest_en']);
                $huntTranslationEn->setAnswer($data['ans_en']);
                $huntTranslationEn->setLocation($data['loc_en']);

                $hunt->addHuntTranslation($huntTranslationFr);
                $hunt->addHuntTranslation($huntTranslationEn);

                $manager->persist($huntTranslationFr);
                $manager->persist($huntTranslationEn);

                $reward = new Reward();
                $reward->setCode($data['reward_code']);
                $cleanCompanyName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $companyName));
                $reward->setLink('https://' . $cleanCompanyName . '.com');
                $reward->setEndDate((new \DateTime())->modify('+3 months'));
                $hunt->setReward($reward);

                $rewardTranslationFr = new RewardTranslation();
                $rewardTranslationFr->setLocale('fr');
                $rewardTranslationFr->setTitle($data['reward_fr']);

                $rewardTranslationEn = new RewardTranslation();
                $rewardTranslationEn->setLocale('en');
                $rewardTranslationEn->setTitle($data['reward_en']);

                $reward->addRewardTranslation($rewardTranslationFr);
                $reward->addRewardTranslation($rewardTranslationEn);

                $manager->persist($rewardTranslationFr);
                $manager->persist($rewardTranslationEn);
                $manager->persist($reward);
                $manager->persist($hunt);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            CategoryFixtures::class,
            RarityFixtures::class,
        ];
    }
}
