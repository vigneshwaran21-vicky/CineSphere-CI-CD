const BASE_URL = '../backend';

async function apiRegister(name, email, password) {
    try {
        const res = await fetch(`${BASE_URL}/auth.php?action=register`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, email, password })
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message);
        return data;
    } catch (e) {
        return { error: true, message: e.message };
    }
}

async function apiLogin(email, password) {
    try {
        const res = await fetch(`${BASE_URL}/auth.php?action=login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message);
        return data;
    } catch (e) {
        return { error: true, message: e.message };
    }
}

async function apiForgotPassword(email) {
    try {
        const res = await fetch(`${BASE_URL}/auth.php?action=forgot`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email })
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message);
        return data;
    } catch (e) {
        return { error: true, message: e.message };
    }
}

async function apiResetPasswordWithToken(token, newPassword) {
    try {
        const res = await fetch(`${BASE_URL}/auth.php?action=reset_token`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ token, newPassword })
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message);
        return data;
    } catch (e) {
        return { error: true, message: e.message };
    }
}

async function apiGetMovies() {
    try {
        const res = await fetch(`${BASE_URL}/movies.php?action=all`);
        return await res.json();
    } catch { return []; }
}

async function apiSearchMovies(query) {
    try {
        const res = await fetch(`${BASE_URL}/movies.php?action=search&q=${encodeURIComponent(query)}`);
        return await res.json();
    } catch { return []; }
}

async function apiGetMovie(id) {
    try {
        const res = await fetch(`${BASE_URL}/movies.php?action=all&t=${new Date().getTime()}`);
        if (!res.ok) return { error: true, message: `HTTP ${res.status}` };
        const text = await res.text();
        try {
            return JSON.parse(text);
        } catch(e) {
            return { error: true, message: `JSON Error: ${e.message}. Data: ${text.substring(0,50)}` };
        }
    } catch(e) { 
        return { error: true, message: `Fetch Error: ${e.message}` }; 
    }
}

async function apiGetWatchlist(userId) {
    try {
        const res = await fetch(`${BASE_URL}/watchlist.php?action=all&user_id=${userId}`);
        return await res.json();
    } catch { return []; }
}

async function apiAddToWatchlist(userId, movieId) {
    try {
        const res = await fetch(`${BASE_URL}/watchlist.php?action=add`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId, movie_id: movieId })
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message);
        return data;
    } catch (e) {
        return { error: true, message: e.message };
    }
}

async function apiRemoveFromWatchlist(userId, movieId) {
    try {
        const res = await fetch(`${BASE_URL}/watchlist.php?action=remove`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId, movie_id: movieId })
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message);
        return data;
    } catch (e) {
        return { error: true, message: e.message };
    }
}

async function apiGetReviews(movieId) {
    try {
        const res = await fetch(`${BASE_URL}/reviews.php?action=movie&movie_id=${movieId}`);
        return await res.json();
    } catch { return []; }
}

async function apiAddReview(userId, movieId, comment, rating) {
    try {
        const res = await fetch(`${BASE_URL}/reviews.php?action=add`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId, movie_id: movieId, comment, rating })
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message);
        return data;
    } catch (e) {
        return { error: true, message: e.message };
    }
}

async function apiGetUserReviews(userId) {
    try {
        const res = await fetch(`${BASE_URL}/reviews.php?action=user&user_id=${userId}`);
        return await res.json();
    } catch { return []; }
}

async function apiUpdateReview(userId, movieId, comment, rating) {
    try {
        const res = await fetch(`${BASE_URL}/reviews.php?action=update`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId, movie_id: movieId, comment, rating })
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message);
        return data;
    } catch (e) {
        return { error: true, message: e.message };
    }
}
