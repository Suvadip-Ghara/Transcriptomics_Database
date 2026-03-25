# CST-Hub<sup>TDB</sup> — Channa striata Transcriptomics Database

A web-based transcriptomics resource for striped murrel (*Channa striata*), developed under the CABin-funded project at ICAR-CIFA, Bhubaneswar.

## Overview

CST-Hub provides searchable access to RNA-Seq derived gene expression data covering reproductive biology, PUFA metabolism, and gut microbiome dynamics in *C. striata*. The platform supports DEG analysis, functional annotation search, KEGG pathway exploration, and interactive visualizations.

## Stack

- **Backend:** PHP 7.4+ · MySQL/MariaDB · PDO
- **Frontend:** HTML5 · CSS3 · JavaScript (Fetch API) · Chart.js 4.4
- **Server:** Apache (XAMPP compatible)

## Setup

1. Copy the project folder to your web server document root (e.g., `htdocs/transcriptome/`).
2. Create the MySQL database using `config/database/schema.sql`.
3. Import data using `config/database/import_data.sql`.
4. Update credentials in `config/database.php`.
5. Access via `http://localhost/transcriptome/`.

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