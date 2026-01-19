-- Database HRIS System
CREATE DATABASE IF NOT EXISTS `hris_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `hris_db`;

-- Tabel Users (Admin & Pengguna)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'karyawan', 'manajer') DEFAULT 'karyawan',
  `status` ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Karyawan
CREATE TABLE IF NOT EXISTS `karyawan` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `nip` VARCHAR(20) UNIQUE NOT NULL,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `telepon` VARCHAR(15),
  `alamat` TEXT,
  `tanggal_lahir` DATE,
  `jenis_kelamin` ENUM('Laki-laki', 'Perempuan'),
  `departemen` VARCHAR(50),
  `posisi` VARCHAR(50),
  `status_kerja` ENUM('Tetap', 'Kontrak', 'Magang') DEFAULT 'Tetap',
  `gaji` DECIMAL(12,2),
  `tanggal_join` DATE,
  `foto` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Departemen
CREATE TABLE IF NOT EXISTS `departemen` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `nama_departemen` VARCHAR(100) NOT NULL,
  `keterangan` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Absensi
CREATE TABLE IF NOT EXISTS `absensi` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `karyawan_id` INT NOT NULL,
  `tanggal` DATE NOT NULL,
  `jam_masuk` TIME,
  `jam_keluar` TIME,
  `status` ENUM('Hadir', 'Sakit', 'Izin', 'Cuti', 'Libur', 'Alfa') DEFAULT 'Hadir',
  `keterangan` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (karyawan_id) REFERENCES karyawan(id),
  UNIQUE KEY unique_absensi (karyawan_id, tanggal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Cuti
CREATE TABLE IF NOT EXISTS `cuti` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `karyawan_id` INT NOT NULL,
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_selesai` DATE NOT NULL,
  `jenis_cuti` ENUM('Cuti Tahunan', 'Cuti Sakit', 'Cuti Khusus') DEFAULT 'Cuti Tahunan',
  `alasan` TEXT,
  `status` ENUM('Pending', 'Disetujui', 'Ditolak') DEFAULT 'Pending',
  `disetujui_oleh` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (karyawan_id) REFERENCES karyawan(id),
  FOREIGN KEY (disetujui_oleh) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Gaji
CREATE TABLE IF NOT EXISTS `gaji` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `karyawan_id` INT NOT NULL,
  `bulan` INT NOT NULL,
  `tahun` INT NOT NULL,
  `gaji_pokok` DECIMAL(12,2),
  `tunjangan` DECIMAL(12,2),
  `bonus` DECIMAL(12,2),
  `potongan` DECIMAL(12,2),
  `total_gaji` DECIMAL(12,2),
  `status` ENUM('Draft', 'Diproses', 'Selesai') DEFAULT 'Draft',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (karyawan_id) REFERENCES karyawan(id),
  UNIQUE KEY unique_gaji (karyawan_id, bulan, tahun)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Performa
CREATE TABLE IF NOT EXISTS `performa` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `karyawan_id` INT NOT NULL,
  `bulan` INT NOT NULL,
  `tahun` INT NOT NULL,
  `nilai_kinerja` DECIMAL(5,2),
  `nilai_perilaku` DECIMAL(5,2),
  `catatan` TEXT,
  `dievaluasi_oleh` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (karyawan_id) REFERENCES karyawan(id),
  FOREIGN KEY (dievaluasi_oleh) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data demo
INSERT INTO `users` (`username`, `email`, `password`, `role`, `status`) VALUES
('admin', 'admin@hris.com', '$2y$10$P3Q8I9X8K3V0O9L8E0U9.OVrY8D7Z5X4C3V1B9N8M7K6J5H4G3F2', 'admin', 'aktif'),
('karyawan1', 'karyawan1@hris.com', '$2y$10$P3Q8I9X8K3V0O9L8E0U9.OVrY8D7Z5X4C3V1B9N8M7K6J5H4G3F2', 'karyawan', 'aktif'),
('manajer1', 'manajer1@hris.com', '$2y$10$P3Q8I9X8K3V0O9L8E0U9.OVrY8D7Z5X4C3V1B9N8M7K6J5H4G3F2', 'manajer', 'aktif');

INSERT INTO `departemen` (`nama_departemen`, `keterangan`) VALUES
('IT', 'Departemen Teknologi Informasi'),
('HR', 'Departemen Sumber Daya Manusia'),
('Finance', 'Departemen Keuangan'),
('Marketing', 'Departemen Pemasaran'),
('Operations', 'Departemen Operasional');

INSERT INTO `karyawan` (`user_id`, `nip`, `nama`, `email`, `telepon`, `alamat`, `tanggal_lahir`, `jenis_kelamin`, `departemen`, `posisi`, `status_kerja`, `gaji`, `tanggal_join`, `foto`) VALUES
(2, '0001', 'Budi Santoso', 'karyawan1@hris.com', '081234567890', 'Jl. Merdeka No. 10, Jakarta', '1990-05-15', 'Laki-laki', 'IT', 'Programmer', 'Tetap', 5000000, '2022-01-15', NULL),
(3, '0002', 'Siti Nurhaliza', 'manajer1@hris.com', '081234567891', 'Jl. Gatot Subroto No. 20, Jakarta', '1988-08-20', 'Perempuan', 'HR', 'Manager', 'Tetap', 7000000, '2021-06-01', NULL);

CREATE TABLE clients (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_client` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150),
    `telepon` VARCHAR(50),
    `perusahaan` VARCHAR(150),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);