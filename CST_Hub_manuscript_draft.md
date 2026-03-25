CST-Hub: A Web-Based Transcriptomics Database for Channa striata Integrating Differential Expression, Functional Annotation, Pathway Analytics, and Aquaculture-Focused Multi-Condition RNA-Seq Resources

Abstract

The rapid growth of RNA-Seq in aquaculture has created a need for species-focused databases that connect expression matrices, differential expression, functional annotations, and pathway interpretation in a reproducible web environment. Here, we present CST-Hub (Channa striata Transcriptomics Hub), a relational, web-accessible transcriptomics database designed for striped murrel, a high-value freshwater species in Asia. The platform consolidates de novo and comparative expression resources from captive-vs-wild and diet-intervention experiments, and provides integrated modules for differential expression filtering, volcano visualization, top-gene ranking, GO/KEGG functional search, pathway-centric exploration, and dataset-level quality/assembly statistics. CST-Hub is implemented using PHP, MySQL/MariaDB, JavaScript, Chart.js, and Plotly, with API-driven page components and indexed query support. The current schema comprises eight linked entities (genes, deg_results, functional_annotations, pathways, datasets, expression_counts, read_statistics, assembly_statistics), with full-text indexing for annotation fields and endpoint-level pagination/sorting. Dataset metadata in the deployed import set includes multiple RNA-Seq assets (e.g., DS-001 to DS-009), covering liver, muscle, brain, and gonad contexts, with condition groups spanning control and PUFA-related dietary treatments. The database architecture follows principles of curated, searchable domain resources established in prior biological databases while targeting aquaculture-specific translational utility. CST-Hub provides a practical and extensible infrastructure for hypothesis generation in nutrition, reproduction, microbiome-linked metabolism, and selective-breeding research in Channa striata.

Introduction

Domain-specific biological databases have repeatedly accelerated discovery by converting fragmented datasets into queryable knowledge resources (Sharma et al., 2014; Stenson et al., 2009; Gilbert, 2005; Dugas et al., 2016). In transcriptomics, this role becomes more critical as modern studies produce high-dimensional count matrices, differential expression outputs, pathway enrichments, and multiple experimental contrasts that are difficult to harmonize without standardized structures and interfaces (Maheswari et al., 2009; Calduch-Giner et al., 2013; Pasquier et al., 2016; Svensson et al., 2020). The species Channa striata has major relevance to tropical freshwater aquaculture, where growth performance, stress response, reproductive physiology, and nutritional lipid metabolism are active research priorities (Siddaiah et al., 2022; Kumar et al., 2022; Rasal et al., 2024; Sharma et al., 2025).

Although transcriptome datasets and analytical outputs for Channa striata are increasing, these resources are often distributed across supplementary files, independent analyses, and static documents. This limits reusability, cross-study exploration, and transparent retrieval of gene-level evidence. Public portals in other systems demonstrate the value of structured access, integrated search, and endpoint-driven retrieval for reproducibility and collaborative reuse (Spooner et al., 2012; Petri et al., 2011; Shen et al., 2016; Childs et al., 2012).

CST-Hub was developed to address this gap for Channa striata by providing a unified transcriptomics platform linking expression counts, differential expression summaries, functional annotations, pathway mapping, and quality metrics in a web application designed for bench and computational users. The design philosophy follows robust database traditions (Sharma et al., 2014; Stano et al., 2016; Liew et al., 2016) while centering aquaculture-specific biological questions, including captive adaptation, diet-responsive molecular signatures, and reproductive biology.

Materials and Methods

System design and implementation.
CST-Hub follows a three-tier architecture: frontend (HTML/CSS/JavaScript), API layer (PHP REST-like endpoints), and persistence layer (MySQL/MariaDB via PDO). Frontend pages communicate with grouped API modules for differential expression, charts, pathways, functional search, expression, datasets, and statistics. This architecture supports asynchronous updates, filter-based retrieval, and modular maintainability.

Database schema.
The relational schema contains eight core tables: genes, deg_results, functional_annotations, pathways, datasets, expression_counts, read_statistics, and assembly_statistics. Referential links are defined primarily through gene_id and pathway relationships. Indexing includes b-tree indexes for frequent filters (gene, tissue, condition, p-value, regulation) and full-text indexes for GO/KEGG annotation search. The schema uses UTF8MB4 and InnoDB, supporting data integrity and scalable query patterns.

Data resources represented.
The import configuration includes curated dataset records such as DS-001 (master count matrix with annotation), DS-004/DS-005 (DESeq2 result collections), DS-006 and DS-008 (captive-wild and male-brain count matrices), DS-007 (diet experiment count matrices), and DS-009 (read and assembly statistics). Experimental contexts include brain, gonad, liver, and muscle tissues, with condition groups for captive-vs-wild and dietary interventions (control, soybean, linseed, mustard, and combined formulations).

Quality and assembly statistics.
Read statistics are stored at sample level (raw reads, processed reads, high-quality ratio, dataset source). Assembly metrics include contig counts, length distributions, N50, and non-ATGC percentages across liver, muscle, and master assemblies. These metrics are exposed through dedicated API endpoints for rapid inspection and potential downstream QC interpretation.

Analytical and visualization modules.
DEG modules provide parameterized filtering and sorting; chart endpoints produce volcano, regulation-distribution, and top-gene views. Functional search supports full-text and fallback matching over GO and KEGG-linked annotations. Pathway analytics include list/detail retrieval and class-level summary statistics. Expression modules expose gene-specific sample profiles and a heatmap data service based on variance-ranked genes.

Design rationale in literature context.
The system adopts patterns common to successful biological resources: curated data integration, multi-entry search, modular query services, and browser-first access (Knudsen & Wiuf, 2010; Norambuena & Melo, 2010; Tung, 2012; Tripathy et al., 2012; Ma et al., 2015).

Database Content and Web Utility

CST-Hub provides user-facing pages for home, about, DEG analysis, functional annotation search, pathway analysis, figures, publications, and policy/disclaimer content. From the user perspective, the most important utility layers are:

1) Differential expression exploration.
Users can interactively subset genes by tissue, condition, log fold change, significance trends, and sort parameters. The volcano view supports rapid detection of prominent up- and down-regulated candidates, while top-gene ranking and regulation distribution provide quick summary interpretation.

2) Functional annotation retrieval.
Gene-level records are linked with GO and KEGG annotations and searchable by IDs, terms, and free text, improving biological interpretation beyond fold-change tables. This aligns with the broader role of ontology/pathway-aware annotation frameworks in transcriptome interpretation (Gene Ontology Consortium, 2004; Gene Ontology Consortium, 2008; Gene Ontology Consortium, 2010; Gene Ontology Consortium, 2012; Gene Ontology Consortium, 2014).

3) Pathway-centric interpretation.
The pathway module supports ranked pathway browsing, class distributions, and pathway detail expansion with associated gene sets. This is especially useful for nutrition and metabolism studies where coordinated pathway shifts are more interpretable than isolated gene effects.

4) Expression-level context.
Sample-wise count retrieval and heatmap-oriented endpoint output support exploratory comparisons across datasets and biological groups.

5) Dataset and QC transparency.
Dedicated dataset metadata endpoints and read/assembly statistics endpoints improve traceability and reproducibility, consistent with database best practices for public scientific resources (Karopka et al., 2006; Li & Gallin, 2004; Killion et al., 2003).

The inclusion of Channa striata-focused publications and figure galleries further situates data outputs in ongoing biological narratives, including diet-response biology, microbiome-associated signatures, endocrine regulation, and larval developmental expression (Rasal et al., 2023; Behera et al., 2025; Kumari et al., 2025; Sharma et al., 2025).

Results and Discussion

From a systems perspective, CST-Hub demonstrates that a species-focused, moderately complex schema can support multiple transcriptomic use-cases without requiring users to navigate raw files manually. The integration of DEG, expression, pathway, and annotation layers in a single navigational flow reduces analytical friction and improves hypothesis generation.

The practical relevance of this architecture is twofold. First, it supports aquaculture translation by enabling cross-condition comparison for traits linked to growth, feed response, and reproductive performance. Second, it creates an extensible substrate for future additions such as richer pathway evidence, machine-learning prioritization, ortholog mapping, and programmatic export. Similar transitions from static repositories to integrated platforms have increased impact in other domain databases (Spooner et al., 2012; Petri et al., 2011; Shen et al., 2016; Svensson et al., 2020).

Compared with broad, cross-species repositories, CST-Hub intentionally prioritizes species depth and study-context richness for Channa striata. This focus is valuable for targeted breeding and nutritional genomics workflows in regional aquaculture programs, where trait-linked interpretation must be grounded in species-specific evidence rather than distant model systems.

Limitations.
The current platform is optimized for currently curated datasets and web-level exploratory analysis; it does not yet include full workflow provenance objects, user-submitted pipeline execution, or advanced cross-species comparative modules. Future work should incorporate standardized metadata schemas, more formal versioning of imports, and enhanced API documentation.

Overall, CST-Hub aligns with established principles from database-centric bioinformatics while providing a concrete implementation tuned to Channa striata and aquaculture-relevant transcriptomics.

Conclusion

CST-Hub provides a comprehensive transcriptomics data portal for Channa striata that unifies expression counts, DEG outputs, functional annotations, pathway context, and QC/assembly metrics in an API-driven web framework. By combining species-focused curation with interactive query/visualization modules, the platform supports efficient exploration of biological signals relevant to nutrition, reproduction, and aquaculture adaptation. The resource is designed to be extensible and can serve as a foundation for future integrative genomics efforts in freshwater aquaculture systems.

Availability

CST-Hub source and deployment assets are organized as a PHP-MySQL web application with static frontend pages and modular API endpoints. Core setup requires importing schema and data SQL files, configuring database credentials, and serving through a standard Apache/PHP stack. The project includes DEG, pathway, functional search, expression, dataset, and statistics services exposed through JSON responses.

Funding

Development is aligned with CABin-supported bioinformatics activities under ICAR-CIFA and collaborating institutional partners.

Conflict of Interest

The authors declare no conflict of interest.

References (APA)

(2007). The Gene Ontology project in 2008. Nucleic Acids Research, 36(suppl_1), D440–D444. https://doi.org/10.1093/nar/gkm883
(2009). The Gene Ontology in 2010: extensions and refinements. Nucleic Acids Research, 38(suppl_1), D331–D335. https://doi.org/10.1093/nar/gkp1018
(2012). Gene Ontology Annotations and Resources. Nucleic Acids Research, 41(D1), D530-D535. https://doi.org/10.1093/nar/gks1050
(2014). Gene Ontology Consortium: going forward. Nucleic Acids Research, 43(D1), D1049-D1056. https://doi.org/10.1093/nar/gku1179
Behera, G., Rakkannan, G., Rasal, K. D., Sawant, P. B., Kumar, R., Sahoo, L., Chadha, N. K., & Sundaray, J. K. (2025). MicroRNA Dynamics in Gonadal Development and Reproductive Regulation of Striped Murrel, Channa striata (Bloch, 1793). Journal of Applied Ichthyology, 2025(1). Portico. https://doi.org/10.1155/jai/5573815
Bhawna, Bonthala, V. S., & Gajula, M. P. (2016). PvTFDB: aPhaseolus vulgaristranscription factors database for expediting functional genomics in legumes. Database, 2016, baw114. https://doi.org/10.1093/database/baw114
Calduch-Giner, J. A., Bermejo-Nogales, A., Benedito-Palos, L., Estensoro, I., Ballester-Lozano, G., Sitjà-Bobadilla, A., & Pérez-Sánchez, J. (2013). Deep sequencing for de novo construction of a marine fish (Sparus aurata)transcriptome database with a large coverage of protein-coding transcripts. BMC Genomics, 14(1). https://doi.org/10.1186/1471-2164-14-178
Chen, Y., Timms, P., & Chen, Y.-P. P. (2007). CIDB: Chlamydia Interactive Database for cross-querying genomics, transcriptomics and proteomics data. Biomolecular Engineering, 24(6), 603–608. https://doi.org/10.1016/j.bioeng.2007.08.017
Childs, K. L., Konganti, K., & Buell, C. R. (2012). The Biofuel Feedstock Genomics Resource: a web-based portal and database to enable functional genomics of plant biofuel feedstock species. Database, 2012. https://doi.org/10.1093/database/bar061
Dugas, M., Neuhaus, P., Meidt, A., Doods, J., Storck, M., Bruland, P., & Varghese, J. (2016). Portal of medical data models: information infrastructure for medical research and healthcare. Database, 2016, bav121. https://doi.org/10.1093/database/bav121
Gene Ontology Consortium. (2004). The Gene Ontology (GO) database and informatics resource. Nucleic Acids Research, 32(90001), 258D – 261. https://doi.org/10.1093/nar/gkh036
Gilbert, D. (2005). Biomolecular Interaction Network Database. Briefings in Bioinformatics, 6(2), 194–198. https://doi.org/10.1093/bib/6.2.194
Karopka, T., Fluck, J., Mevissen, H.-T., & Glass, Ä. (2006). The Autoimmune Disease Database: a dynamically compiled literature-derived database. BMC Bioinformatics, 7(1). https://doi.org/10.1186/1471-2105-7-325
Killion, P. J., Sherlock, G., & Iyer, V. R. (2003). The Longhorn Array Database (LAD): An Open-Source, MIAME compliant implementation of the Stanford Microarray Database (SMD). BMC Bioinformatics, 4(1). https://doi.org/10.1186/1471-2105-4-32
Knowles, D. G., Röder, M., Merkel, A., & Guigó, R. (2013). Grape RNA-Seq analysis pipeline environment. Bioinformatics, 29(5), 614–621. https://doi.org/10.1093/bioinformatics/btt016
Knudsen, M., & Wiuf, C. (2010). The CATH database. Human Genomics, 4(3), 207. https://doi.org/10.1186/1479-7364-4-3-207
Kumar, R., Gokulakrishnan, M., Debbarma, J., & Damle, D. K. (2022). Advances in captive breeding and seed rearing of striped murrel Channa striata, a high value food fish of Asia. Animal Reproduction Science, 238, 106957. https://doi.org/10.1016/j.anireprosci.2022.106957
Kumari, R., Srivastava, P. P., Mohanta, K. N., Das, P., Kumar, R., Sharma, P., Sahoo, L., Rajesh, M., & Siddaiah, G. M. (2025). Expression of pancreatic α-amylase and growth related genes during larval ontogeny in striped murrel (Channa striata). Scientific Reports, 15(1). https://doi.org/10.1038/s41598-025-06945-3
Li, B., & Gallin, W. J. (2004). VKCDB: Voltage-gated potassium channel database. BMC Bioinformatics, 5(1). https://doi.org/10.1186/1471-2105-5-3
Liew, Y. J., Aranda, M., & Voolstra, C. R. (2016). Reefgenomics.Org - a repository for marine genomics data. Database, 2016, baw152. https://doi.org/10.1093/database/baw152
Liu, J. (2007). Genomic resources and their informatic analysis for comparative genomics in catfish. Aquaculture, 272, S286. https://doi.org/10.1016/j.aquaculture.2007.07.128
Ma, Y., Xu, T., Wan, D., Ma, T., Shi, S., Liu, J., & Hu, Q. (2015). The salinity tolerant poplar database (STPD): a comprehensive database for studying tree salt-tolerant adaption and poplar genomics. BMC Genomics, 16(1). https://doi.org/10.1186/s12864-015-1414-7
Maheswari, U., Mock, T., Armbrust, E. V., & Bowler, C. (2009). Update of the Diatom EST Database: a new tool for digital transcriptomics. Nucleic Acids Research, 37(Database), D1001–D1005. https://doi.org/10.1093/nar/gkn905
Maselli, V., Di Bernardo, D., & Banfi, S. (2008). CoGemiR: A comparative genomics microRNA database. BMC Genomics, 9(1), 457. https://doi.org/10.1186/1471-2164-9-457
Miao, Z., Li, D., Zhang, Z., Dong, J., Su, Z., & Wang, T. (2012). Medicago truncatula transporter database: a comprehensive database resource for M. truncatula transporters. BMC Genomics, 13(1). https://doi.org/10.1186/1471-2164-13-60
Norambuena, T., & Melo, F. (2010). The Protein-DNA Interface database. BMC Bioinformatics, 11(1). https://doi.org/10.1186/1471-2105-11-262
Pasquier, J., Cabau, C., Nguyen, T., Jouanno, E., Severac, D., Braasch, I., Journot, L., Pontarotti, P., Klopp, C., Postlethwait, J. H., Guiguen, Y., & Bobe, J. (2016). Gene evolution and gene expression after whole genome duplication in fish: the PhyloFish database. BMC Genomics, 17(1). https://doi.org/10.1186/s12864-016-2709-z
Paul, A., Baral, B., Naik, D. N., Parida, S., Damle, D. K., Kumar, R., & Sahoo, P. K. (2024). First report of massive renomegaly in adult striped murrel Channa striata (Bloch, 1793). Indian Journal of Fisheries, 71(2). https://doi.org/10.21077/ijf.2024.71.2.132283-12
Petri, V., Shimoyama, M., Hayman, G. T., Smith, J. R., Tutaj, M., de Pons, J., Dwinell, M. R., Munzenmaier, D. H., Twigger, S. N., & Jacob, H. J. (2011). The Rat Genome Database Pathway Portal. Database, 2011(0), bar010–bar010. https://doi.org/10.1093/database/bar010
Rasal, K. D., Davu, S., Asgolkar, P., Shinde, S., Kumar, P. V., Dhere, S., Acharya, A., Kumar, R., Sonwane, A., Brahmane, M., Sundaray, J., & Chaudhari, A. (2024). Molecular characterization and expression profiling of 3 beta-hydroxysteroid dehydrogenase and hydroxysteroid 17-beta dehydrogenase in response to HCG stimulation in striped murrel, Channa striata (Bloch, 1793). Gene Reports, 37, 102018. https://doi.org/10.1016/j.genrep.2024.102018
Rasal, K. D., Dixit, S., Swain, P. P., Siriyappagouder, P., Kumar, R., Iquebal, M. A., Vasam, M., Debbarma, J., Angadi, U. B., Jaiswal, S., Rai, A., Kumar, D., & Sundaray, J. K. (2023). Identification of host-specific skin-mucus and gut microbiota in snakehead murrel (Channa striata) (Bloch, 1793) using metagenomics approach. Journal of Applied Biology &amp; Biotechnology. https://doi.org/10.7324/jabb.2023.11508
Rosikiewicz, M., Comte, A., Niknejad, A., Robinson-Rechavi, M., & Bastian, F. B. (2013). Uncovering hidden duplicated content in public transcriptomics data. Database, 2013. https://doi.org/10.1093/database/bat010
Sharma, B. M., Mohapatra, M., Kumar, R., Swain, P. P., Das, I. I., Sahoo, L., Vasam, M., Bag, D., Debbarma, J., Jaiswal, S., Iquebal, M. A., Angadi, U. B., Rai, A., Kumar, D., & Sundaray, J. K. (2025). Comprehensive insight into gut microbiota and their interaction with fatty acid profiles in Channa striata during the spawning season. Scientific Reports, 15(1). https://doi.org/10.1038/s41598-025-20869-y
Sharma, N., Farooqi, M. S., Chaturvedi, K. K., Lal, S. B., Grover, M., Rai, A., & Pandey, P. (2014). The Halophile Protein Database. Database, 2014(0), bau114–bau114. https://doi.org/10.1093/database/bau114
Shen, Y., Yao, H., Li, A., & Wang, M. (2016). CSCdb: a cancer stem cells portal for markers, related genes and functional information. Database, 2016, baw023. https://doi.org/10.1093/database/baw023
Siddaiah, G. M., Kumar, R., Kumari, R., Damle, D. K., Rasal, K. D., Manohar, V., Sundaray, J. K., & Pillai, B. R. (2022). Dietary supplementation of fish protein hydrolysate improves growth, feed efficiency and immune response in freshwater carnivore fish, Channa striata fingerlings. Aquaculture Research, 53(9), 3401–3415. Portico. https://doi.org/10.1111/are.15848
Spooner, W., Youens-Clark, K., Staines, D., & Ware, D. (2012). GrameneMart: the BioMart data portal for the Gramene project. Database, 2012(0), bar056–bar056. https://doi.org/10.1093/database/bar056
Stano, M., Beke, G., & Klucar, L. (2016). viruSITE—integrated database for viral genomics. Database, 2016, baw162. https://doi.org/10.1093/database/baw162
Stenson, P. D., Ball, E. V., Howells, K., Phillips, A. D., Mort, M., & Cooper, D. N. (2009). The Human Gene Mutation Database: providing a comprehensive central mutation database for molecular diagnostics and personalised genomics. Human Genomics, 4(2), 69. https://doi.org/10.1186/1479-7364-4-2-69
Svensson, V., da Veiga Beltrame, E., & Pachter, L. (2020). A curated database reveals trends in single-cell transcriptomics. Database, 2020. https://doi.org/10.1093/database/baaa073
Tripathy, S., Deo, T., & Tyler, B. M. (2012). Oomycete transcriptomics database: A resource for oomycete transcriptomes. BMC Genomics, 13(1), 303. https://doi.org/10.1186/1471-2164-13-303
Tung, C.-W. (2012). PupDB: a database of pupylated proteins. BMC Bioinformatics, 13(1). https://doi.org/10.1186/1471-2105-13-40
Williams, G. (2013). SPIEDw: a searchable platform-independent expression database web tool. BMC Genomics, 14(1), 765. https://doi.org/10.1186/1471-2164-14-765
Zhang, Y., Zhang, J., Fan, H., Lu, R., & Nie, G. (2024). Database construction and comparative genomics analysis of genes involved in nutritional metabolic diseases in fish. Comparative Biochemistry and Physiology Part D: Genomics and Proteomics, 50, 101241. https://doi.org/10.1016/j.cbd.2024.101241