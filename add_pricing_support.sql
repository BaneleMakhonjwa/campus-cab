USE campus_cab_db;

-- ------------------------------------------------------------
-- Known campus locations (used for pickup/destination dropdowns
-- so we get real coordinates without needing a paid maps API)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS campus_location (
    location_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    latitude DECIMAL(10,7) NOT NULL,
    longitude DECIMAL(10,7) NOT NULL
);

INSERT INTO campus_location (name, latitude, longitude) VALUES
('Beda Hall', -32.7830000, 26.8430000),
('Steve Biko Hall', -32.7810000, 26.8400000),
('Chris Hani Hall', -32.7845000, 26.8410000),
('New Res', -32.7855000, 26.8465000),
('Main Library', -32.7850000, 26.8460000),
('Science Building', -32.7845000, 26.8455000),
('Admin Building', -32.7838000, 26.8442000),
('Sports Field', -32.7862000, 26.8420000),
('Main Gate', -32.7800000, 26.8390000),
('Student Centre', -32.7833000, 26.8448000);

-- ------------------------------------------------------------
-- Estimate columns on ride (pickup/destination lat+lng columns
-- already existed in your original design; these are new)
-- ------------------------------------------------------------
ALTER TABLE ride ADD COLUMN estimated_distance_km DECIMAL(6,2) NULL;
ALTER TABLE ride ADD COLUMN estimated_duration_min INT NULL;
ALTER TABLE ride ADD COLUMN estimated_price DECIMAL(8,2) NULL;