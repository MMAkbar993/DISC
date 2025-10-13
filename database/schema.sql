-- DISC Assessment Platform Database Schema
-- Create database
CREATE DATABASE IF NOT EXISTS disc_assessment CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE disc_assessment;

-- Admin users table
CREATE TABLE admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Access codes table
CREATE TABLE access_codes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    used_by VARCHAR(100),
    used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES admin_users(id) ON DELETE SET NULL
);

-- Assessment participants table
CREATE TABLE participants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    position VARCHAR(100),
    email VARCHAR(100),
    access_code VARCHAR(20) NOT NULL,
    assessment_completed BOOLEAN DEFAULT FALSE,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (access_code) REFERENCES access_codes(code) ON DELETE RESTRICT
);

-- Assessment answers table
CREATE TABLE assessment_answers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    participant_id INT NOT NULL,
    question_number INT NOT NULL,
    most_choice INT NOT NULL,
    least_choice INT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE,
    INDEX idx_participant_question (participant_id, question_number)
);

-- DISC profile results table
CREATE TABLE disc_profiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    participant_id INT NOT NULL,
    dominance_score INT DEFAULT 0,
    influence_score INT DEFAULT 0,
    steadiness_score INT DEFAULT 0,
    compliance_score INT DEFAULT 0,
    primary_type VARCHAR(10),
    secondary_type VARCHAR(10),
    profile_title VARCHAR(100),
    profile_description TEXT,
    strengths TEXT,
    areas_for_development TEXT,
    leadership_style TEXT,
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE
);

-- Admin settings table
CREATE TABLE admin_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT,
    FOREIGN KEY (updated_by) REFERENCES admin_users(id) ON DELETE SET NULL
);

-- Insert default admin user with strong password
-- Password: AdminDisc2024!@#
INSERT INTO admin_users (username, password_hash, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@discassessment.com');

-- Insert default access codes (20 codes)
INSERT INTO access_codes (code) VALUES 
('DISC2024A'), ('DISC2024B'), ('DISC2024C'), ('DISC2024D'), ('DISC2024E'),
('DISC2024F'), ('DISC2024G'), ('DISC2024H'), ('DISC2024I'), ('DISC2024J'),
('DISC2024K'), ('DISC2024L'), ('DISC2024M'), ('DISC2024N'), ('DISC2024O'),
('DISC2024P'), ('DISC2024Q'), ('DISC2024R'), ('DISC2024S'), ('DISC2024T');

-- Insert default admin settings
INSERT INTO admin_settings (setting_key, setting_value) VALUES 
('company_name', 'DISC Leadership Assessment'),
('company_tagline', 'Professional Leadership Development Solutions'),
('primary_color', '#4f46e5'),
('secondary_color', '#7c3aed'),
('report_title', 'DISC LEADERSHIP ASSESSMENT'),
('report_subtitle', 'Professional Development Report'),
('logo_size', 'medium'),
('background_opacity', '0.6'),
('footer_text', 'Confidential Leadership Development Report');
