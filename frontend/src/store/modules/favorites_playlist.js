// favorites_playlist.js
import backendApi from "@/api/backend";

export default {
    state: () => ({
        tracksByPlaylist: {}, 
        loading: false,
        error: null,
    }),
    mutations: {
        setTracks(state, tracks) {
            state.tracksByPlaylist = { ...state.tracksByPlaylist, favorites: tracks };
        },
        setLoading(state, val) { state.loading = val; },
        setError(state, val) { state.error = val; },
    },
    actions: {
        async loadFavorites({ commit, rootState }) {
            commit('setLoading', true);
            commit('setError', null);

            try {
                const { data } = await backendApi.get("/api/favorites_playlist.php");

                if (data.status === 'success') {
                    const allTracks = rootState.playlist?.tracksByPlaylist || {};
                    const trackMap = {};
                    Object.values(allTracks).flat().forEach(t => {
                        trackMap[t.id] = t;
                    });
                    const fullTracks = data.data?.tracks.map(t => trackMap[t.id] || t) || [];
                    commit('setTracks', fullTracks);
                } else {
                    commit('setError', data.message || 'Error loading favorites');
                    commit('setTracks', []);
                }
            } catch (err) {
                commit('setError', err?.response?.data?.message || err.message || 'Server error');
                commit('setTracks', []);
            } finally {
                commit('setLoading', false);
            }
        }
    },
    getters: {
        favoritesTracks: (state) => state.tracksByPlaylist.favorites || [],
        favoritesLoading: (state) => state.loading,
        favoritesError: (state) => state.error,
    }
};
