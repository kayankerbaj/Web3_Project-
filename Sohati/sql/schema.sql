CREATE DATABASE IF NOT EXISTS medical_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE medical_platform;


CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('patient','doctor','admin') DEFAULT 'patient',
  gender ENUM('male','female','other') DEFAULT 'other',
  phone VARCHAR(20),
  profile_image VARCHAR(255) DEFAULT 'default-avatar.png',
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_role (role)
) ENGINE=InnoDB;


CREATE TABLE specialties (
  id INT AUTO_INCREMENT PRIMARY KEY,
  specialty_name VARCHAR(100) NOT NULL UNIQUE,
  description TEXT,
  icon VARCHAR(50) DEFAULT 'stethoscope'
) ENGINE=InnoDB;

INSERT INTO specialties (specialty_name, description) VALUES 
('Cardiology', 'Heart and cardiovascular specialists'),
('Dermatology', 'Skin, hair, and nail specialists'),
('Neurology', 'Brain and nervous system specialists'),
('Pediatrics', 'Child healthcare specialists'),
('Orthopedics', 'Bone, joint, and muscle specialists'),
('Ophthalmology', 'Eye care specialists'),
('Dentistry', 'Oral health specialists'),
('General Practice', 'Primary care physicians'),
('Psychiatry', 'Mental health specialists'),
('Oncology', 'Cancer treatment specialists');


CREATE TABLE doctors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNIQUE NOT NULL,
  specialty_id INT,
  years_of_experience INT DEFAULT 0,
  consultation_fee DECIMAL(10,2) DEFAULT 50.00,
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  bio TEXT,
  job_applied TINYINT(1) DEFAULT 0,
  rating DECIMAL(3,2) DEFAULT 0.00,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (specialty_id) REFERENCES specialties(id),
  INDEX idx_status (status),
  INDEX idx_specialty (specialty_id)
) ENGINE=InnoDB;


CREATE TABLE doctor_schedules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  doctor_id INT NOT NULL,
  day_of_week ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  is_available TINYINT(1) DEFAULT 1,
  FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
  UNIQUE KEY unique_doctor_day (doctor_id, day_of_week)
) ENGINE=InnoDB;


CREATE TABLE blood_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  blood_type VARCHAR(5) NOT NULL UNIQUE,
  can_receive_from VARCHAR(50),
  can_donate_to VARCHAR(50)
) ENGINE=InnoDB;

INSERT INTO blood_types (blood_type, can_receive_from, can_donate_to) VALUES 
('A+', 'A+,A-,O+,O-', 'A+,AB+'),
('A-', 'A-,O-', 'A+,A-,AB+,AB-'),
('B+', 'B+,B-,O+,O-', 'B+,AB+'),
('B-', 'B-,O-', 'B+,B-,AB+,AB-'),
('AB+', 'All', 'AB+'),
('AB-', 'A-,B-,AB-,O-', 'AB+,AB-'),
('O+', 'O+,O-', 'A+,B+,AB+,O+'),
('O-', 'O-', 'All');


CREATE TABLE patients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNIQUE NOT NULL,
  blood_type_id INT,
  date_of_birth DATE,
  medical_history TEXT,
  allergies TEXT,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (blood_type_id) REFERENCES blood_types(id)
) ENGINE=InnoDB;


CREATE TABLE appointment_statuses (
  id INT PRIMARY KEY,
  status_name VARCHAR(50) NOT NULL,
  status_color VARCHAR(20) DEFAULT '#6B7280'
) ENGINE=InnoDB;

INSERT INTO appointment_statuses VALUES 
(1,'Pending','#f59e0b'),
(2,'Confirmed','#3b82f6'),
(3,'Completed','#10b981'),
(4,'Cancelled','#ef4444'),
(5,'No Show','#6b7280');

CREATE TABLE appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  doctor_id INT NOT NULL,
  appointment_date DATE NOT NULL,
  appointment_time TIME NOT NULL,
  duration_minutes INT DEFAULT 60,
  status_id INT DEFAULT 1,
  notes TEXT,
  commission DECIMAL(10,2) DEFAULT 0.00,
  reminder_sent TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
  FOREIGN KEY (status_id) REFERENCES appointment_statuses(id),
  INDEX idx_date (appointment_date),
  INDEX idx_doctor_date (doctor_id, appointment_date),
  INDEX idx_patient_date (patient_id, appointment_date)
) ENGINE=InnoDB;


CREATE TABLE commission_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  appointment_id INT NOT NULL,
  commission_amount DECIMAL(10,2) NOT NULL,
  commission_percentage DECIMAL(5,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE blood_donations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  donor_id INT NOT NULL,
  blood_type_id INT NOT NULL,
  needed_blood_type_id INT NOT NULL,
  quantity_ml INT DEFAULT 500,
  urgency ENUM('low','medium','high','critical') DEFAULT 'medium',
  status ENUM('requested','donated','cancelled') DEFAULT 'requested',
  donation_date DATE,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (donor_id) REFERENCES patients(id) ON DELETE CASCADE,
  FOREIGN KEY (blood_type_id) REFERENCES blood_types(id),
  FOREIGN KEY (needed_blood_type_id) REFERENCES blood_types(id)
) ENGINE=InnoDB;


CREATE TABLE messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sender_id INT NOT NULL,
  receiver_id INT NOT NULL,
  message_text TEXT NOT NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_conversation (sender_id, receiver_id),
  INDEX idx_receiver (receiver_id, is_read)
) ENGINE=InnoDB;


CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  notification_type VARCHAR(50) NOT NULL,
  title VARCHAR(200) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_read (user_id, is_read)
) ENGINE=InnoDB;


CREATE TABLE system_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT NOT NULL,
  description TEXT
) ENGINE=InnoDB;

INSERT INTO system_settings (setting_key, setting_value, description) VALUES 
('commission_percentage', '15.00', 'Platform commission percentage'),
('site_name', 'Sohati+', 'Website name'),
('appointment_duration_max', '120', 'Max appointment duration in minutes');
