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
git clone https://github.com/ton-username/mini-linkedin.git
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

## Collection Postman
La collection Postman est disponible dans le dossier `postman/`.