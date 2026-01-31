# Accès à phpMyAdmin en local

Ce projet n'embarque pas phpMyAdmin. L'accès se fait via l'installation Homebrew, puis un serveur PHP local.

## Lancer phpMyAdmin

Commande recommandée :

```
php -S 127.0.0.1:8081 -t /usr/local/Cellar/phpmyadmin/5.2.3/share/phpmyadmin
```

Ensuite, ouvrir dans le navigateur :

```
http://127.0.0.1:8081
```

## Remarque sur le chemin

Si Homebrew met à jour la version, le dossier change. Dans ce cas, vérifier la version installée :

```
brew list phpmyadmin | head -n 5
```

et adapter le chemin `.../Cellar/phpmyadmin/<VERSION>/share/phpmyadmin`.
