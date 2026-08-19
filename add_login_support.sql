USE campus_cab_db;

ALTER TABLE student ADD COLUMN password VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE driver ADD COLUMN password VARCHAR(255) NOT NULL DEFAULT '';

-- Demo password for every sample account is: campus123
-- (hashed with PHP's password_hash, never stored as plain text)
UPDATE student SET password = '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC';
UPDATE driver SET password = '$2y$10$70Z.uxXt8ZD6/9DMj4TlQeMPLO9nluPQcx42LvxHrVdPOnBzkA.jC';