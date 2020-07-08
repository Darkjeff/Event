-- ===========================================================================
-- Copyright (C) 2012 	JF FERRY  <jfefe@aternatik.fr.fr>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program; if not, write to the Free Software
-- Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
--
-- ===========================================================================

create table llx_event_registration
(
  rowid					integer AUTO_INCREMENT PRIMARY KEY,
  entity				integer DEFAULT 1 NOT NULL,
  ref					varchar(50),
  fk_soc				integer,
  fk_event				integer,
  fk_eventday			integer,
  fk_levelday			integer,
  datec					datetime,
  tms					timestamp,
  date_valid			datetime,
  total_ht				double(24,8) DEFAULT 0,
  total_tva				double(24,8) DEFAULT 0,
  total_ttc				double(24,8) DEFAULT 0,
  tva_tx            	double(6,3),
  accountancy_code		varchar(32),
  fk_user_create		integer NOT NULL,
  fk_user_valid			integer NOT NULL,
  fk_user_registered	integer NOT NULL,
  fk_statut				smallint DEFAULT 0 NOT NULL,
  civilite         		varchar(6),
  firstname				varchar(255),
  lastname				varchar(255),
  naiss            		date, 
  address				varchar(255),
  zip					varchar(255),
  town					varchar(255),
  state_id				integer,
  country_id			integer,
  phone					varchar(255),
  phone_perso			varchar(255),
  phone_mobile			varchar(255),
  note_private			text,
  note_public			text,
  unique_key      varchar(255)
)ENGINE=innodb;
