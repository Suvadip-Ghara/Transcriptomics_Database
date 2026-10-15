# CST-Hub<sup>TDB</sup> — Channa striata Transcriptomics Database

A web-based transcriptomics resource for striped murrel (*Channa striata*), developed under the CABin-funded project at ICAR-CIFA, Bhubaneswar.

## Overview

CST-Hub provides searchable access to RNA-Seq derived gene expression data covering reproductive biology, PUFA metabolism, and gut microbiome dynamics in *C. striata*. The platform supports DEG analysis, gene-level expression queries, functional annotations, pathway exploration, and interactive visualizations.

## Scope of this repository

This repository contains the source code of the CST-Hub web application and its relational database only: the PHP/MySQL API (`api/`), database schema (`config/database/schema.sql`), front-end pages (`*.html`, `script_api.js`, `js/api.js`, `styles.css`) and the figure gallery.

It is not a repository for transcriptomic analysis workflows. Scripts for RNA-Seq quality control, read alignment or de novo assembly, quantification, differential expression and enrichment analysis are not distributed here. The tools, versions, thresholds and statistical settings used to generate the data are described in the associated manuscript (Materials and Methods, Tables 2 and 4) and, for the PUFA dietary dataset, in Iyyappan et al. (2026, Journal of Agriculture and Food Research, 27, 102805). Raw sequencing reads are available from NCBI under BioProjects PRJNA1270933 (PUFA dietary dataset) and PRJNA1482812 (captive-versus-wild dataset). Pre-computed differential expression results, annotations and expression counts are served through the CST-Hub web interface and API.

## Stack

- **Backend:** PHP 7.4+ · MySQL/MariaDB · PDO
- **Frontend:** HTML5 · CSS3 · JavaScript (Fetch API) · Chart.js 4.4 · Plotly 2.27
- **Server:** Apache (XAMPP compatible)

## Setup

1. Copy the project folder to your web server document root (e.g., `htdocs/transcriptome/`).
2. Create the MySQL database using `config/database/schema.sql`.
3. Load the data file (`import_data.sql`), available from the corresponding author on request, or build the tables from the schema and load your own datasets.
4. Update credentials in `config/database.php`.
5. Access via `http://localhost/transcriptome/`.

Set `DEBUG_MODE` to false in `config/database.php` for production use.

## Project Structure

```
├── api/                        # PHP REST endpoints
│   ├── charts/                 # Volcano, distribution, top genes
│   ├── datasets/               # Dataset list & detail
│   ├── deg/                    # DEG filter, stats, gene lookup
│   ├── expression/             # Gene expression & heatmap
│   ├── functional/             # GO/KEGG annotation search
│   ├── pathways/               # Pathway list, detail, stats
│   └── stats/                  # Assembly & read statistics
├── config/                     # DB connection, CORS, SQL files
├── FIgures_PUFA_Transcriptomics/  # Analysis figures (tissue-wise)
├── js/api.js                   # AJAX utility module
├── script_api.js               # Page-level UI logic
├── styles.css                  # Stylesheet
└── *.html                      # Application pages
```

## Team

**Principal Investigator:** Dr. J. K. Sundaray, ICAR-CIFA

**Co-Investigators:** Dr. Rajesh Kumar, Dr. Lakshman Sahoo, Mr. Jackson Debbarma (ICAR-CIFA); Dr. Dinesh Kumar, Dr. Sarika (ICAR-IASRI)

**Research Scholars (YP-II):** Dr. Madhusmita Mohapatra, Iyyappan Somasundharam, Suvadip Ghara (ICAR-CIFA)

## License

Copyright © 2026 CST-Hub — Channa striata Transcriptomics Hub | ICAR-CIFA , ICAR-IASRI
