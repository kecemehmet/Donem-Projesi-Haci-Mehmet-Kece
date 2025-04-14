USE fitness_db;

CREATE TABLE IF NOT EXISTS user_programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    program_name VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    duration INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
); 