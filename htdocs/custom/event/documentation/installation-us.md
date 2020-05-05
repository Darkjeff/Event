# Installation:

## Configuration
- [ ] The Company / Institution must be configured
- [ ] VAT must be defined

## Prerequisite module to activate

- 1 modFormStyler -> https://github.com/Darkjeff/Event/tree/master/htdocs/custom

- [ ] Unzip the module in the custom folder

- [ ] Activate the module

- 2 Third Parties

- [ ] Activate third party management

- 3 Invoices and assets

- [ ] Activate invoice management

- 4 Products

- [ ] Activate the management of products and services

- 5 Paypal or Stripe

- [ ] Activate the online payment module (s)

- [ ] Configure your paypal and Stripe account (s)

- 6 Planned works

- [ ] Activate the module

- 7 Create the planned works

- In administrator tools / planned works / new

- [ ] 7.a Label: Reminder (s) pending invitation (s)

Type of scheduled work: Call of a method of a Dolibarr class

Module: registration

File name including the class: custom/event/class/send_reminders.class.php

Instance / object to create: Reminders

Method: send_reminders_waiting

Settings:

Comment: Sending reminders

Perform each task: 5 mins

Priority: 0

- [ ] 7.b Label: Relaunched invitation (s) validated

Type of scheduled work: Call of a method of a Dolibarr class

Module: registration

File name including the class: custom/event/class/send_reminders.class.php

Instance / object to create: Reminders

Method: send_reminders_confirmed

Settings:

Comment: Sending validated invitations

Perform each task: 5 mins

Priority: 0

## Attributes and constants to add

- 1 Add the additional attributes in:

- [ ] 1.a products and services:

Label: EVENT - Number of units

Attribute code: nbunitbuy

Type: Whole digital

Size: 10

Can still be edited

Visibility

- [ ] 1.b Users and groups:

Label: Number of remaining units

Attribute code: event_counter

Type: Whole digital

Size: 10

Position: 2

Can still be edited

Visibility

- [ ] 1.c In the module admin

Duration of the course (in minutes, hour, ...)

Description: Course duration (in minutes)

Attribute code: cours_cours

Type: Whole digital

Size: 10

Position: 0

Can still be edited

Visibility

- 2 redirection to public page after login

- [ ] Add the constant in onfiguration / miscellaneous

`MAIN_LANDING_PAGE: custom/event/public/`
