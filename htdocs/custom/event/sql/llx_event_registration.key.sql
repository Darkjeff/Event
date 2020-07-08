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


ALTER TABLE llx_event_registration ADD INDEX idx_event_registration_fk_event (fk_event);
ALTER TABLE llx_event_registration ADD INDEX idx_event_registration_fk_event (fk_level);
ALTER TABLE llx_event_registration ADD INDEX idx_event_registration_fk_eventday (fk_eventday);
ALTER TABLE llx_event_registration ADD INDEX idx_event_registration_fk_levelday (fk_levelday);
ALTER TABLE llx_event_registration ADD INDEX idx_event_registration_fk_user_create (fk_user_create);
ALTER TABLE llx_event_registration ADD INDEX idx_event_registration_fk_user_valid (fk_user_valid);
ALTER TABLE llx_event_registration ADD INDEX idx_event_registration_fk_soc (fk_soc);


ALTER TABLE llx_event_registration ADD CONSTRAINT fk_event_registration_fk_event FOREIGN KEY (fk_event) REFERENCES llx_event (rowid);
ALTER TABLE llx_event_registration ADD CONSTRAINT fk_event_registration_fk_eventday FOREIGN KEY (fk_event_day) REFERENCES llx_event_day (rowid);
ALTER TABLE llx_event_registration ADD CONSTRAINT fk_event_registration_fk_level FOREIGN KEY (fk_level) REFERENCES llx_event_level_day (rowid);
ALTER TABLE llx_event_registration ADD CONSTRAINT fk_event_registration_fk_user_create FOREIGN KEY (fk_user_create) REFERENCES llx_user(rowid)
ALTER TABLE llx_event_registration ADD CONSTRAINT fk_event_registration_fk_user_valid FOREIGN KEY (fk_user_valid) REFERENCES llx_user(rowid);
