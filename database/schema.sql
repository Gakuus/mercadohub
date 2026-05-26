-- MercadoHub Database Schema
-- Run this file to create the initial database structure
-- Usage: mysql -u root -p < database/schema.sql

CREATE DATABASE IF NOT EXISTS test_mercahub
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE test_mercahub;

-- Users table
CREATE TABLE IF NOT EXISTS usuario (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  contrasena VARCHAR(255) NOT NULL,
  img_perfil BLOB DEFAULT NULL,
  bio TEXT DEFAULT NULL,
  rol VARCHAR(20) NOT NULL DEFAULT 'usuario',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories (juegos)
CREATE TABLE IF NOT EXISTS juegos (
  id_juegos INT AUTO_INCREMENT PRIMARY KEY,
  nombre_juegos VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Items table
CREATE TABLE IF NOT EXISTS items (
  id_items INT AUTO_INCREMENT PRIMARY KEY,
  nombre_items VARCHAR(100) NOT NULL,
  descripcion_items TEXT DEFAULT NULL,
  img_items MEDIUMBLOB DEFAULT NULL,
  img_path VARCHAR(255) DEFAULT NULL,
  items_precio DECIMAL(10,2) DEFAULT NULL,
  id_juegos INT NOT NULL,
  id_usuario INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_juegos) REFERENCES juegos(id_juegos) ON DELETE CASCADE,
  FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Profile images table (legacy)
CREATE TABLE IF NOT EXISTS perfil (
  usuario_id INT PRIMARY KEY,
  imagen VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (usuario_id) REFERENCES usuario(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Forum posts
CREATE TABLE IF NOT EXISTS foro_posts (
  id_post INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(200) NOT NULL,
  contenido TEXT NOT NULL,
  id_usuario INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Forum comments
CREATE TABLE IF NOT EXISTS foro_comentarios (
  id_comentario INT AUTO_INCREMENT PRIMARY KEY,
  contenido TEXT NOT NULL,
  id_post INT NOT NULL,
  id_usuario INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_post) REFERENCES foro_posts(id_post) ON DELETE CASCADE,
  FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Item comments
CREATE TABLE IF NOT EXISTS item_comentarios (
  id_comentario INT AUTO_INCREMENT PRIMARY KEY,
  contenido TEXT NOT NULL,
  id_items INT NOT NULL,
  id_usuario INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_items) REFERENCES items(id_items) ON DELETE CASCADE,
  FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity log
CREATE TABLE IF NOT EXISTS actividad_log (
  id_log INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT,
  accion VARCHAR(100) NOT NULL,
  detalles TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories
INSERT INTO juegos (nombre_juegos) VALUES
  ('Videojuegos'),
  ('Libros'),
  ('Ropa'),
  ('Electrónica'),
  ('Hogar'),
  ('Deportes'),
  ('Juguetes'),
  ('Otros')
ON DUPLICATE KEY UPDATE nombre_juegos = VALUES(nombre_juegos);
