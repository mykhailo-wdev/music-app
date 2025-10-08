import backendApi from "@/api/backend";

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
            try {
                const { data } = await backendApi.get("/favorites.php");
                if (data.status === "success") {
                    commit("setFavorites", data.favorites || []);
                } else {
                    console.warn("Failed to load favorites:", data.message);
                }
            } catch (err) {
                console.error("Failed to fetch favorites:", err);
            }
        },

        async toggleFavorite({ commit, state }, trackId) {
            try {
                const idStr = String(trackId);
                const isFav = state.favorites.includes(idStr);

                const method = isFav ? "delete" : "post";
                const res = await backendApi({
                    url: "/favorites.php",
                    method,
                    data: { track_id: trackId }
                });

                if (res.data.status === "success") {
                    commit("toggleFav", trackId);
                } else {
                    console.warn(`${isFav ? "Remove" : "Add"} favorite failed:`, res.data.message);
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
