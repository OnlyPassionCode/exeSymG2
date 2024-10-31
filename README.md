# sym64kevin

## Bienvenue sur mon projet de TI Symfony 2024

Vous trouverez les étapes a suivre pour installer et lancer le projet en local mais aussi sous Docker.

## Lancer le projet en Local

Il est recommandé d'installer le CLI de symfony pour plus de simplicité : [cli symfony](https://symfony.com/download)

```
git clone https://github.com/OnlyPassionCode/sym64kevin
cd sym64kevin
```
Avant de continuer, si le mot de passe de votre root est différent d'un mot de passe vide changer alors le fichier `.env.local` et mettez `DB_PWD` avec votre mot de passe root.

Une fois fait vous pouvez faire ces commandes :

```
composer install
php bin/console d:d:c
php bin/console d:m:m
php bin/console d:f:l --no-interaction
```

- Une fois ces étapes faîtes vous devez me demander la clé api de sendgrid.
- Placez la clé dans le fichier .env


Si vous souhaiter être en https faîtes cette commande :

```
symfony server:ca:install
```

Une fois fait lancer le serveur :

```
symfony serve -d
```

Si vous avez la clé API pour les mails vous pouvez lancer cette commande :

```
php bin/console messenger:consume async
```

Cela lancera un worker qui tournera sur votre invite de commande pour envoyer les mails.

## Lancer le projet sur Docker
### Attention, n'oubliez pas d'éteindre wamp pour éviter un clonflit de port mysql.
Nous repartons de 0. Faites ces commandes :

```
git clone https://github.com/OnlyPassionCode/sym64kevin
cd sym64kevin
```

- Une fois ces étapes faîtes vous devez me demander la clé api de sendgrid.
- Placez la clé dans le fichier .env

Ensuite vous pouvez executer cette commande :

```
# Ajoutez -d a la fin pour lancer en tache de fond
docker compose up --build
```

Et voilà, vous pouvez aller sur ce lien : http://localhost:8080

