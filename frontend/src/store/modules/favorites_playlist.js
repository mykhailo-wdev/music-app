// store/favorites_playlist.js
import backendApi from "@/api/backend";

export default {
    state: () => ({
        tracksByPlaylist: {}, 
        favLoading: false,
        favError: null,
        favMeta: null, 
    }),

    mutations: {
        setFavTracks(state, tracks) {
        state.tracksByPlaylist = { ...state.tracksByPlaylist, favorites: tracks };
        },
        setFavMeta(state, meta) {   
            state.favMeta = meta || null;
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
                commit('setFavMeta', {
                    name: data.data?.name || 'Favorites',
                    updated_at: data.data?.updated_at || null,
                    updated_at_local: data.data?.updated_at_local || null,
                });
            } else {
                commit('setFavError', data.message || 'Error loading favorites');
                commit('setFavTracks', []);
                commit('setFavMeta', null);
            }
            } catch (err) {
                commit('setFavError', err?.response?.data?.message || err.message || 'Server error');
                commit('setFavTracks', []);
                commit('setFavMeta', null);
            } finally {
                commit('setFavLoading', false);
            }
        }
    },

    getters: {
        favoritesTracks: (state) => state.tracksByPlaylist.favorites || [],
        favoritesMeta: (state) => state.favMeta,
        favoritesLoading: (state) => state.favLoading,
        favoritesError: (state) => state.favError,
    }
};
