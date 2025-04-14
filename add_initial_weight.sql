USE fitness_db;

ALTER TABLE users
ADD COLUMN initial_weight FLOAT DEFAULT NULL AFTER weight; 