/* Live Movie Details Logic */
document.addEventListener('DOMContentLoaded', async () => {
    const params = new URLSearchParams(window.location.search);
    const movieId = params.get('id');

    if (!movieId) {
        window.location.href = 'index.html';
        return;
    }

    // Fetch live movie using imported api.js routine
    const movie = await apiGetMovie(movieId);
    
    if (movie && !movie.error && !movie.message) {
        document.title = `${movie.title} | CineSphere`;
        document.getElementById('detailTitle').textContent = movie.title;
        document.getElementById('detailPoster').src = movie.image_url;
        document.getElementById('detailYear').textContent = new Date().getFullYear();
        document.getElementById('detailDuration').textContent = "120 min";
        document.getElementById('detailGenre').textContent = movie.genre;
        document.getElementById('detailRating').textContent = movie.rating;
        document.getElementById('detailDescription').textContent = movie.description;
        document.getElementById('detailDirector').textContent = "Studio Ghibli";
        document.getElementById('detailCast').textContent = "Animation feature";

        const backdrop = document.getElementById('movieBackdrop');
        backdrop.style.backgroundImage = `linear-gradient(rgba(18, 18, 18, 0.7), rgba(18, 18, 18, 1)), url('${movie.image_url}')`;
    } else {
        document.title = "Movie Unavailable";
        document.getElementById('detailTitle').textContent = "Movie Unavailable";
        document.getElementById('detailDescription').textContent = "This movie could not be loaded from the live API.";
    }
});
