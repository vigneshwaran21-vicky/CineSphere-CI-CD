/* State Management Logic */
class MovieState {
    constructor() {
        this.movies = this.loadFromStorage('movies') || initialMovies;
        this.reviews = this.loadFromStorage('reviews') || [];
        this.init();
    }

    init() {
        if (!localStorage.getItem('movies')) {
            this.saveToStorage('movies', this.movies);
        }
    }

    // Storage Helpers
    saveToStorage(key, data) {
        localStorage.setItem(key, JSON.stringify(data));
    }

    loadFromStorage(key) {
        const data = localStorage.getItem(key);
        return data ? JSON.parse(data) : null;
    }

    // Movie Actions
    getMovies() {
        return this.movies;
    }

    getTrendingMovies() {
        return this.movies.filter(m => m.isTrending);
    }

    getTopRatedMovies() {
        return [...this.movies].sort((a, b) => b.rating - a.rating).slice(0, 4);
    }

    getMovieById(id) {
        return this.movies.find(m => m.id === id);
    }

    addMovie(movie) {
        this.movies.push({ ...movie, id: Date.now().toString() });
        this.saveToStorage('movies', this.movies);
    }

    updateMovie(id, updatedMovie) {
        this.movies = this.movies.map(m => m.id === id ? { ...updatedMovie, id } : m);
        this.saveToStorage('movies', this.movies);
    }

    deleteMovie(id) {
        this.movies = this.movies.filter(m => m.id !== id);
        this.saveToStorage('movies', this.movies);
    }

    // Review Actions
    addReview(review) {
        this.reviews.push({ ...review, id: Date.now().toString(), date: new Date().toISOString() });
        this.saveToStorage('reviews', this.reviews);
    }

    getReviewsByMovieId(movieId) {
        return this.reviews.filter(r => r.movieId === movieId);
    }
}

// Global state instance
const state = new MovieState();
