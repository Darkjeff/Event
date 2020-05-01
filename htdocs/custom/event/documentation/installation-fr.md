##Installation:

Configuration
- La Société/Institution doit être configuré
- la TVA doit être définis

Module pré-requis à activer 

- 1 modFormStyler -> https://github.com/Darkjeff/Event/tree/master/htdocs/custom

-- Décompresser le module dans le dossier custom

-- Activer le module

- 2 Tiers

-- Activer la gestion des tiers

- 3 Factures et avoirs

-- Activer la gestion des factures

- 4 Produits

-- Activer la gestion des produits et services

- 5 Paypal ou Stripe

-- Active le ou les module(s) de paiement en ligne 

-- Configurer votre ou vos compte(s) paypal et Stripe

- 6 Travaux planifiés

-- Activer le module

- 7 Créer les travaux planifier

-- Dans outils administrateur/travaux planfiés/nouveau

7.a Libellé: Relance(s) invitation(s) en attente

Type de travail planifié: Appelle d'une méthode d'une classe Dolibarr

Module: registration

Nom de fichier intégrant la classe: custom/event/class/send_reminders.class.php

Instance/objet à créer: Reminders

Méthode: send_reminders_waiting

Paramètres:

Commentaire: Envoi des invitations en relance

Exécuter chaque tâche: 5 mins

Priorité: 0

7.b Libellé: Relance(s) invitation(s) validé(s)

Type de travail planifié: Appelle d'une méthode d'une classe Dolibarr

Module: registration

Nom de fichier intégrant la classe: custom/event/class/send_reminders.class.php

Instance/objet à créer: Reminders

Méthode: send_reminders_confirmed

Paramètres:

Commentaire: Envoi des invitations validés

Exécuter chaque tâche: 5 mins

Priorité: 0

* 8 Ajouter les attributs supplémentaires dans:

8.a les produits et services:

Libellé: EVENT - Nombre d'unités

Code de l'attribut: nbunitbuy

Type:Numérique entier

Taille: 10

Peut toujours être édité

Visibilité

8.b Utilisateurs et groupes:

Libellé: Nombre unités restantes

Code de l'attribut: event_counter

Type: Numérique entier

Taille: 10

Position: 2

Peut toujours être édité	

Visibilité

8.c Dans l'admin du module

Durée du cours (en minutes, heure, ...)

Libellé: Durée du cours (en minutes)

Code de l'attribut: duree_cours

Type: Numérique entier

Taille: 10

Position: 0

Peut toujours être édité	

Visibilité
