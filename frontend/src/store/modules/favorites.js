import axios from "axios";

export default {
    state: () => ({
        favorites: []
    }),

    mutations: {
        setFavorites(state, favorites) {
            state.favorites = favorites.map(id => String(id));
        },
        toggleFav(state, trackId) {
            const idStr = String(trackId);
            if (state.favorites.includes(idStr)) {
                state.favorites = state.favorites.filter(id => id !== idStr);
            } else {
                state.favorites.push(idStr);
            }
        }
    },

    actions: {
        async loadFavorites({ commit }) {
            const token = localStorage.getItem("access_token");
            if (!token) return;
            try {
                const res = await axios.get("/favorites.php", {
                    headers: { Authorization: `Bearer ${token}` }
                });
                if (res.data.status === "success") {
                    commit("setFavorites", res.data.favorites || []); 
                } else {
                    console.warn("Failed to load favorites:", res.data.message);
                }
            } catch (err) {
                console.error("Failed to fetch favorites:", err);
            }
        },

        async toggleFavorite({ commit, state }, trackId) {
            const token = localStorage.getItem("access_token");
            if (!token) {
                alert("Token не знайдено");
                return;
            }
            try {
                const idStr = String(trackId);
                const isFav = state.favorites.includes(idStr);
                const method = isFav ? "DELETE" : "POST";

                const res = await axios({
                    url: "/favorites.php",
                    method,
                    headers: { Authorization: `Bearer ${token}`, "Content-Type": "application/json" },
                    data: { track_id: trackId }
                });

                if (res.data.status === "success") {
                    commit("toggleFav", trackId);
                } else {
                    console.warn("Failed to toggle favorite:", res.data.message);
                }
            } catch (err) {
                console.error("Fav error:", err);
            }
        }
    },

    getters: {
        isFavorite: (state) => (trackId) => state.favorites.includes(String(trackId)),
        favorites: (state) => state.favorites
    }
};
