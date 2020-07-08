-- ============================================================================
-- Copyright (C) 2015   	Jean-François Ferry     <jfefe@aternatik.fr>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 3 of the License, or
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
-- ============================================================================
--
-- Structure de la table llx_event_level_day_cal
--
CREATE TABLE IF NOT EXISTS llx_event_level_day_cal (
  rowid integer NOT NULL auto_increment PRIMARY KEY,
  fk_event_day integer NOT NULL,
  fk_level	integer NOT NULL,
  date_session date NOT NULL,
  heured datetime NOT NULL,
  heuref datetime NOT NULL,
  fk_actioncomm int DEFAULT NULL,
  fk_user_author integer NOT NULL,
  datec datetime NOT NULL,
  fk_user_mod integer NOT NULL,
  tms timestamp NOT NULL
) ENGINE=InnoDB;
