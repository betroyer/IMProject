USE startup_bms;

-- School demonstration: readable credentials. Use demo-only passwords.
-- Retain old hashes until a successful sign-in supplies the original password.
ALTER TABLE users ADD COLUMN IF NOT EXISTS password TEXT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255) NULL;
