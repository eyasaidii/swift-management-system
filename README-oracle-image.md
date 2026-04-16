Objectif

Ce répertoire contient un modèle pour construire une image Oracle XE prépeuplée à partir d'un export (dump) de votre base actuelle.

Étapes pour créer l'image (localement)

1) Générer un dump de votre base hôte (sur la machine hôte, SQL Developer ou expdp) :
   expdp system/<SYS_PASSWORD>@localhost:1521/XEPDB1 schemas=EYAA directory=DATA_PUMP_DIR dumpfile=eyaa_backup.dmp logfile=eyaa_export.log
   - Remplacez `EYAA` par le schéma source si nécessaire.
   - Placez le fichier `eyaa_backup.dmp` dans `docker/oracle/dumps/` du projet.

2) (Optionnel) vérifier que `docker/oracle/dumps/eyaa_backup.dmp` existe.

3) Construire et lancer l'oracle conteneurisé (sans exposer le port) :
   docker-compose -f docker-compose.yml -f docker-compose.oracle.yml up -d --build

   - Le conteneur Oracle sera accessible depuis d'autres conteneurs sur le réseau Docker par le nom de service `oracle:1521`.
   - Le script d'init tentera d'importer les dumps présents dans `/opt/oracle/dumps`.

Remarques importantes
- Les images Oracle peuvent avoir des contraintes/licences. `gvenzl/oracle-xe` est pratique pour le développement.
- Le script `import-data.sh` est un template : il suppose l'usage de `impdp` et un remappage de schéma `remap_schema=SOURCE_SCHEMA:EYAA`. Adaptez `SOURCE_SCHEMA` au schéma originel de votre dump.
- Si vous souhaitez exposer `1521` sur l'hôte pour accès depuis SQL Developer, décommentez la section `ports` dans `docker-compose.oracle.yml`. Attention aux conflits si Oracle est déjà présent sur l'hôte.

Besoin d'aide
- Je peux :
  - construire l'image pour vous (si vous copiez le dump `eyaa_backup.dmp` dans `docker/oracle/dumps/`),
  - ou générer une version du script `import-data.sh` adaptée à la structure exacte de votre dump.

Utilisation adaptable (cas d'usage)

1) Utiliser votre base Oracle externe (par défaut) — pas de conteneur Oracle lancé :
   make up

2) Utiliser une base Oracle conteneurisée isolée (utile pour tests) :
   make up-oracle

   - Cette commande utilise `docker-compose.oracle.yml` qui construit une image Oracle locale
     et monte le dossier `docker/oracle/dumps/` pour les dumps. Le port 1521 n'est PAS exposé
     sur l'hôte par défaut (le conteneur est accessible depuis d'autres conteneurs via `oracle:1521`).

3) Construire uniquement l'image de l'application (après modifications) :
   make build-app

4) Exécuter les migrations :
   make migrate

5) Nettoyer le conteneur Oracle optionnel :
   make clean-oracle

Notes :
- Le workflow "le plus adaptable" signifie que vous pouvez basculer entre votre base hôte
  (défaut) et une base conteneurisée pour les tests sans modifier le code source : utilisez
  `make up` pour utiliser la DB hôte, ou `make up-oracle` pour démarrer l'oracle conteneurisé.


