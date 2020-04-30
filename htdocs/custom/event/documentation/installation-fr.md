Installation:

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


Attributs supplémentaires à rajouter dans:
--les produits et services: 
- Libellé: EVENT - Nombre d'unités
- Code de l'attribut: nbunitbuy
- Type:Numérique entier
- Taille: 10
- Peut toujours être édité
- Visibilité

--Utilisateurs et groupes:
- Libellé: Nombre unités restantes
- Code de l'attribut: event_counter
- Type: Numérique entier
- Taille: 10
- Position: 2
- Peut toujours être édité	
- Visibilité

--Dans l'admin du module
- Durée du cours (en minutes, heure, ...)
- Libellé: Durée du cours (en minutes)
- Code de l'attribut: duree_cours
- Type: Numérique entier
- Taille: 10
- Position: 0
- Peut toujours être édité	
- Visibilité
