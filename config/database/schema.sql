-- =====================================================
-- Transcriptomics Database Schema
-- MySQL 5.7+ / MariaDB 10.3+
-- Character Set: UTF8MB4 for Unicode support
-- =====================================================

-- Create database
CREATE DATABASE IF NOT EXISTS transcriptomics_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE transcriptomics_db;

-- =====================================================
-- Table: genes
-- Stores gene identifiers and basic annotations
-- =====================================================
CREATE TABLE IF NOT EXISTS genes (
  gene_id VARCHAR(50) PRIMARY KEY,
  gene_name VARCHAR(100),
  protein_description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_gene_name (gene_name),
  FULLTEXT INDEX ft_protein_desc (protein_description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: deg_results
-- Stores differentially expressed genes analysis results
-- =====================================================
CREATE TABLE IF NOT EXISTS deg_results (
  id INT AUTO_INCREMENT PRIMARY KEY,
  gene_id VARCHAR(50) NOT NULL,
  tissue VARCHAR(50),
  condition_name VARCHAR(100),
  log_fc DECIMAL(8,4),
  p_value DECIMAL(12,10),
  regulation ENUM('Up', 'Down') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_gene (gene_id),
  INDEX idx_tissue (tissue),
  INDEX idx_condition (condition_name),
  INDEX idx_log_fc (log_fc),
  INDEX idx_p_value (p_value),
  INDEX idx_regulation (regulation),
  FOREIGN KEY (gene_id) REFERENCES genes(gene_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: functional_annotations
-- Stores GO terms and KEGG pathway annotations
-- =====================================================
CREATE TABLE IF NOT EXISTS functional_annotations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  gene_id VARCHAR(50) NOT NULL,
  go_terms TEXT,
  kegg_pathway VARCHAR(500),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_gene (gene_id),
  FULLTEXT INDEX ft_go_terms (go_terms),
  FULLTEXT INDEX ft_kegg (kegg_pathway),
  FULLTEXT INDEX ft_go_kegg_combined (go_terms, kegg_pathway),
  FOREIGN KEY (gene_id) REFERENCES genes(gene_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: pathways
-- Stores KEGG pathway metadata
-- =====================================================
CREATE TABLE IF NOT EXISTS pathways (
  pathway_id VARCHAR(20) PRIMARY KEY,
  pathway_name VARCHAR(200) NOT NULL,
  pathway_class VARCHAR(100),
  gene_count INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_name (pathway_name),
  INDEX idx_class (pathway_class),
  INDEX idx_gene_count (gene_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: datasets
-- Stores dataset metadata
-- =====================================================
CREATE TABLE IF NOT EXISTS datasets (
  dataset_id VARCHAR(10) PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  type VARCHAR(100),
  size VARCHAR(20),
  upload_date DATE,
  description TEXT,
  samples VARCHAR(100),
  format VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: expression_counts
-- Stores raw expression count data from count matrices
-- =====================================================
CREATE TABLE IF NOT EXISTS expression_counts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  gene_id VARCHAR(50) NOT NULL,
  sample_name VARCHAR(50) NOT NULL,
  raw_count INT NOT NULL DEFAULT 0,
  dataset_name VARCHAR(100),
  tissue VARCHAR(50),
  sex VARCHAR(20),
  condition_group VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_gene (gene_id),
  INDEX idx_sample (sample_name),
  INDEX idx_dataset (dataset_name),
  INDEX idx_tissue_condition (tissue, condition_group),
  INDEX idx_gene_dataset (gene_id, dataset_name),
  FOREIGN KEY (gene_id) REFERENCES genes(gene_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: read_statistics
-- Stores per-sample read QC metrics from sequencing
-- =====================================================
CREATE TABLE IF NOT EXISTS read_statistics (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sample_name VARCHAR(50) NOT NULL,
  raw_reads BIGINT,
  processed_reads BIGINT,
  high_quality_ratio DECIMAL(8,6),
  dataset_source VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sample (sample_name),
  INDEX idx_dataset_source (dataset_source)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: assembly_statistics
-- Stores transcriptome assembly metrics
-- =====================================================
CREATE TABLE IF NOT EXISTS assembly_statistics (
  id INT AUTO_INCREMENT PRIMARY KEY,
  metric_name VARCHAR(100) NOT NULL,
  liver_assembled VARCHAR(50),
  liver_assembled_300 VARCHAR(50),
  liver_clustered VARCHAR(50),
  liver_cds VARCHAR(50),
  muscle_assembled VARCHAR(50),
  muscle_assembled_300 VARCHAR(50),
  muscle_clustered VARCHAR(50),
  muscle_cds VARCHAR(50),
  master_assembled_cds VARCHAR(50),
  master_clustered_cds VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
