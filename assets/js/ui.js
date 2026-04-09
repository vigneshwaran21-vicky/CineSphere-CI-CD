/* UI Utility Functions */
const UI = {
    renderMovieCard(movie) {
        const user = JSON.parse(localStorage.getItem('user'));
        const reviewAction = user 
            ? `<a href="movie-details.html?id=${movie.id}" class="btn btn-outline" style="font-size:12px; padding: 6px 14px; margin-top:10px; width:100%; text-align:center; display:block;">Rate & Review</a>` 
            : `<a href="login.html" class="btn btn-outline" style="font-size:12px; padding: 6px 14px; margin-top:10px; width:100%; text-align:center; display:block; opacity:0.8;">Login to Review</a>`;

        return `
            <div class="movie-card">
                <div class="movie-poster-wrapper" onclick="app.showMovieDetails('${movie.id}')" style="cursor:pointer;">
                    <img src="${movie.image_url}" alt="${movie.title}" class="movie-poster">
                    <div class="movie-rating">
                        <i class="fas fa-star text-primary"></i>
                        <span>${movie.rating}/10</span>
                    </div>
                </div>
                <div class="movie-info">
                    <h3 onclick="app.showMovieDetails('${movie.id}')" style="cursor:pointer;" title="${movie.title}">${movie.title.length > 22 ? movie.title.substring(0, 22) + '...' : movie.title}</h3>
                    <div class="movie-meta">
                        <span style="color:var(--text-muted); font-size:13px;">${movie.genre ? movie.genre.split(', ')[0] : 'Feature Film'}</span>
                    </div>
                    ${reviewAction}
                </div>
            </div>
        `;
    },

    renderMovieGrid(containerId, movies) {
        const container = document.getElementById(containerId);
        if (!container) return;

        if (!movies || movies.length === 0) {
            container.innerHTML = '<p class="text-muted" style="width:100%; text-align:center; padding: 40px;">No movies found in Live API database.</p>';
            return;
        }

        container.innerHTML = movies.map(movie => this.renderMovieCard(movie)).join('');
    },

    showToast(message, type = 'info') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        let icon = 'info-circle';
        if (type === 'success') icon = 'check-circle';
        if (type === 'error') icon = 'exclamation-circle';

        toast.innerHTML = `
            <i class="fas fa-${icon}"></i>
            <span>${message}</span>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('fade-out');
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }
};
