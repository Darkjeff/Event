-- ============================================================================
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
-- ============================================================================

ALTER TABLE llx_event_level_day ADD INDEX idx_event_level_day_fk_event (fk_event);
ALTER TABLE llx_event_level_day ADD INDEX idx_event_level_day_fk_eventday (fk_eventday);

ALTER TABLE llx_event_level_day ADD CONSTRAINT fk_event_level_day_fk_eventday FOREIGN KEY (fk_eventday) REFERENCES llx_event_day
ALTER TABLE llx_event_level_day ADD CONSTRAINT fk_event_level_day_fk_user_create FOREIGN KEY (fk_user_create) REFERENCES llx_user