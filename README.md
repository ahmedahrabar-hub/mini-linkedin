# Mini LinkedIn — API REST Laravel

API REST d'une plateforme de recrutement mettant en relation candidats et recruteurs.

## Technologies utilisées
- Laravel 13
- PHP 8.4
- MySQL 8
- Docker
- JWT Authentication

## Prérequis
- Docker Desktop
- Composer
- PHP 8.4

## Installation

1. Cloner le projet
git clone https://github.com/ahmedahrabar-hub/mini-linkedin.git
cd mini-linkedin

2. Copier le fichier .env
cp .env.example .env

3. Lancer Docker
docker-compose up --build

4. Lancer les migrations et seeders
docker exec -it mini-linkedin-app php artisan migrate:fresh --seed

5. Générer la clé JWT
docker exec -it mini-linkedin-app php artisan jwt:secret

## Rôles
- **candidat** → créer profil, ajouter compétences, postuler
- **recruteur** → publier offres, gérer candidatures
- **admin** → superviser utilisateurs et offres

## Routes API

### Authentification
| Méthode | URL | Description |
|---------|-----|-------------|
| POST | /api/auth/register | Inscription |
| POST | /api/auth/login | Connexion |
| GET | /api/auth/me | Profil connecté |
| POST | /api/auth/logout | Déconnexion |

### Profil (candidat)
| Méthode | URL | Description |
|---------|-----|-------------|
| POST | /api/profil | Créer profil |
| GET | /api/profil | Voir profil |
| PUT | /api/profil | Modifier profil |
| POST | /api/profil/competences | Ajouter compétence |
| DELETE | /api/profil/competences/{id} | Retirer compétence |

### Offres
| Méthode | URL | Description |
|---------|-----|-------------|
| GET | /api/offres | Liste offres |
| GET | /api/offres/{id} | Détail offre |
| POST | /api/offres | Créer offre (recruteur) |
| PUT | /api/offres/{id} | Modifier offre (recruteur) |
| DELETE | /api/offres/{id} | Supprimer offre (recruteur) |

### Candidatures
| Méthode | URL | Description |
|---------|-----|-------------|
| POST | /api/offres/{id}/candidater | Postuler |
| GET | /api/mes-candidatures | Mes candidatures |
| GET | /api/offres/{id}/candidatures | Candidatures reçues |
| PATCH | /api/candidatures/{id}/statut | Changer statut |

### Administration
| Méthode | URL | Description |
|---------|-----|-------------|
| GET | /api/admin/users | Liste utilisateurs |
| DELETE | /api/admin/users/{id} | Supprimer utilisateur |
| PATCH | /api/admin/offres/{id} | Activer/désactiver offre |

## Explication du code

### Factories & Faker

Les factories utilisent la librairie **Faker** pour générer des données de test réalistes automatiquement.

#### UserFactory
```php
public function definition(): array
{
    return [
        'name' => fake()->name(),        // Génère un nom aléatoire ex: "John Doe"
        'email' => fake()->unique()->safeEmail(), // Email unique ex: "john@example.com"
        'password' => Hash::make('password'),     // Mot de passe hashé
        'role' => 'candidat',                     // Rôle par défaut
    ];
}

// State recruteur — change uniquement le rôle
public function recruteur(): static
{
    return $this->state(fn (array $attributes) => [
        'role' => 'recruteur',
    ]);
}
```

#### ProfilFactory
```php
public function definition(): array
{
    return [
        'titre' => fake()->jobTitle(),      // Titre de poste ex: "Software Engineer"
        'bio' => fake()->paragraph(),       // Paragraphe de texte aléatoire
        'localisation' => fake()->city(),   // Ville aléatoire ex: "Paris"
        'disponible' => fake()->boolean(),  // true ou false aléatoire
    ];
}
```

#### CompetenceFactory
```php
$competences = ['PHP', 'Laravel', 'Python', ...]; // Liste prédéfinie
$categories = ['Backend', 'Frontend', ...];

return [
    'nom' => fake()->randomElement($competences), // Choisit un élément aléatoire
    'categorie' => fake()->randomElement($categories),
];
```

#### OffreFactory
```php
return [
    'titre' => fake()->jobTitle(),
    'description' => fake()->paragraphs(3, true), // 3 paragraphes en string
    'localisation' => fake()->city(),
    'type' => fake()->randomElement(['CDI', 'CDD', 'stage']), // Choix aléatoire
    'actif' => true,
];
```

### Seeder

Le seeder crée des données de test structurées :

```php
// 1. Créer 15 compétences
$competences = Competence::factory(15)->create();

// 2. Créer 2 admins
User::factory(2)->admin()->create();

// 3. Créer 5 recruteurs avec 2-3 offres chacun
User::factory(5)->recruteur()->create()->each(function ($recruteur) {
    Offre::factory(rand(2, 3))->create([ // rand(2,3) = nombre aléatoire entre 2 et 3
        'user_id' => $recruteur->id,
    ]);
});

// 4. Créer 10 candidats avec profil et compétences
User::factory(10)->create()->each(function ($candidat) use ($competences) {
    $profil = Profil::factory()->create(['user_id' => $candidat->id]);
    
    // Attacher 3 compétences aléatoires avec niveau
    $niveaux = ['débutant', 'intermédiaire', 'expert'];
    $competencesAleatoires = $competences->random(3); // Prend 3 compétences au hasard
    foreach ($competencesAleatoires as $competence) {
        $profil->competences()->attach($competence->id, [
            'niveau' => $niveaux[array_rand($niveaux)] // array_rand = index aléatoire
        ]);
    }
});
```

### Relations Eloquent

```php
// Un User a un Profil (hasOne)
public function profil() {
    return $this->hasOne(Profil::class);
}

// Un Profil a plusieurs Compétences via table pivot (belongsToMany)
public function competences() {
    return $this->belongsToMany(Competence::class, 'profil_competence')
                ->withPivot('niveau'); // inclut la colonne niveau de la table pivot
}

// Une Offre appartient à un User/Recruteur (belongsTo)
public function recruteur() {
    return $this->belongsTo(User::class, 'user_id');
}
```

### Events & Listeners

```php
// Déclencher un event
event(new CandidatureDeposee($candidature));

// Le Listener réagit automatiquement
public function handle(CandidatureDeposee $event): void
{
    $message = sprintf(
        '[%s] Candidature déposée - Candidat: %s - Offre: %s',
        now()->format('Y-m-d H:i:s'), // Date et heure actuelle
        $candidature->profil->user->name,
        $candidature->offre->titre
    );
    Log::channel('candidatures')->info($message); // Écrit dans candidatures.log
}
```

### Middleware CheckRole

```php
public function handle(Request $request, Closure $next, string ...$roles)
{
    $user = Auth::guard('api')->user();
    
    // Vérifie si le rôle de l'user est dans la liste des rôles autorisés
    if (!$user || !in_array($user->role, $roles)) {
        return response()->json(['message' => 'Accès refusé'], 403);
    }
    
    return $next($request); // Continue vers le controller
}
```

## Collection Postman
La collection Postman est disponible dans le dossier `postman/`.
**Note :** Les fichiers JSON ont été écrasés accidentellement. Par conséquent, des captures d’écran ont été fournies comme remplacement pour chaque scénario de méthode.
