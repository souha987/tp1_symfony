# Réponses aux questions du TP2

## Partie 1 - Formulaires
**Question 1** : L'avantage de créer un FormType dans une classe séparée est de pouvoir réutiliser le formulaire à plusieurs endroits et de séparer la logique de présentation de la logique métier.

**Question 2** : Cycle de vie d'un formulaire :
1. Création du formulaire vide lié à l'entité
2. Affichage du formulaire dans Twig
3. Soumission par l'utilisateur
4. handleRequest() remplit l'entité avec les données
5. Validation des données
6. Persistance en base de données
7. Redirection

**Question 3** : form_row() affiche tout en une fois (label + champ + erreurs), tandis que form_label() + form_widget() permet de personnaliser l'agencement HTML.

## Partie 2 - Validation
**Question 4** : La validation côté client est contournable, la validation côté serveur est obligatoire pour la sécurité.

## Partie 3 - Relations
**Question 5** : Une clé étrangère est une colonne qui fait référence à la clé primaire d'une autre table.

**Question 6** : createFormBuilder() est plus simple pour les petits formulaires, mais un FormType séparé est plus réutilisable.

## Partie 4 - CRUD
**Question 7** : persist() n'est pas nécessaire car l'entité est déjà "managed" par Doctrine.

**Question 8** : CSRF protège contre les attaques où un site malveillant force un utilisateur à exécuter des actions.

# Réponses aux questions du TP3 - Sécurité, Authentification et Autorisation

---

### Question 1 : Password Hasher et algorithme "auto"

**Qu'est-ce qu'un Password Hasher ?**

Un Password Hasher est un composant de sécurité Symfony qui transforme un mot de passe en clair en une version hashée (cryptée) avant de le stocker en base de données. Ce processus est irréversible : on ne peut pas retrouver le mot de passe original à partir du hash.

**Pourquoi Symfony utilise-t-il l'algorithme "auto" par défaut ?**

L'algorithme `auto` est utilisé pour plusieurs raisons :
- **Adaptabilité** : Il choisit automatiquement l'algorithme le plus sécurisé disponible sur le serveur (bcrypt, argon2i, etc.)
- **Évolutivité** : Il permet de changer d'algorithme sans modifier le code lors des mises à jour de sécurité
- **Simplicité** : Les développeurs n'ont pas besoin de se soucier des détails techniques du hashage
- **Sécurité** : Il applique automatiquement un "salt" (valeur aléatoire) pour chaque mot de passe, protégeant contre les attaques par tables arc-en-ciel

---

###  Question 2 : Providers et UserProvider

**À quoi sert la section "providers" dans security.yaml ?**

La section `providers` définit comment les utilisateurs sont chargés depuis leur source de données. C'est le point d'entrée pour récupérer les informations d'authentification.

**Quel est le rôle du UserProvider dans Symfony ?**

Le UserProvider est un service qui :
1. **Charge les utilisateurs** : Récupère un utilisateur depuis la base de données (ou autre source) à partir de son identifiant (email, username)
2. **Implémente UserProviderInterface** : Avec les méthodes obligatoires :
   - `loadUserByIdentifier()` : Charge un utilisateur par son identifiant
   - `refreshUser()` : Rafraîchit les données de l'utilisateur (utile si les permissions changent en cours de session)
3. **Assure la compatibilité** : Transforme les données brutes en objet implémentant `UserInterface`

Exemple de configuration :
```yaml
providers:
    app_user_provider:
        entity:
            class: App\Entity\User
            property: email
