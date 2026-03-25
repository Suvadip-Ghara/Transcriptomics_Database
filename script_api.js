// ===============================================
// CABPCIFASM Database - Main JavaScript File
// Using API-based architecture with AJAX calls
// ===============================================

// ===============================================
// Footer Information Sections Toggle
// ===============================================
function toggleFooterSection(sectionId) {
    event.preventDefault();
    
    const section = document.getElementById(sectionId);
    const container = document.getElementById('footerInfoContent');
    
    if (!section || !container) return;
    
    // Close all other sections
    const allSections = document.querySelectorAll('.footer-info-section');
    allSections.forEach(sec => {
        if (sec.id !== sectionId) {
            sec.style.display = 'none';
        }
    });
    
    // Toggle the clicked section
    if (section.style.display === 'none') {
        section.style.display = 'block';
        container.style.display = 'block';
        
        // Smooth scroll to footer info
        setTimeout(() => {
            container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 100);
    } else {
        section.style.display = 'none';
        
        // Check if any section is still visible
        const visibleSections = Array.from(allSections).filter(s => s.style.display !== 'none');
        if (visibleSections.length === 0) {
            container.style.display = 'none';
        }
    }
}

// ===============================================
// Mobile Menu Handler
// ===============================================
const hamburger = document.getElementById('mobileMenuBtn');
const navMenu = document.getElementById('navMenu');

if (hamburger && navMenu) {
    hamburger.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        hamburger.classList.toggle('active');
    });

    // Close menu when link is clicked
    const navLinks = navMenu.querySelectorAll('a');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('active');
            hamburger.classList.remove('active');
        });
    });

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
            navMenu.classList.remove('active');
            hamburger.classList.remove('active');
        }
    });
}

// Header Scroll Effect
const header = document.querySelector('header');
let lastScrollTop = 0;

window.addEventListener('scroll', () => {
    let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    if (scrollTop > lastScrollTop && scrollTop > 80) {
        header.classList.add('hide');
    } else {
        header.classList.remove('hide');
    }
    lastScrollTop = scrollTop;
});

// ===============================================
// Utility: Render Tables
// ===============================================
function renderTable(data, tableBodyId, columns, actionCallback) {
    const tbody = document.getElementById(tableBodyId);
    if (!tbody) return;

    tbody.innerHTML = '';

    if (data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="${columns.length + 1}" style="text-align:center; padding: 2rem; color: #64748b;">No results found matching your criteria.</td></tr>`;
        return;
    }

    data.forEach(item => {
        const tr = document.createElement('tr');

        columns.forEach(col => {
            const td = document.createElement('td');
            if (col.render) {
                td.innerHTML = col.render(item[col.key], item);
            } else {
                td.textContent = item[col.key] || '';
            }
            tr.appendChild(td);
        });

        // Action Column
        const actionTd = document.createElement('td');
        const btn = document.createElement('button');
        btn.className = 'btn btn-primary';
        btn.style.padding = '0.25rem 0.75rem';
        btn.style.fontSize = '0.8rem';
        btn.innerHTML = '<i class="fas fa-eye"></i> View';
        btn.onclick = () => actionCallback(item);
        actionTd.appendChild(btn);
        tr.appendChild(actionTd);

        tbody.appendChild(tr);
    });
}

// ===============================================
// Utility: Pagination Controls
// ===============================================
function renderPagination(containerId, currentPage, totalPages, onPageChange) {
    const container = document.getElementById(containerId);
    if (!container) return;

    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '';
    
    // Previous button
    html += `<button class="page-btn" ${currentPage <= 1 ? 'disabled' : ''} data-page="${currentPage - 1}">
                <i class="fas fa-chevron-left"></i>
             </button>`;
    
    // Page numbers
    const maxButtons = 5;
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, startPage + maxButtons - 1);
    
    if (endPage - startPage < maxButtons - 1) {
        startPage = Math.max(1, endPage - maxButtons + 1);
    }
    
    for (let i = startPage; i <= endPage; i++) {
        html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
    }
    
    // Next button
    html += `<button class="page-btn" ${currentPage >= totalPages ? 'disabled' : ''} data-page="${currentPage + 1}">
                <i class="fas fa-chevron-right"></i>
             </button>`;

    container.innerHTML = html;
    
    // Add click handlers
    container.querySelectorAll('.page-btn[data-page]').forEach(btn => {
        btn.addEventListener('click', () => {
            if (!btn.disabled) {
                onPageChange(parseInt(btn.dataset.page));
            }
        });
    });
}

// ===============================================
// DEG PAGE
// ===============================================
if (document.getElementById('degTable')) {
    const degState = {
        currentPage: 1,
        limit: 100,
        sortBy: 'p_value',
        sortOrder: 'asc',
        filters: {
            tissue: '',
            condition: '',
            minLogFC: 0
        }
    };

    const columns = [
        { key: 'gene_id' },
        { key: 'gene_name' },
        { key: 'tissue' },
        { key: 'condition' },
        { key: 'logFC', render: (val) => `<span style="font-weight:600; color: ${val > 0 ? '#166534' : '#991b1b'}">${val > 0 ? '+' : ''}${val}</span>` },
        { key: 'pVal', render: (val) => val ? val.toExponential(2) : 'N/A' },
        { key: 'reg', render: (val) => `<span class="status-badge ${val === 'Up' ? 'status-up' : 'status-down'}">${val}</span>` }
    ];

    async function loadDEGData() {
        try {
            const result = await DEGAPI.filter(
                degState.filters.tissue || null,
                degState.filters.condition || null,
                degState.filters.minLogFC,
                degState.sortBy,
                degState.sortOrder,
                degState.currentPage,
                degState.limit
            );

            // Update results counter
            const resultsCount = document.getElementById('degResultsCount');
            if (resultsCount) {
                resultsCount.innerHTML = `<i class="fas fa-info-circle"></i> Showing <strong>${result.total}</strong> genes (<span style="color: #22c55e;">${result.up} up</span>, <span style="color: #ef4444;">${result.down} down</span>)`;
            }

            // Render table
            renderTable(result.data, 'degTableBody', columns, async (item) => {
                try {
                    const geneData = await DEGAPI.getGene(item.gene_id);
                    alert(`Gene Details:\n\nID: ${geneData.gene.gene_id}\nName: ${geneData.gene.gene_name || 'N/A'}\nDescription: ${geneData.gene.protein_description || 'N/A'}\nLogFC: ${item.logFC}\nP-Value: ${item.pVal}`);
                } catch (error) {
                    alert(`Error loading gene details: ${error.message}`);
                }
            });

            // Render pagination
            renderPagination('degPagination', degState.currentPage, result.totalPages, (page) => {
                degState.currentPage = page;
                loadDEGData();
            });

        } catch (error) {
            showError(error.message, document.querySelector('.container'));
        }
    }

    async function loadDEGCharts() {
        try {
            // Volcano Plot
            const volcanoData = await ChartAPI.getVolcano(
                degState.filters.tissue || null,
                degState.filters.condition || null
            );
            
            const volcanoCtx = document.getElementById('volcanoChart');
            if (volcanoCtx && volcanoData.data.length > 0) {
                new Chart(volcanoCtx, {
                    type: 'scatter',
                    data: {
                        datasets: [{
                            label: 'Up-regulated',
                            data: volcanoData.data.filter(d => d.regulation === 'Up').map(d => ({ x: d.x, y: d.y })),
                            backgroundColor: 'rgba(34, 197, 94, 0.5)',
                            pointRadius: 3
                        }, {
                            label: 'Down-regulated',
                            data: volcanoData.data.filter(d => d.regulation === 'Down').map(d => ({ x: d.x, y: d.y })),
                            backgroundColor: 'rgba(239, 68, 68, 0.5)',
                            pointRadius: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'top' },
                            title: { display: false }
                        },
                        scales: {
                            x: { title: { display: true, text: 'Log2 Fold Change' } },
                            y: { title: { display: true, text: '-Log10(P-Value)' } }
                        }
                    }
                });
            }

            // Regulation Distribution
            const distData = await ChartAPI.getDEGDistribution(
                degState.filters.tissue || null,
                degState.filters.condition || null
            );
            
            const regulationCtx = document.getElementById('regulationChart');
            if (regulationCtx) {
                new Chart(regulationCtx, {
                    type: 'pie',
                    data: {
                        labels: ['Up-regulated', 'Down-regulated'],
                        datasets: [{
                            data: [distData.up, distData.down],
                            backgroundColor: ['rgba(34, 197, 94, 0.8)', 'rgba(239, 68, 68, 0.8)']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            }

            // Top Genes
            const topGenesData = await ChartAPI.getTopGenes(
                degState.filters.tissue || null,
                degState.filters.condition || null,
                10
            );
            
            const topGenesCtx = document.getElementById('topGenesChart');
            if (topGenesCtx && topGenesData.data.length > 0) {
                new Chart(topGenesCtx, {
                    type: 'bar',
                    data: {
                        labels: topGenesData.data.map(g => g.gene.length > 25 ? g.gene.substring(0, 25) + '...' : g.gene),
                        datasets: [{
                            label: '|Log2 Fold Change|',
                            data: topGenesData.data.map(g => g.absLogFC),
                            backgroundColor: topGenesData.data.map(g => g.regulation === 'Up' ? 'rgba(34, 197, 94, 0.8)' : 'rgba(239, 68, 68, 0.8)')
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: { title: { display: true, text: '|Log2 Fold Change|' }, beginAtZero: true }
                        }
                    }
                });
            }

        } catch (error) {
            console.error('Error loading charts:', error);
        }
    }

    // Event Listeners
    document.getElementById('degSearchBtn').addEventListener('click', () => {
        degState.filters.tissue = document.getElementById('degTissue').value;
        degState.filters.condition = document.getElementById('degCondition').value;
        degState.filters.minLogFC = parseFloat(document.getElementById('degLogFC').value) || 0;
        degState.currentPage = 1;
        loadDEGData();
    });

    document.getElementById('degResetBtn').addEventListener('click', () => {
        document.getElementById('degTissue').value = '';
        document.getElementById('degCondition').value = '';
        document.getElementById('degLogFC').value = '';
        degState.filters = { tissue: '', condition: '', minLogFC: 0 };
        degState.currentPage = 1;
        loadDEGData();
    });

    // Table sorting
    const degTable = document.getElementById('degTable');
    if (degTable) {
        const headers = degTable.querySelectorAll('thead th');
        const sortMapping = {
            'Gene ID': 'gene_id',
            'Gene Name': 'gene_name',
            'Tissue': 'tissue',
            'Condition': 'condition_name',
            'Log2 Fold Change': 'log_fc',
            'P-Value': 'p_value',
            'Regulation': 'regulation'
        };

        headers.forEach((header, index) => {
            if (index === headers.length - 1) return; // Skip action column
            
            header.classList.add('sortable');
            header.style.cursor = 'pointer';
            
            header.addEventListener('click', () => {
                const columnName = header.textContent.trim();
                const sortKey = sortMapping[columnName];
                
                if (!sortKey) return;
                
                if (degState.sortBy === sortKey) {
                    degState.sortOrder = degState.sortOrder === 'asc' ? 'desc' : 'asc';
                } else {
                    degState.sortBy = sortKey;
                    degState.sortOrder = 'asc';
                }
                
                degState.currentPage = 1;
                loadDEGData();
                
                // Update header visual
                headers.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
                header.classList.add(degState.sortOrder === 'asc' ? 'sort-asc' : 'sort-desc');
            });
        });
    }

    // Initial load
    loadDEGData();
    loadDEGCharts();
}

// ===============================================
// FUNCTIONAL SEARCH PAGE
// ===============================================
if (document.getElementById('funcTable')) {
    const funcState = {
        currentPage: 1,
        limit: 100,
        sortBy: 'gene_id',
        sortOrder: 'asc',
        query: ''
    };

    const columns = [
        { key: 'gene_id' },
        { key: 'name', render: (val) => `<strong>${val || 'N/A'}</strong>` },
        { key: 'desc' },
        { key: 'go', render: (val) => `<span style="font-size: 0.85rem; color: #475569;">${val || 'N/A'}</span>` },
        { key: 'kegg', render: (val) => `<span style="color: var(--secondary-color);">${val || 'N/A'}</span>` }
    ];

    async function searchFunctional() {
        const query = document.getElementById('funcSearchInput').value.trim();
        
        if (!query) {
            alert('Please enter a search term');
            return;
        }

        funcState.query = query;
        funcState.currentPage = 1;
        
        try {
            const result = await FunctionalAPI.search(
                funcState.query,
                funcState.sortBy,
                funcState.sortOrder,
                funcState.currentPage,
                funcState.limit
            );

            // Update results counter
            const resultsCount = document.getElementById('funcResultsCount');
            if (resultsCount) {
                resultsCount.innerHTML = `<i class="fas fa-info-circle"></i> Found <strong>${result.total}</strong> results for "${funcState.query}"`;
            }

            // Render table
            renderTable(result.data, 'funcTableBody', columns, (item) => {
                alert(`Functional Details:\n\nGene ID: ${item.gene_id}\nName: ${item.name}\nDescription: ${item.desc}\nGO Terms: ${item.go}\nKEGG: ${item.kegg}`);
            });

            // Render pagination
            renderPagination('funcPagination', funcState.currentPage, result.totalPages, (page) => {
                funcState.currentPage = page;
                loadFunctionalData();
            });

        } catch (error) {
            showError(error.message, document.querySelector('.container'));
        }
    }

    async function loadFunctionalData() {
        if (!funcState.query) return;
        
        try {
            const result = await FunctionalAPI.search(
                funcState.query,
                funcState.sortBy,
                funcState.sortOrder,
                funcState.currentPage,
                funcState.limit
            );

            renderTable(result.data, 'funcTableBody', columns, (item) => {
                alert(`Functional Details:\n\nGene ID: ${item.gene_id}\nName: ${item.name}\nDescription: ${item.desc}\nGO Terms: ${item.go}\nKEGG: ${item.kegg}`);
            });

            renderPagination('funcPagination', funcState.currentPage, result.totalPages, (page) => {
                funcState.currentPage = page;
                loadFunctionalData();
            });

        } catch (error) {
            showError(error.message, document.querySelector('.container'));
        }
    }

    // Event Listeners
    const searchBtn = document.getElementById('funcSearchBtn');
    const searchInput = document.getElementById('funcSearchInput');
    
    if (searchBtn) {
        searchBtn.addEventListener('click', searchFunctional);
    }
    
    if (searchInput) {
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                searchFunctional();
            }
        });
        
        // Debounced search on input
        let debounceTimer;
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                if (searchInput.value.trim().length > 2) {
                    searchFunctional();
                }
            }, 500);
        });
    }
}

// ===============================================
// PATHWAY PAGE
// ===============================================
if (document.getElementById('pathwayGrid')) {
    let pathwayData = [];
    let filteredPathways = [];

    async function loadPathways(searchTerm = null) {
        try {
            const result = await PathwayAPI.list(searchTerm);
            pathwayData = result.data;
            filteredPathways = result.data;
            renderPathways();
        } catch (error) {
            showError(error.message, document.querySelector('.container'));
        }
    }

    function renderPathways() {
        const grid = document.getElementById('pathwayGrid');
        if (!grid) return;

        grid.innerHTML = '';

        if (filteredPathways.length === 0) {
            grid.innerHTML = '<p style="text-align: center; padding: 2rem; color: #64748b;">No pathways found.</p>';
            return;
        }

        filteredPathways.forEach(pathway => {
            const card = document.createElement('div');
            card.className = 'feature-card';
            card.innerHTML = `
                <div class="feature-icon" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <h3>${pathway.name}</h3>
                <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 0.5rem;">
                    <strong>ID:</strong> ${pathway.id}<br>
                    <strong>Category:</strong> ${pathway.class}<br>
                    <strong>Genes:</strong> ${pathway.genes}
                </p>
                <a href="https://www.genome.jp/entry/${pathway.id}" target="_blank" class="btn btn-secondary" style="margin-top: auto;">
                    <i class="fas fa-external-link-alt"></i> View in KEGG
                </a>
            `;
            grid.appendChild(card);
        });
    }

    async function loadPathwayCharts() {
        try {
            const stats = await PathwayAPI.getStats();

            // Top pathways chart
            const topPathwaysCtx = document.getElementById('pathwayBarChart');
            if (topPathwaysCtx && stats.topPathways.length > 0) {
                new Chart(topPathwaysCtx, {
                    type: 'bar',
                    data: {
                        labels: stats.topPathways.map(p => p.name.length > 30 ? p.name.substring(0, 30) + '...' : p.name),
                        datasets: [{
                            label: 'Gene Count',
                            data: stats.topPathways.map(p => p.genes),
                            backgroundColor: 'rgba(79, 70, 229, 0.8)'
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: { beginAtZero: true, title: { display: true, text: 'Number of Genes' } }
                        }
                    }
                });
            }

            // Pathway class chart
            const classCtx = document.getElementById('pathwayClassChart');
            if (classCtx && stats.byClass) {
                const labels = Object.keys(stats.byClass);
                const values = Object.values(stats.byClass);
                
                new Chart(classCtx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: [
                                'rgba(79, 70, 229, 0.8)',
                                'rgba(34, 197, 94, 0.8)',
                                'rgba(251, 146, 60, 0.8)',
                                'rgba(239, 68, 68, 0.8)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            }

        } catch (error) {
            console.error('Error loading pathway charts:', error);
        }
    }

    // Search functionality
    const searchInput = document.getElementById('pathwaySearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(() => {
            const searchTerm = searchInput.value.trim();
            if (searchTerm.length > 0) {
                loadPathways(searchTerm);
            } else {
                loadPathways();
            }
        }, 300));
    }

    // Initial load
    loadPathways();
    loadPathwayCharts();
}

// ===============================================
// DATASETS PAGE
// ===============================================
if (document.getElementById('datasetTable')) {
    const columns = [
        { key: 'id' },
        { key: 'title' },
        { key: 'type' },
        { key: 'size' },
        { key: 'date' }
    ];

    async function loadDatasets() {
        try {
            const result = await DatasetAPI.list();

            renderTable(result.data, 'datasetTableBody', columns, async (item) => {
                try {
                    const details = await DatasetAPI.getDetail(item.id);
                    const dataset = details.dataset;
                    
                    const message = `Dataset Details:\n\n` +
                                  `ID: ${dataset.id}\n` +
                                  `Title: ${dataset.title}\n` +
                                  `Type: ${dataset.type}\n` +
                                  `Size: ${dataset.size}\n` +
                                  `Date: ${dataset.date}\n` +
                                  `Description: ${dataset.description || 'N/A'}\n` +
                                  `Samples: ${dataset.samples || 'N/A'}\n` +
                                  `Format: ${dataset.format || 'N/A'}`;
                    
                    alert(message);
                } catch (error) {
                    alert(`Error loading dataset details: ${error.message}`);
                }
            });

        } catch (error) {
            showError(error.message, document.querySelector('.container'));
        }
    }

    // Initial load
    loadDatasets();
}

// ===============================================
// EXPRESSION HEATMAP (on DEG page)
// ===============================================
if (document.getElementById('heatmapPlot')) {
    // Dataset-specific filter options
    const datasetFilters = {
        'CvsW': {
            tissues: [['', 'All Tissues'], ['Brain', 'Brain'], ['Gonad', 'Gonad']],
            conditions: [['', 'All Conditions'], ['Captive', 'Captive'], ['Wild', 'Wild']]
        },
        'Diet_Liver': {
            tissues: [['', 'All Tissues'], ['Liver', 'Liver']],
            conditions: [['', 'All Conditions'], ['Control', 'Control'], ['Soybean Oil Diet', 'Soybean Oil Diet'], ['Linseed Oil Diet', 'Linseed Oil Diet'], ['Mustard Oil Diet', 'Mustard Oil Diet'], ['Combined Diet', 'Combined Diet']]
        },
        'Diet_Muscle': {
            tissues: [['', 'All Tissues'], ['Muscle', 'Muscle']],
            conditions: [['', 'All Conditions'], ['Control', 'Control'], ['Soybean Oil Diet', 'Soybean Oil Diet'], ['Linseed Oil Diet', 'Linseed Oil Diet'], ['Mustard Oil Diet', 'Mustard Oil Diet'], ['Combined Diet', 'Combined Diet']]
        },
        'Diet_Master': {
            tissues: [['', 'All Tissues'], ['Liver', 'Liver'], ['Muscle', 'Muscle']],
            conditions: [['', 'All Conditions'], ['Control', 'Control'], ['Soybean Oil Diet', 'Soybean Oil Diet'], ['Linseed Oil Diet', 'Linseed Oil Diet'], ['Mustard Oil Diet', 'Mustard Oil Diet'], ['Combined Diet', 'Combined Diet']]
        },
        'MB': {
            tissues: [['', 'All Tissues'], ['Brain', 'Brain']],
            conditions: [['', 'All Conditions'], ['Captive', 'Captive'], ['Wild', 'Wild']]
        }
    };

    function updateHeatmapFilters() {
        const dataset = document.getElementById('heatmapDataset').value;
        const filters = datasetFilters[dataset] || datasetFilters['CvsW'];

        const tissueEl = document.getElementById('heatmapTissue');
        const condEl = document.getElementById('heatmapCondition');

        tissueEl.innerHTML = filters.tissues.map(([v, t]) => '<option value="' + v + '">' + t + '</option>').join('');
        condEl.innerHTML = filters.conditions.map(([v, t]) => '<option value="' + v + '">' + t + '</option>').join('');
    }

    document.getElementById('heatmapDataset').addEventListener('change', updateHeatmapFilters);

    async function loadHeatmap() {
        const dataset = document.getElementById('heatmapDataset').value || 'CvsW';
        const tissue = document.getElementById('heatmapTissue').value || null;
        const condition = document.getElementById('heatmapCondition').value || null;
        const limit = parseInt(document.getElementById('heatmapLimit').value) || 50;
        const statusEl = document.getElementById('heatmapStatus');

        try {
            if (statusEl) statusEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading heatmap data...';

            const result = await ExpressionAPI.getHeatmap(dataset, limit, tissue, condition);

            if (!result.genes || result.genes.length === 0) {
                if (statusEl) statusEl.innerHTML = '<i class="fas fa-info-circle"></i> No expression data available. Please run the data import first.';
                return;
            }

            // Sort samples by tissue → condition → sex for grouping
            const sortedIndices = result.samples.map((s, i) => i);
            sortedIndices.sort((a, b) => {
                const ga = result.groups[result.samples[a]];
                const gb = result.groups[result.samples[b]];
                const keyA = (ga.tissue || '') + (ga.condition || '') + (ga.sex || '');
                const keyB = (gb.tissue || '') + (gb.condition || '') + (gb.sex || '');
                return keyA.localeCompare(keyB);
            });

            const sortedSamples = sortedIndices.map(i => result.samples[i]);
            const sortedValues = result.values.map(row => sortedIndices.map(i => row[i]));

            // Build sample labels with group info
            const sampleLabels = sortedSamples.map(s => {
                const g = result.groups[s];
                return s + ' (' + g.tissue + '/' + g.sex + '/' + g.condition + ')';
            });

            // Create annotation trace for tissue grouping
            const tissueColors = sortedSamples.map(s => {
                const g = result.groups[s];
                if (g.tissue === 'Brain' && g.condition === 'Captive') return '#1f77b4';
                if (g.tissue === 'Brain' && g.condition === 'Wild') return '#aec7e8';
                if (g.tissue === 'Gonad' && g.condition === 'Captive') return '#ff7f0e';
                if (g.tissue === 'Gonad' && g.condition === 'Wild') return '#ffbb78';
                return '#999';
            });

            const heatmapTrace = {
                z: sortedValues,
                x: sampleLabels,
                y: result.genes,
                type: 'heatmap',
                colorscale: [
                    [0, '#313695'],
                    [0.25, '#4575b4'],
                    [0.5, '#ffffbf'],
                    [0.75, '#f46d43'],
                    [1, '#a50026']
                ],
                colorbar: {
                    title: 'Log2(Count+1)',
                    titleside: 'right'
                },
                hoverongaps: false,
                hovertemplate: 'Gene: %{y}<br>Sample: %{x}<br>Log2(Count+1): %{z:.2f}<extra></extra>'
            };

            const layout = {
                title: 'Top ' + result.genes.length + ' Most Variable Genes - Expression Heatmap',
                xaxis: {
                    title: 'Samples',
                    tickangle: -45,
                    tickfont: { size: 9 },
                    side: 'bottom'
                },
                yaxis: {
                    title: 'Genes',
                    tickfont: { size: 8 },
                    autorange: 'reversed'
                },
                margin: { l: 120, r: 80, t: 60, b: 200 },
                height: Math.max(500, result.genes.length * 14 + 280)
            };

            Plotly.newPlot('heatmapPlot', [heatmapTrace], layout, {
                responsive: true,
                displayModeBar: true,
                modeBarButtonsToRemove: ['lasso2d', 'select2d']
            });

            if (statusEl) {
                statusEl.innerHTML = '<i class="fas fa-check-circle" style="color: #22c55e;"></i> Showing <strong>' + result.genes.length + '</strong> most variable genes across <strong>' + result.samples.length + '</strong> samples';
            }

        } catch (error) {
            console.error('Heatmap error:', error);
            if (statusEl) statusEl.innerHTML = '<i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i> ' + error.message;
        }
    }

    // Event listener
    document.getElementById('heatmapLoadBtn').addEventListener('click', loadHeatmap);

    // Initial load
    loadHeatmap();
}
