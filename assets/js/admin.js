/* Admin Logic */
const admin = {
    init() {
        this.renderMovies();
        this.updateStats();
        this.bindEvents();
    },

    bindEvents() {
        const movieForm = document.getElementById('movieForm');
        movieForm.addEventListener('submit', (e) => this.handleFormSubmit(e));
    },

    updateStats() {
        document.getElementById('totalMoviesCount').textContent = state.getMovies().length;
        document.getElementById('totalReviewsCount').textContent = state.reviews.length;
    },

    renderMovies() {
        const movies = state.getMovies();
        const tbody = document.getElementById('adminMovieTableBody');

        tbody.innerHTML = movies.map(movie => `
            <tr>
                <td>
                    <div class="table-movie-info">
                        <img src="${movie.poster}" class="table-movie-poster">
                        <div>
                            <div style="font-weight: 600;">${movie.title}</div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">${movie.director}</div>
                        </div>
                    </div>
                </td>
                <td>${movie.year}</td>
                <td>
                    <span style="color: var(--primary-color); font-weight: 700;">
                        <i class="fas fa-star" style="font-size: 0.8rem;"></i> ${movie.rating}
                    </span>
                </td>
                <td>${movie.genre.join(', ')}</td>
                <td>
                    <div class="action-btns">
                        <button class="btn-icon btn-edit" onclick="admin.editMovie('${movie.id}')" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon btn-delete" onclick="admin.deleteMovie('${movie.id}')" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    },

    openModal(id = null) {
        const modal = document.getElementById('movieModal');
        const title = document.getElementById('modalTitle');
        const form = document.getElementById('movieForm');

        form.reset();
        document.getElementById('movieId').value = '';

        if (id) {
            const movie = state.getMovieById(id);
            if (movie) {
                title.textContent = 'Edit Movie';
                document.getElementById('movieId').value = movie.id;
                document.getElementById('mTitle').value = movie.title;
                document.getElementById('mYear').value = movie.year;
                document.getElementById('mRating').value = movie.rating;
                document.getElementById('mGenre').value = movie.genre.join(', ');
                document.getElementById('mPoster').value = movie.poster;
                document.getElementById('mDescription').value = movie.description;
            }
        } else {
            title.textContent = 'Add Movie';
        }

        modal.style.display = 'flex';
    },

    closeModal() {
        document.getElementById('movieModal').style.display = 'none';
    },

    handleFormSubmit(e) {
        e.preventDefault();

        const id = document.getElementById('movieId').value;
        const movieData = {
            title: document.getElementById('mTitle').value,
            year: parseInt(document.getElementById('mYear').value),
            rating: parseFloat(document.getElementById('mRating').value),
            genre: document.getElementById('mGenre').value.split(',').map(g => g.trim()),
            poster: document.getElementById('mPoster').value,
            description: document.getElementById('mDescription').value,
            director: "Admin Added", // Placeholder
            cast: [], // Placeholder
            duration: "N/A", // Placeholder
            isTrending: false
        };

        if (id) {
            state.updateMovie(id, movieData);
        } else {
            state.addMovie(movieData);
        }

        this.closeModal();
        this.renderMovies();
        this.updateStats();
    },

    deleteMovie(id) {
        if (confirm('Are you sure you want to delete this movie?')) {
            state.deleteMovie(id);
            this.renderMovies();
            this.updateStats();
        }
    },

    editMovie(id) {
        this.openModal(id);
    }
};

// Start admin on load
document.addEventListener('DOMContentLoaded', () => admin.init());
