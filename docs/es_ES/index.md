Description 
===

Plugin permettant d'utiliser le protocole PLCBUS avec votre domotique
Jeedom.

Configuration du plugin 
===

La configuration est pré-configurée pour être fonctionnelle, cependant
vous modifier les éléments suivants :

-   Port PLCBus

-   Nombre de Phases

-   UserCode

-   Port socket interne

Port PLCBus
-----------

Le plugin reconnait automatiquement votre module USB PLCBus.
Si ce n'est pas le cas alors le sélectionner dans la liste.

Nombre de Phases
-----------

Nombre de phases de votre installation électrique.

UserCode
-----------

A définir en fonction de votre installation, la valeur 0xFF ou FF est la
valeur par défaut. Vous pouvez écrire la valeur avec ou sans le "0x".

Port socket interne
-----------

Permet de définir le port du socket pour communiquer avec le script
python. A ne modifier que si le port est déjà utilisé.

Configuration des équipements
===

Permet de configurer vos modules PLCBus.

ID
-----------

Correspond à l'adresse de votre module.
A1, A2 ... A16
B1, B2 ... B16
...
P1, P2 ... P16

ACK (accuser réception de la commande)
-----------

Si actif, permet d'interroger le module afin de vérifier que la commande
soit bien reçue. A n'utiliser qu'avec les modules compatibles.
Si le module ne répond pas, le plugin passera l'équipement à l'état off.

Equipement
-----------

Permet de sélection le type d'équipement, le choix influence les commandes
possibles.