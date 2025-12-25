-- Script SQL untuk membuat Admin dan Pemerintah
-- Jalankan di phpMyAdmin atau MySQL client

-- Password untuk admin123 dan gov123 sudah di-hash dengan bcrypt

-- 1. BUAT ADMIN
INSERT INTO users (name, email, password, role, email_verified_at, created_at, updated_at)
VALUES (
    'Admin Kutkatha',
    'admin@kutkatha.com',
    '$2y$10$YQIn3YgYToiJt0dNlPrpGui9vNqDw5Z6YKRkd4nHgwxgcHHR.b/Zm',
    'admin',
    NOW(),
    NOW(),
    NOW()
);

-- 2. BUAT PEMERINTAH
INSERT INTO users (name, email, password, role, email_verified_at, created_at, updated_at)
VALUES (
    'Pemerintah Kutkut',
    'pemerintah@kutkatha.com',
    '$2y$10$Rh7oekztXQbXzFyFAFymtOSQ/XYzmyJVTkQM3RMb0i55L2K61wiSK',
    'pemerintah',
    NOW(),
    NOW(),
    NOW()
);

-- =============================================
-- LOGIN CREDENTIALS:
-- =============================================
-- Admin:
--   Email: admin@kutkatha.com
--   Password: admin123
--   Dashboard: /admin/dashboard
--
-- Pemerintah:
--   Email: pemerintah@kutkatha.com
--   Password: gov123
--   Dashboard: /pemerintah/dashboard
-- =============================================
