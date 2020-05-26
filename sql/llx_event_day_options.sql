-- ===================================================================
-- Copyright (C) 2013	Jean-François Ferry	<jfefe@aternatik.fr>
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
-- along with this program. If not, see <http://www.gnu.org/licenses/>.
--
-- ===================================================================

CREATE TABLE llx_event_day_options
(
	rowid					integer AUTO_INCREMENT PRIMARY KEY,
	entity					integer DEFAULT 1 NOT NULL,
	fk_eventday				integer			NOT NULL,
	fk_product_options		varchar(100)	NOT NULL,
	qty						integer			NOT NULL,
	price					double			NOT NULL,
	notes					text			NOT NULL,
	datec					datetime		NOT NULL,
	actif					enum('0','1')	NOT NULL default '0'

)ENGINE=innodb;
