/* Main Application Logic */
const app = {
    async init() {
        this.bindEvents();
        await this.renderHomePage();
    },

    bindEvents() {
        const searchInput = document.getElementById('movieSearch');
        if (searchInput) {
            let timeout = null;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => this.handleSearch(e), 500);
            });
        }
    },

    async renderHomePage() {
        const featuredGrid = document.getElementById('featuredMovies');
        const topRatedGrid = document.getElementById('topRatedMovies');
        
        if(!featuredGrid) return; // not on homepage

        featuredGrid.innerHTML = '<div style="margin:50px auto; border:4px solid rgba(255,255,255,0.1); border-top:4px solid var(--primary-color); border-radius:50%; width:40px; height:40px; animation:spin 1s linear infinite;"></div>';
        
        try {
            const movies = await apiGetMovies();
            if(!movies.error && movies.length > 0) {
                UI.renderMovieGrid('featuredMovies', movies.slice(0, 12));
                UI.renderMovieGrid('topRatedMovies', movies.slice(12, 24));
            } else {
                featuredGrid.innerHTML = '<p style="text-align:center;width:100%;">Failed to connect to Live API. Please check your connection.</p>';
            }
        } catch(e) {
            featuredGrid.innerHTML = '<p style="text-align:center;width:100%;">Live Data Server Error.</p>';
        }
    },

    async handleSearch(e) {
        const query = e.target.value.toLowerCase().trim();
        const featuredTitle = document.querySelector('#movies .section-title');
        const grid = document.getElementById('featuredMovies');
        const topRatedSection = document.getElementById('top-rated');
        
        if (query) {
            featuredTitle.textContent = `Live Search Results for "${query}"`;
            grid.innerHTML = '<div style="margin:50px auto; border:4px solid rgba(255,255,255,0.1); border-top:4px solid var(--primary-color); border-radius:50%; width:40px; height:40px; animation:spin 1s linear infinite;"></div>';
            
            try {
                const movies = await apiSearchMovies(query);
                UI.renderMovieGrid('featuredMovies', movies);
                if(topRatedSection) topRatedSection.style.display = 'none';
            } catch(e) {
                grid.innerHTML = '<p style="text-align:center;width:100%;">Search failed across the Live API.</p>';
            }
        } else {
            featuredTitle.textContent = 'Trending Real-Time';
            this.renderHomePage();
            if(topRatedSection) topRatedSection.style.display = 'block';
        }
    },

    showMovieDetails(id) {
        window.location.href = `movie-details.html?id=${id}`;
    }
};

document.addEventListener('DOMContentLoaded', () => app.init());
