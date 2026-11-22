-- Import lewat phpMyAdmin: tab "Import" -> pilih file ini
CREATE DATABASE IF NOT EXISTS manga_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE manga_db;

CREATE TABLE manga (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  author VARCHAR(100) DEFAULT NULL,
  genre VARCHAR(150) DEFAULT NULL,
  description TEXT,
  cover VARCHAR(255) DEFAULT NULL,
  status ENUM('Ongoing','Tamat') NOT NULL DEFAULT 'Ongoing',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE chapters (
  id INT AUTO_INCREMENT PRIMARY KEY,
  manga_id INT NOT NULL,
  number DECIMAL(6,1) NOT NULL,
  title VARCHAR(150) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_manga_number (manga_id, number),
  FOREIGN KEY (manga_id) REFERENCES manga(id) ON DELETE CASCADE
);

CREATE TABLE pages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  chapter_id INT NOT NULL,
  page_number INT NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE CASCADE
);

INSERT INTO manga (title, author, genre, description, status) VALUES
('Contoh Manga', 'Nama Penulis', 'Aksi, Petualangan', 'Ini data contoh. Hapus lewat panel admin dan tambahkan manga Anda sendiri.', 'Ongoing');
