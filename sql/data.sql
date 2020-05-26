ALTER TABLE llx_actioncomm MODIFY elementtype VARCHAR(32);

DELETE FROM llx_c_actioncomm WHERE code LIKE 'EVE_%';

INSERT INTO llx_c_actioncomm (`id`, `code`, `type`, `libelle`, `module`, `active`, `todo`, `position`) VALUES (1680001, 'EVE_RV', 'event', 'Validation of registration', 'event', 1, NULL, 50);
INSERT INTO llx_c_actioncomm (`id`, `code`, `type`, `libelle`, `module`, `active`, `todo`, `position`) VALUES (1680002, 'EVE_RC', 'event', 'Confirmation of registration', 'event', 1, NULL, 51);
INSERT INTO llx_c_actioncomm (`id`, `code`, `type`, `libelle`, `module`, `active`, `todo`, `position`) VALUES (1680003, 'EVE_RQ', 'event', 'Inscription placed on the waiting list ', 'event', 1, NULL, 52);
INSERT INTO llx_c_actioncomm (`id`, `code`, `type`, `libelle`, `module`, `active`, `todo`, `position`) VALUES (1680004, 'EVE_REMAIL', 'event', 'Registration sent by email ', 'event', 1, NULL, 53);
INSERT INTO llx_c_actioncomm (`id`, `code`, `type`, `libelle`, `module`, `active`, `todo`, `position`) VALUES (1680005, 'EVE_RPAID', 'event', 'Registration marked as paid ', 'event', 1, NULL, 54);
INSERT INTO llx_c_actioncomm (`id`, `code`, `type`, `libelle`, `module`, `active`, `todo`, `position`) VALUES (1680006, 'EVE_RESMS', 'event', 'Registration sent by SMS ', 'event', 1, NULL, 55);
INSERT INTO llx_c_actioncomm (`id`, `code`, `type`, `libelle`, `module`, `active`, `todo`, `position`) VALUES (1680007, 'EVE_REMIND', 'event', 'Registration reminder sent by email ', 'event', 1, NULL, 56);
