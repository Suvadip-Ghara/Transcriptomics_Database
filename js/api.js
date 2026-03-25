/**
 * API Utility Module
 * Handles all AJAX requests to the PHP backend
 */

// API base URL configuration
// Auto-detect base path from current page location
const API_BASE_URL = (function() {
    const path = window.location.pathname;
    const dir = path.substring(0, path.lastIndexOf('/') + 1);
    return window.location.origin + dir + 'api/';
})();

// Loading state management
let activeRequests = 0;

/**
 * Show loading spinner
 */
function showLoading() {
    activeRequests++;
    const spinner = document.getElementById('loading-spinner');
    if (spinner) {
        spinner.style.display = 'flex';
    }
}

/**
 * Hide loading spinner
 */
function hideLoading() {
    activeRequests--;
    if (activeRequests <= 0) {
        activeRequests = 0;
        const spinner = document.getElementById('loading-spinner');
        if (spinner) {
            spinner.style.display = 'none';
        }
    }
}

/**
 * Build URL with query parameters
 * @param {string} endpoint - API endpoint path
 * @param {object} params - Query parameters
 * @returns {string} Complete URL with parameters
 */
function buildURL(endpoint, params = {}) {
    const url = new URL(API_BASE_URL + endpoint);
    
    // Add parameters to URL
    Object.keys(params).forEach(key => {
        if (params[key] !== null && params[key] !== undefined && params[key] !== '') {
            url.searchParams.append(key, params[key]);
        }
    });
    
    return url.toString();
}

/**
 * Make an API request
 * @param {string} endpoint - API endpoint path (e.g., 'deg/filter.php')
 * @param {object} params - Query parameters
 * @param {object} options - Fetch options
 * @returns {Promise} Fetch promise with JSON data
 */
async function apiRequest(endpoint, params = {}, options = {}) {
    showLoading();
    
    const url = buildURL(endpoint, params);
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        },
        signal: AbortSignal.timeout(30000) // 30 second timeout
    };
    
    const fetchOptions = { ...defaultOptions, ...options };
    
    try {
        const response = await fetch(url, fetchOptions);
        
        // Check if response is OK
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || `HTTP error ${response.status}`);
        }
        
        const data = await response.json();
        
        // Check if API returned success flag
        if (data.success === false) {
            throw new Error(data.message || data.error || 'API request failed');
        }
        
        hideLoading();
        return data;
        
    } catch (error) {
        hideLoading();
        
        // Handle different types of errors
        if (error.name === 'AbortError' || error.name === 'TimeoutError') {
            throw new Error('Request timeout - please try again');
        } else if (error.message.includes('Failed to fetch')) {
            throw new Error('Network error - please check your connection');
        } else {
            throw error;
        }
    }
}

/**
 * Show error message to user
 * @param {string} message - Error message
 * @param {HTMLElement} container - Container element to show error in
 */
function showError(message, container = null) {
    console.error('API Error:', message);
    
    const errorHTML = `
        <div class="error-message" style="
            background-color: #fee;
            border: 1px solid #fcc;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            color: #c33;
        ">
            <strong>Error:</strong> ${message}
        </div>
    `;
    
    if (container) {
        container.innerHTML = errorHTML;
    } else {
        // Show in default location or alert
        const mainContent = document.querySelector('.main-content') || document.querySelector('main');
        if (mainContent) {
            const errorDiv = document.createElement('div');
            errorDiv.innerHTML = errorHTML;
            mainContent.insertBefore(errorDiv, mainContent.firstChild);
        } else {
            alert('Error: ' + message);
        }
    }
}

/**
 * DEG-specific API calls
 */
const DEGAPI = {
    /**
     * Filter DEG results
     */
    filter: async (tissue, condition, minLogFC, sortBy = 'p_value', sortOrder = 'asc', page = 1, limit = 100) => {
        return await apiRequest('deg/filter.php', {
            tissue,
            condition,
            minLogFC,
            sortBy,
            sortOrder,
            page,
            limit
        });
    },
    
    /**
     * Get DEG statistics
     */
    getStats: async () => {
        return await apiRequest('deg/stats.php');
    },
    
    /**
     * Get gene details
     */
    getGene: async (geneId) => {
        return await apiRequest('deg/gene.php', { id: geneId });
    }
};

/**
 * Functional annotation API calls
 */
const FunctionalAPI = {
    /**
     * Search functional annotations
     */
    search: async (query, sortBy = 'gene_id', sortOrder = 'asc', page = 1, limit = 100) => {
        return await apiRequest('functional/search.php', {
            query,
            sortBy,
            sortOrder,
            page,
            limit
        });
    }
};

/**
 * Pathway API calls
 */
const PathwayAPI = {
    /**
     * List pathways
     */
    list: async (search = null, sortBy = 'gene_count', sortOrder = 'desc', limit = null) => {
        return await apiRequest('pathways/list.php', {
            search,
            sortBy,
            sortOrder,
            limit
        });
    },
    
    /**
     * Get pathway details
     */
    getDetail: async (pathwayId) => {
        return await apiRequest('pathways/detail.php', { id: pathwayId });
    },
    
    /**
     * Get pathway statistics
     */
    getStats: async () => {
        return await apiRequest('pathways/stats.php');
    }
};

/**
 * Chart data API calls
 */
const ChartAPI = {
    /**
     * Get volcano plot data
     */
    getVolcano: async (tissue = null, condition = null, limit = null) => {
        return await apiRequest('charts/volcano.php', {
            tissue,
            condition,
            limit
        });
    },
    
    /**
     * Get DEG distribution
     */
    getDEGDistribution: async (tissue = null, condition = null) => {
        return await apiRequest('charts/deg_distribution.php', {
            tissue,
            condition
        });
    },
    
    /**
     * Get top genes
     */
    getTopGenes: async (tissue = null, condition = null, limit = 10) => {
        return await apiRequest('charts/top_genes.php', {
            tissue,
            condition,
            limit
        });
    }
};

/**
 * Dataset API calls
 */
const DatasetAPI = {
    /**
     * List all datasets
     */
    list: async () => {
        return await apiRequest('datasets/list.php');
    },
    
    /**
     * Get dataset details
     */
    getDetail: async (datasetId) => {
        return await apiRequest('datasets/detail.php', { id: datasetId });
    }
};

/**
 * Expression data API calls
 */
const ExpressionAPI = {
    /**
     * Get heatmap data (top variable genes)
     */
    getHeatmap: async (dataset = 'CvsW', limit = 50, tissue = null, conditionGroup = null) => {
        return await apiRequest('expression/heatmap.php', {
            dataset,
            limit,
            tissue,
            condition_group: conditionGroup
        });
    },

    /**
     * Get expression data for a single gene
     */
    getGene: async (geneId, dataset = 'CvsW') => {
        return await apiRequest('expression/gene.php', {
            id: geneId,
            dataset
        });
    }
};

/**
 * Statistics API calls (read stats, assembly stats)
 */
const StatsAPI = {
    getReadStats: async (datasetSource = null) => {
        const params = {};
        if (datasetSource) params.dataset_source = datasetSource;
        return await apiRequest('stats/read_stats.php', params);
    },

    getAssemblyStats: async () => {
        return await apiRequest('stats/assembly_stats.php', {});
    }
};

/**
 * Debounce helper function
 * Delays function execution until after wait milliseconds have elapsed
 * since the last time it was invoked
 */
function debounce(func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
