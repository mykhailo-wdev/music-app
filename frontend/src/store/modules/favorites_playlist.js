// store/favorites_playlist.js
import backendApi from "@/api/backend";

export default {
    state: () => ({
        tracksByPlaylist: {}, 
        favLoading: false,
        favError: null,
    }),

    mutations: {
        setFavTracks(state, tracks) {
        state.tracksByPlaylist = { ...state.tracksByPlaylist, favorites: tracks };
        },
        setFavLoading(state, val) { state.favLoading = val; },
        setFavError(state, val) { state.favError = val; },
    },

    actions: {
        async loadFavoritesPlaylist({ commit, rootState }) {
        commit('setFavLoading', true);
        commit('setFavError', null);

        try {
            const { data } = await backendApi.get("/favorites_playlist.php");

            if (data.status === 'success') {
            const allTracks = rootState.playlist?.tracksByPlaylist || {};
            const trackMap = {};
            Object.values(allTracks).flat().forEach(t => { trackMap[t.id] = t; });
            const fullTracks = data.data?.tracks.map(t => trackMap[t.id] || t) || [];
            commit('setFavTracks', fullTracks);
            } else {
            commit('setFavError', data.message || 'Error loading favorites');
            commit('setFavTracks', []);
            }
        } catch (err) {
            commit('setFavError', err?.response?.data?.message || err.message || 'Server error');
            commit('setFavTracks', []);
        } finally {
            commit('setFavLoading', false);
        }
        }
    },

    getters: {
        favoritesTracks: (state) => state.tracksByPlaylist.favorites || [],
        favoritesLoading: (state) => state.favLoading,
        favoritesError: (state) => state.favError,
    }
};
