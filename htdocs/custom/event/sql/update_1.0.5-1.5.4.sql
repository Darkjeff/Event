ALTER TABLE  `llx_event_day` ADD `relance_waiting_auto` INT NULL DEFAULT NULL ;
ALTER TABLE  `llx_event_day` ADD `relance_confirmed_auto` INT NULL DEFAULT NULL ;
INSERT INTO  `llx_cronjob` (
`tms` ,
`datec` ,
`jobtype` ,
`label` ,
`command` ,
`classesname` ,
`objectname` ,
`methodename` ,
`params` ,
`md5params` ,
`module_name` ,
`priority` ,
`datelastrun` ,
`datenextrun` ,
`datestart` ,
`dateend` ,
`datelastresult` ,
`lastresult` ,
`lastoutput` ,
`unitfrequency` ,
`frequency` ,
`maxrun` ,
`nbrun` ,
`autodelete` ,
`status` ,
`test` ,
`fk_user_author` ,
`fk_user_mod` ,
`fk_mailing` ,
`note` ,
`libname` ,
`entity`
)
VALUES (
NOW(),  NOW(),  'method',  'Relance(s) invitation(s) en attente',  '',  'custom/event/class/send_reminders.class.php',  'Reminders',  'send_reminders_waiting',  '',  '', 'registration',  '0',  '',  '',  NOW(), NULL ,  '',  '0',  '',  '3600',  '1', '0',  '0',  '0',  '1', NULL ,  '1',  '1', NULL ,  'Envoi des invitations en relance', NULL ,  '0'
);

INSERT INTO `llx_cronjob` (
`tms` ,
`datec` ,
`jobtype` ,
`label` ,
`command` ,
`classesname` ,
`objectname` ,
`methodename` ,
`params` ,
`md5params` ,
`module_name` ,
`priority` ,
`datelastrun` ,
`datenextrun` ,
`datestart` ,
`dateend` ,
`datelastresult` ,
`lastresult` ,
`lastoutput` ,
`unitfrequency` ,
`frequency` ,
`maxrun` ,
`nbrun` ,
`autodelete` ,
`status` ,
`test` ,
`fk_user_author` ,
`fk_user_mod` ,
`fk_mailing` ,
`note` ,
`libname` ,
`entity`
)
VALUES (
NOW(),  NOW(),  'method',  'Relance(s) invitation(s) validé(s)',  '',  'custom/event/class/send_reminders.class.php',  'Reminders',  'send_reminders_confirmed',  '',  '', 'registration',  '0',  '',  '',  NOW(), NULL ,  '',  '0',  '',  '3600',  '1', '0',  '0',  '0',  '1', NULL ,  '1',  '1', NULL ,  'Envoi des invitations validés', NULL ,  '0'
);