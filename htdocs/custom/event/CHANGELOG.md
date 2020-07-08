#Module Event#

##ROADMAP##

### ChangeLog for 2.0 ###
 - Integrate mailing class to stream large mailing

### ChangeLog for 1.9 ###
 - Test add company instead of contact
 - Lien cliquable pour l'ajout des textes additionnelles
 - New PDF #120
 - Correct billing system (move do_payement.php | orders_state.php) #113

## DONE ##

### ChangeLog for 1.8.6

* Fix Payment

### ChangeLog for 1.8.3

* Add module ID
* Add license

### ChangeLog for 1.8.2 ###
 - Enhance interface #161
 - Many fixes

### ChangeLog for 1.8.1 ###
- Modifcation CSS MAIL in admin
 - Modifcation TEXT MAIL in admin
 - Enhance interface #148
 - Remind auto with auto send mail for inpending & validated registration #155
 - Many fixes
 - Clone Day #141

### ChangeLog for 1.8 ###
 - Add a public page to # 130, #146 & #147 :
  - show day & information
  - can create account
  - can register
  - can buy unit to increase account & use them after to register to event & day

### ChangeLog for 1.6 ###
 - New admin Panel
 - Dedicated Tab for Mail & Html Web Content Day
 - Add groupe local (event), insted of group GLOBAL (global for module) 
 - Rendre obligatoire l'activation des modules ... & test droit Produits, 

###ChangeLog for 1.5.4###
 - Register to reg for a day
 - Test the max number of person #107
 - Add Ics file for calendar #110
 - Correct the PDF invitation #57
 - Add search in event day page #134
 - Relaunch invitation with planned tasks #155
  
 ###ChangeLog for 1.5.3.1###
 - Relance auto #108
 - Add public page #130
 - ADMIN page customisation #131 #132 #133

###ChangeLog for 1.5.3 ###
 - Valid work with DOLIBAR 4.0.X #60
 - Add ChangeLog #63
 - Add Clone fonction #106
 - Admin add new menu #91 #111 #114
 - Manage the content of mail from the admin menu #109
 - Correct minor #103 #112 #115 #116 #117

###ChangeLog for 1.5.2 ###
 - HIDE PDF - Admin & registration/list.php : OK
 - HIDE MOVE GROUP - Admin & registration/list.php : OK
 - ADMIN : Add cat. : OK
 - ADMIN : Mise à jour de la mise en forme : OK
 - WEB PAGE : réactivation du contenu dynamique : OK
 - INSCRIPTION PAR TAG : problème d'affichage du TAG : OK
 - ADMIN in fisrt menu : OK
 - PB DE RELANCE : OK + ajout d'une information complémentaire
 - Hide the button 'Billing' when the cost is 0

GROUPE
 - Correction changement de groupes : Désactivé, il faut pour cela entré dans la fiche d'une personne

LANGS - Correct global french lang
 - Afficher la page des expirations
 - Afficher la page des états des règlements
 - EventSmsNoModuleConfigured
 - 0=Places illimité

ADMIN
 - Correct mise en forme : OK
 - Correct save Description : OK
 FICHE.PHP
 - Changement du libellé du bouton Cancel : OK
 - Ajout d'un bouton pour désactiver/réactiver une inscription : OK
INDEX.PHP
 - Add information about the number of day for an event : OK
GROUPE
 - Liste des invitations: OK
 - Pas de décompte des cours : OK
 - Uniformisation de l'affichage & de la manipulation des informations : OK
 - LIST_PRINT.PHP - Version imprimable gestion des GROUPES

### ChangeLog for 1.5.1 ###
 - Add a new/specific cell for the content of the public page

###ChangeLog for 1.5###
 - Add specific icon for day & event
 - Change ergonomy of the display into the pages
 - Add a public to validate the presence + VAR in MAILING
 - ADMIN - Move admin page in plugins
 - ADMIN - Add new functions
 - DAY - Add Start & End date + VAR in MAILING
 - REMINDER - Add 2 buttons for reminder
 - EXPORT - CSV

###ChangeLog for 1.3###
 - Rewrite by Code 42
 - Dolibarr 3.9 compatibility
 - Fix many bugs

***** ChangeLog for 1.2.1 compared to 1.2.0 version *****
Add more info about event and event day into module pages
NEW : show related orders into registration list
Fix: bad method name into reminders list
New: add list of registration's order sorted by thirdparty
New : can add time on day levels to be able to manage resources
Rename Eventday class into Day : to be able to link timing level with actioncomm


***** ChangeLog for 1.2.0 compared to 1.1.0 version *****
Dolibarr 3.6 compatibility
New : add checkbox to validate all days when validate a event
New : add options on days to link product as option

***** ChangeLog for 1.1.0 compared to 1.0.12 version *****
Dolibarr 3.5 compatibility
New : add EN langage
Fix : error when no sms module activated)
Fix : bad id for registered user when get registrations list


***** ChangeLog for 1.0.12 compared to 1.0.11 version *****
Fix : bad order fieldname into events list
Fix : security check
Fix : navigation arraox for event

***** ChangeLog for 1.0.11 compared to 1.0.10 version *****
Work on registration form : add a checkbox to valid after creation (with PDF generation)
Attach registration PDF only when it's validated (not when confirmed)
Various bugfixes

***** ChangeLog for 1.0.10 compared to 1.0.9 version *****
New form to search registration by number or ref
New webservice to get registration PDF
Fix in registration PDF (extrafields / target address)
Add contact of registration by day into emailing module
Show progress bar for registration number in days list
Add link to send email with registration from days list
Add new tab on event with link to expires registration
Fix : ref of day was blank after event creation
In days list show link to manage level if no level define
Can filter list of days by year

***** ChangeLog for 1.0.9 compared to 1.0.8 version *****
New screen to list event's day with registration sum

***** ChangeLog for 1.0.8 compared to 1.0.7 version *****
Add extrafield support in webservice
Add info about level in webservice
Can valid a registration after editing note (quickly)

***** ChangeLog for 1.0.7 compared to 1.0.6 version *****
Complete support for extrafields on registration and contact
Box to show last registration on home screen
New : update note quickly in list view
Add link to left menu
New webservices to get and create registration
Registration confirm is sent by mail (with PDF) & SMS
Contact infos are used for registration (to avoid duplicated datas)

***** ChangeLog for 1.0.6 compared to 1.0.5 version *****
New : stats on registration for a day
Rename 'level' by 'group' in IHM
New : printed version for registration list of a day

***** ChangeLog for 1.0.5 compared to 1.0.4 version *****
New : webservice for event list & registration list and creation

***** ChangeLog for 1.0.4 compared to 1.0.3 version *****
New : level is not required for registration (optionnal in config)
New : add config option to require email for a registration
New : billing registration and price management for a day
New : flag 'paid' on registration
Fix bug on extrafields


